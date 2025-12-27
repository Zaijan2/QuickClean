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

// ------------------ FETCH BOOKINGS ------------------
$sql = "SELECT * FROM bookings ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bookings Management - QuickClean</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* ------------------ SHARED DESIGN SYSTEM ------------------ */
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

    /* Main Content */
    .main-content { margin-left: 260px; min-height: 100vh; background: var(--light); }
    
    /* Topbar */
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

    /* Content & Table */
    .content-container { padding: 32px; max-width: 1400px; }
    
    .table-section { 
        background: white; border-radius: 12px; box-shadow: var(--shadow); 
        border: 1px solid var(--border); overflow: hidden; 
    }
    .table-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .table-header h2 { font-size: 18px; font-weight: 600; color: var(--text-primary); }

    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th, td { padding: 16px 24px; text-align: left; border-bottom: 1px solid var(--border); }
    th { background: var(--light); color: var(--text-secondary); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    td { font-size: 14px; color: var(--text-primary); }
    tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--light); }

    /* Status Badges */
    .badge { padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; display: inline-block; }
    .status-pending { background: #FEF3C7; color: #D97706; } /* Yellow */
    .status-approved, .status-accepted { background: #D1FAE5; color: #059669; } /* Green */
    .status-declined { background: #FEE2E2; color: #DC2626; } /* Red */
    .status-completed { background: #DBEAFE; color: #1D4ED8; } /* Blue */
    .status-ontheway { background: #FFEDD5; color: #C2410C; } /* Orange */

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
        th, td { padding: 12px 16px; }
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
    <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
    <li><a href="booking.php" class="active"><i class="fas fa-clipboard-list"></i> Bookings</a></li>
    <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main-content">

  <div class="topbar">
    <div class="topbar-left">
      <h1>Booking Management</h1>
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
        <h2><i class="fas fa-list"></i> All Bookings List</h2>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Customer</th>
              <th>Service Details</th>
              <th>Schedule</th>
              <th>Contact Info</th>
              <th>Location</th>
              <th>Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                    // Normalize status string for CSS class
                    $statusRaw = strtolower(trim($row['status']));
                    $statusClass = 'status-' . str_replace(' ', '', $statusRaw); // e.g. "on the way" -> "status-ontheway"
                ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                  </td>
                  <td>
                    <?= htmlspecialchars($row['service_name']) ?>
                    <?php if(!empty($row['notes'])): ?>
                        <br><small style="color: var(--text-secondary); font-style:italic;"><?= htmlspecialchars(substr($row['notes'], 0, 30)) ?>...</small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= date("M d, Y", strtotime($row['date'])) ?><br>
                    <small style="color: var(--text-secondary);"><?= htmlspecialchars($row['time']) ?></small>
                  </td>
                  <td>
                    <?= htmlspecialchars($row['phone']) ?><br>
                    <small style="color: var(--text-secondary);"><?= htmlspecialchars($row['email']) ?></small>
                  </td>
                  <td>
                    <span title="<?= htmlspecialchars($row['address']) ?>">
                        <?= htmlspecialchars(substr($row['address'], 0, 25)) . (strlen($row['address']) > 25 ? '...' : '') ?>
                    </span>
                  </td>
                  <td style="font-weight: 600;">
                    ₱<?= number_format($row['price'], 2) ?>
                  </td>
                  <td>
                    <span class="badge <?= $statusClass ?>">
                        <?= htmlspecialchars(ucwords($row['status'])) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 32px;">
                    No bookings found in the database.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

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
</script>

</body>
</html>