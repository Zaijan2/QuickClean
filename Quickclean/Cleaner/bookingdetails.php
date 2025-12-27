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

// Get booking ID from URL
$booking_id = isset($_GET['booking']) ? (int)$_GET['booking'] : 0;

// Fetch booking details
$sql = "SELECT b.*, t.status AS transaction_status, t.proof_image, t.completed_at
        FROM bookings b
        LEFT JOIN transactions t ON b.booking_id = t.booking_id
        WHERE b.booking_id = $booking_id";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    die("Booking not found.");
}

$booking = mysqli_fetch_assoc($result);

// Parse extras if it's JSON
$extras = [];
if (!empty($booking['extras'])) {
    $decoded = json_decode($booking['extras'], true);
    if (is_array($decoded)) {
        $extras = $decoded;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking #<?php echo $booking['booking_id']; ?> Details</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* --- CSS Variables & Reset --- */
    :root {
      --primary: #667eea;
      --secondary: #764ba2;
      --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    .wrapper { display: flex; min-height: 100vh; position: relative; }

    /* --- Sidebar --- */
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
      font-size: 24px; font-weight: 800; margin-bottom: 40px; color: white;
      display: flex; justify-content: space-between; align-items: center;
    }

    .close-btn {
      display: none; background: none; border: none; color: white; font-size: 24px; cursor: pointer;
    }

    .nav-item { margin: 8px 0; }

    .nav-item a {
      display: flex; align-items: center; gap: 12px;
      background: rgba(255, 255, 255, 0.05);
      padding: 14px 16px; border-radius: 12px;
      text-decoration: none; color: rgba(255, 255, 255, 0.9);
      font-size: 15px; font-weight: 500;
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }

    .nav-item a:hover { background: rgba(255, 255, 255, 0.15); transform: translateX(4px); }
    .nav-item a.active { background: white; color: var(--primary); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }

    /* --- Mobile Overlay --- */
    .overlay {
      display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.5); z-index: 90; backdrop-filter: blur(2px);
    }

    /* --- Content --- */
    .content {
      flex: 1; padding: 30px 40px; width: 100%;
    }

    .header {
      margin-bottom: 30px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;
    }

    .header-title-wrapper {
        flex: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }

    .menu-toggle {
      display: none; background: rgba(255,255,255,0.2); border: none;
      color: white; padding: 8px 12px; border-radius: 8px; font-size: 20px; cursor: pointer;
    }

    .header h1 { color: white; font-size: 28px; font-weight: 700; margin: 0; }

    /* --- Status Badges --- */
    .status-badge {
      padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700;
      color: white; display: inline-block; text-transform: uppercase;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .status-badge.pending { background: #f59e0b; }
    .status-badge.accepted { background: #8b5cf6; }
    .status-badge.ontheway { background: #3b82f6; }
    .status-badge.completed { background: #10b981; }

    /* --- Card --- */
    .card {
      background: white; border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      padding: 25px; margin-bottom: 24px;
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .card h2 {
      color: var(--primary); font-size: 18px; font-weight: 700;
      margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
      padding-bottom: 12px; border-bottom: 2px solid #e2e8f0;
    }

    /* --- Grid Layout for Details --- */
    .info-grid {
      display: grid;
      /* Auto-fit: Creates 2 columns on desktop, 1 on mobile automatically */
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .info-item {
      display: flex; align-items: flex-start; gap: 15px;
      padding: 16px; background: #f8fafc; border-radius: 10px;
      border: 1px solid #edf2f7;
    }

    .info-icon { font-size: 24px; }
    .info-label { color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
    .info-value { color: #1e293b; font-size: 15px; font-weight: 500; word-break: break-word; }

    /* --- Lists & Notes --- */
    .card ul { list-style: none; padding: 0; }
    .card ul li {
      padding: 12px 16px; margin-bottom: 10px;
      background: #f0f4ff; border-radius: 8px; font-size: 15px;
      color: #1e293b; display: flex; align-items: center; gap: 12px;
      border-left: 4px solid var(--primary);
    }
    .card ul li::before { content: '✓'; font-weight: bold; color: var(--primary); }

    .notes-box {
      background: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px;
      border-radius: 8px; color: #92400e; line-height: 1.6;
    }

    .proof-image {
      width: 100%; max-width: 400px; height: auto;
      border-radius: 12px; margin-top: 15px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* --- Buttons --- */
    .button-group { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
    
    .button {
      display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px;
      color: white; text-decoration: none; border: none; cursor: pointer;
      transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .button:hover { transform: translateY(-2px); opacity: 0.9; }
    
    .btn-back { background: #64748b; }
    .btn-accept { background: #10b981; }
    .btn-decline { background: #ef4444; }
    .btn-update { background: #3b82f6; }

    /* --- Responsive Media Queries --- */
    @media (max-width: 768px) {
      .sidebar {
        position: fixed; left: -100%; width: 280px;
        background: #2d3748; /* Solid dark bg for visibility */
        box-shadow: 4px 0 15px rgba(0,0,0,0.2);
      }
      .sidebar.active { left: 0; }
      .overlay.active { display: block; }
      .menu-toggle { display: block; }
      .close-btn { display: block; }

      .content { padding: 20px 15px; }
      .header h1 { font-size: 20px; }
      
      /* Make buttons full width on mobile for easier tapping */
      .button-group { flex-direction: column; }
      .button { width: 100%; padding: 16px; font-size: 16px; }
      
      .info-grid { grid-template-columns: 1fr; }
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
      <div class="nav-item"><a href="assigned.php" class="active">📋 My Bookings</a></div>
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
        <div class="header-title-wrapper">
          <h1>Booking #<?php echo $booking['booking_id']; ?></h1>
          <?php
          if ($booking['transaction_status'] == 'completed') {
              echo '<span class="status-badge completed">✓ Completed</span>';
          } elseif ($booking['transaction_status'] == 'on the way') {
              echo '<span class="status-badge ontheway">🚗 On the Way</span>';
          } elseif ($booking['status'] == 'accepted') {
              echo '<span class="status-badge accepted">✓ Accepted</span>';
          } elseif ($booking['status'] == 'pending') {
              echo '<span class="status-badge pending">⏳ Pending</span>';
          }
          ?>
        </div>
      </div>

      <div class="card">
        <h2>👤 Customer Info</h2>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-icon">👨</span>
            <div class="info-content">
              <div class="info-label">Name</div>
              <div class="info-value"><?php echo htmlspecialchars($booking['name']); ?></div>
            </div>
          </div>
          <div class="info-item">
            <span class="info-icon">📍</span>
            <div class="info-content">
              <div class="info-label">Address</div>
              <div class="info-value"><?php echo htmlspecialchars($booking['address']); ?></div>
            </div>
          </div>
          <div class="info-item">
            <span class="info-icon">📱</span>
            <div class="info-content">
              <div class="info-label">Phone</div>
              <div class="info-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
            </div>
          </div>
          <?php if (!empty($booking['email'])): ?>
          <div class="info-item">
            <span class="info-icon">📧</span>
            <div class="info-content">
              <div class="info-label">Email</div>
              <div class="info-value"><?php echo htmlspecialchars($booking['email']); ?></div>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <h2>🧹 Service Details</h2>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-icon">🏠</span>
            <div class="info-content">
              <div class="info-label">Service</div>
              <div class="info-value"><?php echo htmlspecialchars($booking['service_name']); ?></div>
            </div>
          </div>
          <div class="info-item">
            <span class="info-icon">📅</span>
            <div class="info-content">
              <div class="info-label">Schedule</div>
              <div class="info-value"><?php echo date('M j, Y', strtotime($booking['date'])); ?> @ <?php echo $booking['time']; ?></div>
            </div>
          </div>
          <div class="info-item">
            <span class="info-icon">💵</span>
            <div class="info-content">
              <div class="info-label">Price</div>
              <div class="info-value">₱<?php echo number_format($booking['price'], 2); ?></div>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($extras)): ?>
      <div class="card">
        <h2>➕ Add-ons</h2>
        <ul>
          <?php foreach ($extras as $extra): ?>
            <li><?php echo htmlspecialchars($extra); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($booking['notes'])): ?>
      <div class="card">
        <h2>📝 Notes</h2>
        <div class="notes-box">
          <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($booking['transaction_status'] == 'completed' && !empty($booking['proof_image'])): ?>
      <div class="card">
        <h2>📸 Proof of Completion</h2>
        <div class="info-item" style="margin-bottom: 10px;">
          <span class="info-icon">✅</span>
          <div class="info-content">
            <div class="info-label">Completed At</div>
            <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($booking['completed_at'])); ?></div>
          </div>
        </div>
        <img src="<?php echo htmlspecialchars($booking['proof_image']); ?>" alt="Proof" class="proof-image">
      </div>
      <?php endif; ?>

      <div class="card">
        <h2>🔄 Actions</h2>
        <div class="button-group">
          <a class="button btn-back" href="assigned.php">← Back</a>
          
          <?php
          if ($booking['status'] == 'pending' || ($booking['status'] == 'accepted' && $booking['transaction_status'] == NULL)) {
              echo '<a class="button btn-accept" href="accept_booking.php?id='.$booking['booking_id'].'">Accept</a>';
              echo '<a class="button btn-decline" href="decline_booking.php?id='.$booking['booking_id'].'">Decline</a>';
          } elseif ($booking['transaction_status'] == 'on the way') {
              echo '<button class="button btn-update" onclick="window.location.href=\'assigned.php\'">Update Status</button>';
          }
          ?>
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