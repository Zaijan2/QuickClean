<?php
session_start();

// ------------------ LOGIN VALIDATION ------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? "Admin";

// ------------------ DATABASE CONNECTION ------------------
$conn = new mysqli("localhost", "root", "", "quickclean");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ------------------ HANDLE ADMIN ACTION ------------------
if (isset($_POST['id']) && isset($_POST['action'])) {
  $id = intval($_POST['id']);
  $action = $_POST['action'];

  if ($action === "accept") {
    // ✅ Fetch booking details first
    $result = $conn->query("SELECT * FROM bookings WHERE booking_id = $id LIMIT 1");
    if ($result && $result->num_rows > 0) {
      $booking = $result->fetch_assoc();

      // 1️⃣ Update booking status
      $conn->query("UPDATE bookings SET status='accepted' WHERE booking_id=$id");

      // 2️⃣ Insert into transactions table
      $stmt = $conn->prepare("INSERT INTO transactions 
        (booking_id, customer_name, service_name, date, time, address, phone, email, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'on the way')");
      $stmt->bind_param(
        "isssssss",
        $booking['booking_id'],
        $booking['name'],
        $booking['service_name'],
        $booking['date'],
        $booking['time'],
        $booking['address'],
        $booking['phone'],
        $booking['email']
      );
      $stmt->execute();

      echo "accepted";
    } else {
      echo "booking_not_found";
    }
  } elseif ($action === "decline") {
    // ❌ Just update the booking status
    $conn->query("UPDATE bookings SET status='declined' WHERE booking_id=$id");
    echo "declined";
  } else {
    echo "invalid_action";
  }
} else {
  echo "missing_data";
}

$conn->close();
?>