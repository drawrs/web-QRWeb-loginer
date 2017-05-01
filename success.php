<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Success Login</title>
    <style>
        .wrapd {
            width: 600px;
            margin: 0 auto;
            padding-top: 15%;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="wrapd">
    <div class="center">
            Welcome,  <b><?= $_SESSION['email']; ?></b>
        <h1>You are logged now!</h1>
    </div>
</div>
</body>
</html>