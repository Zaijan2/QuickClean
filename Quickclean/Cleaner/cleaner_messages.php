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

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$cleaner_id = $_SESSION['user_id'];

// --- SEND MESSAGE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    $user_id = intval($_POST['user_id']);
    $message = trim($_POST['message']);
    if ($user_id && $message !== '') {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $cleaner_id, $user_id, $message);
        $stmt->execute();
        $stmt->close();
        header("Location: cleaner_messages.php?u=".$user_id);
        exit();
    }
}

// --- USERS WHO CHATTED ---
$users_stmt = $conn->prepare("
    SELECT DISTINCT u.user_id, u.name 
    FROM messages m
    JOIN user u ON (u.user_id = m.sender_id OR u.user_id = m.receiver_id)
    WHERE (m.sender_id = ? OR m.receiver_id = ?)
      AND u.user_id != ?
    ORDER BY u.name ASC
");
$users_stmt->bind_param("iii", $cleaner_id, $cleaner_id, $cleaner_id);
$users_stmt->execute();
$users = $users_stmt->get_result();
$users_stmt->close();

// --- SELECTED USER ---
$selected_user = isset($_GET['u']) ? intval($_GET['u']) : 0;
$conversation = null;
$selected_user_name = "";

if ($selected_user) {
    $stmt = $conn->prepare("
        SELECT m.sender_id, m.receiver_id, m.message, m.created_at, u.name AS sender_name
        FROM messages m
        LEFT JOIN user u ON u.user_id = m.sender_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param("iiii", $cleaner_id, $selected_user, $selected_user, $cleaner_id);
    $stmt->execute();
    $conversation = $stmt->get_result();
    $stmt->close();

    $stmt2 = $conn->prepare("SELECT name FROM user WHERE user_id = ?");
    $stmt2->bind_param("i", $selected_user);
    $stmt2->execute();
    $result = $stmt2->get_result();
    if ($row = $result->fetch_assoc()) {
        $selected_user_name = $row['name'];
    }
    $stmt2->close();
}

$conn->close();

// Determine Mobile View State
$mobile_view_class = $selected_user ? 'show-chat' : 'show-list';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages - QuickClean</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
      height: 100vh;
      overflow: hidden; /* Prevent body scroll */
    }

    .wrapper {
      display: flex;
      height: 100vh;
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
      height: 100%;
      display: flex;
      flex-direction: column;
      z-index: 100;
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

    .close-sidebar {
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

    .nav-item a:hover, .nav-item a.active {
      background: white;
      color: var(--primary);
      transform: translateX(4px);
    }

    /* --- OVERLAY --- */
    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 90;
      backdrop-filter: blur(2px);
    }

    /* --- MAIN CONTENT --- */
    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100%;
      padding: 20px;
      overflow: hidden;
    }

    .header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
      flex-shrink: 0;
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
      font-size: 28px;
      font-weight: 700;
    }

    /* --- CHAT CONTAINER --- */
    .chat-container {
      flex: 1;
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      display: flex;
      min-height: 0; /* Important for flex child scrolling */
    }

    /* --- LEFT: USERS LIST --- */
    .users-list {
      width: 320px;
      border-right: 1px solid #e2e8f0;
      display: flex;
      flex-direction: column;
      background: #fff;
    }

    .users-header {
      background: linear-gradient(135deg, #bfb010ff 0%, #c4bc20ff 100%);
      padding: 20px;
      color: white;
      flex-shrink: 0;
    }

    .users-header h3 { font-size: 14px; text-transform: uppercase; font-weight: 700; }

    .users-scroll {
      flex: 1;
      overflow-y: auto;
    }

    .user-item {
      padding: 15px 20px;
      border-bottom: 1px solid #f1f5f9;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 15px;
      transition: background 0.2s;
    }

    .user-item:hover { background: #f8fafc; }
    .user-item.active { background: #eff6ff; border-left: 4px solid var(--primary); }

    .user-avatar {
      width: 40px; height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 14px;
      flex-shrink: 0;
    }

    .user-name { font-weight: 600; color: #334155; font-size: 15px; }

    /* --- RIGHT: CHAT BOX --- */
    .chat-box {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: #f8fafc;
    }

    .chat-header {
      padding: 15px 20px;
      background: white;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      gap: 15px;
      flex-shrink: 0;
    }

    .back-btn {
      display: none; /* Hidden on desktop */
      background: none; border: none; font-size: 18px; color: #64748b; cursor: pointer;
    }

    .messages-scroll {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .msg {
      max-width: 75%;
      display: flex;
      flex-direction: column;
    }

    .msg.me { align-self: flex-end; align-items: flex-end; }
    .msg.them { align-self: flex-start; align-items: flex-start; }

    .msg-content {
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 15px;
      line-height: 1.5;
      position: relative;
    }

    .msg.me .msg-content {
      background: var(--primary);
      color: white;
      border-bottom-right-radius: 2px;
    }

    .msg.them .msg-content {
      background: white;
      color: #1e293b;
      border: 1px solid #e2e8f0;
      border-bottom-left-radius: 2px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .timestamp { font-size: 11px; color: #94a3b8; margin-top: 5px; }

    .send-box {
      padding: 15px;
      background: white;
      border-top: 1px solid #e2e8f0;
      flex-shrink: 0;
    }

    .send-form { display: flex; gap: 10px; }
    
    .send-form input {
      flex: 1;
      padding: 12px 20px;
      border-radius: 25px;
      border: 1px solid #e2e8f0;
      background: #f1f5f9;
      outline: none;
    }
    
    .send-form input:focus { background: white; border-color: var(--primary); }

    .send-btn {
      width: 45px; height: 45px;
      border-radius: 50%;
      border: none;
      background: linear-gradient(135deg, #bfb010ff 0%, #c4bc20ff 100%);
      color: white;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: transform 0.2s;
    }
    
    .send-btn:hover { transform: scale(1.05); }

    .empty-state {
      height: 100%;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      color: #94a3b8; text-align: center;
    }
    
    .empty-state i { font-size: 40px; margin-bottom: 10px; opacity: 0.5; }

    /* --- RESPONSIVE LOGIC --- */
    @media (max-width: 900px) {
        .content { padding: 0; }
        .header { background: var(--primary); margin: 0; padding: 15px; }
        .chat-container { border-radius: 0; box-shadow: none; }
        
        .sidebar {
            position: fixed; left: -100%; width: 280px; background: #1e293b; box-shadow: 4px 0 15px rgba(0,0,0,0.2);
        }
        .sidebar.active { left: 0; }
        .overlay.active { display: block; }
        
        .menu-toggle { display: block; }
        .close-sidebar { display: block; }

        /* VIEW SWITCHING LOGIC */
        .show-list .users-list { display: flex; width: 100%; }
        .show-list .chat-box { display: none; }
        
        .show-chat .users-list { display: none; }
        .show-chat .chat-box { display: flex; width: 100%; }

        .back-btn { display: block; } /* Show back button on mobile chat */
        .users-list { border-right: none; }
    }
  </style>
</head>
<body class="<?= $mobile_view_class ?>">

  <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

  <div class="wrapper">
    <div class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        QuickClean
        <button class="close-sidebar" onclick="toggleSidebar()">&times;</button>
      </div>
      <div class="nav-item"><a href="dashboard.php">📊 Dashboard</a></div>
      <div class="nav-item"><a href="assigned.php">📋 My Bookings</a></div>
      <div class="nav-item"><a href="schedule.php">📅 Schedule</a></div>
      <div class="nav-item"><a href="earnings.php">💰 Earnings</a></div>
      <div class="nav-item"><a href="cleaner_messages.php" class="active">💬 Messages</a></div>
      <div class="nav-item"><a href="cleaner-notification.php">🔔 Notifications</a></div>
      <div class="nav-item"><a href="profile.php">👤 Profile</a></div>
      <div class="nav-item"><a href="logout.php">Logout</a></div>
    </div>

    <div class="content">
      <div class="header">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <h1>Messages</h1>
      </div>

      <div class="chat-container">
        
        <div class="users-list">
          <div class="users-header">
            <h3>Conversations</h3>
          </div>
          <div class="users-scroll">
            <?php 
            $has_users = false;
            if ($users->num_rows > 0) {
                $has_users = true;
                $users->data_seek(0);
                while ($u = $users->fetch_assoc()): 
                    $initials = strtoupper(substr($u['name'], 0, 1));
            ?>
                <div class="user-item <?= ($u['user_id'] == $selected_user) ? 'active' : '' ?>"
                     onclick="window.location='cleaner_messages.php?u=<?= $u['user_id'] ?>'">
                    <div class="user-avatar"><?= $initials ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($u['name']) ?></div>
                    </div>
                </div>
            <?php endwhile; 
            } ?>
            
            <?php if (!$has_users): ?>
                <div class="empty-state" style="padding-top: 50px;">
                    <i class="far fa-comments"></i>
                    <p>No conversations yet.</p>
                </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="chat-box">
          <?php if (!$selected_user): ?>
              <div class="empty-state">
                  <i class="fas fa-paper-plane"></i>
                  <h3>Select a conversation</h3>
                  <p>Choose a customer from the list to start chatting.</p>
              </div>
          <?php else: ?>
              
              <div class="chat-header">
                  <button class="back-btn" onclick="window.location='cleaner_messages.php'"><i class="fas fa-arrow-left"></i></button>
                  <div class="user-avatar" style="width:32px; height:32px; font-size:12px;">
                      <?= strtoupper(substr($selected_user_name, 0, 1)) ?>
                  </div>
                  <h3><?= htmlspecialchars($selected_user_name) ?></h3>
              </div>

              <div class="messages-scroll" id="messagesScroll">
                  <?php 
                  if ($conversation && $conversation->num_rows > 0) {
                      $conversation->data_seek(0);
                      while ($msg = $conversation->fetch_assoc()): 
                  ?>
                      <div class="msg <?= ($msg['sender_id'] == $cleaner_id) ? 'me' : 'them' ?>">
                          <div class="msg-content">
                              <?= nl2br(htmlspecialchars($msg['message'])) ?>
                          </div>
                          <div class="timestamp">
                              <?= date('h:i A', strtotime($msg['created_at'])) ?>
                          </div>
                      </div>
                  <?php 
                      endwhile; 
                  } else {
                  ?>
                      <div class="empty-state">
                          <i class="fas fa-hand-sparkles"></i>
                          <p>Say hello to <?= htmlspecialchars($selected_user_name) ?>!</p>
                      </div>
                  <?php } ?>
              </div>

              <div class="send-box">
                  <form method="POST" class="send-form">
                      <input type="hidden" name="user_id" value="<?= $selected_user ?>">
                      <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
                      <button type="submit" name="send_reply" class="send-btn">
                          <i class="fas fa-paper-plane"></i>
                      </button>
                  </form>
              </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>

<script>
    // Sidebar Toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // Auto scroll to bottom of chat
    const messagesScroll = document.getElementById('messagesScroll');
    if (messagesScroll) {
        messagesScroll.scrollTop = messagesScroll.scrollHeight;
    }
</script>

</body>
</html>