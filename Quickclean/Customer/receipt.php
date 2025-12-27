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
$conn = new mysqli("localhost","root","","quickclean");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Get booking_id from session
if(!isset($_SESSION['booking_id'])) die("Booking not found.");
$booking_id = $_SESSION['booking_id'];

// Fetch booking
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id=?");
$stmt->bind_param("i",$booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch payment
$stmt = $conn->prepare("SELECT * FROM payments WHERE booking_id=?");
$stmt->bind_param("i",$booking_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- UPDATED REDIRECT LOGIC ---
if(isset($_POST['done'])){
    // Check payment method to decide which Thank You page to show
    if ($payment['payment_method'] === 'cash') {
        header("Location: thankyoucod.php");
    } else {
        header("Location: thankyou.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt</title>
<style>
body{font-family:"Poppins",sans-serif;background:#F0F4F5;padding:40px;}
.receipt-box{background:white;max-width:600px;margin:auto;padding:30px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
h2{text-align:center;color:#2E89F0;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
table,th,td{border:1px solid #ddd;padding:10px;}
th{background:#f7f7f7;text-align:left;}
.total{text-align:right;font-weight:bold;padding-top:10px;}
.btn{background:#2E89F0;color:white;padding:12px 25px;border:none;border-radius:8px;cursor:pointer;font-size:16px;font-weight:600;display:block;width:100%;text-align:center;text-decoration:none;}

/* Optional: Add color for status */
.status-paid { color: #28a745; font-weight: bold; }
.status-pending { color: #f59e0b; font-weight: bold; }
</style>
</head>
<body>
<div class="receipt-box">
<h2>Payment Receipt</h2>
<p><strong>Name:</strong> <?php echo htmlspecialchars($booking['name']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
<p><strong>Booking Date:</strong> <?php echo $booking['date'];?> at <?php echo $booking['time'];?></p>

<table>
<tr><th>Service Name</th><th>Extras</th><th>Price</th></tr>
<tr>
<td><?php echo htmlspecialchars($booking['service_name']);?></td>
<td><?php echo htmlspecialchars($booking['extras'] ?? 'None');?></td>
<td>₱<?php echo number_format($booking['price'],2);?></td>
</tr>
</table>

<p class="total">Total Amount: ₱<?php echo number_format($payment['amount'],2);?></p>
<p><strong>Payment Method:</strong> <?php echo ucfirst($payment['payment_method']);?></p>

<p><strong>Payment Status:</strong> 
    <?php 
        $status = ucfirst($payment['payment_status']);
        $colorClass = ($payment['payment_status'] == 'paid') ? 'status-paid' : 'status-pending';
        echo "<span class='$colorClass'>$status</span>";
    ?>
</p>

<p><strong>Transaction ID:</strong> <?php echo $payment['transaction_id'];?></p>

<form method="POST">
<button type="submit" name="done" class="btn">Done</button>
</form>
</div>
</body>
</html>