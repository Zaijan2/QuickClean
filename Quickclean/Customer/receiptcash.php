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
        header("Location: admin-dashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// --- DB CONNECTION ---
$conn = new mysqli("localhost", "root", "", "quickclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- VALIDATION ---
if (!isset($_SESSION['booking_id'])) {
    die("Error: No booking found.");
}
$booking_id = $_SESSION['booking_id'];

// 1. Fetch Booking Details (Total Price)
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id=?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch Payment Details (The Down Payment they just paid)
$stmt = $conn->prepare("SELECT * FROM payments WHERE booking_id=?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- CALCULATE BALANCE ---
$total_price = $booking['price'];
$amount_paid = $payment['amount']; // This is the down payment
$balance_due = $total_price - $amount_paid; // Remaining balance

// --- HANDLE DONE BUTTON ---
if (isset($_POST['done'])) {
    // Redirect to the Cash Thank You page
    header("Location: thankyoucod.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Down Payment Receipt - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    body { font-family: "Poppins", sans-serif; background: #F0F4F5; padding: 40px 20px; }
    
    .receipt-box {
        background: white; max-width: 600px; margin: auto;
        padding: 30px; border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    h2 { text-align: center; color: #2E89F0; margin-bottom: 5px; }
    p.sub-header { text-align: center; color: #666; font-size: 14px; margin-bottom: 25px; }

    .info-group { margin-bottom: 15px; }
    .info-group strong { display: inline-block; width: 120px; color: #333; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; }
    table, th, td { border: 1px solid #eee; }
    th { background: #f8f9fa; padding: 12px; text-align: left; color: #555; }
    td { padding: 12px; color: #333; }
    
    .financials { margin-top: 20px; border-top: 2px dashed #ddd; padding-top: 20px; }
    .row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; }
    .row.total { font-weight: 600; font-size: 18px; color: #333; }
    .row.paid { color: #28a745; }
    .row.balance { color: #e67e22; font-weight: 600; font-size: 18px; border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px; }

    .btn {
        background: #2E89F0; color: white; padding: 12px 25px;
        border: none; border-radius: 8px; cursor: pointer;
        font-size: 16px; font-weight: 600; display: block;
        width: 100%; text-align: center; margin-top: 30px;
    }
    .btn:hover { background: #1c6ed6; }
</style>
</head>
<body>

<div class="receipt-box">
    <h2>Down Payment Receipt</h2>
    <p class="sub-header">Transaction ID: <?php echo htmlspecialchars($payment['transaction_id']); ?></p>

    <div class="info-group">
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['name']); ?></p>
        <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
        <p><strong>Date/Time:</strong> <?php echo $booking['date']; ?> at <?php echo $booking['time']; ?></p>
    </div>

    <div class="financials">
        <div class="row">
            <span>Total Service Price:</span>
            <span>₱<?php echo number_format($total_price, 2); ?></span>
        </div>
        
        <div class="row paid">
            <span>Down Payment (Paid Now):</span>
            <span>- ₱<?php echo number_format($amount_paid, 2); ?></span>
        </div>
        
        <div class="row balance">
            <span>Balance Due (Cash):</span>
            <span>₱<?php echo number_format($balance_due, 2); ?></span>
        </div>
    </div>
    
    <p style="text-align: center; font-size: 13px; color: #777; margin-top: 20px;">
        Please pay the <strong>Balance Due</strong> directly to the cleaner after the service.
    </p>

    <form method="POST">
        <button type="submit" name="done" class="btn">Done</button>
    </form>
</div>

</body>
</html>
<?php $conn->close(); ?>