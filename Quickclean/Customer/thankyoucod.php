<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Booking Confirmed</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body {
        font-family: "Poppins", sans-serif;
        background: #F0F4F5;
        text-align: center;
        padding: 50px;
    }
    .box {
        background: white;
        max-width: 500px;
        margin: auto;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }
    .icon-box {
        font-size: 50px;
        color: #28a745; /* Green for success */
        margin-bottom: 20px;
    }
    h2 {
        color: #2E89F0;
        font-size: 28px;
        margin-bottom: 15px;
    }
    p {
        font-size: 16px;
        color: #555;
        margin-bottom: 10px;
        line-height: 1.6;
    }
    .notice-box {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        font-size: 14px;
        font-weight: 600;
    }
    .btn {
        display: inline-block;
        margin-top: 20px;
        background: #2E89F0;
        color: #fff;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.3s;
    }
    .btn:hover {
        background: #1c6ed6;
    }
</style>

</head>
<body>

<div class="box">
    <div class="icon-box">
        <i class="fas fa-check-circle"></i>
    </div>
    
    <h2>Booking Confirmed!</h2>
    <p>Your service request has been successfully received.</p>
    
    <div class="notice-box">
        <i class="fas fa-hand-holding-usd"></i> 
        Please prepare the exact amount in cash. Payment is to be given directly to the cleaner upon arrival or after the service is finished.
    </div>

    <p>We appreciate your business and look forward to serving you!</p>

    <a href="customer-home.php" class="btn">Back to Home</a>
</div>

</body>
</html>