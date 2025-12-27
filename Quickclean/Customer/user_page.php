<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

require_login();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// 1. FETCH USER PROFILE
$stmt = $conn->prepare("SELECT user_id, name, email, address, contact_num, profile_pic FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. FETCH BOOKING HISTORY (MODIFIED WITH LEFT JOIN)
// We join 'bookings' (alias b) with 'transactions' (alias t)
// We prioritize getting the status from the transactions table if it exists.
$stmt = $conn->prepare("SELECT 
    b.booking_id, 
    b.service_name, 
    b.price, 
    b.date, 
    b.time, 
    b.status AS booking_status, 
    t.status AS transaction_status, 
    b.created_at, 
    b.address, 
    b.phone, 
    b.email 
    FROM bookings b
    LEFT JOIN transactions t ON b.booking_id = t.booking_id
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root{
  --brand-blue: #6DAFF2;
  --nav-yellow: #FFDB58;
  --text-blue: #2E89F0;
  --header-height: 110px;
  --nav-height: 64px;
  --max-content-width: 1360px;
}
* { box-sizing: border-box; }
body { font-family:"Poppins",sans-serif; background:#f4f6f8; margin:0; color:#123; }

/* HEADER & NAV */
.site-header{ background:var(--brand-blue); height:var(--header-height); display:flex; align-items:center; }
.header-inner{ width:100%; max-width:var(--max-content-width); margin:0 auto; display:flex; align-items:center; justify-content:space-between; padding:12px 24px; }
.logo-text{ font-family:'Baloo 2'; font-size:32px; color:white; font-weight:800; min-width:150px; }
.tagline{ font-family:'Baloo 2'; color:#fff; font-weight:600; font-size:20px; flex-grow:1; text-align:center; padding:0 20px; }
.header-placeholder { min-width:150px; }
.nav-bar{ background:var(--nav-yellow); height:var(--nav-height); display:flex; align-items:center; }
.nav-list{ display:flex; justify-content:center; gap:30px; list-style:none; width:100%; margin:0; padding:0; }
.nav-link{ color:#0b3b66; text-decoration:none; font-weight:600; font-size:18px; }
.nav-link.active{ text-decoration:underline; text-underline-offset:6px; font-weight:800; }
.nav-link:hover{ color:var(--text-blue); }

/* LAYOUT CONTAINERS */
.main-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
}

/* PROFILE SECTION */
.profile-wrap{
    background:#fff; 
    padding:30px; 
    border-radius:12px; 
    box-shadow:0 3px 8px rgba(0,0,0,0.06);
    margin-bottom: 30px;
}
.profile{display:flex; gap:24px; align-items:flex-start;}
.avatar{width:80px; height:80px; border-radius:50%; background:#ddd; display:flex; justify-content:center; align-items:center; font-weight:700; font-size:24px; overflow:hidden;}
.dashboard-title{ font-family:"Baloo 2"; color:var(--text-blue); margin:0 0 10px 0; }
.edit-btn{margin-top:20px; padding:10px 20px; background:var(--text-blue); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; text-decoration:none; display:inline-block; transition: background 0.2s;}
.edit-btn:hover{background:#1a6cb3;}

/* BOOKINGS SECTION */
.bookings-wrap {
    background:#fff; 
    padding:30px; 
    border-radius:12px; 
    box-shadow:0 3px 8px rgba(0,0,0,0.06);
}
.section-title { font-family: "Baloo 2"; color: var(--text-blue); margin-top: 0; font-size: 24px; }

table{width:100%; border-collapse:collapse; table-layout: fixed; margin-top: 15px;}
th,td{padding:12px; border-bottom:1px solid #eee; text-align:left; word-wrap: break-word; font-size: 14px;}
th{background:var(--brand-blue); color: white; font-weight: 600; border-bottom: none;}
tr:last-child td { border-bottom: none; }

/* Status Badges */
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}
.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-ontheway { background: #e0cffc; color: #5a32a3; } /* Purple for on the way */
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-completed { background: #cce5ff; color: #004085; }

/* ------------------ RESPONSIVE MEDIA QUERIES ------------------ */

@media (max-width: 1024px){
    .logo-text { font-size:28px; }
    .tagline { font-size:18px; }
    .nav-link { font-size:16px; padding:6px 10px; }
    .header-placeholder { display: none; }
}

@media (max-width: 768px){
    /* 1. Header & Nav Unstick */
    body { padding-top: 0; }
    .site-header, .nav-bar { position: static; height: auto; padding: 10px 0; }
    .header-inner { flex-direction: column; gap: 5px; text-align: center; }
    .tagline { display: none; }
    .nav-list { gap: 10px; }
    .nav-link { font-size: 14px; padding: 5px 8px; }

    /* 2. Stacked Profile */
    .profile-wrap { padding: 20px; }
    .profile { flex-direction: column; align-items: center; text-align: center; }
    .edit-btn { width: 100%; box-sizing: border-box; text-align: center; }

    /* 3. Card View Table */
    table, thead, tbody, th, td, tr { display: block; }
    thead tr { position: absolute; top: -9999px; left: -9999px; }
    
    tr { 
        border: 1px solid #e1e4e8; 
        border-radius: 8px;
        margin-bottom: 15px; 
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }
    
    td { 
        border: none;
        border-bottom: 1px solid #eee; 
        position: relative;
        padding-left: 50%; 
        text-align: right; 
        padding-top: 10px;
        padding-bottom: 10px;
        min-height: 40px;
    }
    
    td:last-child { border-bottom: 0; }

    td:before { 
        position: absolute;
        top: 10px;
        left: 12px;
        width: 45%; 
        padding-right: 10px; 
        white-space: nowrap;
        text-align: left;
        font-weight: 700;
        color: var(--brand-blue);
        content: attr(data-label); 
    }
}

</style>
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <div class="logo-text">QuickClean</div>
    <div class="tagline">Clean Spaces, Happy Faces.</div>
    <span class="header-placeholder"></span>
  </div>
</header>

<nav class="nav-bar">
  <ul class="nav-list">
    <li><a href="customer-home.php" class="nav-link">Home</a></li>
    <li><a href="user_page.php" class="nav-link active">Dashboard</a></li>
    <li><a href="tracking.php" class="nav-link">Track Services</a></li>
    <li><a href="messages.php" class="nav-link">Messages</a></li>
    <li><a href="logout.php" class="nav-link">Logout</a></li>
  </ul>
</nav>

<div class="main-container">
    
    <div class="profile-wrap">
      <div class="profile">
        <div class="avatar">
          <?php
            if (!empty($user_data['profile_pic']) && file_exists("uploads/".$user_data['profile_pic'])) {
                echo '<img src="uploads/'.htmlspecialchars($user_data['profile_pic']).'" style="width:100%;height:100%;object-fit:cover;">';
            } else {
                echo strtoupper(substr($user_data['name'] ?? 'U',0,1));
            }
          ?>
        </div>
        <div>
          <h2 class="dashboard-title"><?php echo htmlspecialchars($user_data['name']); ?></h2>
          <div><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></div>
          <div><strong>Contact No.:</strong> <?php echo htmlspecialchars($user_data['contact_num']); ?></div>
          <div><strong>Address:</strong> <?php echo htmlspecialchars($user_data['address']); ?></div>

          <a href="edit_profile.php" class="edit-btn">Edit Profile</a>
        </div>
      </div>
    </div>

    <div class="bookings-wrap">
      <h2 class="section-title">My Booking History</h2>
      
      <?php if ($bookings->num_rows > 0): ?>
      <table>
        <thead>
            <tr>
                <th style="width: 15%;">Service</th>
                <th style="width: 10%;">Price</th>
                <th style="width: 20%;">Schedule</th>
                <th style="width: 25%;">Address</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%;">Booked On</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($b = $bookings->fetch_assoc()): 
            // LOGIC TO DETERMINE WHICH STATUS TO SHOW
            // If transaction_status exists (meaning it's in the transactions table), use that.
            // Otherwise, fall back to the booking_status.
            if (!empty($b['transaction_status'])) {
                $finalStatus = $b['transaction_status'];
            } else {
                $finalStatus = $b['booking_status'];
            }

            // Determine status class for styling
            $statusClass = 'status-pending'; // default
            $s = strtolower($finalStatus);
            
            if(strpos($s, 'confirm') !== false || strpos($s, 'accept') !== false) {
                 $statusClass = 'status-confirmed';
            }
            elseif(strpos($s, 'cancel') !== false) {
                 $statusClass = 'status-cancelled';
            }
            elseif(strpos($s, 'complet') !== false) {
                 $statusClass = 'status-completed';
            }
            elseif(strpos($s, 'way') !== false) { // Check for "on the way"
                 $statusClass = 'status-ontheway';
            }
        ?>
          <tr>
            <td><?php echo htmlspecialchars($b['service_name']); ?></td>
            <td>₱<?php echo number_format($b['price'],2); ?></td>
            <td><?php echo htmlspecialchars($b['date'] . ' @ ' . $b['time']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($b['address'])); ?></td>
            <td>
                <span class="status-badge <?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($finalStatus); ?>
                </span>
            </td>
            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($b['created_at']))); ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="text-align:center; padding: 20px; color: #666;">You haven't made any bookings yet.</p>
      <?php endif; ?>
    </div>

</div>

</body>
</html>