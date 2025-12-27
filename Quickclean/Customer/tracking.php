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

$stmt = $conn->prepare("SELECT 
    b.booking_id,
    b.service_id,
    b.service_name, 
    b.price, 
    b.date, 
    b.time, 
    b.status as booking_status, 
    b.created_at, 
    b.address, 
    b.phone, 
    b.email,
    b.name as customer_name,
    t.status as transaction_status,
    t.proof_image,
    t.completed_at,
    t.action_date
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
<title>Track Services - QuickClean</title>

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

body{ 
    font-family:"Poppins",sans-serif; 
    background:#f4f6f8; 
    margin:0; 
    color:#123; 
    padding-top: calc(var(--header-height) + var(--nav-height));
}

/* ------------------ HEADER ------------------ */
.site-header{ 
    background:var(--brand-blue); 
    height:var(--header-height); 
    display:flex; 
    align-items:center; 
    position: fixed;
    top:0;
    left:0;
    width:100%;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.header-inner{ 
    width:100%; 
    max-width:var(--max-content-width); 
    margin:0 auto; 
    display:flex; 
    align-items:center; 
    justify-content:space-between; 
    padding:12px 24px; 
}
.logo-text{ 
    font-family:'Baloo 2'; 
    font-size:32px; 
    color:white; 
    font-weight:800; 
}
.tagline{ 
    font-family:'Baloo 2'; 
    color:#fff; 
    font-weight:600; 
    font-size:20px; 
    flex-grow:1; 
    text-align:center; 
    padding:0 20px; 
}
/* Helper to balance flex layout */
.header-placeholder { width: 140px; display: block; } 
.logo-text { min-width: 140px; }

/* ------------------ NAVBAR ------------------ */
.nav-bar{
    background: var(--nav-yellow);
    height: var(--nav-height);
    display: flex;
    align-items: center;
    position: fixed; 
    top: var(--header-height);
    left: 0;
    width: 100%;
    z-index: 999;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.nav-list{
    display:flex;
    justify-content:center;
    flex-wrap: wrap;
    gap: 20px;
    list-style:none;
    width:100%;
    margin:0;
    padding:0;
}
.nav-link{
    color:#0b3b66;
    text-decoration:none;
    font-weight:600;
    font-size:18px;
    padding:8px 12px;
    white-space: nowrap;
}
.nav-link.active{
    text-decoration: underline;
    text-underline-offset:6px;
    font-weight:800;
}
.nav-link:hover{
    color: var(--text-blue);
}

/* ------------------ MAIN CONTAINER ------------------ */
.main-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
}

/* ------------------ TRACKING SECTION ------------------ */
.wrap{
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 3px 8px rgba(0,0,0,0.06);
}
h2{
    font-family: "Baloo 2";
    color: var(--text-blue);
    margin-top:0;
    margin-bottom:24px;
    font-size: 28px;
}

.table-container{ width: 100%; overflow-x: auto; }

table{
    width:100%;
    border-collapse:collapse;
    table-layout: auto;
}
th,td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:left;
    font-size:14px;
    vertical-align: top;
}
th{
    background:var(--brand-blue);
    color:white;
    font-weight:600;
    border-bottom:none;
}
tr:last-child td { border-bottom:none; }

/* ------------------ STATUS BADGES ------------------ */
.status-badge{
    display:inline-block;
    padding:4px 8px;
    border-radius:6px;
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.status-pending { background:#fff3cd; color:#856404; }
.status-accepted { background:#d4edda; color:#155724; }
.status-declined { background:#f8d7da; color:#721c24; }
.status-ontheway { background:#ddd6fe; color:#5b21b6; }
.status-completed { background:#cce5ff; color:#004085; }

.btn-proof{
    display:inline-block;
    padding:6px 12px;
    background:var(--brand-blue);
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-size:12px;
    font-weight:600;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-proof:hover{ background:#5a9ee0; transform:translateY(-1px); }

.no-bookings{
    text-align:center; padding:60px 20px; color:#6b7280;
}
.no-bookings-icon{ font-size:64px; margin-bottom:16px; opacity:0.5; }
.no-bookings-text{ font-size:18px; font-weight:600; margin-bottom:8px; }
.no-bookings-subtext{ font-size:14px; color:#9ca3af; }

/* ------------------ MODAL ------------------ */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.modal-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    width: auto;
    background: transparent;
    text-align: center;
}
.modal-content img{
    width: auto;
    max-width: 100%;
    max-height: 80vh;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}
.modal-close{
    position:absolute; top:-40px; right:0; color:white; font-size:35px; font-weight:bold; cursor:pointer;
    z-index: 2001;
    text-shadow: 0 0 5px rgba(0,0,0,0.5);
}
.modal-close:hover{ color:#ccc; }

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
    <li><a href="user_page.php" class="nav-link">Dashboard</a></li>
    <li><a href="tracking.php" class="nav-link active">Track Services</a></li>
    <li><a href="messages.php" class="nav-link">Messages</a></li>
    <li><a href="logout.php" class="nav-link">Logout</a></li>
  </ul>
</nav>

<div class="main-container wrap">
  <h2>📦 Track My Services</h2>

  <?php if($bookings->num_rows > 0): ?>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Service</th>
          <th>Price</th>
          <th>Schedule</th>
          <th>Address</th>
          <th>Contact</th>
          <th>Status</th>
          <th>Booked On</th>
          <th>Proof</th>
        </tr>
      </thead>
      <tbody>
        <?php while($b = $bookings->fetch_assoc()):
          $status_display=''; $status_class='';
          
          // --- Logic to determine current status ---
          if($b['booking_status']=='declined'){
              $status_display='Declined'; 
              $status_class='status-declined';
          }
          elseif($b['booking_status']=='pending'){
              $status_display='Pending'; 
              $status_class='status-pending';
          }
          elseif($b['booking_status']=='accepted' && $b['transaction_status']=='completed'){
              $status_display='Completed'; 
              $status_class='status-completed';
          }
          elseif($b['booking_status']=='accepted' && $b['transaction_status']=='on the way'){
              $status_display='On The Way'; 
              $status_class='status-ontheway';
          }
          elseif($b['booking_status']=='accepted' && $b['transaction_status']==NULL){
              $status_display='Accepted'; 
              $status_class='status-accepted';
          }
          // --- End Status Logic ---
          
         // --- FIXED Image Path Logic ---
$image_path = $b['proof_image'];
$corrected_path = '#';

if ($image_path) {

    // 1. If already full path to cleaner/uploads
    if (strpos($image_path, 'cleaner/uploads/') !== false) {
        $corrected_path = "../" . $image_path;
    }

    // 2. If stored as only filename → add correct folder
    else if (!str_contains($image_path, "/")) {
        $corrected_path = "../cleaner/uploads/" . $image_path;
    }

    // 3. If stored as uploads/filename
    else if (strpos($image_path, 'uploads/') === 0) {
        $corrected_path = "../cleaner/" . $image_path;
    }

    // 4. Already has ../ path → use it
    else if (strpos($image_path, '../') === 0) {
        $corrected_path = $image_path;
    }
}

        ?>
        <tr>
          <td data-label="Booking ID"><strong>#<?php echo htmlspecialchars($b['booking_id']); ?></strong></td>
          <td data-label="Service"><?php echo htmlspecialchars($b['service_name']); ?></td>
          <td data-label="Price">₱<?php echo number_format($b['price'],2); ?></td>
          <td data-label="Schedule"><?php echo htmlspecialchars(date('M j, Y', strtotime($b['date'])) . ' @ ' . $b['time']); ?></td>
          <td data-label="Address"><?php echo htmlspecialchars(substr($b['address'],0,50)) . (strlen($b['address'])>50?'...':''); ?></td>
          <td data-label="Contact">
              <?php echo htmlspecialchars($b['phone']); ?><br>
              <small style="color:#6b7280;"><?php echo htmlspecialchars($b['email'] ?: ''); ?></small>
          </td>
          <td data-label="Status"><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_display; ?></span></td>
          <td data-label="Booked On"><?php echo htmlspecialchars(date('M j, Y', strtotime($b['created_at']))); ?></td>
          <td data-label="Proof">
            <?php if($b['proof_image'] && $corrected_path != '#'): ?>
            <a href="#" class="btn-proof" onclick="showProof('<?php echo htmlspecialchars($corrected_path); ?>'); return false;">📷 View Proof</a>
            <?php else: ?>
            <span style="color:#9ca3af; font-size:12px;">N/A</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="no-bookings">
    <div class="no-bookings-icon">📭</div>
    <div class="no-bookings-text">No bookings yet</div>
    <div class="no-bookings-subtext">Book a service to start tracking your orders</div>
  </div>
  <?php endif; ?>
</div>

<div id="proofModal" class="modal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeProof()">&times;</span>
    <img id="proofImage" src="" alt="Proof of Completion">
  </div>
</div>

<script>
function showProof(imagePath){
    document.getElementById('proofImage').src=imagePath;
    document.getElementById('proofModal').style.display='flex';
}
function closeProof(){
    document.getElementById('proofModal').style.display='none';
}
window.onclick = function(event){
    const modal=document.getElementById('proofModal');
    if(event.target==modal){ closeProof(); }
}
</script>

</body>
</html>