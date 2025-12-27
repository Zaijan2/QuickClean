<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
require_login();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Collect form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$contact_num = $_POST['contact_num'] ?? '';

// Handle profile picture upload
$profile_pic = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $filename = 'profile_'.$user_id.'_'.time().'.'.$ext;
    $target = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
        $profile_pic = $filename;
    }
}

// Update query
if ($profile_pic) {
    $stmt = $conn->prepare("UPDATE user SET name=?, email=?, address=?, contact_num=?, profile_pic=? WHERE user_id=?");
    $stmt->bind_param("sssssi", $name, $email, $address, $contact_num, $profile_pic, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE user SET name=?, email=?, address=?, contact_num=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $name, $email, $address, $contact_num, $user_id);
}

$stmt->execute();
$stmt->close();
$conn->close();

// Redirect back to user dashboard
header("Location: user_page.php");
exit();
?>