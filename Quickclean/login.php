<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: Admin/admindashboard.php");
    } elseif ($_SESSION['role'] === 'cleaner') {
        header("Location: Cleaner/dashboard.php");
    } else {
        header("Location: Customer/customer-home.php");
    }
    exit();
}

// DB CONNECTION
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "quickclean";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

// LOGIN PROCESS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass  = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($pass, $user['password'])) {

            // Session Set
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: Admin/admindashboard.php");
            } elseif ($user['role'] === 'cleaner') {
                header("Location: Cleaner/dashboard.php");
            } else {
                header("Location: Customer/customer-home.php");
            }
            exit();

        } else {
            $message = "❌ Incorrect password!";
        }
    } else {
        $message = "❌ No account found with that email!";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }

body {
    background: linear-gradient(135deg, #FFDB58 0%, #FFC107 90%);
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
    position:relative;
    overflow:hidden;
}

.bg-circle {
    position:absolute;
    border-radius:50%;
    background: rgba(255,255,255,0.2);
    animation: float 20s infinite ease-in-out;
}
.bg-circle:nth-child(1){ width:300px; height:300px; top:-100px; left:-100px; }
.bg-circle:nth-child(2){ width:200px; height:200px; bottom:-50px; right:-50px; animation-delay:5s; }
.bg-circle:nth-child(3){ width:150px; height:150px; top:50%; left:-75px; animation-delay:10s; }

@keyframes float { 
    0%,100%{transform:translate(0,0) scale(1);} 
    33%{transform:translate(30px,-30px) scale(1.1);} 
    66%{transform:translate(-20px,20px) scale(0.9);} 
}

.auth-container {
    width:100%; max-width:400px;
    background:white;
    padding:25px 20px;
    border-radius:20px;
    box-shadow:0 20px 60px rgba(0,0,0,0.3);
    text-align:center;
    z-index:10;
}

.logo-container { margin-bottom:15px; }
.logo-icon { font-size:48px; margin-bottom:10px; }

.auth-container h2 {
    color:#0b3b66;
    margin-bottom:8px;
    font-size:24px;
    font-weight:800;
}

.subtitle { color:#4a5568; font-size:14px; margin-bottom:20px; }

.auth-container form { display:flex; flex-direction:column; gap:10px; }

.input-group { position:relative; }
.input-group input {
    width:100%; padding:10px 12px; padding-left:36px;
    border:2px solid #e2e8f0;
    border-radius:12px;
    background:#f7fafc;
    transition:0.3s;
}
.input-group input:focus {
    border-color:#0b3b66; background:white;
    box-shadow:0 0 0 3px rgba(11,59,102,0.1);
}
.icon {
    position:absolute; left:12px; top:50%;
    transform:translateY(-50%);
    font-size:16px; color:#a0aec0;
}

button {
    padding:12px;
    border:none;
    background: linear-gradient(135deg,#2E89F0 0%,#1c6ed6 100%);
    color:white;
    font-weight:700; font-size:14px;
    border-radius:12px; cursor:pointer;
    transition:0.3s;
}
button:hover {
    transform:translateY(-2px);
    box-shadow:0 6px 20px rgba(46,137,240,0.5);
}

.divider { margin:18px 0; display:flex; align-items:center; gap:10px; }
.divider::before,.divider::after { content:''; flex:1; height:1px; background:#e2e8f0; }
.divider span { color:#a0aec0; }

.message {
    padding:12px 14px; border-radius:10px;
    background:#fee2e2; color:#991b1b;
    border:1px solid #fecaca;
    margin-bottom:10px;
}
</style>
</head>
<body>

<div class="bg-circle"></div>
<div class="bg-circle"></div>
<div class="bg-circle"></div>

<div class="auth-container">
    <div class="logo-container">
        <div class="logo-icon">🧹</div>
        <h2>Login</h2>
        <p class="subtitle">Welcome back! Please login to continue.</p>
    </div>

    <?php if ($message) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
            <span class="icon">📧</span>
        </div>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
            <span class="icon">🔒</span>
        </div>

        <button type="submit">🚀 Login</button>
    </form>

    <div class="divider"><span>Don't have an account?</span></div>

    <p><a href="register.php">Register here</a></p>
</div>

</body>
</html>
