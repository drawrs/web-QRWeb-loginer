<?php
require './config/db.php';
session_start();
// get type value from url
$type = (isset($_GET['type']) ? $_GET['type'] : "");

switch ($type) {
    case 'login':
        // bentuk url http://821bd8ed.ngrok.io/qrloginer/api.php?type=login
        // cegah karakter html & menghapus spasi
        $email = trim(strip_tags($_POST['email']));
        $pwd = trim(strip_tags($_POST['password']));
        // buat sql login
        $sql_log = "SELECT*FROM users WHERE email = '" . $email . "' AND password = '" . $pwd . "'";
        $log_query = $conn->query($sql_log);
        if ($log_query->num_rows > 0) { // hitung baris
            // set status login true
            $log_status = 'true';
            $log_msg = "Login berhasil";
            //$update = $conn->query("UPDATE sessions SET session_status = '" . $sc_st . "' WHERE session_id = '" . $session_id . "'");
        } else {
            $log_status = 'false';
            $log_msg = "Login gagal ! periksa kembali email dan password Anda!";
        }
        // array untuk di convert ke JSON
        $result = array('status' => $log_status, 'msg' => $log_msg);
        echo json_encode($result);
        break;
    case 'weblogin':
        // bentuk url http://821bd8ed.ngrok.io/qrloginer/api.php?type=weblogin&_SESSION=sesilogin
        // dapatkan sesssion dari url
        $session_id = $_GET['_SESSION'];
        $data = $session_id;
        /*$sql = "INSERT INTO logs (data) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s",$data);
        $stmt->execute();*/

        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = $_POST['email'];
            $pwd = $_POST['password'];
        } else {
            $email = "";
            $pwd = "";
        }

        $sc_st = 0;
        $sql = "SELECT*FROM sessions WHERE session_id ='" . $session_id . "' ";
        $query = $conn->query($sql);


        if ($query->num_rows > 0) {
            $session_status = "true";
            $session_msg = "sesi ditemukan";

            $sql_log = "SELECT*FROM users WHERE email = '" . $email . "' AND password = '" . $pwd . "'";
            $log_query = $conn->query($sql_log);
            if ($log_query->num_rows > 0) {
                $log_status = 'true';
                $log_msg = "Login berhasil";
                // ambil user_id buat dimasukin ke session (user_id)
                $user_id = $log_query->fetch_assoc()['user_id'];
                // update buat masukin user_id
                $update = $conn->query("UPDATE sessions SET session_status = '" . $sc_st . "', user_id = '".$user_id."' WHERE session_id = '" . $session_id . "'");
                
            } else {
                $log_status = 'false';
                $log_msg = "Login gagal";
            }
        } else {
            $session_status = "true";
            $session_msg = "sesi tidak ditemukan";
            $log_status = 'false';
            $log_msg = "Login gagal";
        }
        // array untuk response
        $result = array('session_msg' =>  $session_msg,
                        'session_status' => $session_status,
                        'log_status' => $log_status,
                        'log_msg' => $log_msg);

        echo json_encode($result);
        break;
    case 'cek_session':
        // bentuk url http://821bd8ed.ngrok.io/qrloginer/api.php?type=ceksession&ssid=ssid
        // session id
        $ssid = $_GET['ssid'];
        $sql = "SELECT*FROM sessions WHERE session_id ='$ssid'";
        $query = $conn->query($sql);
        $get = $query->fetch_assoc();
        // masukan kedalam variable
        $ss_status = $get['session_status'];
        $user_id = $get['user_id'];
        // kalo user_id nya tidal null (berarti udah ada yang sukses login)
        if ($user_id !== NULL) {
            $sql_u = "SELECT email FROM users WHERE user_id = '" . $user_id . "'";
            $user = $conn->query($sql_u)->fetch_assoc();
            $email = $user['email'];
            // masukan kedalam session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
        }
        $result = array('status' => $ss_status );
        echo json_encode($result);
        break;
    case 'get_qrcode':
        include "./lib/phpqrcode/qrlib.php";
        // url untuk weblogin
        $url = "http://" . $_SERVER['HTTP_HOST'] . "/qrloginer/api.php?type=weblogin";
        // tempat gamar temporary disimpan
        $tmp_dir = 'lib'.DIRECTORY_SEPARATOR.'phpqrcode'.DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR;
        // prefix untuk session
        $prefix = "X00";
        // buat keynya dari random jam 
        $d_key = date("D-m-y H:i:s.u") . "" . rand();
        // tambah prefix terus bungkus key -> md5
        $fix_key = $prefix . "" . md5($d_key);
        // potong hasil md5 agar pendek = 10 karakter
        $_session = substr($fix_key, 0, 10);
        // siapin nama file temporaray
        $filename = $tmp_dir . "" . $_session . ".png";
        // buat gambar QR Code
        QRcode::png($url . "&_SESSION=" . $_session, $filename, 'L', 8, 1);
        $qr_location = $url . "&_SESSION=" . $_session;
        $status = "true";
        // url tempat gambar disimpan
        $img = "http://". $_SERVER['HTTP_HOST']."" .DIRECTORY_SEPARATOR . "qrloginer" .DIRECTORY_SEPARATOR.$filename;
        // insert to db
        $sql = "INSERT INTO sessions (session_id, session_status)  VALUES ('".$_session."', '1')";
        $stmt = $conn->prepare($sql);
        //$stmt->bind_param("ss", $_session, "1");
        $stmt->execute();
        echo json_encode(array('status' => $status, 'img' => $img, 'ssid' => $_session, 'url' => $url));
        break;
    default:
        echo json_encode(array('status' => 'error not found'));
        break;
}