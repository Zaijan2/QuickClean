<?php  
session_start();

// ------------------ LOGIN VALIDATION ------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? "Admin";

// ------------------ DATABASE CONNECTION ------------------
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "quickclean";    

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ------------------ FETCH COMPLETED TRANSACTIONS WITH BOOKING INFO ------------------
$sql = "
    SELECT t.transaction_id, t.booking_id, t.customer_name, t.service_name, t.date, t.time, t.address, t.phone, t.email, t.status, t.action_date,
           b.extras, b.notes, b.price
    FROM transactions t
    LEFT JOIN bookings b ON t.booking_id = b.booking_id
    WHERE t.status = 'completed'
    ORDER BY t.action_date DESC
";

$result = $conn->query($sql);
$total_completed = $result->num_rows;

// Calculate total revenue from completed transactions
$revenue_sql = "SELECT SUM(b.price) as total_revenue FROM transactions t LEFT JOIN bookings b ON t.booking_id = b.booking_id WHERE t.status = 'completed'";
$revenue_result = $conn->query($revenue_sql);
$total_revenue = $revenue_result->fetch_assoc()['total_revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Completed History - QuickClean</title>
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

/* Stats Row */
.stats-row {
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

.stat-icon.completed {
    background: #D1FAE5;
    color: var(--secondary);
}

.stat-icon.revenue {
    background: #DBEAFE;
    color: var(--info);
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

/* Filter Bar */
.filter-bar {
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

.filter-select {
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
}

/* History Cards */
.history-grid {
    display: grid;
    gap: 20px;
}

.history-card {
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: all 0.3s ease;
}

.history-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.history-header {
    background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
    padding: 20px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border);
}

.history-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.history-icon {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--secondary);
}

.history-info h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 2px;
}

.history-info p {
    font-size: 13px;
    color: var(--text-secondary);
}

.history-ids {
    text-align: right;
}

.history-ids .label {
    font-size: 11px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.history-ids .value {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
}

.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--secondary);
    color: white;
}

.history-body {
    padding: 24px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: start;
    gap: 12px;
}

.detail-icon {
    width: 40px;
    height: 40px;
    background: var(--light);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: var(--primary);
    flex-shrink: 0;
}

.detail-content {
    flex: 1;
}

.detail-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.detail-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
}

.additional-info {
    background: var(--light);
    padding: 16px;
    border-radius: 8px;
    margin-top: 16px;
}

.additional-info h4 {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 13px;
    color: var(--text-secondary);
    font-weight: 500;
}

.info-value {
    font-size: 13px;
    color: var(--text-primary);
    font-weight: 600;
}

.history-footer {
    padding: 16px 24px;
    background: var(--light);
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-time {
    font-size: 13px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-view {
    background: #EEF2FF;
    color: var(--primary);
}

.btn-view:hover {
    background: var(--primary);
    color: white;
}

.btn-print {
    background: #F3F4F6;
    color: var(--text-primary);
}

.btn-print:hover {
    background: var(--text-primary);
    color: white;
}

/* Empty State */
.no-history {
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border);
    padding: 64px 24px;
    text-align: center;
}

.no-history i {
    font-size: 64px;
    color: var(--text-secondary);
    opacity: 0.3;
    margin-bottom: 16px;
}

.no-history h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.no-history p {
    font-size: 14px;
    color: var(--text-secondary);
}

/* Responsive */
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

    .history-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .history-footer {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }

    .action-buttons {
        width: 100%;
    }

    .btn {
        flex: 1;
        justify-content: center;
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

.history-card {
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
    <li><a href="customers.php"><i class="fas fa-users"></i> Users</a></li>
    <li><a href="services.php"><i class="fas fa-concierge-bell"></i> Services</a></li>
    <li><a href="cleaners.php"><i class="fas fa-user-tie"></i> Cleaners</a></li>
    <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
    <li><a href="booking.php"><i class="fas fa-clipboard-list"></i> Bookings</a></li>
    <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
    <li><a href="history.php" class="active"><i class="fas fa-history"></i> History</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-left">
      <h1><i class="fas fa-check-circle"></i> Completed History</h1>
      <p>View all completed transactions and bookings</p>
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

    <!-- Stats Row -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon completed">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <h3>Completed Transactions</h3>
          <p><?= number_format($total_completed) ?></p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon revenue">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
          <h3>Total Revenue</h3>
          <p>₱<?= number_format($total_revenue, 2) ?></p>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search by customer, service, or transaction ID...">
      </div>
      <select class="filter-select" id="sortFilter">
        <option value="recent">Most Recent</option>
        <option value="oldest">Oldest First</option>
        <option value="price-high">Price: High to Low</option>
        <option value="price-low">Price: Low to High</option>
      </select>
    </div>

    <!-- History Grid -->
    <div class="history-grid">
      <?php
      if ($result && $result->num_rows > 0) {
        $result->data_seek(0); // Reset pointer
        while ($row = $result->fetch_assoc()) {
          $extras = $row['extras'] ? $row['extras'] : 'None';
          $notes = $row['notes'] ? $row['notes'] : 'No notes';
          $price = $row['price'] ? number_format($row['price'], 2) : '0.00';
          
          echo "
          <div class='history-card' 
               data-customer='" . strtolower($row['customer_name']) . "' 
               data-service='" . strtolower($row['service_name']) . "'
               data-transaction='" . $row['transaction_id'] . "'
               data-price='" . $row['price'] . "'
               data-date='" . strtotime($row['action_date']) . "'>
            
            <div class='history-header'>
              <div class='history-title'>
                <div class='history-icon'>
                  <i class='fas fa-broom'></i>
                </div>
                <div class='history-info'>
                  <h3>{$row['customer_name']}</h3>
                  <p>{$row['service_name']}</p>
                </div>
              </div>
              <div class='history-ids'>
                <div class='label'>Transaction ID</div>
                <div class='value'>#{$row['transaction_id']}</div>
                <div class='label' style='margin-top: 4px;'>Booking ID</div>
                <div class='value'>#{$row['booking_id']}</div>
              </div>
            </div>
            
            <div class='history-body'>
              <div class='detail-grid'>
                <div class='detail-item'>
                  <div class='detail-icon'>
                    <i class='fas fa-dollar-sign'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Price</div>
                    <div class='detail-value'>₱{$price}</div>
                  </div>
                </div>
                
                <div class='detail-item'>
                  <div class='detail-icon'>
                    <i class='fas fa-calendar'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Service Date</div>
                    <div class='detail-value'>" . date('M d, Y', strtotime($row['date'])) . "</div>
                  </div>
                </div>
                
                <div class='detail-item'>
                  <div class='detail-icon'>
                    <i class='fas fa-clock'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Time</div>
                    <div class='detail-value'>{$row['time']}</div>
                  </div>
                </div>
                
                <div class='detail-item'>
                  <div class='detail-icon'>
                    <i class='fas fa-map-marker-alt'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Address</div>
                    <div class='detail-value'>{$row['address']}</div>
                  </div>
                </div>
                
                <div class='detail-item'>
                  <div class='detail-icon'>
                    <i class='fas fa-envelope'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Email</div>
                    <div class='detail-value'>{$row['email']}</div>
                  </div>
                </div>
                
                <div class='detail-item'>
                  <div class='detail-icon'>
                    <i class='fas fa-phone'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Phone</div>
                    <div class='detail-value'>{$row['phone']}</div>
                  </div>
                </div>
              </div>
              
              <div class='additional-info'>
                <h4><i class='fas fa-info-circle'></i> Additional Details</h4>
                <div class='info-row'>
                  <span class='info-label'>Extras:</span>
                  <span class='info-value'>{$extras}</span>
                </div>
                <div class='info-row'>
                  <span class='info-label'>Notes:</span>
                  <span class='info-value'>{$notes}</span>
                </div>
                <div class='info-row'>
                  <span class='info-label'>Status:</span>
                  <span class='info-value' style='color: var(--secondary);'>
                    <i class='fas fa-check-circle'></i> Completed
                  </span>
                </div>
              </div>
            </div>
            
            <div class='history-footer'>
              <div class='footer-time'>
                <i class='fas fa-calendar-check'></i>
                Completed on " . date('M d, Y - h:i A', strtotime($row['action_date'])) . "
              </div>
              <div class='action-buttons'>
                <button class='btn btn-view' title='View Full Details'>
                  <i class='fas fa-eye'></i> View
                </button>
                <button class='btn btn-print' title='Print Receipt'>
                  <i class='fas fa-print'></i> Print
                </button>
              </div>
            </div>
          </div>
          ";
        }
      } else {
        echo "
        <div class='no-history'>
          <i class='fas fa-history'></i>
          <h3>No Completed Transactions</h3>
          <p>Completed transactions will appear here once services are finished.</p>
        </div>
        ";
      }
      $conn->close();
      ?>
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

// Search Functionality
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', function() {
    const keyword = this.value.toLowerCase();
    const cards = document.querySelectorAll('.history-card');
    
    cards.forEach(card => {
        const customer = card.dataset.customer;
        const service = card.dataset.service;
        const transaction = card.dataset.transaction;
        
        if (customer.includes(keyword) || service.includes(keyword) || transaction.includes(keyword)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Sort Functionality
const sortFilter = document.getElementById('sortFilter');
sortFilter.addEventListener('change', function() {
    const grid = document.querySelector('.history-grid');
    const cards = Array.from(document.querySelectorAll('.history-card'));
    
    cards.sort((a, b) => {
        switch(this.value) {
            case 'recent':
                return parseInt(b.dataset.date) - parseInt(a.dataset.date);
            case 'oldest':
                return parseInt(a.dataset.date) - parseInt(b.dataset.date);
            case 'price-high':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'price-low':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            default:
                return 0;
        }
    });
    
    // Clear and re-append sorted cards
    grid.innerHTML = '';
    cards.forEach(card => grid.appendChild(card));
});

// Action Buttons (Placeholder functionality)
document.querySelectorAll('.btn-view').forEach(btn => {
    btn.addEventListener('click', function() {
        alert('View full transaction details functionality coming soon!');
    });
});

document.querySelectorAll('.btn-print').forEach(btn => {
    btn.addEventListener('click', function() {
        alert('Print receipt functionality coming soon!');
    });
});
</script>

</body>
</html>