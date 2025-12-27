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

// Database connection
$conn = new mysqli("localhost", "root", "", "quickclean");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: If user clicked "Confirm", insert into database
if (isset($_POST['confirm'])) {
    
    // *** NEW: Retrieve user_id from the hidden form field ***
    // This is a safety measure to guarantee the ID is available for insertion.
    $insert_user_id = intval($_POST['user_id']); 
    
    // Use the values sent from the confirmation form's hidden fields
    $service_id = $_POST['service_id'];
    $service_name = $_POST['service_name'];
    $price = $_POST['price'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $notes = $_POST['notes'];
    $status = 'pending'; 
    
    // Check for a valid ID (though theoretically $_SESSION should prevent 0)
    if ($insert_user_id == 0) {
         $error = "Critical Error: User ID was lost during submission. Booking failed.";
    } else {
        // SQL query structure remains correct
        $sql = "INSERT INTO bookings (
            user_id, service_id, service_name, date, time, name, phone, email, address, notes, status, price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
        $stmt = $conn->prepare($sql);
        
        // Bind parameters: i = integer (for user_id), s = string, d = decimal (for price)
        $stmt->bind_param("iissssssssds", 
            $insert_user_id, // Use the ID from the form field
            $service_id, 
            $service_name, 
            $date, 
            $time, 
            $name, 
            $phone, 
            $email, 
            $address, 
            $notes, 
            $status, 
            $price);

        if ($stmt->execute()) {
            $inserted = true;
        } else {
            $error = "Error saving booking: " . $stmt->error;
        }
        $stmt->close();
    }
} 
// Step 2: if this is first visit, show confirmation preview
elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // fetch service details using service_id
    $sid = $_POST['service_name']; 
    // Use prepared statement here too, for safety
    $stmt = $conn->prepare("SELECT * FROM services WHERE service_id=? LIMIT 1");
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $serviceData = $stmt->get_result();
    $service = $serviceData->fetch_assoc();
    $stmt->close();
    
    // Check if service was found
    if ($service) {
        $service_name = $service['service_name'];
        $price = $service['price']; 
    } else {
        $error = "Error: Service ID not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    </head>
<body>
<div class="box">
<?php if (!empty($inserted)): ?>
    <?php elseif (!empty($error)): ?>
    <?php else: ?>
  <h2>Confirm Your Booking</h2>
  <div class="detail">
    </div>

  <form method="POST">
    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
    
    <input type="hidden" name="service_id" value="<?= htmlspecialchars($sid) ?>">
    <input type="hidden" name="service_name" value="<?= htmlspecialchars($service_name) ?>">
    <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>">
    <input type="hidden" name="name" value="<?= htmlspecialchars($_POST['name']) ?>">
    <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email']) ?>">
    <input type="hidden" name="phone" value="<?= htmlspecialchars($_POST['phone']) ?>">
    <input type="hidden" name="address" value="<?= htmlspecialchars($_POST['address']) ?>">
    <input type="hidden" name="date" value="<?= htmlspecialchars($_POST['date']) ?>">
    <input type="hidden" name="time" value="<?= htmlspecialchars($_POST['time']) ?>">
    <input type="hidden" name="notes" value="<?= htmlspecialchars($_POST['notes']) ?>">

    <button type="submit" name="confirm" class="btn">Confirm Booking</button>
    <a href="customer-booking.php" class="btn cancel">Cancel</a>
  </form>
<?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>