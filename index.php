<!DOCTYPE html>
<html>
<head>
    <title>QR Loginer Demo</title>
    <style>
        body {
            padding-top: 8%;
        }
        .wrap {
            width: 600px;
            margin: 0 auto;
            text-align:center;
        }
        button.btn {
            padding: 10px;
        }
        #panel {
            padding: 10px 0;
        }
        #text_display {

        }
        #qr_display {
            display: none;
        }
        #loader {
            display: none;
        }
        #text_info {
            display: none;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div id="text_display">
        <p>Click button bellow to get your QR Code!</p>
    </div>
    <div id="text_info"></div>
    <div id="qr_display">
        <img src="" id="qr_code">
    </div>
    <div id="loader">
        <img src="./img/ring.svg" alt="" width="100">
    </div>
    <div id="panel">
        <button class="btn btn-get-qr" id="getQR">Get QR Code</button>
    </div>
</div>
<script src= "./js/jquery-3.2.1.min.js"></script>
<script>
var ssid = null;
var loginStatus = null;
    $(document).ready(function() {
        $('#getQR').click(function(event) {
            /* Act on the event */
            // hide qrcode display place
            $('#qr_display').hide();
            // showing loader
            $('#loader').fadeIn('slow', function(){
                // send ajax data
                $.ajax({
                    url: 'api.php?type=get_qrcode',
                    type: 'GET',
                    success: function(data){
                        //console.log(data);
                        var obj = jQuery.parseJSON(data);
                        // output json
                        //"status":"true","img":"http:\/\/localhost\/belajar_api\/qrloginer\/phpqrcode\/temp\/X00b83a1f2.png"}
                        var status  = obj.status;
                        var img  = obj.img;
                        //console.log( status + " - " + img);
                        // insert to img value
                        $('#qr_code').prop({src: img});
                        console.log(obj.url);
                        // hide loader
                        $('#loader').fadeOut('1', function(){
                            // fill ssid var
                            ssid = obj.ssid;
                            $('#qr_display').fadeIn();
                            $('#text_info').show();
                        });
                        
                    }
                });
            });
            /*$('#text_display').hide('400', function() {
                
            });*/
        });
    });
</script>
<script>
    $(function update(){
        var timer;
         $.ajax({
            url: 'api.php?type=cek_session&ssid=' + ssid,
            type: 'GET',
            data: {param1: 'value1'},
            success: function(data){
                console.log(data);
                console.log(ssid);
                var rs = jQuery.parseJSON(data);
                loginStatus = rs.status;
            }
        }).then(function() {   
            console.log(loginStatus);
                // on completion, restart
            if (loginStatus == 0) {
                    $('#qr_display').hide();
                    $('#getQR').hide();
                    $('#text_info').text('Login successfully! Please wait...');
                    window.location.href = 'success.php';
                    /*setTimeout(function(){
                    }, 1000);*/
                    //clearTimeout(timer);
                    console.log('Logged');
                } else {
                    timer = setTimeout(update, 1500);  // function refers to itself
                    //$('#text_info').text('Session not found!');
                }
        });
    })
</script>
</body>
</html>