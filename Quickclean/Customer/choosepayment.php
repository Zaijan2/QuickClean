<?php
session_start();

// --- DEBUGGING ON ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- CHECK LOGIN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- DATABASE CONNECTION ---
$conn = new mysqli("localhost", "root", "", "quickclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- GET BOOKING INFO ---
if (!isset($_SESSION['booking_id'])) {
    die("Error: No booking found.");
}
$booking_id = $_SESSION['booking_id'];
$user_id = $_SESSION['user_id'];

// Fetch booking price
$stmt = $conn->prepare("SELECT price, service_name FROM bookings WHERE booking_id=?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Booking ID not found in database.");
}

$booking = $result->fetch_assoc();
$stmt->close();

// --- CALCULATE DOWNPAYMENT (50%) ---
$full_price = $booking['price'];
$downpayment = $full_price * 0.5;

// --- HANDLE SELECTION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // OPTION 1: ONLINE PAYMENT (Full Payment)
    if (isset($_POST['pay_online'])) {
        // Set session variable so payment.php knows it's full online payment
        $_SESSION['payment_type'] = 'full'; 
        $_SESSION['payment_method_choice'] = 'online';

        header("Location: payment.php");
        exit();
    }

    // OPTION 2: CASH PAYMENT (Down Payment via QR/Bank)
    if (isset($_POST['pay_cash'])) {
        
        // Set session variables so payment.php knows this is a downpayment for cash
        $_SESSION['payment_type'] = 'downpayment';
        $_SESSION['payment_method_choice'] = 'cash';
        $_SESSION['amount_to_pay'] = $downpayment; // Pass the 50% amount

        // *** CHANGED: Redirect to payment.php for the downpayment ***
        header("Location: payment.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Choose Payment Method - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "Poppins", sans-serif; background: #F0F4F5; padding: 40px 20px; }
    
    .container { max-width: 800px; margin: 0 auto; text-align: center; }

    h1 { color: #2E89F0; margin-bottom: 10px; }
    p.subtitle { color: #666; margin-bottom: 40px; font-size: 18px; }

    .payment-options { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }

    .card {
        background: white; width: 300px; padding: 40px 20px;
        border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: pointer; position: relative; border: 2px solid transparent;
    }
    .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(46, 137, 240, 0.2); border-color: #2E89F0; }

    .icon-box { font-size: 50px; color: #2E89F0; margin-bottom: 20px; }
    h3 { margin-bottom: 10px; color: #333; }
    p.desc { color: #777; font-size: 14px; margin-bottom: 25px; }

    .submit-btn {
        background: #2E89F0; color: white; border: none; padding: 12px 30px;
        border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer;
        width: 100%; transition: background 0.3s;
    }
    .submit-btn:hover { background: #1c6ed6; }
    .submit-btn.cash { background: #28a745; }
    .submit-btn.cash:hover { background: #218838; }

    /* --- MODAL STYLES --- */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); z-index: 1000;
        align-items: center; justify-content: center;
    }
    .modal-box {
        background: white; width: 90%; max-width: 450px;
        padding: 30px; border-radius: 12px; text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        animation: fadeIn 0.3s;
    }
    .modal-box h2 { color: #e67e22; margin-bottom: 15px; font-size: 22px; }
    .modal-box p { color: #555; font-size: 15px; margin-bottom: 20px; line-height: 1.6; }
    .highlight-price { font-weight: bold; color: #333; font-size: 18px; }
    
    .modal-actions { display: flex; gap: 10px; justify-content: center; }
    .btn-cancel {
        background: #ccc; color: #333; padding: 10px 20px; border-radius: 6px;
        text-decoration: none; font-weight: 600; cursor: pointer; border: none;
    }
    .btn-confirm {
        background: #28a745; color: white; padding: 10px 20px; border-radius: 6px;
        font-weight: 600; cursor: pointer; border: none;
    }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>

<div class="container">
    <h1>Payment Method</h1>
    <p class="subtitle">How would you like to pay for your <strong><?php echo htmlspecialchars($booking['service_name']); ?></strong>?</p>

    <form method="POST" id="paymentForm" class="payment-options">
        
        <div class="card">
            <div class="icon-box"><i class="fas fa-qrcode"></i></div>
            <h3>Online Payment</h3>
            <p class="desc">Pay now securely using GCash or QR Code.</p>
            <button type="submit" name="pay_online" class="submit-btn">Pay Online</button>
        </div>

        <div class="card">
            <div class="icon-box"><i class="fas fa-hand-holding-usd"></i></div>
            <h3>Cash Payment</h3>
            <p class="desc">Pay in person when the cleaner arrives.</p>
            <button type="button" class="submit-btn cash" onclick="openCashModal()">Pay Cash</button>
        </div>

        <input type="hidden" name="pay_cash" id="cashInput" disabled>

    </form>
</div>

<div class="modal-overlay" id="cashModal">
    <div class="modal-box">
        <div class="icon-box" style="font-size: 40px; color: #e67e22; margin-bottom: 10px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Down Payment Required</h2>
        <p>To secure your booking with Cash Payment, a <strong>50% down payment</strong> is required.</p>
        
        <p>Total Price: ₱<?php echo number_format($full_price, 2); ?><br>
        <span class="highlight-price">You need to pay now: ₱<?php echo number_format($downpayment, 2); ?></span></p>

        <p style="font-size: 13px; color: #777;">You will be redirected to the payment page to settle the down payment. The remaining balance will be collected after the service.</p>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeCashModal()">Cancel</button>
            <button class="btn-confirm" onclick="confirmCashPayment()">I Understand, Proceed</button>
        </div>
    </div>
</div>

<script>
    function openCashModal() { document.getElementById('cashModal').style.display = 'flex'; }
    function closeCashModal() { document.getElementById('cashModal').style.display = 'none'; }
    function confirmCashPayment() {
        document.getElementById('cashInput').disabled = false;
        document.getElementById('paymentForm').submit();
    }
</script>

</body>
</html>
<?php $conn->close(); ?>