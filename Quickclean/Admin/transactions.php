<?php
session_start();

// ------------------ LOGIN VALIDATION ------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? "Admin";

// ------------------ DATABASE CONNECTION ------------------
$conn = new mysqli("localhost", "root", "", "quickclean");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ------------------ FETCH ACTIVE TRANSACTIONS ------------------
$sql = "SELECT * FROM transactions WHERE status='on the way' ORDER BY action_date DESC";
$result = $conn->query($sql);
$total_active = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transactions - QuickClean</title>
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

    /* Stats Card */
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

    .stat-icon.active {
        background: #FEF3C7;
        color: var(--warning);
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

    /* Search Bar */
    .search-bar {
        background: white;
        padding: 20px 24px;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        margin-bottom: 24px;
    }

    .search-box {
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

    /* Transactions Grid */
    .transactions-grid {
        display: grid;
        gap: 20px;
    }

    .transaction-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .transaction-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .transaction-header {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }

    .transaction-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .transaction-icon {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--warning);
    }

    .transaction-info h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .transaction-info p {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        background: var(--warning);
        color: white;
    }

    .status-badge i {
        font-size: 12px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .transaction-body {
        padding: 24px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
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

    .transaction-footer {
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

    .btn-complete {
        background: #D1FAE5;
        color: var(--secondary);
    }

    .btn-complete:hover {
        background: var(--secondary);
        color: white;
    }

    /* Empty State */
    .no-transactions {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 64px 24px;
        text-align: center;
    }

    .no-transactions i {
        font-size: 64px;
        color: var(--text-secondary);
        opacity: 0.3;
        margin-bottom: 16px;
    }

    .no-transactions h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .no-transactions p {
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

        .transaction-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .transaction-footer {
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

    .transaction-card {
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
    <li><a href="transactions.php" class="active"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-left">
      <h1><i class="fas fa-truck"></i> Active Transactions</h1>
      <p>Monitor ongoing deliveries and services</p>
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
        <div class="stat-icon active">
          <i class="fas fa-shipping-fast"></i>
        </div>
        <div class="stat-info">
          <h3>Active Transactions</h3>
          <p><?= number_format($total_active) ?></p>
        </div>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search by customer name, service, or address...">
      </div>
    </div>

    <!-- Transactions Grid -->
    <div class="transactions-grid">
      <?php
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "
          <div class='transaction-card' data-customer='" . strtolower($row['customer_name']) . "' data-service='" . strtolower($row['service_name']) . "' data-address='" . strtolower($row['address']) . "'>
            <div class='transaction-header'>
              <div class='transaction-title'>
                <div class='transaction-icon'>
                  <i class='fas fa-broom'></i>
                </div>
                <div class='transaction-info'>
                  <h3>{$row['customer_name']}</h3>
                  <p>{$row['service_name']}</p>
                </div>
              </div>
              <div class='status-badge'>
                <i class='fas fa-circle'></i>
                On the Way
              </div>
            </div>
            
            <div class='transaction-body'>
              <div class='detail-grid'>
                <div class='detail-item'>
                  <div class='detail-icon'>
                    <i class='fas fa-calendar'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Date</div>
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
                    <i class='fas fa-phone'></i>
                  </div>
                  <div class='detail-content'>
                    <div class='detail-label'>Contact</div>
                    <div class='detail-value'>{$row['phone']}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class='transaction-footer'>
              <div class='footer-time'>
                <i class='fas fa-clock'></i>
                Updated " . date('M d, Y - h:i A', strtotime($row['action_date'])) . "
              </div>
              <div class='action-buttons'>
                <button class='btn btn-view' title='View Details'>
                  <i class='fas fa-eye'></i> View
                </button>
                <button class='btn btn-complete' title='Mark as Complete'>
                  <i class='fas fa-check'></i> Complete
                </button>
              </div>
            </div>
          </div>
          ";
        }
      } else {
        echo "
        <div class='no-transactions'>
          <i class='fas fa-truck'></i>
          <h3>No Active Transactions</h3>
          <p>All transactions have been completed or there are no ongoing deliveries at the moment.</p>
        </div>
        ";
      }
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
    const cards = document.querySelectorAll('.transaction-card');
    
    cards.forEach(card => {
        const customer = card.dataset.customer;
        const service = card.dataset.service;
        const address = card.dataset.address;
        
        if (customer.includes(keyword) || service.includes(keyword) || address.includes(keyword)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Action Buttons (Placeholder functionality)
document.querySelectorAll('.btn-view').forEach(btn => {
    btn.addEventListener('click', function() {
        alert('View transaction details functionality coming soon!');
    });
});

document.querySelectorAll('.btn-complete').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('Mark this transaction as complete?')) {
            alert('Complete transaction functionality coming soon!');
        }
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>