<?php
session_start();

// ------------------ LOGIN VALIDATION ------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? "Admin";

// ------------------ DATABASE CONNECTION ------------------
$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "quickclean";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ------------------ HANDLE FORM SUBMISSION ------------------
if (isset($_POST['add_cleaner'])) {
    $name = $_POST['cleanerName'];
    $email = $_POST['cleanerEmail'];
    $pass = password_hash($_POST['cleanerPassword'], PASSWORD_DEFAULT);

    // Assuming table 'user' has columns: name, email, password, role, date_created
    $stmt = $conn->prepare("INSERT INTO user (name, email, password, role, date_created) VALUES (?, ?, ?, 'cleaner', NOW())");
    $stmt->bind_param("sss", $name, $email, $pass);
    
    if($stmt->execute()){
        // Success
        header("Location: cleaners.php");
        exit();
    } else {
        $error = "Error adding cleaner.";
    }
    $stmt->close();
}

// ------------------ FETCH CLEANERS ------------------
$cleaners = $conn->query("SELECT * FROM user WHERE role='cleaner' ORDER BY date_created DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cleaner Management - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ------------------ COPIED DASHBOARD CSS ------------------ */
* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --primary: #4F46E5;
    --primary-dark: #4338CA;
    --secondary: #10B981;
    --danger: #EF4444;
    --warning: #F59E0B;
    --dark: #1F2937;
    --light: #F9FAFB;
    --border: #E5E7EB;
    --text-primary: #111827;
    --text-secondary: #6B7280;
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

body { 
    font-family: "Inter", sans-serif; 
    background: var(--light);
    color: var(--text-primary);
    line-height: 1.6;
}

/* Sidebar & Layout */
.sidebar { 
    width: 260px; 
    background: white;
    border-right: 1px solid var(--border);
    display: flex; 
    flex-direction: column; 
    position: fixed; 
    height: 100vh;
    z-index: 1000;
    transition: transform 0.3s ease;
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
.sidebar-menu li a:hover { background: var(--light); color: var(--primary); }
.sidebar-menu li a.active { background: var(--primary); color: white; }
.sidebar-menu li a i { width: 20px; font-size: 18px; }

.main-content { margin-left: 260px; min-height: 100vh; background: var(--light); }
.topbar { 
    background: white; padding: 20px 32px; border-bottom: 1px solid var(--border); 
    display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; 
}
.topbar-left h1 { font-size: 24px; font-weight: 700; color: var(--text-primary); }
.admin-profile { display: flex; align-items: center; gap: 12px; cursor: pointer; }
.admin-profile img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary); }
.admin-profile-info { display: flex; flex-direction: column; }
.admin-profile-info span { font-size: 14px; font-weight: 600; }
.admin-profile-info small { font-size: 12px; color: var(--text-secondary); }

/* Content & Tables */
.content-container { padding: 32px; max-width: 1400px; }
.table-section { 
    background: white; border-radius: 12px; box-shadow: var(--shadow); 
    border: 1px solid var(--border); overflow: hidden; margin-top: 20px;
}
.table-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.table-header h2 { font-size: 18px; font-weight: 600; color: var(--text-primary); }

table { width: 100%; border-collapse: collapse; }
th, td { padding: 16px 24px; text-align: left; border-bottom: 1px solid var(--border); }
th { background: var(--light); color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; }
td { font-size: 14px; color: var(--text-primary); }
tr:last-child td { border-bottom: none; }
tbody tr:hover { background: var(--light); }

/* Buttons */
.btn { 
    padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; 
    font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;
}
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); }
.btn-secondary { background: white; border: 1px solid var(--border); color: var(--text-secondary); }
.btn-secondary:hover { background: var(--light); color: var(--text-primary); }

/* Modal Styling */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.5); z-index: 2000;
    display: none; justify-content: center; align-items: center;
    backdrop-filter: blur(2px);
}
.modal-box {
    background: white; width: 100%; max-width: 450px;
    border-radius: 12px; padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    animation: slideUp 0.3s ease;
}
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.modal-header h3 { font-size: 18px; font-weight: 600; }
.close-modal { cursor: pointer; color: var(--text-secondary); font-size: 20px; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; color: var(--text-secondary); }
.form-control {
    width: 100%; padding: 10px 12px; border-radius: 8px;
    border: 1px solid var(--border); font-family: inherit; font-size: 14px;
    transition: border-color 0.2s;
}
.form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Mobile Toggle */
.menu-toggle { display: none; position: fixed; top: 20px; left: 20px; z-index: 1001; background: white; padding: 10px; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; }

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.active { transform: translateX(0); }
    .main-content { margin-left: 0; }
    .menu-toggle { display: block; }
    .topbar { padding: 16px 20px; }
    .content-container { padding: 20px; }
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
    <li><a href="cleaners.php" class="active"><i class="fas fa-user-tie"></i> Cleaners</a></li>
    <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
    <li><a href="booking.php"><i class="fas fa-clipboard-list"></i> Bookings</a></li>
    <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main-content">

  <div class="topbar">
    <div class="topbar-left">
      <h1>Cleaner Management</h1>
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
    
    <div class="table-section">
      <div class="table-header">
        <h2><i class="fas fa-user-tie"></i> All Cleaners</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Add Cleaner
        </button>
      </div>

      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if($cleaners && $cleaners->num_rows > 0): ?>
            <?php while($c = $cleaners->fetch_assoc()): ?>
              <tr>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><span style="background: #EEF2FF; color: var(--primary); padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">Cleaner</span></td>
                <td>
                    <?php 
                        // Display date if available, else show N/A
                        echo isset($c['date_created']) ? date("M d, Y", strtotime($c['date_created'])) : 'N/A'; 
                    ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align: center; color: var(--text-secondary);">No cleaners found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<div class="modal-overlay" id="cleanerModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Cleaner</h3>
            <i class="fas fa-times close-modal" onclick="closeModal()"></i>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="cleanerName" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="cleanerEmail" class="form-control" placeholder="Enter email address" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="cleanerPassword" class="form-control" placeholder="Create password" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" name="add_cleaner" class="btn btn-primary">Save Cleaner</button>
            </div>
        </form>
    </div>
</div>

<script>
// Sidebar Toggle (Mobile)
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});

// Modal Logic
function openModal() {
    document.getElementById("cleanerModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("cleanerModal").style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("cleanerModal");
    if (event.target == modal) {
        closeModal();
    }
}
</script>

</body>
</html>