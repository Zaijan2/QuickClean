<?php
$conn = mysqli_connect("localhost", "root", "", "quickclean");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$booking_id = (int)$_GET['id']; // Sanitize input

// Update booking status to 'declined'
mysqli_query($conn, "UPDATE bookings SET status='declined' WHERE booking_id=$booking_id");

// Remove from transactions if exists
mysqli_query($conn, "DELETE FROM transactions WHERE booking_id=$booking_id");

mysqli_close($conn);

header("Location: assigned.php");
exit();
?>