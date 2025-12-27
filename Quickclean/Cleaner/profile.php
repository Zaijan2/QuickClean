<?php
session_start();

// --- CONFIGURATION ---
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "quickclean";

// Database Connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// 2. FETCH CLEANER DATA
// Note: This fetches the FIRST cleaner found. 
$sql = "SELECT * FROM user WHERE role = 'cleaner' LIMIT 1";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    echo "<h1>Error: No Cleaner Account Found</h1>";
    exit();
}

$cleaner_id = $user['user_id'];

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Handle File Upload
    $sql_pic = ""; 
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $sql_pic = ", profile_pic='$target_file'";
        }
    }

    $sql = "UPDATE user SET name=?, contact_num=?, email=?, address=? $sql_pic WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fullname, $phone, $email, $address, $cleaner_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        header("Refresh:0"); 
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cleaner Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* --- CSS VARIABLES & RESET --- */
    :root {
      --primary: #2563eb;
      --primary-dark: #1e40af;
      --bg-gradient: linear-gradient(135deg, #217edbff 0%, #05a6d6ff 100%);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --text-dark: #1e293b;
      --text-light: #64748b;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-gradient);
      color: var(--text-dark);
      min-height: 100vh;
      overflow-x: hidden;
    }

    .wrapper { display: flex; min-height: 100vh; position: relative; }

    /* --- SIDEBAR --- */
    .sidebar {
      width: 260px;
      background: var(--glass-bg);
      backdrop-filter: blur(10px);
      border-right: 1px solid rgba(255, 255, 255, 0.2);
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
    }

    .nav-item a:hover { background: rgba(255, 255, 255, 0.15); transform: translateX(4px); }
    .nav-item a.active { background: white; color: var(--primary); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    /* --- OVERLAY --- */
    .overlay {
      display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.5); z-index: 90; backdrop-filter: blur(2px);
    }

    /* --- CONTENT --- */
    .content {
      flex: 1; padding: 30px 40px; width: 100%;
    }

    .header {
      margin-bottom: 30px; display: flex; align-items: center; gap: 15px;
    }

    .menu-toggle {
      display: none; background: rgba(255,255,255,0.2); border: none;
      color: white; padding: 8px 12px; border-radius: 8px; font-size: 20px; cursor: pointer;
    }

    .header h1 { color: white; font-size: 32px; font-weight: 700; }

    /* --- PROFILE HEADER CARD --- */
    .profile-card {
      background: white; border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      padding: 30px; margin-bottom: 24px;
      display: flex; align-items: center; gap: 24px;
      position: relative; overflow: hidden;
    }

    .profile-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
      background: linear-gradient(90deg, #2563eb, #7c3aed);
    }

    .avatar-wrapper {
      position: relative; cursor: pointer;
    }

    .profile-avatar {
      width: 100px; height: 100px; border-radius: 50%;
      background-color: #e2e8f0; background-size: cover; background-position: center;
      display: flex; align-items: center; justify-content: center;
      font-size: 36px; color: #64748b; border: 4px solid #f8fafc;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }

    .avatar-wrapper:hover .profile-avatar { transform: scale(1.05); }
    
    .camera-icon {
      position: absolute; bottom: 0; right: 0;
      background: var(--primary); color: white;
      width: 32px; height: 32px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; border: 2px solid white;
    }

    .profile-info h2 { font-size: 24px; color: #1e293b; margin-bottom: 5px; }
    .profile-meta { display: flex; flex-wrap: wrap; gap: 15px; color: #64748b; font-size: 14px; }
    .meta-item { display: flex; align-items: center; gap: 5px; }

    /* --- EDIT FORM CARD --- */
    .edit-card {
      background: white; border-radius: 16px;
      padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .edit-card h3 {
      font-size: 18px; color: #1e293b; margin-bottom: 20px;
      padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;
    }

    .form-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .form-group { margin-bottom: 20px; }
    .form-group.full { grid-column: 1 / -1; }

    .form-group label {
      display: block; margin-bottom: 8px; font-weight: 600;
      font-size: 13px; color: #475569; text-transform: uppercase;
    }

    .form-group input, .form-group textarea {
      width: 100%; padding: 12px 16px; border-radius: 8px;
      border: 1px solid #cbd5e1; background: #f8fafc;
      font-size: 15px; transition: border 0.2s;
    }

    .form-group input:focus { border-color: var(--primary); background: white; outline: none; }

    .btn-save {
      background: var(--primary); color: white; border: none;
      padding: 14px 24px; border-radius: 8px; font-weight: 600; font-size: 15px;
      cursor: pointer; transition: background 0.2s;
      width: 100%; margin-top: 10px;
    }

    .btn-save:hover { background: var(--primary-dark); }

    .alert {
      padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500;
    }
    .alert.success { background: #d1fae5; color: #065f46; }
    .alert.error { background: #fee2e2; color: #991b1b; }

    /* --- RESPONSIVE MEDIA QUERIES --- */
    @media (max-width: 768px) {
      /* Sidebar Drawer Logic */
      .sidebar {
        position: fixed; left: -100%; width: 280px;
        background: #1e293b; /* Dark bg for contrast */
        box-shadow: 4px 0 15px rgba(0,0,0,0.2);
      }
      .sidebar.active { left: 0; }
      .overlay.active { display: block; }
      .menu-toggle { display: block; }
      .close-btn { display: block; }

      .content { padding: 20px; }
      .header h1 { font-size: 24px; }

      /* Stack Profile Card */
      .profile-card { flex-direction: column; text-align: center; padding: 25px; }
      .profile-meta { justify-content: center; }

      /* Form Adjustments */
      .edit-card { padding: 20px; }
      .form-grid { grid-template-columns: 1fr; } /* Stack inputs vertically */
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
      <div class="nav-item"><a href="cleaner-notification.php">🔔 Notifications</a></div>
      <div class="nav-item"><a href="profile.php" class="active">👤 Profile</a></div>
      <div class="nav-item"><a href="logout.php">Logout</a></div>
    </div>

    <div class="content">
      <div class="header">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <h1>Profile Settings</h1>
      </div>

      <?php if($message): ?>
        <div class="alert <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
            <?= $message ?>
        </div>
      <?php endif; ?>

      <div class="profile-card">
        <div class="avatar-wrapper" onclick="document.getElementById('fileInput').click()">
            <?php 
              $bgImage = !empty($user['profile_pic']) ? $user['profile_pic'] : '';
              $initials = empty($bgImage) ? substr($user['name'], 0, 1) : '';
            ?>
            <div class="profile-avatar" id="avatarPreview" style="background-image: url('<?= $bgImage ?>')">
                <?= $initials ?>
            </div>
            <div class="camera-icon">📷</div>
        </div>
        <div class="profile-info">
          <h2><?= htmlspecialchars($user['name']) ?></h2>
          <div class="profile-meta">
            <div class="meta-item"><span>📧</span> <?= htmlspecialchars($user['email']) ?></div>
            <div class="meta-item"><span>📅</span> Joined <?= date("M Y", strtotime($user['date_created'])) ?></div>
          </div>
        </div>
      </div>

      <div class="edit-card">
        <h3>✏️ Edit Personal Information</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" id="fileInput" name="profile_pic" accept="image/*" style="display: none;" onchange="previewImage(this)">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['contact_num'] ?? '') ?>" placeholder="e.g. 09123456789">
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group full">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Your complete address">
                </div>
            </div>

            <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
        </form>
      </div>

    </div>
  </div>

  <script>
    // Toggle Sidebar
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
    }

    // Image Preview Logic
    function previewImage(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          const preview = document.getElementById('avatarPreview');
          preview.style.backgroundImage = 'url(' + e.target.result + ')';
          preview.innerText = ''; // Remove initials
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>
</html>