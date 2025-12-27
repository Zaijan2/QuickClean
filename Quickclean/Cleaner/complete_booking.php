
<?php
// complete_booking.php
$conn = mysqli_connect("localhost", "root", "", "quickclean");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get booking ID
$booking_id = (int)$_POST['booking_id'];

// Handle file upload
$upload_success = false;
$image_path = null;

if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
    
    // Create uploads directory if it doesn't exist
    $upload_dir = "uploads/completion_proofs/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Get file info
    $file_tmp = $_FILES['proof_image']['tmp_name'];
    $file_name = $_FILES['proof_image']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file type
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
    
    if (in_array($file_ext, $allowed_extensions)) {
        // Generate unique filename
        $new_filename = "completion_" . $booking_id . "_" . time() . "." . $file_ext;
        $upload_path = $upload_dir . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $upload_success = true;
            $image_path = $upload_path;
        }
    }
}

if ($upload_success) {
    // Update transaction status to completed
    $sql = "UPDATE transactions 
            SET status = 'completed', 
                proof_image = '$image_path',
                completed_at = NOW()
            WHERE booking_id = $booking_id";
    
    if (mysqli_query($conn, $sql)) {
        // Success - redirect back
        header("Location: assigned.php?success=1");
    } else {
        // Error
        header("Location: assigned.php?error=db");
    }
} else {
    // File upload failed
    header("Location: assigned.php?error=upload");
}

mysqli_close($conn);
exit();
?>
