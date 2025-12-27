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

// GET ALL BOOKINGS INCLUDING COMPLETED
$sql = "SELECT b.booking_id, b.name, b.date, b.time, b.status AS booking_status,
               t.status AS transaction_status
        FROM bookings b
        LEFT JOIN transactions t ON b.booking_id = t.booking_id
        ORDER BY 
            CASE WHEN b.status = 'pending' THEN 1
                 WHEN b.status = 'accepted' THEN 2
                 ELSE 3 END,
            b.date, b.time";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - QuickClean</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* --- RESET & VARS --- */
    :root {
      --primary-grad: linear-gradient(135deg, #217edbff 0%, #05a6d6ff 100%);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
      --text-dark: #1a202c;
      --text-light: #ffffff;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--primary-grad);
      color: var(--text-dark);
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
      z-index: 50;
      transition: transform 0.3s ease;
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

    .close-sidebar-btn {
      display: none; /* Hidden on desktop */
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

    .nav-item a:hover, .nav-item a.active {
      background: white;
      color: #217edb;
      transform: translateX(4px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* --- MOBILE OVERLAY --- */
    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 40;
      backdrop-filter: blur(2px);
    }

    /* --- MAIN CONTENT --- */
    .content {
      flex: 1;
      padding: 30px 40px;
      width: 100%; /* Ensure content takes full width on mobile */
      max-width: 100vw;
    }

    .header {
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .menu-btn {
      display: none; /* Hidden on desktop */
      background: rgba(255,255,255,0.2);
      border: none;
      color: white;
      padding: 8px 12px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 20px;
    }

    .header h1 {
      color: white;
      font-size: 32px;
      font-weight: 700;
    }

    /* --- CARD & TABLE --- */
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      padding: 0; /* Remove padding from card to let table fill it */
      overflow: hidden; /* For rounded corners */
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      min-width: 800px; /* Forces scroll on small screens */
    }

    .table th, .table td {
      padding: 16px 20px;
      text-align: left;
      border-bottom: 1px solid #e2e8f0;
      white-space: nowrap; /* Prevents text wrapping awkwardly */
    }

    .table th {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
      font-weight: 700;
      color: #4a5568;
      font-size: 13px;
      text-transform: uppercase;
    }

    .table tbody tr:hover { background: #f7fafc; }
    .table tbody tr.highlight-new { background: #f0fdf4; } /* Lighter green for new */

    /* --- STATUS BADGES --- */
    .status {
      padding: 4px 12px;
      border-radius: 20px;
      color: white;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }
    .status.new-booking { background: #8b5cf6; }
    .status.awaiting { background: #f59e0b; }
    .status.ontheway { background: #3b82f6; }
    .status.completed { background: #10b981; }

    /* --- BUTTONS --- */
    .btn {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      color: white;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s;
    }
    .btn:hover { transform: translateY(-1px); opacity: 0.9; }
    
    .btn-info { background: #667eea; }
    .btn-accept { background: #10b981; }
    .btn-decline { background: #ef4444; }
    .btn-update { background: #3b82f6; }
    .btn-complete { background: #10b981; width: 100%; padding: 12px; font-size: 16px; margin-top: 10px;}

    .action-group { display: flex; gap: 5px; }
    .no-action { color: #a0aec0; font-style: italic; font-size: 13px; }

    /* --- MODAL --- */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 16px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      position: relative;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      font-size: 20px;
      font-weight: 700;
    }

    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;}
    .form-group input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 2px dashed #cbd5e0;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .image-preview {
      width: 100%;
      max-height: 200px;
      object-fit: contain;
      display: none;
      border-radius: 8px;
      margin-bottom: 15px;
      border: 1px solid #e2e8f0;
    }

    /* --- RESPONSIVE MEDIA QUERIES --- */
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -100%; /* Hide completely off-screen */
        width: 280px;
        background: #1a202c; /* Solid background for mobile readability */
        box-shadow: 4px 0 15px rgba(0,0,0,0.1);
      }
      
      .sidebar.active {
        left: 0; /* Slide in */
      }
      
      .close-sidebar-btn { display: block; }
      .menu-btn { display: block; }
      .overlay.active { display: block; }

      .content { padding: 20px 15px; }
      .header h1 { font-size: 24px; }
      
      /* Card padding on mobile */
      .card { border-radius: 12px; }
    }
  </style>
</head>
<body>


<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="wrapper">
  <div class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        QuickClean
        <button class="close-sidebar-btn" onclick="toggleSidebar()">&times;</button>
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
      <button class="menu-btn" onclick="toggleSidebar()">☰</button>
      <h1>My Bookings</h1>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Date / Time</th>
              <th>Info</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $rowClass = ($row['booking_status'] == 'pending') ? "class='highlight-new'" : "";

                    echo "<tr $rowClass>";
                    echo "<td><strong>#".$row['booking_id']."</strong></td>";
                    echo "<td>".htmlspecialchars($row['name'])."</td>";
                    echo "<td>".date('M d', strtotime($row['date']))." <small>".$row['time']."</small></td>";
                    echo "<td><a class='btn btn-info' href='bookingdetails.php?booking=".$row['booking_id']."'>View</a></td>";

                    // STATUS
                    echo "<td>";
                    if ($row['booking_status'] == 'pending') {
                        echo "<span class='status new-booking'>New</span>";
                    } elseif ($row['transaction_status'] == NULL) {
                        echo "<span class='status awaiting'>Accepted</span>";
                    } elseif ($row['transaction_status'] == "on the way") {
                        echo "<span class='status ontheway'>On the Way</span>";
                    } elseif ($row['transaction_status'] == "completed") {
                        echo "<span class='status completed'>Completed</span>";
                    } else {
                        echo "<span class='status awaiting'>Pending</span>";
                    }
                    echo "</td>";

                    // ACTION
                    echo "<td>";
                    if ($row['booking_status'] == 'pending' || 
                        ($row['booking_status'] == 'accepted' && $row['transaction_status'] == NULL)) {
                        echo "<div class='action-group'>";
                        echo "<a class='btn btn-accept' href='accept_booking.php?id=".$row['booking_id']."'>✓</a>";
                        echo "<a class='btn btn-decline' href='decline_booking.php?id=".$row['booking_id']."'>✕</a>";
                        echo "</div>";
                    } elseif ($row['transaction_status'] == "on the way") {
                        echo "<button class='btn btn-update' onclick='openCompleteModal(".$row['booking_id'].")'>Complete Job</button>";
                    } elseif ($row['transaction_status'] == "completed") {
                        echo "<span class='no-action'>—</span>";
                    } else {
                        echo "<span class='no-action'>Wait...</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;padding:40px;color:#a0aec0;'>No bookings found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="completeModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span>Mark as Complete</span>
      <span style="cursor:pointer;" onclick="closeCompleteModal()">&times;</span>
    </div>
    <form id="completeForm" action="complete_booking.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="booking_id" id="modal_booking_id">
      <div class="form-group">
        <label>Upload Proof (Required)</label>
        <input type="file" name="proof_image" id="proof_image" accept="image/*" required onchange="previewImage(event)">
        <img id="imagePreview" class="image-preview" alt="Image preview">
      </div>
      <button type="submit" class="btn btn-complete">Submit & Complete</button>
    </form>
  </div>
</div>

<script>
// Sidebar Toggle Logic
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

// Modal Logic
function openCompleteModal(bookingId) {
    document.getElementById('modal_booking_id').value = bookingId;
    document.getElementById('completeModal').style.display = 'flex'; // Changed to flex for centering
    document.getElementById('completeForm').reset();
    document.getElementById('imagePreview').style.display = 'none';
}

function closeCompleteModal() { 
    document.getElementById('completeModal').style.display = 'none'; 
}

function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('completeModal');
    if (event.target == modal) { closeCompleteModal(); }
}
</script>

</body>
</html>