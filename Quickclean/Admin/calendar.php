<?php
session_start();

// ------------------ LOGIN VALIDATION ------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? 'Admin';

// ------------------ DATABASE CONNECTION ------------------
$host = 'localhost'; 
$dbuser = 'root'; 
$dbpass = ''; 
$dbname = 'quickclean';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) die("DB error: " . $conn->connect_error);

// ------------------ AJAX: HANDLE STATUS UPDATE ------------------
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $bid = intval($_POST['booking_id']);
    $st = $_POST['status'];
    $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE booking_id=?");
    $stmt->bind_param("si", $st, $bid);
    $stmt->execute();
    exit("ok");
}

// ------------------ CHECK COLUMNS ------------------
$has_cleaner = false;
$chk = $conn->query("SHOW COLUMNS FROM `bookings` LIKE 'cleaner_id'");
if ($chk && $chk->num_rows > 0) $has_cleaner = true;

// ------------------ HANDLE CREATE BOOKING ------------------
$create_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_booking'])) {
    $s_name = trim($_POST['name'] ?? '');
    $s_phone = trim($_POST['phone'] ?? '');
    $s_email = trim($_POST['email'] ?? null);
    $s_service = intval($_POST['service_id'] ?? 0);
    $s_date = $_POST['date'] ?? null;
    $s_time = trim($_POST['time'] ?? '');
    $s_address = trim($_POST['address'] ?? '');
    $s_notes = trim($_POST['notes'] ?? '');
    $s_price = $_POST['price'] ?? null;
    $s_cleaner = $has_cleaner ? ($_POST['cleaner_id'] ? intval($_POST['cleaner_id']) : null) : null;

    if (!$s_name || !$s_service || !$s_date || !$s_time) {
        $create_msg = 'Please fill required fields.';
    } else {
        $user_id_for_insert = 0;
        $svcName = '';
        
        $rs = $conn->prepare("SELECT service_name, price FROM services WHERE service_id = ?");
        $rs->bind_param("i", $s_service);
        $rs->execute();
        $resSvc = $rs->get_result();
        if ($resSvc && $rowSvc = $resSvc->fetch_assoc()) {
            $svcName = $rowSvc['service_name'];
            if (empty($s_price)) $s_price = $rowSvc['price'];
        }
        $rs->close();

        if ($has_cleaner) {
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, service_name, price, date, time, extras, name, phone, email, address, notes, cleaner_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("iisdsssssssis", $user_id_for_insert, $s_service, $svcName, $s_price, $s_date, $s_time, $s_notes, $s_name, $s_phone, $s_email, $s_address, $s_notes, $s_cleaner);
        } else {
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, service_name, price, date, time, extras, name, phone, email, address, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("iisdssssssss", $user_id_for_insert, $s_service, $svcName, $s_price, $s_date, $s_time, $s_notes, $s_name, $s_phone, $s_email, $s_address, $s_notes);
        }
        
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $create_msg = "DB error creating booking.";
        }
    }
}

// ------------------ FETCH DATA ------------------
if ($has_cleaner) {
    $sql = "SELECT b.booking_id, b.name, b.service_name, b.date, b.time, b.status, b.phone, b.email, b.address, b.notes, b.service_id, b.price, b.cleaner_id, c.name AS cleaner_name
            FROM bookings b LEFT JOIN user c ON b.cleaner_id = c.user_id
            ORDER BY b.date ASC, b.time ASC";
} else {
    $sql = "SELECT booking_id, name, service_name, date, time, status, phone, email, address, notes, service_id, price
            FROM bookings
            ORDER BY date ASC, time ASC";
}
$res = $conn->query($sql);
$bookings = [];
while ($r = $res->fetch_assoc()) $bookings[] = $r;

// Transactions
$tx = $conn->query("SELECT booking_id, status FROM transactions");
$transactions = [];
if ($tx) {
    while ($t = $tx->fetch_assoc()) $transactions[$t['booking_id']] = $t['status'];
}
$transactions_json = json_encode($transactions);

// Services
$sv = $conn->query("SELECT service_id, service_name, price FROM services ORDER BY service_name ASC");
$services = [];
while ($s = $sv->fetch_assoc()) $services[] = $s;

// Cleaners
$cleaners = [];
if ($has_cleaner) {
    $cl = $conn->query("SELECT user_id, name FROM user WHERE role='cleaner' ORDER BY name ASC");
    while ($c = $cl->fetch_assoc()) $cleaners[] = $c;
}

$bookings_json = json_encode($bookings);
$services_json = json_encode($services);
$cleaners_json = json_encode($cleaners);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendar - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --primary: #4F46E5;
    --primary-dark: #4338CA;
    --secondary: #10B981;
    --danger: #EF4444;
    --warning: #F59E0B;
    --info: #3B82F6;
    --dark: #1F2937;
    --light: #F9FAFB;
    --border: #E5E7EB;
    --text-primary: #111827;
    --text-secondary: #6B7280;
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

body { 
    font-family: "Inter", sans-serif; 
    background: var(--light);
    color: var(--text-primary);
    line-height: 1.6;
}

/* Sidebar */
.sidebar { 
    width: 260px; background: white; border-right: 1px solid var(--border);
    display: flex; flex-direction: column; position: fixed; height: 100vh;
    z-index: 1000; transition: transform 0.3s ease; overflow-y: auto;
}
.sidebar-header { padding: 24px 20px; border-bottom: 1px solid var(--border); }
.sidebar-header h2 { font-size: 24px; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 10px; }
.sidebar-menu { list-style: none; padding: 16px 12px; flex: 1; }
.sidebar-menu li { margin-bottom: 4px; }
.sidebar-menu li a { 
    text-decoration: none; color: var(--text-secondary); 
    font-size: 15px; font-weight: 500; display: flex; align-items: center; 
    gap: 12px; padding: 12px 16px; border-radius: 8px; transition: all 0.2s ease; 
}
.sidebar-menu li a i { width: 20px; font-size: 18px; }
.sidebar-menu li a:hover { background: var(--light); color: var(--primary); }
.sidebar-menu li a.active { background: var(--primary); color: white; }

/* Main Layout */
.main-content { margin-left: 260px; min-height: 100vh; background: var(--light); }
.topbar { 
    background: white; padding: 20px 32px; border-bottom: 1px solid var(--border); 
    display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; 
}
.topbar-left h1 { font-size: 24px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px; }
.topbar-left h1 i { color: var(--primary); }
.topbar-left p { font-size: 14px; color: var(--text-secondary); margin-top: 4px; }
.admin-profile { display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: var(--light); border-radius: 50px; cursor: pointer; transition: all 0.2s; }
.admin-profile:hover { background: var(--border); }
.admin-profile img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary); }
.admin-profile-info span { font-size: 14px; font-weight: 600; display: block;}
.admin-profile-info small { font-size: 12px; color: var(--text-secondary); }

.content-container { padding: 32px; max-width: 1600px; }

/* Card */
.card { background: white; border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border); padding: 0; overflow: hidden; }

/* Toolbar */
.calendar-toolbar { 
    display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; 
    gap: 16px; padding: 24px; background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
    border-bottom: 1px solid var(--border);
}
.toolbar-group { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.toolbar-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-left: 8px; }

.btn { 
    padding: 10px 18px; border-radius: 8px; border: 1px solid transparent; 
    cursor: pointer; font-size: 14px; font-weight: 500; display: inline-flex; 
    align-items: center; gap: 8px; transition: all 0.2s; font-family: inherit; 
}
.btn-primary { background: var(--primary); color: white; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2); }
.btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3); }
.btn-secondary { background: white; border-color: var(--border); color: var(--text-primary); }
.btn-secondary:hover { background: var(--light); border-color: var(--text-secondary); }
.btn-success { background: var(--secondary); color: white; }
.btn-success:hover { background: #059669; }
.btn-danger { background: white; border-color: var(--danger); color: var(--danger); }
.btn-danger:hover { background: #FEF2F2; }

.form-select { 
    padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); 
    font-size: 14px; color: var(--text-primary); outline: none; background: white; 
    cursor: pointer; transition: all 0.2s; font-family: inherit;
}
.form-select:hover { border-color: var(--text-secondary); }
.form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

/* Grid Container */
.calendar-container { padding: 24px; }

/* Week Day Headers */
.day-headers { 
    display: grid; 
    grid-template-columns: repeat(7, 1fr); 
    gap: 2px; 
    margin-bottom: 2px;
    background: var(--light);
    border-radius: 8px;
    overflow: hidden;
}
.day-header { 
    background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%); 
    padding: 14px; 
    text-align: center; 
    font-weight: 700; 
    font-size: 13px; 
    color: var(--primary); 
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Calendar Grid */
.calendar-grid { 
    display: grid; 
    grid-template-columns: repeat(7, 1fr); 
    gap: 2px; 
    background: var(--border);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.calendar-day { 
    background: white; 
    min-height: 150px; 
    padding: 12px; 
    position: relative;
    transition: all 0.2s;
    border: 1px solid transparent;
}
.calendar-day:hover { background: #FAFBFC; border-color: var(--primary); transform: scale(1.02); z-index: 1; box-shadow: var(--shadow-lg); }

.day-number { 
    font-size: 16px; 
    font-weight: 700; 
    color: var(--text-secondary); 
    margin-bottom: 8px; 
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.day-weekday { font-size: 11px; font-weight: 500; color: var(--text-secondary); text-transform: uppercase; }

.calendar-day.today { background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%); border-color: var(--primary); }
.calendar-day.today .day-number { color: var(--primary); }
.calendar-day.today::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), #8B5CF6);
}

/* Events */
.event-item { 
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px; 
    border-radius: 6px; 
    font-size: 12px; 
    font-weight: 500; 
    margin-bottom: 6px; 
    cursor: pointer; 
    color: white; 
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}
.event-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: rgba(255, 255, 255, 0.5);
}
.event-item:hover { transform: translateX(4px); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); }
.event-time { font-size: 11px; opacity: 0.9; }
.event-name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Event Colors */
.status-pending { background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); }
.status-accepted { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
.status-declined { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); }
.status-completed { background: linear-gradient(135deg, #FCD34D 0%, #F59E0B 100%); color: #1F2937 !important; }
.status-ontheway { background: linear-gradient(135deg, #FB923C 0%, #F97316 100%); }

/* Legend */
.legend { 
    display: flex; 
    gap: 20px; 
    margin-top: 24px; 
    padding: 20px; 
    background: var(--light); 
    border-radius: 8px;
    flex-wrap: wrap; 
    justify-content: center;
}
.legend-item { 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    font-size: 13px; 
    font-weight: 500;
    color: var(--text-primary); 
}
.legend-dot { 
    width: 12px; 
    height: 12px; 
    border-radius: 3px; 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Modals */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.6); z-index: 2000;
    display: none; justify-content: center; align-items: center;
    backdrop-filter: blur(4px);
    animation: fadeIn 0.2s ease;
}
.modal-box {
    background: white; width: 100%; max-width: 550px;
    border-radius: 16px; padding: 0;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    max-height: 90vh; overflow-y: auto;
    animation: slideUp 0.3s ease;
}
.modal-header { 
    display: flex; justify-content: space-between; align-items: center; 
    padding: 24px; border-bottom: 1px solid var(--border); 
    background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
}
.modal-header h3 { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
.modal-header h3 i { color: var(--primary); }
.close-modal { cursor: pointer; color: var(--text-secondary); font-size: 24px; transition: all 0.2s; }
.close-modal:hover { color: var(--text-primary); transform: rotate(90deg); }

.modal-body { padding: 24px; }

.form-group { margin-bottom: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-label { 
    display: block; 
    font-size: 13px; 
    font-weight: 600; 
    margin-bottom: 6px; 
    color: var(--text-primary); 
}
.form-control {
    width: 100%; padding: 12px 14px; border-radius: 8px;
    border: 1px solid var(--border); font-family: inherit; font-size: 14px;
    transition: all 0.2s;
}
.form-control:focus { 
    outline: none; 
    border-color: var(--primary); 
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); 
}
textarea.form-control { min-height: 90px; resize: vertical; }

.detail-row { 
    display: flex; 
    padding: 14px 0; 
    font-size: 14px; 
    border-bottom: 1px solid var(--light);
}
.detail-row:last-child { border-bottom: none; }
.detail-label { 
    width: 120px; 
    font-weight: 600; 
    color: var(--text-secondary); 
    display: flex;
    align-items: center;
    gap: 8px;
}
.detail-label i { width: 16px; color: var(--primary); }
.detail-val { flex: 1; color: var(--text-primary); }

.modal-footer {
    padding: 20px 24px;
    background: var(--light);
    border-top: 1px solid var(--border);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Mobile Menu */
.menu-toggle { 
    display: none; position: fixed; top: 20px; left: 20px; z-index: 1001; 
    background: white; padding: 10px 12px; border-radius: 8px; 
    border: 1px solid var(--border); cursor: pointer; box-shadow: var(--shadow); 
}

/* Responsive */
@media (max-width: 1200px) {
    .calendar-grid { grid-template-columns: repeat(4, 1fr); }
    .day-headers { grid-template-columns: repeat(4, 1fr); }
}

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.active { transform: translateX(0); }
    .main-content { margin-left: 0; }
    .menu-toggle { display: block; }
    .topbar { padding: 16px 20px; }
    .content-container { padding: 16px; }
    .calendar-toolbar { flex-direction: column; align-items: stretch; }
    .toolbar-group { width: 100%; }
    .calendar-grid { grid-template-columns: 1fr; }
    .day-headers { grid-template-columns: 1fr; }
    .calendar-day { min-height: auto; }
    .form-row { grid-template-columns: 1fr; }
    .admin-profile-info { display: none; }
}
</style>
</head>
<body>

<div class="menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</div>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2><i class="fas fa-sparkles"></i> QuickClean</h2>
  </div>
  <ul class="sidebar-menu">
    <li><a href="admindashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="customers.php"><i class="fas fa-users"></i> Users</a></li>
    <li><a href="services.php"><i class="fas fa-concierge-bell"></i> Services</a></li>
    <li><a href="cleaners.php"><i class="fas fa-user-tie"></i> Cleaners</a></li>
    <li><a href="calendar.php" class="active"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
    <li><a href="booking.php"><i class="fas fa-clipboard-list"></i> Bookings</a></li>
    <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main-content">

  <div class="topbar">
    <div class="topbar-left">
      <h1><i class="fas fa-calendar-alt"></i> Schedule Calendar</h1>
      <p>Manage bookings and appointments</p>
    </div>
    <div class="admin-profile">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin">
      <div class="admin-profile-info">
        <span><?= htmlspecialchars($admin_name) ?></span>
        <small>Administrator</small>
      </div>
    </div>
  </div>

  <div class="content-container">
    <div class="card">
      
      <div class="calendar-toolbar">
        <div class="toolbar-group">
            <button class="btn btn-secondary" id="prevBtn"><i class="fas fa-chevron-left"></i> Prev</button>
            <button class="btn btn-secondary" id="todayBtn"><i class="fas fa-calendar-day"></i> Today</button>
            <button class="btn btn-secondary" id="nextBtn">Next <i class="fas fa-chevron-right"></i></button>
            <h3 class="toolbar-title" id="rangeTitle">Loading...</h3>
        </div>

        <div class="toolbar-group">
            <select id="viewSelect" class="form-select">
                <option value="week">📅 Weekly View</option>
                <option value="month">📆 Monthly View</option>
            </select>

            <?php if ($has_cleaner): ?>
            <select id="cleanerFilter" class="form-select">
                <option value="">👥 All Cleaners</option>
                <?php foreach ($cleaners as $cl): ?>
                  <option value="<?= $cl['user_id'] ?>"><?= htmlspecialchars($cl['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select id="statusFilter" class="form-select">
                <option value="">📊 All Status</option>
                <option value="pending">⏳ Pending</option>
                <option value="accepted">⭐ Completed</option>
                <option value="ontheway">🚚 On the Way</option>
                <option value="declined">❌ Declined</option>
            </select>

            <button class="btn btn-primary" id="addBookingBtn">
                <i class="fas fa-plus-circle"></i> New Booking
            </button>
        </div>
      </div>

      <div class="calendar-container">
        <div class="day-headers">
            <div class="day-header">Sunday</div>
            <div class="day-header">Monday</div>
            <div class="day-header">Tuesday</div>
            <div class="day-header">Wednesday</div>
            <div class="day-header">Thursday</div>
            <div class="day-header">Friday</div>
            <div class="day-header">Saturday</div>
        </div>

        <div id="calendarGrid" class="calendar-grid"></div>

        <div class="legend">
          <div class="legend-item"><div class="legend-dot status-pending"></div> Pending</div>
          <div class="legend-item"><div class="legend-dot status-ontheway"></div> On The Way</div>
          <div class="legend-item"><div class="legend-dot status-completed"></div> Completed</div>
          <div class="legend-item"><div class="legend-dot status-declined"></div> Declined</div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Create Modal -->
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Create New Booking</h3>
            <i class="fas fa-times close-modal" id="closeCreate"></i>
        </div>
        <div class="modal-body">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Service Type *</label>
                    <select name="service_id" id="serviceSelect" class="form-control" required>
                        <option value="">Select a service...</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Client Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter client name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Enter phone number" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="client@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" id="dateInput" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time *</label>
                        <input type="time" name="time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address *</label>
                    <input type="text" name="address" class="form-control" placeholder="Enter service address" required>
                </div>

                <?php if ($has_cleaner): ?>
                <div class="form-group">
                    <label class="form-label">Assign Cleaner</label>
                    <select name="cleaner_id" class="form-control">
                        <option value="">-- Unassigned --</option>
                        <?php foreach($cleaners as $cl): ?>
                        <option value="<?= $cl['user_id'] ?>"><?= htmlspecialchars($cl['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" placeholder="Any special instructions or notes..."></textarea>
                </div>

                <div class="modal-footer" style="margin: 0 -24px -24px; padding: 20px 24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Cancel</button>
                    <button type="submit" name="create_booking" class="btn btn-primary">
                        <i class="fas fa-check"></i> Create Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal-overlay" id="detailsModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Booking Details</h3>
            <i class="fas fa-times close-modal" id="closeDetails"></i>
        </div>
        <div class="modal-body">
            <div id="detailsBody"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('detailsModal')">Close</button>
            <button class="btn btn-success" id="acceptBtn">
                <i class="fas fa-check-circle"></i> Accept
            </button>
            <button class="btn btn-danger" id="declineBtn">
                <i class="fas fa-times-circle"></i> Decline
            </button>
        </div>
    </div>
</div>

<script>
// ------------------ DATA ------------------
const rawBookings = <?php echo $bookings_json ?: '[]'; ?>;
const services = <?php echo $services_json ?: '[]'; ?>;
const cleaners = <?php echo $cleaners_json ?: '[]'; ?>;
const transactions = <?php echo $transactions_json ?: '{}'; ?>;
const hasCleaner = <?php echo $has_cleaner ? 'true' : 'false'; ?>;

// ------------------ ELEMENTS ------------------
const grid = document.getElementById('calendarGrid');
const rangeTitle = document.getElementById('rangeTitle');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const todayBtn = document.getElementById('todayBtn');
const viewSelect = document.getElementById('viewSelect');
const statusFilter = document.getElementById('statusFilter');
const cleanerFilter = document.getElementById('cleanerFilter');
const addBookingBtn = document.getElementById('addBookingBtn');
const createModal = document.getElementById('createModal');
const closeCreate = document.getElementById('closeCreate');
const detailsModal = document.getElementById('detailsModal');
const closeDetails = document.getElementById('closeDetails');
const serviceSelect = document.getElementById('serviceSelect');

// Populate Services
services.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.service_id;
    opt.textContent = s.service_name + (s.price ? ` (₱${parseFloat(s.price).toFixed(2)})` : '');
    serviceSelect.appendChild(opt);
});

// Normalize Bookings
const bookings = rawBookings.map(b => {
    let d = b.date;
    if (typeof d === 'string' && d.length > 10) d = d.slice(0,10);
    return {
      ...b,
      date: d,
      cleaner_id: b.cleaner_id || null,
      cleaner_name: b.cleaner_name || null,
      status: b.status || 'pending'
    };
});

// ------------------ STATE & LOGIC ------------------
let current = new Date();
let view = 'week'; 

function getWeekRange(base){
    const dt = new Date(base);
    const day = dt.getDay(); 
    const start = new Date(dt); start.setDate(dt.getDate() - day);
    const end = new Date(start); end.setDate(start.getDate() + 6);
    return {start, end};
}

function getMonthRange(base){
    const dt = new Date(base);
    const start = new Date(dt.getFullYear(), dt.getMonth(), 1);
    const end = new Date(dt.getFullYear(), dt.getMonth() + 1, 0);
    return {start, end};
}

function applyFilters(list){
    const status = statusFilter.value;
    const cleaner = cleanerFilter ? cleanerFilter.value : '';
    return list.filter(b => {
        if (status && b.status !== status) return false;
        if (hasCleaner && cleaner && String(b.cleaner_id) !== String(cleaner)) return false;
        return true;
    });
}

function render(){
    grid.innerHTML = '';
    
    let start, end, loopDays;
    
    if(view === 'week') {
        const range = getWeekRange(current);
        start = range.start;
        end = range.end;
        loopDays = 7;
        rangeTitle.textContent = `${start.toLocaleDateString('en-US', {month: 'short', day: 'numeric'})} — ${end.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}`;
    } else {
        const range = getMonthRange(current);
        start = range.start;
        end = range.end;
        loopDays = end.getDate(); 
        rangeTitle.textContent = `${start.toLocaleString('default',{month:'long'})} ${start.getFullYear()}`;
        
        const offset = start.getDay(); 
        for(let k=0; k<offset; k++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-day';
            empty.style.background = '#f9fafb';
            grid.appendChild(empty);
        }
    }

    const todayStr = new Date().toISOString().split('T')[0];

    for (let i=0; i < (view === 'week' ? 7 : loopDays); i++){
        let d;
        if(view === 'week') {
            d = new Date(start); 
            d.setDate(start.getDate() + i);
        } else {
            d = new Date(start); 
            d.setDate(start.getDate() + i);
        }
        
        const ymd = d.toISOString().split('T')[0];
        
        const dayDiv = document.createElement('div');
        dayDiv.className = 'calendar-day';
        if(ymd === todayStr) dayDiv.classList.add('today');
        
        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.innerHTML = `
            <div>
                <div style="font-size: 18px;">${d.getDate()}</div>
                <div class="day-weekday">${d.toLocaleDateString('en-US', {weekday:'short'})}</div>
            </div>
        `;
        dayDiv.appendChild(dayNumber);

        let dayBookings = bookings.filter(b => b.date === ymd);
        dayBookings = applyFilters(dayBookings);
        dayBookings.sort((a,b)=> (a.time||'').localeCompare(b.time||''));

        dayBookings.forEach(b => {
            const ev = document.createElement('div');
            
            const txStatus = transactions[b.booking_id];
            let statusClass = 'status-' + b.status;
            
            if (txStatus === 'completed') statusClass = 'status-completed';
            else if (txStatus === 'on the way') statusClass = 'status-ontheway';

            ev.className = `event-item ${statusClass}`;
            
            ev.innerHTML = `
                <span class="event-time">${b.time ? b.time.slice(0,5) : ''}</span>
                <span class="event-name">${b.name}</span>
            `;
            
            ev.onclick = (e)=> { e.stopPropagation(); openDetails(b); };
            dayDiv.appendChild(ev);
        });

        dayDiv.onclick = (e) => {
             if(e.target === dayDiv || e.target.closest('.day-number')) openCreate(ymd);
        };

        grid.appendChild(dayDiv);
    }
}

// ------------------ EVENTS ------------------
prevBtn.onclick = () => {
    if(view === 'week') current.setDate(current.getDate() - 7);
    else current.setMonth(current.getMonth() - 1);
    render();
};
nextBtn.onclick = () => {
    if(view === 'week') current.setDate(current.getDate() + 7);
    else current.setMonth(current.getMonth() + 1);
    render();
};
todayBtn.onclick = () => { current = new Date(); render(); };
viewSelect.onchange = () => { view = viewSelect.value; render(); };
statusFilter.onchange = render;
if(cleanerFilter) cleanerFilter.onchange = render;

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// ------------------ MODAL LOGIC ------------------
function openCreate(dateStr) {
    createModal.style.display = 'flex';
    document.getElementById('dateInput').value = dateStr || new Date().toISOString().split('T')[0];
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

addBookingBtn.onclick = () => openCreate();
closeCreate.onclick = () => closeModal('createModal');

function openDetails(b) {
    const cleanName = b.cleaner_name ? b.cleaner_name : 'Unassigned';
    const priceDisplay = b.price ? '₱' + parseFloat(b.price).toFixed(2) : 'N/A';
    const txStatus = transactions[b.booking_id];
    let displayStatus = b.status;
    if (txStatus === 'completed') displayStatus = 'Completed';
    else if (txStatus === 'on the way') displayStatus = 'On the Way';
    
    document.getElementById('detailsBody').innerHTML = `
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-user"></i> Client</span>
            <span class="detail-val">${escapeHtml(b.name)}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-concierge-bell"></i> Service</span>
            <span class="detail-val">${escapeHtml(b.service_name)}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-calendar"></i> Date</span>
            <span class="detail-val">${b.date}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-clock"></i> Time</span>
            <span class="detail-val">${b.time}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Address</span>
            <span class="detail-val">${escapeHtml(b.address)}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-phone"></i> Phone</span>
            <span class="detail-val">${escapeHtml(b.phone)}</span>
        </div>
        ${b.email ? `<div class="detail-row">
            <span class="detail-label"><i class="fas fa-envelope"></i> Email</span>
            <span class="detail-val">${escapeHtml(b.email)}</span>
        </div>` : ''}
        ${hasCleaner ? `<div class="detail-row">
            <span class="detail-label"><i class="fas fa-user-tie"></i> Cleaner</span>
            <span class="detail-val">${escapeHtml(cleanName)}</span>
        </div>` : ''}
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-dollar-sign"></i> Price</span>
            <span class="detail-val">${priceDisplay}</span>
        </div>
        ${b.notes ? `<div class="detail-row">
            <span class="detail-label"><i class="fas fa-sticky-note"></i> Notes</span>
            <span class="detail-val">${escapeHtml(b.notes)}</span>
        </div>` : ''}
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-info-circle"></i> Status</span>
            <span class="detail-val" style="text-transform:capitalize; font-weight: 600;">${escapeHtml(displayStatus)}</span>
        </div>
    `;
    
    document.getElementById('acceptBtn').onclick = () => updateStatus(b.booking_id, 'accepted');
    document.getElementById('declineBtn').onclick = () => updateStatus(b.booking_id, 'declined');
    
    detailsModal.style.display = 'flex';
}
closeDetails.onclick = () => closeModal('detailsModal');

window.onclick = (e) => {
    if(e.target === createModal) closeModal('createModal');
    if(e.target === detailsModal) closeModal('detailsModal');
}

async function updateStatus(id, status) {
    if(!confirm('Change status to ' + status + '?')) return;
    
    const fd = new FormData();
    fd.append('action', 'update_status');
    fd.append('booking_id', id);
    fd.append('status', status);
    
    try {
        await fetch('', { method: 'POST', body: fd });
        location.reload();
    } catch(e) {
        alert('Error updating status');
    }
}

function escapeHtml(text) {
  if (!text) return '';
  return text.toString().replace(/&/g, "&amp;")
    .replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

render();

</script>
</body>
</html>