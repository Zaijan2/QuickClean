<?php
session_start();

// --- LOGIN CHECK FUNCTION ---
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// --- DATABASE CONNECTION ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get total earned
$total_earned_sql = "SELECT SUM(p.amount) as total FROM payments p
                     JOIN transactions t ON p.booking_id = t.booking_id
                     WHERE t.status = 'completed' 
                     AND p.payment_status = 'paid'";
$total_earned_result = mysqli_query($conn, $total_earned_sql);
$total_earned = mysqli_fetch_assoc($total_earned_result)['total'] ?? 0;

// Get pending payouts
$pending_payouts_sql = "SELECT SUM(p.amount) as total FROM payments p
                        JOIN transactions t ON p.booking_id = t.booking_id
                        WHERE t.status = 'completed' 
                        AND p.payment_status = 'pending'";
$pending_payouts_result = mysqli_query($conn, $pending_payouts_sql);
$pending_payouts = mysqli_fetch_assoc($pending_payouts_result)['total'] ?? 0;

// Get completed jobs count
$completed_jobs_sql = "SELECT COUNT(*) as count FROM transactions 
                       WHERE status = 'completed'";
$completed_jobs_result = mysqli_query($conn, $completed_jobs_sql);
$completed_jobs = mysqli_fetch_assoc($completed_jobs_result)['count'];

// Get transaction history
$history_sql = "SELECT 
                t.transaction_id,
                t.booking_id,
                t.customer_name,
                t.service_name,
                t.date,
                t.time,
                t.completed_at,
                p.amount,
                p.payment_status,
                p.payment_method,
                p.payment_date
                FROM transactions t
                LEFT JOIN payments p ON t.booking_id = p.booking_id
                WHERE t.status = 'completed'
                ORDER BY t.completed_at DESC";
$history_result = mysqli_query($conn, $history_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Earnings</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #667eea;
      --primary-dark: #5a67d8;
      --bg-gradient: linear-gradient(135deg, #217edbff 0%, #05a6d6ff 100%);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-gradient);
      color: #1a202c;
      min-height: 100vh;
      overflow-x: hidden; /* Prevent body scroll when sidebar opens */
    }

    .wrapper {
      display: flex;
      min-height: 100vh;
      position: relative;
    }

    /* --- SIDEBAR --- */
    .sidebar {
      width: 260px;
      background: var(--glass-bg);
      backdrop-filter: blur(10px);
      border-right: 1px solid var(--glass-border);
      padding: 30px 20px;
      color: white;
      height: 100vh;
      position: sticky;
      top: 0;
      z-index: 100;
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
    }

    .sidebar-logo {
      font-size: 24px;
      font-weight: 800;
      margin-bottom: 40px;
      color: white;
      letter-spacing: -0.5px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .close-btn {
      display: none;
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
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

    .nav-item a:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateX(4px);
    }

    .nav-item a.active {
      background: white;
      color: var(--primary);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* --- MOBILE OVERLAY --- */
    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 90;
      backdrop-filter: blur(2px);
    }

    /* --- CONTENT --- */
    .content {
      flex: 1;
      padding: 30px 40px;
      width: 100%;
    }

    .header {
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .menu-toggle {
      display: none;
      background: rgba(255,255,255,0.2);
      border: none;
      color: white;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 20px;
      cursor: pointer;
    }

    .header h1 {
      color: white;
      font-size: 32px;
      font-weight: 700;
    }

    /* --- EARNINGS GRID (RESPONSIVE) --- */
    .earnings-grid {
      display: grid;
      /* This auto-fits columns. Minimum width 250px, otherwise stretch */
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .earnings-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
      transition: transform 0.2s;
    }

    .earnings-card:hover { transform: translateY(-4px); }

    .earnings-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    }
    .earnings-card.total::before { background: linear-gradient(90deg, #10b981 0%, #059669 100%); }
    .earnings-card.pending::before { background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%); }
    .earnings-card.completed::before { background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); }

    .earnings-icon { font-size: 32px; margin-bottom: 12px; }
    .earnings-label { font-size: 12px; color: #718096; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
    
    .earnings-value {
      font-size: 28px; font-weight: 800; color: #2d3748; margin-bottom: 4px;
      word-break: break-word; /* Prevents overflow if numbers are huge */
    }
    
    .earnings-card.total .earnings-value { color: #059669; }
    .earnings-card.pending .earnings-value { color: #d97706; }
    .earnings-card.completed .earnings-value { color: #667eea; }

    .earnings-subtext { font-size: 13px; color: #a0aec0; }

    /* --- CARD & TABLE --- */
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
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

    /* Scroll Wrapper for Table */
    .table-container {
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px; /* Forces scroll on mobile */
    }

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
      border-bottom: 1px solid #edf2f7;
      color: #2d3748;
      font-size: 14px;
      white-space: nowrap; /* Keeps rows on one line */
    }

    .table tbody tr:hover { background-color: #f7fafc; cursor: pointer; }

    /* --- BADGES --- */
    .status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 700; text-transform: uppercase; color: white;
    }
    .status-badge.paid { background: #10b981; }
    .status-badge.pending { background: #f59e0b; }
    .status-badge.failed { background: #ef4444; }
    .status-badge.refunded { background: #6b7280; }
    
    .amount { font-weight: 700; color: #059669; }
    .empty-state { padding: 50px; text-align: center; color: #a0aec0; }
    .empty-state-icon { font-size: 48px; margin-bottom: 10px; opacity: 0.5; }

    /* --- RESPONSIVE QUERIES --- */
    @media (max-width: 768px) {
      /* Navigation Drawer Style */
      .sidebar {
        position: fixed;
        left: -100%; /* Hide off-screen */
        width: 280px;
        background: #1a202c; /* Solid background for legibility on mobile */
        box-shadow: 4px 0 15px rgba(0,0,0,0.2);
      }
      
      .sidebar.active { left: 0; transform: translateX(0); }
      .overlay.active { display: block; }
      .menu-toggle { display: block; }
      .close-btn { display: block; }

      .content { padding: 20px; }
      .header h1 { font-size: 24px; }
      
      /* Stack the grid if needed, though auto-fit usually handles it */
      .earnings-grid { gap: 15px; }
      
      .earnings-card { padding: 20px; }
    }
  </style>
</head>
<body>

  <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

  <div class="wrapper">
    <div class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        QuickClean
        <button class="close-btn" onclick="toggleSidebar()">&times;</button>
      </div>
      <div class="nav-item"><a href="dashboard.php">📊 Dashboard</a></div>
      <div class="nav-item"><a href="assigned.php">📋 My Bookings</a></div>
      <div class="nav-item"><a href="schedule.php">📅 Schedule</a></div>
      <div class="nav-item"><a href="earnings.php" class="active">💰 Earnings</a></div>
      <div class="nav-item"><a href="cleaner_messages.php">💬 Messages</a></div>
      <div class="nav-item"><a href="cleaner-notification.php">🔔 Notifications</a></div>
      <div class="nav-item"><a href="profile.php">👤 Profile</a></div>
      <div class="nav-item"><a href="logout.php">Logout</a></div>
    </div>

    <div class="content">
      <div class="header">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <h1>Earnings</h1>
      </div>

      <div class="earnings-grid">
        <div class="earnings-card total">
          <div class="earnings-icon">💵</div>
          <div class="earnings-label">Total Earned</div>
          <div class="earnings-value">₱<?php echo number_format($total_earned, 2); ?></div>
          <div class="earnings-subtext">All time earnings</div>
        </div>

        <div class="earnings-card pending">
          <div class="earnings-icon">⏳</div>
          <div class="earnings-label">Pending Payouts</div>
          <div class="earnings-value">₱<?php echo number_format($pending_payouts, 2); ?></div>
          <div class="earnings-subtext">Awaiting transfer</div>
        </div>

        <div class="earnings-card completed">
          <div class="earnings-icon">✅</div>
          <div class="earnings-label">Completed Jobs</div>
          <div class="earnings-value"><?php echo $completed_jobs; ?></div>
          <div class="earnings-subtext">Successfully finished</div>
        </div>
      </div>

      <div class="card">
        <h2>📜 History</h2>
        <div class="table-container">
          <?php if (mysqli_num_rows($history_result) > 0): ?>
          <table class="table">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($transaction = mysqli_fetch_assoc($history_result)) {
                  $payment_status = $transaction['payment_status'] ?? 'pending';
                  $status_class = strtolower($payment_status);
                  
                  // Visual Checkmark/Icon Logic
                  $icon = '';
                  if($status_class == 'paid') $icon = '✓ ';
                  if($status_class == 'pending') $icon = '⏳ ';
                  if($status_class == 'failed') $icon = '✕ ';

                  echo "<tr onclick=\"window.location.href='bookingdetails.php?booking=".$transaction['booking_id']."'\">";
                  echo "<td>#".$transaction['booking_id']."</td>";
                  echo "<td>".htmlspecialchars($transaction['customer_name'])."</td>";
                  echo "<td>".htmlspecialchars($transaction['service_name'])."</td>";
                  echo "<td>".date('M d, Y', strtotime($transaction['date']))."</td>";
                  
                  echo "<td>";
                  if ($transaction['amount']) {
                      echo "<span class='amount'>₱".number_format($transaction['amount'], 2)."</span>";
                  } else {
                      echo "<span style='color:#ccc;'>—</span>";
                  }
                  echo "</td>";
                  
                  echo "<td><span class='status-badge ".$status_class."'>".$icon.ucfirst($payment_status)."</span></td>";
                  echo "</tr>";
              }
              ?>
            </tbody>
          </table>
          <?php else: ?>
          <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <div class="empty-state-text">No transaction history found.</div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
    }
  </script>
</body>
</html>
<?php mysqli_close($conn); ?>