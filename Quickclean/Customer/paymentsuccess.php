<?php
session_start();

// OPTIONAL: If payment requires authentication
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Successful</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
    body {
        margin: 0;
        padding: 0;
        font-family: "Poppins", sans-serif;
        background: #f4f6f8;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .success-box {
        background: white;
        width: 95%;
        max-width: 480px;
        text-align: center;
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 5px 16px rgba(0,0,0,0.12);
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .checkmark {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #4BB543;
        display:flex;
        justify-content:center;
        align-items:center;
        margin: 0 auto 20px;
        animation: pop 0.4s ease-out;
    }

    @keyframes pop {
        0% { transform: scale(0.5); }
        100% { transform: scale(1); }
    }

    .checkmark svg {
        width: 50px;
        fill: white;
    }

    h2 {
        color: #2E89F0;
        font-size: 26px;
        margin-bottom: 10px;
        font-weight: 600;
    }

    p {
        color: #555;
        font-size: 16px;
        margin: 10px 0 20px;
    }

    .btn {
        display: inline-block;
        background: #2E89F0;
        padding: 12px 25px;
        color: #fff;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: 0.2s ease;
    }

    .btn:hover {
        background: #1f6ec0;
    }
</style>
</head>
<body>

<div class="success-box">

    <div class="checkmark">
        <svg viewBox="0 0 24 24">
            <path d="M20.285 6.708l-11.4 11.4-5.657-5.657 1.414-1.414 4.243 4.243 9.986-9.986z"/>
        </svg>
    </div>

    <h2>Payment Successful!</h2>
    <p>Your payment has been processed successfully.  
       Thank you for using QuickClean!</p>

</div>

</body>
</html>
