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

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ------------------ FETCH DATA FOR DASHBOARD ------------------

// Total users
$total_users = $conn->query("SELECT COUNT(*) AS total FROM user")->fetch_assoc()['total'] ?? 0;

// Total cleaners
$total_cleaners = $conn->query("SELECT COUNT(*) AS total FROM user WHERE role='cleaner'")->fetch_assoc()['total'] ?? 0;

// Total services
$total_services = $conn->query("SELECT COUNT(*) AS total FROM services")->fetch_assoc()['total'] ?? 0;

// Total bookings
$total_bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'] ?? 0;

// Total revenue
$total_revenue = $conn->query("SELECT SUM(amount) AS total FROM payments")->fetch_assoc()['total'] ?? 0;

// Yearly revenue percentage
$yearly_target = 1000000; // ₱1,000,000
$yearly_percentage = min(($total_revenue / $yearly_target) * 100, 100);

// Weekly revenue (current week)
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end   = date('Y-m-d', strtotime('sunday this week'));
$weekly_result = $conn->query("
    SELECT SUM(amount) AS total 
    FROM payments 
    WHERE DATE(payment_date) BETWEEN '$week_start' AND '$week_end'
");
$weekly_revenue = $weekly_result->fetch_assoc()['total'] ?? 0;
$weekly_goal = 21000; // target per week
$weekly_percentage = ($weekly_revenue / $weekly_goal) * 100;

// Monthly revenue (current month)
$current_month = date('m');
$current_year  = date('Y');
$monthly_result = $conn->query("
    SELECT SUM(amount) AS total 
    FROM payments 
    WHERE MONTH(payment_date)=$current_month AND YEAR(payment_date)=$current_year
");
$monthly_revenue = $monthly_result->fetch_assoc()['total'] ?? 0;
$monthly_goal = 85000; // target per month
$monthly_percentage = ($monthly_revenue / $monthly_goal) * 100;

// Yearly revenue
$yearly_result = $conn->query("
    SELECT SUM(amount) AS total 
    FROM payments 
    WHERE YEAR(payment_date)=$current_year
");
$yearly_revenue = $yearly_result->fetch_assoc()['total'] ?? 0;
$yearly_goal = 1000000; // target per year
$yearly_percentage = ($yearly_revenue / $yearly_goal) * 100;

// Ensure percentages don't exceed 100%
$weekly_percentage  = min($weekly_percentage, 100);
$monthly_percentage = min($monthly_percentage, 100);
$yearly_percentage  = min($yearly_percentage, 100);


// Most booked services
$popular_services = $conn->query("
    SELECT s.service_name, COUNT(b.booking_id) AS count_booked
    FROM services s
    LEFT JOIN bookings b ON s.service_id = b.service_id
    GROUP BY s.service_id
    ORDER BY count_booked DESC
    LIMIT 5
");

// Recent users
$recent_users = $conn->query("SELECT name, email, date_created FROM user ORDER BY date_created DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}

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
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
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
    width: 260px; 
    background: white;
    border-right: 1px solid var(--border);
    display: flex; 
    flex-direction: column; 
    position: fixed; 
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.sidebar-header {
    padding: 24px 20px;
    border-bottom: 1px solid var(--border);
}

.sidebar-header h2 { 
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-header h2 i {
    font-size: 28px;
}

.sidebar-menu { 
    list-style: none; 
    padding: 16px 12px;
    flex: 1;
}

.sidebar-menu li { 
    margin-bottom: 4px;
}

.sidebar-menu li a { 
    text-decoration: none; 
    color: var(--text-secondary);
    font-size: 15px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.sidebar-menu li a i {
    width: 20px;
    font-size: 18px;
}

.sidebar-menu li a:hover { 
    background: var(--light);
    color: var(--primary);
}

.sidebar-menu li a.active { 
    background: var(--primary);
    color: white;
}

/* Mobile menu toggle */
.menu-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 12px;
    cursor: pointer;
    box-shadow: var(--shadow);
}

.menu-toggle i {
    font-size: 20px;
    color: var(--text-primary);
}

/* Main Content */
.main-content { 
    margin-left: 260px; 
    min-height: 100vh;
    background: var(--light);
}

/* Topbar */
.topbar { 
    background: white;
    padding: 20px 32px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
}

.topbar-left h1 { 
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.topbar-left p {
    font-size: 14px;
    color: var(--text-secondary);
}

.admin-profile { 
    display: flex; 
    align-items: center; 
    gap: 12px;
    padding: 8px 16px;
    background: var(--light);
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.admin-profile:hover {
    background: var(--border);
}

.admin-profile img { 
    width: 40px; 
    height: 40px; 
    border-radius: 50%;
    border: 2px solid var(--primary);
}

.admin-profile-info {
    display: flex;
    flex-direction: column;
}

.admin-profile-info span {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
}

.admin-profile-info small {
    font-size: 12px;
    color: var(--text-secondary);
}

/* Content Container */
.content-container {
    padding: 32px;
    max-width: 1400px;
}

/* Dashboard Cards */
.cards-section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 20px;
}

.cards { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); 
    gap: 20px;
}

.card { 
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.card-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.card-trend {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 600;
}

.card-trend.up {
    background: #DCFCE7;
    color: #16A34A;
}

.card-trend.down {
    background: #FEE2E2;
    color: #DC2626;
}

.card-body h3 { 
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.card-body p { 
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 12px;
}

/* Card Color Variations */
.card-users .card-icon { background: #EEF2FF; color: var(--primary); }
.card-cleaners .card-icon { background: #D1FAE5; color: var(--secondary); }
.card-services .card-icon { background: #FEF3C7; color: var(--warning); }
.card-messages .card-icon { background: #E0E7FF; color: #8B5CF6; }
.card-revenue .card-icon { background: #FEE2E2; color: var(--danger); }
.card-yearly .card-icon { background: #D1FAE5; color: #059669; }
.card-monthly .card-icon { background: #DBEAFE; color: var(--info); }
.card-weekly .card-icon { background: #EDE9FE; color: #7C3AED; }

/* Progress Bar */
.progress-container {
    margin-top: 12px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.progress-label {
    font-size: 12px;
    color: var(--text-secondary);
    font-weight: 500;
}

.progress-value {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}

.progress-bar { 
    background: var(--light);
    border-radius: 10px;
    overflow: hidden;
    height: 8px;
    position: relative;
}

.progress-fill { 
    height: 100%;
    border-radius: 10px;
    transition: width 0.8s ease;
    position: relative;
}

.card-yearly .progress-fill { background: linear-gradient(90deg, #059669, #10B981); }
.card-monthly .progress-fill { background: linear-gradient(90deg, #2563EB, #3B82F6); }
.card-weekly .progress-fill { background: linear-gradient(90deg, #6D28D9, #8B5CF6); }

/* Table Section */
.table-section { 
    margin-bottom: 32px;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

.table-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
}

.table-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

table { 
    width: 100%; 
    border-collapse: collapse;
}

th, td { 
    padding: 16px 24px;
    text-align: left;
}

th { 
    background: var(--light);
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border);
}

td {
    font-size: 14px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border);
}

tr:last-child td {
    border-bottom: none;
}

tbody tr { 
    transition: background 0.2s ease;
}

tbody tr:hover { 
    background: var(--light);
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.badge-primary {
    background: #EEF2FF;
    color: var(--primary);
}

/* Responsive */
@media (max-width: 1024px) {
    .cards {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
}

@media (max-width: 768px) {
    .menu-toggle {
        display: block;
    }

    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }

    .topbar {
        padding: 16px 20px;
    }

    .content-container {
        padding: 20px;
    }

    .cards {
        grid-template-columns: 1fr;
    }

    .admin-profile-info {
        display: none;
    }

    th, td {
        padding: 12px 16px;
    }
}

@media (max-width: 480px) {
    .topbar-left h1 {
        font-size: 20px;
    }

    .card-body p {
        font-size: 24px;
    }

    table {
        font-size: 13px;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card, .table-section {
    animation: fadeIn 0.5s ease;
}
</style>
</head>
<body>

<!-- Mobile Menu Toggle -->
<div class="menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2><i class="fas fa-sparkles"></i> QuickClean</h2>
  </div>
  <ul class="sidebar-menu">
    <li><a href="admindashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="customers.php"><i class="fas fa-users"></i> Users</a></li>
    <li><a href="services.php"><i class="fas fa-concierge-bell"></i> Services</a></li>
    <li><a href="cleaners.php"><i class="fas fa-user-tie"></i> Cleaners</a></li>
    <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
    <li><a href="booking.php"><i class="fas fa-clipboard-list"></i> Bookings</a></li>
    <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-left">
      <h1>Dashboard Overview</h1>
      <p>Welcome back, <?= htmlspecialchars($admin_name) ?>!</p>
    </div>
    <div class="admin-profile">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin">
      <div class="admin-profile-info">
        <span><?= htmlspecialchars($admin_name) ?></span>
        <small>Administrator</small>
      </div>
    </div>
  </div>

  <!-- Content Container -->
  <div class="content-container">

    <!-- Quick Stats Cards -->
    <div class="cards-section">
      <h2 class="section-title">Quick Statistics</h2>
      <div class="cards">
        <div class="card card-users">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-users"></i>
            </div>
            <span class="card-trend up"><i class="fas fa-arrow-up"></i> 12%</span>
          </div>
          <div class="card-body">
            <h3>Total Users</h3>
            <p><?= number_format($total_users) ?></p>
          </div>
        </div>

        <div class="card card-cleaners">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-user-tie"></i>
            </div>
            <span class="card-trend up"><i class="fas fa-arrow-up"></i> 8%</span>
          </div>
          <div class="card-body">
            <h3>Active Cleaners</h3>
            <p><?= number_format($total_cleaners) ?></p>
          </div>
        </div>

        <div class="card card-services">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-concierge-bell"></i>
            </div>
          </div>
          <div class="card-body">
            <h3>Total Services</h3>
            <p><?= number_format($total_services) ?></p>
          </div>
        </div>

        <div class="card card-messages">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <span class="card-trend up"><i class="fas fa-arrow-up"></i> 24%</span>
          </div>
          <div class="card-body">
            <h3>Bookings</h3>
            <p><?= number_format($total_bookings) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Revenue Cards -->
    <div class="cards-section">
      <h2 class="section-title">Revenue Analytics</h2>
      <div class="cards">
        <div class="card card-revenue">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-dollar-sign"></i>
            </div>
          </div>
          <div class="card-body">
            <h3>Total Revenue</h3>
            <p>₱<?= number_format($total_revenue, 2) ?></p>
          </div>
        </div>

        <div class="card card-yearly">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-chart-line"></i>
            </div>
          </div>
          <div class="card-body">
            <h3>Yearly Target</h3>
            <p>₱<?= number_format($yearly_revenue, 2) ?></p>
            <div class="progress-container">
              <div class="progress-info">
                <span class="progress-label">Goal: ₱<?= number_format($yearly_goal, 2) ?></span>
                <span class="progress-value"><?= number_format($yearly_percentage, 1) ?>%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $yearly_percentage ?>%"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card card-monthly">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-calendar"></i>
            </div>
          </div>
          <div class="card-body">
            <h3>Monthly Revenue</h3>
            <p>₱<?= number_format($monthly_revenue, 2) ?></p>
            <div class="progress-container">
              <div class="progress-info">
                <span class="progress-label">Goal: ₱<?= number_format($monthly_goal, 2) ?></span>
                <span class="progress-value"><?= number_format($monthly_percentage, 1) ?>%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $monthly_percentage ?>%"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card card-weekly">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-calendar-week"></i>
            </div>
          </div>
          <div class="card-body">
            <h3>Weekly Revenue</h3>
            <p>₱<?= number_format($weekly_revenue, 2) ?></p>
            <div class="progress-container">
              <div class="progress-info">
                <span class="progress-label">Goal: ₱<?= number_format($weekly_goal, 2) ?></span>
                <span class="progress-value"><?= number_format($weekly_percentage, 1) ?>%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $weekly_percentage ?>%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Most Popular Services -->
    <div class="table-section">
      <div class="table-header">
        <h2><i class="fas fa-star"></i> Most Booked Services</h2>
      </div>
      <table>
        <thead>
          <tr>
            <th>Service Name</th>
            <th>Times Booked</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if($popular_services && $popular_services->num_rows > 0): ?>
            <?php while($service = $popular_services->fetch_assoc()): ?>
              <tr>
                <td><strong><?= htmlspecialchars($service['service_name']) ?></strong></td>
                <td><?= number_format($service['count_booked']) ?> bookings</td>
                <td><span class="badge badge-primary">Active</span></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3" style="text-align: center; color: var(--text-secondary);">No services found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Recent Users -->
    <div class="table-section">
      <div class="table-header">
        <h2><i class="fas fa-user-plus"></i> Recent Users</h2>
      </div>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Joined Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recent_users && $recent_users->num_rows > 0): ?>
            <?php while ($user = $recent_users->fetch_assoc()): ?>
              <tr>
                <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= date("M d, Y", strtotime($user['date_created'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3" style="text-align: center; color: var(--text-secondary);">No recent users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>

</body>
</html>