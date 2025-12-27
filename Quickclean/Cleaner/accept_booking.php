<!-- accept_booking.php -->
<?php
$conn = mysqli_connect("localhost", "root", "", "quickclean");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$booking_id = (int)$_GET['id'];

// First, get the booking details
$booking_query = "SELECT b.*, s.service_name, s.price 
                  FROM bookings b 
                  LEFT JOIN services s ON b.service_id = s.service_id 
                  WHERE b.booking_id = $booking_id";
$booking_result = mysqli_query($conn, $booking_query);
$booking = mysqli_fetch_assoc($booking_result);

if ($booking) {
    // Update booking status to accepted
    $update_booking = "UPDATE bookings SET status = 'accepted' WHERE booking_id = $booking_id";
    
    if (mysqli_query($conn, $update_booking)) {
        
        // Check if transaction already exists
        $check_transaction = "SELECT transaction_id FROM transactions WHERE booking_id = $booking_id";
        $trans_result = mysqli_query($conn, $check_transaction);
        
        if (mysqli_num_rows($trans_result) == 0) {
            // Create transaction record with "on the way" status
            $insert_transaction = "INSERT INTO transactions 
                                   (booking_id, customer_name, service_name, date, time, address, phone, email, status, action_date) 
                                   VALUES 
                                   ($booking_id, 
                                    '{$booking['name']}', 
                                    '{$booking['service_name']}', 
                                    '{$booking['date']}', 
                                    '{$booking['time']}', 
                                    '{$booking['address']}', 
                                    '{$booking['phone']}', 
                                    '{$booking['email']}', 
                                    'on the way', 
                                    NOW())";
            
            if (mysqli_query($conn, $insert_transaction)) {
                header("Location: assigned.php?success=accepted");
                exit();
            } else {
                echo "Error creating transaction: " . mysqli_error($conn);
            }
        } else {
            // Transaction already exists, just update it
            $update_transaction = "UPDATE transactions 
                                  SET status = 'on the way', 
                                      action_date = NOW() 
                                  WHERE booking_id = $booking_id";
            mysqli_query($conn, $update_transaction);
            header("Location: assigned.php?success=accepted");
            exit();
        }
    } else {
        echo "Error updating booking: " . mysqli_error($conn);
    }
} else {
    echo "Booking not found.";
}

mysqli_close($conn);
?>

<!-- complete_booking.php (FIXED VERSION) -->
<?php
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
    // First check if transaction exists
    $check_query = "SELECT transaction_id FROM transactions WHERE booking_id = $booking_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Transaction exists - UPDATE it
        $sql = "UPDATE transactions 
                SET status = 'completed', 
                    proof_image = '$image_path',
                    completed_at = NOW(),
                    action_date = NOW()
                WHERE booking_id = $booking_id";
        
        if (mysqli_query($conn, $sql)) {
            // Also update the booking status
            $update_booking = "UPDATE bookings SET status = 'completed' WHERE booking_id = $booking_id";
            mysqli_query($conn, $update_booking);
            
            header("Location: assigned.php?success=completed");
            exit();
        } else {
            header("Location: assigned.php?error=db&msg=" . urlencode(mysqli_error($conn)));
            exit();
        }
    } else {
        // Transaction doesn't exist - CREATE it first, then complete it
        // Get booking details
        $booking_query = "SELECT b.*, s.service_name 
                         FROM bookings b 
                         LEFT JOIN services s ON b.service_id = s.service_id 
                         WHERE b.booking_id = $booking_id";
        $booking_result = mysqli_query($conn, $booking_query);
        $booking = mysqli_fetch_assoc($booking_result);
        
        if ($booking) {
            // Insert transaction as completed
            $insert_sql = "INSERT INTO transactions 
                          (booking_id, customer_name, service_name, date, time, address, phone, email, status, proof_image, completed_at, action_date) 
                          VALUES 
                          ($booking_id, 
                           '{$booking['name']}', 
                           '{$booking['service_name']}', 
                           '{$booking['date']}', 
                           '{$booking['time']}', 
                           '{$booking['address']}', 
                           '{$booking['phone']}', 
                           '{$booking['email']}', 
                           'completed',
                           '$image_path',
                           NOW(),
                           NOW())";
            
            if (mysqli_query($conn, $insert_sql)) {
                // Also update the booking status
                $update_booking = "UPDATE bookings SET status = 'completed' WHERE booking_id = $booking_id";
                mysqli_query($conn, $update_booking);
                
                header("Location: assigned.php?success=completed");
                exit();
            } else {
                header("Location: assigned.php?error=db&msg=" . urlencode(mysqli_error($conn)));
                exit();
            }
        } else {
            header("Location: assigned.php?error=booking_not_found");
            exit();
        }
    }
} else {
    // File upload failed
    $error_msg = isset($_FILES['proof_image']) ? "Upload error code: " . $_FILES['proof_image']['error'] : "No file uploaded";
    header("Location: assigned.php?error=upload&msg=" . urlencode($error_msg));
    exit();
}

mysqli_close($conn);
?>

<!-- DEBUG SCRIPT - debug_transactions.php -->
<?php
// Create this file to check your database status
$conn = mysqli_connect("localhost", "root", "", "quickclean");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Database Debug Information</h2>";
echo "<style>body { font-family: Arial; padding: 20px; } table { border-collapse: collapse; width: 100%; margin: 20px 0; } th, td { border: 1px solid #ddd; padding: 12px; text-align: left; } th { background: #667eea; color: white; }</style>";

// Check bookings
echo "<h3>Bookings Table</h3>";
$bookings = mysqli_query($conn, "SELECT booking_id, name, status, date, time FROM bookings ORDER BY booking_id DESC LIMIT 10");
echo "<table><tr><th>Booking ID</th><th>Name</th><th>Status</th><th>Date</th><th>Time</th></tr>";
while ($row = mysqli_fetch_assoc($bookings)) {
    echo "<tr><td>{$row['booking_id']}</td><td>{$row['name']}</td><td>{$row['status']}</td><td>{$row['date']}</td><td>{$row['time']}</td></tr>";
}
echo "</table>";

// Check transactions
echo "<h3>Transactions Table</h3>";
$transactions = mysqli_query($conn, "SELECT transaction_id, booking_id, customer_name, status, action_date, completed_at FROM transactions ORDER BY transaction_id DESC LIMIT 10");
echo "<table><tr><th>Transaction ID</th><th>Booking ID</th><th>Customer</th><th>Status</th><th>Action Date</th><th>Completed At</th></tr>";
if (mysqli_num_rows($transactions) > 0) {
    while ($row = mysqli_fetch_assoc($transactions)) {
        echo "<tr><td>{$row['transaction_id']}</td><td>{$row['booking_id']}</td><td>{$row['customer_name']}</td><td>{$row['status']}</td><td>{$row['action_date']}</td><td>{$row['completed_at']}</td></tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center; color: red;'>No transactions found! This might be your problem.</td></tr>";
}
echo "</table>";

// Check if upload directory exists
echo "<h3>Upload Directory Check</h3>";
$upload_dir = "uploads/completion_proofs/";
if (is_dir($upload_dir)) {
    echo "<p style='color: green;'>✓ Upload directory exists: $upload_dir</p>";
    if (is_writable($upload_dir)) {
        echo "<p style='color: green;'>✓ Directory is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Directory is NOT writable. Fix permissions!</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Upload directory does not exist. It will be created automatically.</p>";
}

mysqli_close($conn);
?>

<!-- decline_booking.php -->
<?php
$conn = mysqli_connect("localhost", "root", "", "quickclean");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$booking_id = (int)$_GET['id'];

// Update booking status to declined/cancelled
$sql = "UPDATE bookings SET status = 'declined' WHERE booking_id = $booking_id";

if (mysqli_query($conn, $sql)) {
    // Also delete any associated transaction if exists
    $delete_transaction = "DELETE FROM transactions WHERE booking_id = $booking_id";
    mysqli_query($conn, $delete_transaction);
    
    header("Location: assigned.php?success=declined");
} else {
    header("Location: assigned.php?error=decline_failed");
}

mysqli_close($conn);
exit();
?>

<!-- IMPROVED assigned.php WITH SUCCESS/ERROR MESSAGES -->
<?php
// At the top of assigned.php, add this for displaying messages:

// Show success/error messages
if (isset($_GET['success'])) {
    $message = '';
    $type = 'success';
    
    switch($_GET['success']) {
        case 'accepted':
            $message = 'Booking accepted successfully!';
            break;
        case 'completed':
            $message = 'Booking marked as completed!';
            break;
        case 'declined':
            $message = 'Booking declined successfully.';
            break;
        case '1':
            $message = 'Operation completed successfully!';
            break;
    }
    
    if ($message) {
        echo "<div class='alert alert-success' style='position: fixed; top: 20px; right: 20px; z-index: 9999; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); animation: slideIn 0.3s ease;'>
            <strong>✓ Success!</strong> $message
          </div>
          <script>
            setTimeout(function() {
                document.querySelector('.alert-success').style.animation = 'slideOut 0.3s ease';
                setTimeout(function() {
                    document.querySelector('.alert-success').remove();
                }, 300);
            }, 3000);
          </script>";
    }
}

if (isset($_GET['error'])) {
    $message = '';
    
    switch($_GET['error']) {
        case 'upload':
            $message = 'File upload failed. Please try again.';
            if (isset($_GET['msg'])) {
                $message .= '<br><small>' . htmlspecialchars($_GET['msg']) . '</small>';
            }
            break;
        case 'db':
            $message = 'Database error occurred.';
            if (isset($_GET['msg'])) {
                $message .= '<br><small>' . htmlspecialchars($_GET['msg']) . '</small>';
            }
            break;
        case 'booking_not_found':
            $message = 'Booking not found.';
            break;
        case 'decline_failed':
            $message = 'Failed to decline booking.';
            break;
        default:
            $message = 'An error occurred.';
    }
    
    echo "<div class='alert alert-error' style='position: fixed; top: 20px; right: 20px; z-index: 9999; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4); animation: slideIn 0.3s ease;'>
            <strong>✗ Error!</strong> $message
          </div>
          <script>
            setTimeout(function() {
                document.querySelector('.alert-error').style.animation = 'slideOut 0.3s ease';
                setTimeout(function() {
                    document.querySelector('.alert-error').remove();
                }, 300);
            }, 5000);
          </script>";
}
?>

<style>
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}
</style>