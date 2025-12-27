<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Thank You</title>

<style>
    body {
        font-family: "Poppins", sans-serif;
        background: #F0F4F5;
        text-align: center;
        padding: 50px;
    }
    .box {
        background: white;
        max-width: 450px;
        margin: auto;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }
    h2 {
        color: #2E89F0;
        font-size: 28px;
    }
    p {
        font-size: 18px;
        margin-top: 10px;
    }
    .btn {
        display: inline-block;
        margin-top: 20px;
        background: #2E89F0;
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
    }
</style>

</head>
<body>

<div class="box">
    <h2>Thank You!</h2>
    <p>Your payment has been recorded successfully.</p>
    <p>We appreciate your business.</p>

    <a href="customer-home.php" class="btn">Back to Home</a>
</div>

</body>
</html>
