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

// Collect all notifications
$notifications = [];

// 1. New bookings assigned
$new_bookings_sql = "SELECT booking_id, name, service_name, date, time, created_at FROM bookings WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC";
$new_bookings_result = mysqli_query($conn, $new_bookings_sql);
while ($booking = mysqli_fetch_assoc($new_bookings_result)) {
    $time_diff = time() - strtotime($booking['created_at']);
    $notifications[] = [
        'type' => 'new_booking',
        'icon' => '📋',
        'icon_class' => 'new',
        'title' => 'New booking assigned',
        'message' => "You have been assigned to booking #{$booking['booking_id']} - {$booking['service_name']} for {$booking['name']} on " . date('M j, Y', strtotime($booking['date'])) . " at {$booking['time']}.",
        'badge' => 'New Assignment',
        'badge_class' => 'new',
        'time' => $booking['created_at'],
        'time_diff' => $time_diff,
        'booking_id' => $booking['booking_id'],
        'unread' => $time_diff < 86400
    ];
}

// 2. Accepted bookings
$accepted_bookings_sql = "SELECT b.booking_id, b.name, b.service_name, b.date, b.time, t.action_date FROM bookings b JOIN transactions t ON b.booking_id = t.booking_id WHERE b.status = 'accepted' AND t.action_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY t.action_date DESC";
$accepted_bookings_result = mysqli_query($conn, $accepted_bookings_sql);
while ($booking = mysqli_fetch_assoc($accepted_bookings_result)) {
    $time_diff = time() - strtotime($booking['action_date']);
    $notifications[] = [
        'type' => 'accepted',
        'icon' => '✓',
        'icon_class' => 'new',
        'title' => 'Booking accepted',
        'message' => "You accepted booking #{$booking['booking_id']} - {$booking['service_name']} scheduled for " . date('M j, Y', strtotime($booking['date'])) . " at {$booking['time']}.",
        'badge' => 'Accepted',
        'badge_class' => 'new',
        'time' => $booking['action_date'],
        'time_diff' => $time_diff,
        'booking_id' => $booking['booking_id'],
        'unread' => $time_diff < 86400
    ];
}

// 3. Declined bookings
$declined_bookings_sql = "SELECT booking_id, name, service_name, date, time, created_at FROM bookings WHERE status = 'declined' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC";
$declined_bookings_result = mysqli_query($conn, $declined_bookings_sql);
while ($booking = mysqli_fetch_assoc($declined_bookings_result)) {
    $time_diff = time() - strtotime($booking['created_at']);
    $notifications[] = [
        'type' => 'declined',
        'icon' => '✕',
        'icon_class' => 'cancelled',
        'title' => 'Booking declined',
        'message' => "You declined booking #{$booking['booking_id']} - {$booking['service_name']} for {$booking['name']}.",
        'badge' => 'Declined',
        'badge_class' => 'cancelled',
        'time' => $booking['created_at'],
        'time_diff' => $time_diff,
        'booking_id' => $booking['booking_id'],
        'unread' => false
    ];
}

// 4. Completed jobs
$completed_jobs_sql = "SELECT t.booking_id, t.customer_name, t.service_name, t.completed_at, p.amount, p.payment_status FROM transactions t LEFT JOIN payments p ON t.booking_id = p.booking_id WHERE t.status = 'completed' AND t.completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY t.completed_at DESC";
$completed_jobs_result = mysqli_query($conn, $completed_jobs_sql);
while ($job = mysqli_fetch_assoc($completed_jobs_result)) {
    $time_diff = time() - strtotime($job['completed_at']);
    $notifications[] = [
        'type' => 'completed',
        'icon' => '✓',
        'icon_class' => 'new',
        'title' => 'Job completed',
        'message' => "You completed booking #{$job['booking_id']} - {$job['service_name']} for {$job['customer_name']}.",
        'badge' => 'Completed',
        'badge_class' => 'new',
        'time' => $job['completed_at'],
        'time_diff' => $time_diff,
        'booking_id' => $job['booking_id'],
        'unread' => $time_diff < 86400
    ];
}

// 5. Payment notifications
$payment_notifications_sql = "SELECT p.booking_id, p.amount, p.payment_status, p.payment_date, b.name, b.service_name FROM payments p JOIN bookings b ON p.booking_id = b.booking_id WHERE p.payment_status = 'paid' AND p.payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY p.payment_date DESC";
$payment_notifications_result = mysqli_query($conn, $payment_notifications_sql);
while ($payment = mysqli_fetch_assoc($payment_notifications_result)) {
    $time_diff = time() - strtotime($payment['payment_date']);
    $notifications[] = [
        'type' => 'payment',
        'icon' => '💰',
        'icon_class' => 'info',
        'title' => 'Payment received',
        'message' => "Payment of ₱" . number_format($payment['amount'], 2) . " for booking #{$payment['booking_id']} has been processed successfully.",
        'badge' => null,
        'badge_class' => null,
        'time' => $payment['payment_date'],
        'time_diff' => $time_diff,
        'booking_id' => $payment['booking_id'],
        'unread' => $time_diff < 86400
    ];
}

// 6. Upcoming jobs reminder
$upcoming_jobs_sql = "SELECT b.booking_id, b.name, b.service_name, b.date, b.time, b.address FROM bookings b JOIN transactions t ON b.booking_id = t.booking_id WHERE b.date = CURDATE() + INTERVAL 1 DAY AND t.status = 'on the way' ORDER BY b.time";
$upcoming_jobs_result = mysqli_query($conn, $upcoming_jobs_sql);
while ($job = mysqli_fetch_assoc($upcoming_jobs_result)) {
    $notifications[] = [
        'type' => 'reminder',
        'icon' => '⏰',
        'icon_class' => 'info',
        'title' => 'Upcoming job reminder',
        'message' => "You have a job tomorrow: booking #{$job['booking_id']} - {$job['service_name']} at {$job['time']} for {$job['name']}. Location: {$job['address']}",
        'badge' => 'Tomorrow',
        'badge_class' => 'new',
        'time' => date('Y-m-d H:i:s'),
        'time_diff' => 0,
        'booking_id' => $job['booking_id'],
        'unread' => true
    ];
}

// Sort notifications
usort($notifications, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

// Time Ago Function
function timeAgo($time_diff) {
    if ($time_diff < 60) return 'Just now';
    if ($time_diff < 3600) return floor($time_diff / 60) . 'm ago';
    if ($time_diff < 86400) return floor($time_diff / 3600) . 'h ago';
    if ($time_diff < 172800) return 'Yesterday';
    if ($time_diff < 604800) return floor($time_diff / 86400) . 'd ago';
    return date('M j', strtotime($time)); // Shortened date for mobile friendliness
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications</title>
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
      overflow-x: hidden;
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

    /* --- NOTIFICATION CARD --- */
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .notification-list { list-style: none; padding: 0; margin: 0; }

    .notification-item {
      padding: 20px 24px;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      gap: 16px;
      transition: background 0.2s ease;
      cursor: pointer;
      position: relative;
    }

    .notification-item:hover { background: #f7fafc; }
    
    /* Highlight unread items nicely */
    .notification-item.unread { background: #f0f9ff; }
    .notification-item.unread:hover { background: #e0f2fe; }
    
    .notification-item.unread::before {
      content: ''; position: absolute; left: 0; top: 0; bottom: 0;
      width: 4px; background: var(--primary);
    }

    .notification-icon {
      flex-shrink: 0; width: 48px; height: 48px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 24px; position: relative;
    }

    .notification-icon.new { background: #d1fae5; color: #059669; }
    .notification-icon.cancelled { background: #fee2e2; color: #dc2626; }
    .notification-icon.info { background: #dbeafe; color: #2563eb; }

    .notification-content { flex: 1; display: flex; flex-direction: column; gap: 4px; }

    .notification-header {
        display: flex; justify-content: space-between; align-items: flex-start;
    }

    .notification-title { font-size: 15px; font-weight: 700; color: #2d3748; }
    .notification-time { font-size: 12px; color: #a0aec0; white-space: nowrap; margin-left: 10px; }

    .notification-message { font-size: 14px; color: #718096; line-height: 1.5; margin-right: 20px;}

    .notification-badge {
      display: inline-flex; width: fit-content;
      padding: 2px 8px; border-radius: 6px;
      font-size: 11px; font-weight: 700; margin-top: 6px; text-transform: uppercase;
    }
    
    .notification-badge.new { background: #d1fae5; color: #059669; }
    .notification-badge.cancelled { background: #fee2e2; color: #dc2626; }

    /* Empty State */
    .empty-state { padding: 60px 20px; text-align: center; color: #718096; }
    .empty-state-icon { font-size: 50px; margin-bottom: 16px; opacity: 0.5; }

    /* --- RESPONSIVE MEDIA QUERIES --- */
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -100%; /* Hide off-screen */
        width: 280px;
        background: #1a202c; /* Solid dark background for mobile legibility */
        box-shadow: 4px 0 15px rgba(0,0,0,0.2);
      }
      
      .sidebar.active { left: 0; }
      .overlay.active { display: block; }
      .menu-toggle { display: block; }
      .close-btn { display: block; }

      .content { padding: 20px; }
      .header h1 { font-size: 24px; }

      /* Adjust Notification Item for Mobile */
      .notification-item { padding: 16px; gap: 12px; }
      .notification-icon { width: 40px; height: 40px; font-size: 20px; }
      .notification-title { font-size: 14px; }
      .notification-message { font-size: 13px; }
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
      <div class="nav-item"><a href="earnings.php">💰 Earnings</a></div>
      <div class="nav-item"><a href="cleaner_messages.php">💬 Messages</a></div>
      <div class="nav-item"><a href="cleaner-notification.php" class="active">🔔 Notifications</a></div>
      <div class="nav-item"><a href="profile.php">👤 Profile</a></div>
      <div class="nav-item"><a href="logout.php">Logout</a></div>
    </div>

    <div class="content">
      <div class="header">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <h1>Notifications</h1>
      </div>

      <div class="card">
        <?php if (count($notifications) > 0): ?>
        <ul class="notification-list">
          <?php foreach ($notifications as $notif): ?>
          <li class="notification-item <?php echo $notif['unread'] ? 'unread' : ''; ?>" 
              onclick="window.location.href='bookingdetails.php?booking=<?php echo $notif['booking_id']; ?>'">
            
            <div class="notification-icon <?php echo $notif['icon_class']; ?>">
              <?php echo $notif['icon']; ?>
            </div>
            
            <div class="notification-content">
              <div class="notification-header">
                  <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                  <div class="notification-time"><?php echo timeAgo($notif['time_diff']); ?></div>
              </div>
              
              <div class="notification-message">
                  <?php echo htmlspecialchars($notif['message']); ?>
              </div>
              
              <?php if ($notif['badge']): ?>
                <div class="notification-badge <?php echo $notif['badge_class']; ?>">
                    <?php echo $notif['badge']; ?>
                </div>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">🔔</div>
          <div class="empty-state-text">No notifications</div>
          <div class="empty-state-subtext">You're all caught up!</div>
        </div>
        <?php endif; ?>
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