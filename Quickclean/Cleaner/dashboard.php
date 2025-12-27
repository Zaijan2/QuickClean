<?php
// --- CONFIGURATION ---
// FIX 1: Set the timezone to ensure PHP "Today" matches your local "Today"
date_default_timezone_set('Asia/Manila'); 

// --- DATABASE CONNECTION ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}
require_login(); // Ensure this is called!

// Get today's date (In your local timezone)
$today = date('Y-m-d');

// --- 1. TODAY'S BOOKINGS QUERIES ---

// Count Today
// FIX 2: Use DATE() function to ignore time components (e.g. 14:00:00)
$today_bookings_sql = "SELECT COUNT(*) as count FROM bookings WHERE DATE(date) = '$today' AND status != 'declined'";
$today_bookings_result = mysqli_query($conn, $today_bookings_sql);
$today_bookings_count = mysqli_fetch_assoc($today_bookings_result)['count'];

// Count Completed Today
$completed_today_sql = "SELECT COUNT(*) as count FROM transactions t JOIN bookings b ON t.booking_id = b.booking_id WHERE DATE(b.date) = '$today' AND t.status = 'completed'";
$completed_today_result = mysqli_query($conn, $completed_today_sql);
$completed_today_count = mysqli_fetch_assoc($completed_today_result)['count'];

// Monthly Earnings
$current_month = date('m');
$current_year = date('Y');
$earnings_monthly_sql = "SELECT SUM(p.amount) as total FROM payments p JOIN bookings b ON p.booking_id = b.booking_id JOIN transactions t ON b.booking_id = t.booking_id WHERE MONTH(b.date) = '$current_month' AND YEAR(b.date) = '$current_year' AND t.status = 'completed' AND p.payment_status = 'paid'";
$earnings_monthly_result = mysqli_query($conn, $earnings_monthly_sql);
$earnings_monthly = mysqli_fetch_assoc($earnings_monthly_result)['total'] ?? 0;

// Total Stats
$total_completed_sql = "SELECT COUNT(*) as count FROM transactions WHERE status = 'completed'";
$total_completed_result = mysqli_query($conn, $total_completed_sql);
$total_completed = mysqli_fetch_assoc($total_completed_result)['count'];

// Details for Today's List
$today_details_sql = "SELECT b.booking_id, b.name, b.service_name, b.time, b.status as booking_status, t.status as transaction_status, p.payment_status FROM bookings b LEFT JOIN transactions t ON b.booking_id = t.booking_id LEFT JOIN payments p ON b.booking_id = p.booking_id WHERE DATE(b.date) = '$today' AND b.status != 'declined' GROUP BY b.booking_id ORDER BY b.time";
$today_details_result = mysqli_query($conn, $today_details_sql);


// --- 2. UPCOMING BOOKINGS QUERIES ---

// Get upcoming bookings
$next_week = date('Y-m-d', strtotime('+7 days'));

// FIX 3: STRICT COMPARISON
// We use DATE(b.date) > '$today'. 
// This forces the database to look for dates strictly AFTER today.
$upcoming_sql = "SELECT b.booking_id, b.name, b.service_name, b.date, b.time, b.status as booking_status, t.status as transaction_status, p.payment_status FROM bookings b LEFT JOIN transactions t ON b.booking_id = t.booking_id LEFT JOIN payments p ON b.booking_id = p.booking_id WHERE DATE(b.date) > '$today' AND DATE(b.date) <= '$next_week' AND b.status != 'declined' GROUP BY b.booking_id ORDER BY b.date, b.time LIMIT 5";
$upcoming_result = mysqli_query($conn, $upcoming_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cleaner Dashboard</title>
  <style>
    :root {
      --primary: #667eea;
      --secondary: #764ba2;
      --bg-gradient: linear-gradient(135deg, #217edbff 0%, #05a6d6ff 100%);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--bg-gradient);
      color: #1a202c;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .wrapper {
      display: flex;
      min-height: 100vh;
      position: relative;
    }

    /* --- Sidebar --- */
    .sidebar {
      width: 260px;
      background: var(--glass-bg);
      backdrop-filter: blur(10px);
      border-right: 1px solid var(--glass-border);
      padding: 30px 20px;
      color: white;
      display: flex;
      flex-direction: column;
      z-index: 100;
      transition: transform 0.3s ease;
      height: 100vh;
      position: sticky;
      top: 0;
    }

    .sidebar-logo {
      font-size: 24px;
      font-weight: 800;
      margin-bottom: 40px;
      color: white;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nav-item { margin: 8px 0; }

    .nav-item a {
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(255, 255, 255, 0.05);
      padding: 14px 16px;
      border-radius: 12px;
      text-decoration: none;
      color: rgba(255, 255, 255, 0.9);
      font-size: 15px;
      font-weight: 500;
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }

    .nav-item a:hover, .nav-item a.active {
      background: white;
      color: var(--primary);
      transform: translateX(4px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* --- Mobile Overlay --- */
    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 90;
      backdrop-filter: blur(2px);
    }

    /* --- Content --- */
    .content {
      flex: 1;
      padding: 30px 40px;
      width: 100%;
      max-width: 100%;
    }

    .header {
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .menu-toggle {
      display: none;
      background: rgba(255,255,255,0.2);
      border: none;
      color: white;
      padding: 10px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 24px;
    }

    .header h1 {
      color: white;
      font-size: 32px;
      font-weight: 700;
    }

    /* --- Stats Grid --- */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
      transition: transform 0.2s;
    }

    .stat-card:hover { transform: translateY(-4px); }
    
    .stat-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
      background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
    }

    .stat-icon { font-size: 32px; margin-bottom: 12px; }
    .stat-label { font-size: 13px; color: #718096; font-weight: 600; text-transform: uppercase; }
    .stat-value {
      font-size: 28px; font-weight: 700; color: #2d3748;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    /* --- Cards & Tables --- */
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      margin-bottom: 24px;
      overflow: hidden;
    }

    .card h2 {
      padding: 20px 24px;
      color: var(--primary);
      font-size: 18px;
      font-weight: 700;
      border-bottom: 1px solid #e2e8f0;
    }

    .table-container {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table { width: 100%; border-collapse: collapse; min-width: 600px; }
    
    .table th {
      background: linear-gradient(135deg, #bfb010ff 0%, #c4bc20ff 100%);
      color: white;
      padding: 16px 20px;
      text-align: left;
      font-size: 12px;
      text-transform: uppercase;
      font-weight: 600;
      white-space: nowrap;
    }

    .table td {
      padding: 16px 20px;
      font-size: 14px;
      color: #2d3748;
      border-bottom: 1px solid #edf2f7;
      white-space: nowrap;
    }

    .table tbody tr:hover { background-color: #f7fafc; cursor: pointer; }

    /* --- Status Badges --- */
    .status {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 4px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: white; }
    .status.pending { background: #f59e0b; color: white; }
    .status.accepted { background: #8b5cf6; color: white; }
    .status.ontheway { background: #3b82f6; color: white; }
    .status.completed { background: #10b981; color: white; }

    .empty-state { padding: 40px; text-align: center; color: #a0aec0; }
    .empty-state-icon { font-size: 40px; margin-bottom: 10px; }

    /* --- RESPONSIVE --- */
    @media (max-width: 1024px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -100%;
        top: 0;
        bottom: 0;
        width: 280px;
        background: rgba(30, 41, 59, 0.95);
        box-shadow: 4px 0 15px rgba(0,0,0,0.2);
      }
      .sidebar.active { left: 0; transform: translateX(0); }
      .overlay.active { display: block; }
      .content { padding: 20px; }
      .menu-toggle { display: block; }
      .header h1 { font-size: 22px; }
      .stats-grid { grid-template-columns: 1fr; }
      .stat-card { padding: 20px; display: flex; align-items: center; gap: 20px; }
      .stat-icon { margin-bottom: 0; font-size: 28px; }
      .stat-info { display: flex; flex-direction: column; }
    }
  </style>
</head>
<body>

  <div class="overlay" id="overlay"></div>

  <div class="wrapper">
    
    <div class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        QuickClean
        <span style="font-size: 24px; cursor: pointer; display: none;" class="mobile-close" onclick="toggleSidebar()">&times;</span>
      </div>
      <div class="nav-item"><a href="dashboard.php" class="active">📊 Dashboard</a></div>
      <div class="nav-item"><a href="assigned.php">📋 My Bookings</a></div>
      <div class="nav-item"><a href="schedule.php">📅 Schedule</a></div>
      <div class="nav-item"><a href="earnings.php">💰 Earnings</a></div>
      <div class="nav-item"><a href="cleaner_messages.php">💬 Messages</a></div>
      <div class="nav-item"><a href="cleaner-notification.php">🔔 Notifications</a></div>
      <div class="nav-item"><a href="profile.php">👤 Profile</a></div>
      <div class="nav-item"><a href="logout.php">Logout</a></div>
    </div>

    <div class="content">
      <div class="header">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <h1>Cleaner Dashboard</h1>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">📋</div>
          <div class="stat-info">
            <div class="stat-label">Today's Bookings</div>
            <div class="stat-value"><?php echo $today_bookings_count; ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-info">
            <div class="stat-label">Completed Today</div>
            <div class="stat-value"><?php echo $completed_today_count; ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">💵</div>
          <div class="stat-info">
            <div class="stat-label">Monthly Earnings</div>
            <div class="stat-value">₱<?php echo number_format($earnings_monthly, 2); ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⭐</div>
          <div class="stat-info">
            <div class="stat-label">Total Completed</div>
            <div class="stat-value"><?php echo $total_completed; ?></div>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>📅 Today's Bookings</h2>
        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Time</th>
                <th>Payment</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (mysqli_num_rows($today_details_result) > 0) {
                  while ($booking = mysqli_fetch_assoc($today_details_result)) {
                      $status_class = 'pending'; $status_text = 'Pending';
                      if ($booking['transaction_status'] == 'completed') { $status_class = 'completed'; $status_text = 'Completed'; }
                      elseif ($booking['transaction_status'] == 'on the way') { $status_class = 'ontheway'; $status_text = 'On the way'; }
                      elseif ($booking['booking_status'] == 'accepted') { $status_class = 'accepted'; $status_text = 'Accepted'; }

                      $payment_class = ''; $payment_text = 'N/A';
                      if (!empty($booking['payment_status'])) {
                          if ($booking['payment_status'] == 'paid') { $payment_class = 'completed'; $payment_text = 'Paid'; }
                          elseif ($booking['payment_status'] == 'pending') { $payment_class = 'pending'; $payment_text = 'Pending'; }
                          else { $payment_class = 'status'; $payment_text = $booking['payment_status']; }
                      }

                      echo "<tr onclick=\"window.location.href='bookingdetails.php?booking=".$booking['booking_id']."'\">";
                      echo "<td>#".$booking['booking_id']."</td>";
                      echo "<td>".htmlspecialchars($booking['name'])."</td>";
                      echo "<td>".htmlspecialchars($booking['service_name'])."</td>";
                      echo "<td>".$booking['time']."</td>";
                      echo "<td><span class='status ".$payment_class."'>".$payment_text."</span></td>";
                      echo "<td><span class='status ".$status_class."'>".$status_text."</span></td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='6' class='empty-state'><div class='empty-state-icon'>📭</div>No bookings today.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <h2>🔜 Next 7 Days</h2>
        <?php if (mysqli_num_rows($upcoming_result) > 0): ?>
        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Payment</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($booking = mysqli_fetch_assoc($upcoming_result)) {
                  $status_class = ($booking['transaction_status'] == 'completed') ? 'completed' : (($booking['transaction_status'] == 'on the way') ? 'ontheway' : (($booking['booking_status'] == 'accepted') ? 'accepted' : 'pending'));
                  $status_text = ucfirst(str_replace('_', ' ', $status_class));
                  
                  $pay_class = ($booking['payment_status'] == 'paid') ? 'completed' : 'pending';
                  $pay_text = ucfirst($booking['payment_status'] ?? 'N/A');

                  echo "<tr onclick=\"window.location.href='bookingdetails.php?booking=".$booking['booking_id']."'\">";
                  echo "<td>".date('M j', strtotime($booking['date']))."</td>";
                  echo "<td>".$booking['time']."</td>";
                  echo "<td>".htmlspecialchars($booking['name'])."</td>";
                  echo "<td>".htmlspecialchars($booking['service_name'])."</td>";
                  echo "<td><span class='status ".$pay_class."'>".$pay_text."</span></td>";
                  echo "<td><span class='status ".$status_class."'>".$status_text."</span></td>";
                  echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">📭</div>
          <div class="empty-state-text">No upcoming bookings.</div>
        </div>
        <?php endif; ?>
      </div>

    </div> 
  </div> 

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      const closeBtn = document.querySelector('.mobile-close');
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
      closeBtn.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
    }
    document.getElementById('overlay').addEventListener('click', toggleSidebar);
  </script>
</body>
</html>
<?php mysqli_close($conn); ?>