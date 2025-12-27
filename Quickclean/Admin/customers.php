<?php 
session_start();

// ------------------ LOGIN VALIDATION ------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? "Admin";

// ------------------ DB CONNECTION ------------------
$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management - QuickClean</title>
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
    display: flex;
    align-items: center;
    gap: 10px;
}

.topbar-left h1 i {
    color: var(--primary);
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

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-icon.customers {
    background: #EEF2FF;
    color: var(--primary);
}

.stat-icon.cleaners {
    background: #D1FAE5;
    color: var(--secondary);
}

.stat-info h3 {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 4px;
}

.stat-info p {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
}

/* Search & Filter Bar */
.search-filter-bar {
    background: white;
    padding: 20px 24px;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    margin-bottom: 24px;
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    font-size: 16px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.filter-tabs {
    display: flex;
    gap: 8px;
}

.filter-tab {
    padding: 8px 16px;
    border: 1px solid var(--border);
    background: white;
    color: var(--text-secondary);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-tab:hover {
    background: var(--light);
}

.filter-tab.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Section */
.section { 
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h2 i {
    font-size: 20px;
}

.user-count {
    background: var(--light);
    color: var(--text-secondary);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

/* Table */
.table-container {
    overflow-x: auto;
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

tbody tr { 
    transition: background 0.2s ease;
}

tbody tr:hover { 
    background: var(--light);
}

tbody tr:last-child td {
    border-bottom: none;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.user-avatar.cleaner {
    background: var(--secondary);
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 2px;
}

.user-email {
    font-size: 13px;
    color: var(--text-secondary);
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.badge-customer {
    background: #EEF2FF;
    color: var(--primary);
}

.badge-cleaner {
    background: #D1FAE5;
    color: var(--secondary);
}

.no-data {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-secondary);
}

.no-data i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.no-data p {
    font-size: 16px;
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

    .admin-profile-info {
        display: none;
    }

    .search-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-tabs {
        width: 100%;
        justify-content: stretch;
    }

    .filter-tab {
        flex: 1;
        text-align: center;
    }

    table {
        font-size: 13px;
    }

    th, td {
        padding: 12px 16px;
    }

    .user-info {
        flex-direction: column;
        align-items: flex-start;
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

.section {
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
    <li><a href="admindashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Users</a></li>
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
      <h1><i class="fas fa-users"></i> User Management</h1>
      <p>Manage customers and cleaners</p>
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

    <!-- Stats Cards -->
    <div class="stats-cards">
      <?php
      $customer_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE role='customer'")->fetch_assoc()['total'];
      $cleaner_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE role='cleaner'")->fetch_assoc()['total'];
      ?>
      <div class="stat-card">
        <div class="stat-icon customers">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
          <h3>Total Customers</h3>
          <p><?= number_format($customer_count) ?></p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon cleaners">
          <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-info">
          <h3>Total Cleaners</h3>
          <p><?= number_format($cleaner_count) ?></p>
        </div>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search by name, email, or contact...">
      </div>
      <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterUsers('all')">All Users</button>
        <button class="filter-tab" onclick="filterUsers('customer')">Customers</button>
        <button class="filter-tab" onclick="filterUsers('cleaner')">Cleaners</button>
      </div>
    </div>

    <!-- Customers Section -->
    <div class="section" id="customerSection">
      <div class="section-header">
        <h2><i class="fas fa-user"></i> Customers</h2>
        <span class="user-count" id="customerCount"><?= $customer_count ?> users</span>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Role</th>
              <th>Joined Date</th>
            </tr>
          </thead>
          <tbody id="customerTable">
            <?php
            $customer_result = mysqli_query($conn, "SELECT * FROM user WHERE role='customer' ORDER BY name");
            if(mysqli_num_rows($customer_result) > 0) {
                while ($row = mysqli_fetch_assoc($customer_result)) {
                    $initials = strtoupper(substr($row['name'], 0, 1));
                    $date = date("M d, Y", strtotime($row['date_created']));
                    echo "<tr>
                            <td>
                              <div class='user-info'>
                                <div class='user-avatar'>{$initials}</div>
                                <div class='user-details'>
                                  <span class='user-name'>{$row['name']}</span>
                                  <span class='user-email'>{$row['email']}</span>
                                </div>
                              </div>
                            </td>
                            <td>{$row['contact_num']}</td>
                            <td>{$row['address']}</td>
                            <td><span class='badge badge-customer'>Customer</span></td>
                            <td>{$date}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'><div class='no-data'><i class='fas fa-users'></i><p>No customers found</p></div></td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Cleaners Section -->
    <div class="section" id="cleanerSection">
      <div class="section-header">
        <h2><i class="fas fa-user-tie"></i> Cleaners</h2>
        <span class="user-count" id="cleanerCount"><?= $cleaner_count ?> users</span>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Role</th>
              <th>Joined Date</th>
            </tr>
          </thead>
          <tbody id="cleanerTable">
            <?php
            $cleaner_result = mysqli_query($conn, "SELECT * FROM user WHERE role='cleaner' ORDER BY name");
            if(mysqli_num_rows($cleaner_result) > 0) {
                while ($row = mysqli_fetch_assoc($cleaner_result)) {
                    $initials = strtoupper(substr($row['name'], 0, 1));
                    $date = date("M d, Y", strtotime($row['date_created']));
                    echo "<tr>
                            <td>
                              <div class='user-info'>
                                <div class='user-avatar cleaner'>{$initials}</div>
                                <div class='user-details'>
                                  <span class='user-name'>{$row['name']}</span>
                                  <span class='user-email'>{$row['email']}</span>
                                </div>
                              </div>
                            </td>
                            <td>{$row['contact_num']}</td>
                            <td>{$row['address']}</td>
                            <td><span class='badge badge-cleaner'>Cleaner</span></td>
                            <td>{$date}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'><div class='no-data'><i class='fas fa-user-tie'></i><p>No cleaners found</p></div></td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
// Toggle Sidebar
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

// Search functionality
const searchInput = document.getElementById("searchInput");
searchInput.addEventListener("input", function() {
    const keyword = this.value.toLowerCase();
    
    // Filter customer table
    let visibleCustomers = 0;
    document.querySelectorAll("#customerTable tr").forEach(row => {
        const name = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
        const email = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
        const contact = row.cells[1]?.textContent.toLowerCase() || '';
        
        const isVisible = name.includes(keyword) || email.includes(keyword) || contact.includes(keyword);
        row.style.display = isVisible ? "" : "none";
        if (isVisible && !row.querySelector('.no-data')) visibleCustomers++;
    });
    document.getElementById('customerCount').textContent = visibleCustomers + ' users';

    // Filter cleaner table
    let visibleCleaners = 0;
    document.querySelectorAll("#cleanerTable tr").forEach(row => {
        const name = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
        const email = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
        const contact = row.cells[1]?.textContent.toLowerCase() || '';
        
        const isVisible = name.includes(keyword) || email.includes(keyword) || contact.includes(keyword);
        row.style.display = isVisible ? "" : "none";
        if (isVisible && !row.querySelector('.no-data')) visibleCleaners++;
    });
    document.getElementById('cleanerCount').textContent = visibleCleaners + ' users';
});

// Filter by role
function filterUsers(role) {
    const customerSection = document.getElementById('customerSection');
    const cleanerSection = document.getElementById('cleanerSection');
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    // Update active tab
    filterTabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Show/hide sections
    if (role === 'all') {
        customerSection.style.display = 'block';
        cleanerSection.style.display = 'block';
    } else if (role === 'customer') {
        customerSection.style.display = 'block';
        cleanerSection.style.display = 'none';
    } else if (role === 'cleaner') {
        customerSection.style.display = 'none';
        cleanerSection.style.display = 'block';
    }
}
</script>

</body>
</html>