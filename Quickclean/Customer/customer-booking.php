<?php
session_start();

// --- ERROR DISPLAY ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CHECK LOGIN ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- DATABASE CONNECTION ---
$conn = new mysqli("localhost", "root", "", "quickclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- GET SERVICE ID FROM URL ---
if (!isset($_GET['service_id'])) {
    die("No service selected.");
}
$service_id = intval($_GET['service_id']);

// --- GET SERVICE DETAILS ---
$stmtService = $conn->prepare("SELECT service_name, price, description FROM services WHERE service_id=? AND status='active'");
$stmtService->bind_param("i", $service_id);
$stmtService->execute();
$service = $stmtService->get_result()->fetch_assoc();
$stmtService->close();

if (!$service) {
    die("Selected service not found or inactive.");
}

// --- GET CUSTOMER DETAILS ---
$stmtUser = $conn->prepare("SELECT name, email, contact_num, address FROM user WHERE user_id=?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();
$stmtUser->close();

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $date = $_POST['date'];
    $time = $_POST['time'];
    $landmark = $_POST['landmark'];
    $notes = $_POST['notes'] ?? '';

    // COMBINE ADDRESS AND LANDMARK
    $final_address = $user['address'];
    if (!empty($landmark)) {
        $final_address .= " (Landmark: " . $landmark . ")";
    }

    $stmt = $conn->prepare("INSERT INTO bookings 
        (user_id, service_id, service_name, price, date, time, name, phone, email, address, notes, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        
    $stmt->bind_param(
        "iisssssssss", 
        $user_id,
        $service_id,
        $service['service_name'],
        $service['price'],
        $date,
        $time,
        $user['name'],
        $user['contact_num'],
        $user['email'],
        $final_address, 
        $notes
    );
    $stmt->execute();

    $_SESSION['booking_id'] = $stmt->insert_id;
    $stmt->close();

    header("Location: choosepayment.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Service - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body { 
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        /* UPDATED: Light Blue Background Only */
        background: linear-gradient(135deg, #5baff3ff 100%);
        min-height: 100vh;
        padding: 20px;
    }
    
    .container { 
        max-width: 700px; 
        margin: 40px auto; 
        background: #fff; 
        padding: 0;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header Section (Kept Yellow) */
    .header {
        background: linear-gradient(135deg, #fff176 0%, #ffd54f 100%);
        padding: 40px 30px;
        color: #374151; 
        text-align: center;
    }

    .header h2 {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .header .service-price {
        font-size: 32px;
        font-weight: 800;
        margin-top: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #1f2937;
    }

    .header .service-price .currency {
        font-size: 20px;
        opacity: 0.8;
    }

    /* Form Section */
    .form-content {
        padding: 35px 30px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 12px;
        border-bottom: 2px solid #fff9c4;
    }

    .section-title i {
        color: #fbc02d; /* Yellow Icon */
    }

    .form-group { 
        margin-bottom: 24px;
    }

    label { 
        font-weight: 600;
        font-size: 14px;
        display: block;
        margin-bottom: 8px;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 12px;
    }

    input, textarea { 
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-family: inherit;
        font-size: 15px;
        transition: all 0.3s ease;
        background: #f9fafb;
    }

    input:focus, textarea:focus {
        outline: none;
        border-color: #fbc02d; /* Yellow Border */
        background: white;
        box-shadow: 0 0 0 3px rgba(253, 216, 53, 0.2);
    }

    input[readonly], textarea[readonly] { 
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* Grid Layout for Date/Time */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* Price Summary Box */
    .price-summary {
        background: #fffde7;
        border: 1px solid #fff59d;
        padding: 20px;
        border-radius: 12px;
        margin: 24px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .price-summary .label {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
    }

    .price-summary .amount {
        font-size: 28px;
        font-weight: 700;
        color: #f57f17;
    }

    /* Submit Button (Kept Yellow) */
    .btn-submit { 
        background: linear-gradient(135deg, #fff176 0%, #ffd54f 100%);
        color: #1f2937;
        border: none;
        padding: 16px;
        width: 100%;
        font-size: 16px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        margin-top: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(253, 216, 53, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-submit:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(253, 216, 53, 0.4);
        background: linear-gradient(135deg, #ffee58 0%, #ffca28 100%);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .modal-box {
        background: white;
        width: 90%;
        max-width: 500px;
        padding: 40px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalSlide 0.3s ease;
    }

    @keyframes modalSlide {
        from { opacity: 0; transform: scale(0.9) translateY(-20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: #fff9c4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: #fbc02d;
    }

    .modal-box h3 { 
        color: #1f2937;
        margin-bottom: 12px;
        font-size: 24px;
        font-weight: 700;
    }

    .modal-box p { 
        color: #4b5563;
        font-size: 15px;
        margin-bottom: 12px;
        line-height: 1.7;
    }

    .modal-box .highlight {
        background: #fffde7;
        padding: 16px;
        border-radius: 10px;
        margin: 20px 0;
        border-left: 4px solid #fbc02d;
    }

    .modal-box .highlight p {
        font-weight: 600;
        color: #f57f17;
        margin: 0;
    }

    .modal-actions { 
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #4b5563;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-confirm {
        background: linear-gradient(135deg, #fff176 0%, #ffd54f 100%);
        color: #1f2937;
        padding: 14px 32px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(253, 216, 53, 0.3);
    }

    .btn-confirm:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(253, 216, 53, 0.4);
    }

    /* Help Text */
    .help-text {
        font-size: 13px;
        color: #6b7280;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .help-text i {
        color: #fbc02d;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            margin: 20px auto;
        }

        .form-content {
            padding: 25px 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .modal-box {
            padding: 30px 25px;
        }
    }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2><?php echo htmlspecialchars($service['service_name']); ?></h2>
        <div class="service-price">
            <span class="currency">₱</span><?php echo number_format($service['price'], 2); ?>
        </div>
    </div>

    <div class="form-content">
        <form method="POST" id="bookingForm">
            
            <div class="section-title">
                <i class="fas fa-user"></i>
                Personal Information
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" value="<?php echo htmlspecialchars($user['contact_num']); ?>" readonly>
            </div>

            <div class="section-title">
                <i class="fas fa-map-marker-alt"></i>
                Location Details
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea readonly rows="2"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Landmark / Nearest Location</label>
                <input type="text" name="landmark" placeholder="e.g., Near 7-Eleven, Blue Gate" />
                <div class="help-text">
                    <i class="fas fa-info-circle"></i>
                    Help us find your location easier
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Schedule
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="time" required>
                </div>
            </div>

            <div class="form-group">
                <label>Additional Notes (Optional)</label>
                <textarea name="notes" placeholder="Any special instructions or requirements..."></textarea>
            </div>

            <div class="price-summary">
                <span class="label">Total Amount</span>
                <span class="amount">₱<?php echo number_format($service['price'], 2); ?></span>
            </div>

            <button type="button" class="btn-submit" onclick="showWarning()">
                <i class="fas fa-arrow-right"></i>
                Proceed to Payment
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="warningModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Important Notice</h3>
        <p>Please read carefully before proceeding:</p>

        <div class="highlight">
            <p>Once confirmed, bookings <strong>cannot be cancelled</strong> and all payments are <strong>non-refundable</strong>.</p>
        </div>

        <p style="font-size: 14px; color: #6b7280;">
            Please ensure all details are correct before continuing.
        </p>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeWarning()">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
            <button class="btn-confirm" onclick="confirmBooking()">
                I Agree, Continue <i class="fas fa-check"></i>
            </button>
        </div>
    </div>
</div>

<script>
    function showWarning() {
        const date = document.querySelector('input[name="date"]').value;
        const time = document.querySelector('input[name="time"]').value;

        if(!date || !time) {
            alert("⚠️ Please select a Date and Time first.");
            return;
        }

        document.getElementById('warningModal').style.display = 'flex';
    }

    function closeWarning() {
        document.getElementById('warningModal').style.display = 'none';
    }

    function confirmBooking() {
        document.getElementById('bookingForm').submit();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('warningModal');
        if (event.target === modal) {
            closeWarning();
        }
    }
</script>

</body>
</html>
<?php $conn->close(); ?>