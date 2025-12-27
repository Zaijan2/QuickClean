<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only allow customers
if ($_SESSION['role'] !== 'customer') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admindashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

$conn = new mysqli("localhost", "root", "", "quickclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get booking_id from session
if(!isset($_SESSION['booking_id'])) die("Booking not found.");
$booking_id = $_SESSION['booking_id'];
$user_id = $_SESSION['user_id']; 

// Fetch booking details (for fallback price)
$stmtBooking = $conn->prepare("SELECT price FROM bookings WHERE booking_id=?");
$stmtBooking->bind_param("i",$booking_id);
$stmtBooking->execute();
$booking = $stmtBooking->get_result()->fetch_assoc();
$stmtBooking->close();

// --- DETERMINE AMOUNT TO PAY ---
// Check if a specific amount was passed from choosepayment.php (e.g. 50% down payment)
if (isset($_SESSION['amount_to_pay'])) {
    $amount_to_pay = $_SESSION['amount_to_pay'];
} else {
    // Default to full price if not set
    $amount_to_pay = $booking['price'];
}

// --- HANDLE "DONE" BUTTON ---
if(isset($_POST['done'])){
    
    // Check if payment already exists to prevent duplicates
    $stmtCheck = $conn->prepare("SELECT * FROM payments WHERE booking_id=?");
    $stmtCheck->bind_param("i",$booking_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if($resultCheck->num_rows == 0){
        $payment_method = 'qrcode'; // Even for cash downpayment, this step is via QR
        $payment_status = 'paid'; // The downpayment itself is "paid"
        $transaction_id = 'TXN'.time();
        
        $stmtInsert = $conn->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
        // Use $amount_to_pay here
        $stmtInsert->bind_param("iidsss", $booking_id, $user_id, $amount_to_pay, $payment_method, $payment_status, $transaction_id);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
    $stmtCheck->close();
    
    // --- REDIRECT LOGIC ---
    // If this was a down payment for Cash, go to the Cash Receipt
    if (isset($_SESSION['payment_type']) && $_SESSION['payment_type'] == 'downpayment') {
        // Clear the specific session vars to clean up
        unset($_SESSION['amount_to_pay']);
        unset($_SESSION['payment_type']);
        
        header("Location: receiptcash.php");
    } else {
        // Normal full payment
        header("Location: receipt.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>QR Code Payment</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    body { font-family:"Poppins",sans-serif; background:#F0F4F5; text-align:center; padding:40px; }
    .box { background:white; max-width:450px; margin:auto; padding:30px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    img.qr { width:240px; margin-top:15px; border:8px solid #eaeaea; border-radius:10px; }
    h2 { color:#2E89F0; margin-bottom: 5px; }
    .amount-display { font-size: 24px; font-weight: 600; color: #333; margin: 15px 0; }
    .label { font-size: 14px; color: #777; }
    
    .btn { background:#2E89F0; color:white; padding:12px 25px; border:none; border-radius:8px; cursor:pointer; font-size:16px; font-weight:600; margin-top:20px; width: 100%; }
    .btn:hover { background: #1c6ed6; }
</style>
</head>
<body>

<div class="box">
    <h2>Scan to Pay</h2>
    
    <p class="label">Total Amount Due Now:</p>
    <div class="amount-display">₱<?php echo number_format($amount_to_pay, 2); ?></div>

    <img src="qr-success.jpeg" class="qr" alt="QR Code">
    
    <p style="margin-top: 20px; font-size: 14px; color: #555;">After completing the payment via your banking app, please click the button below.</p>
    
    <form method="POST">
        <button type="submit" name="done" class="btn">I Have Sent the Payment</button>
    </form>
</div>

</body>
</html>