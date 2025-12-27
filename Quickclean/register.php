<?php 
session_start();

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = "customer";

            // Insert into database
            $insert = $conn->prepare("INSERT INTO user (name, email, password, address, contact_num, role) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("ssssss", $name, $email, $hashed, $address, $contact, $role);

            if ($insert->execute()) {
                $_SESSION['registration_success'] = true;
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
            $insert->close();
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(135deg, #FFDB58 0%, #FFC107 90%);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

/* Animated background elements */
.bg-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 20s infinite ease-in-out;
}

.bg-circle:nth-child(1) { width: 300px; height: 300px; top: -100px; left: -100px; animation-delay: 0s; }
.bg-circle:nth-child(2) { width: 200px; height: 200px; bottom: -50px; right: -50px; animation-delay: 5s; }
.bg-circle:nth-child(3) { width: 150px; height: 150px; top: 50%; left: -75px; animation-delay: 10s; }

@keyframes float {
    0%, 100% { transform: translate(0,0) scale(1); }
    33% { transform: translate(30px,-30px) scale(1.1); }
    66% { transform: translate(-20px,20px) scale(0.9); }
}

.auth-container {
    width: 100%;
    max-width: 400px;
    background: white;
    padding: 25px 20px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    text-align: center;
    animation: slideUp 0.6s cubic-bezier(0.4,0,0.2,1);
    position: relative;
    z-index: 10;
    max-height: none;
    overflow-y: visible;
}

@keyframes slideUp {
    from { opacity:0; transform: translateY(30px) scale(0.95); }
    to { opacity:1; transform: translateY(0) scale(1); }
}

.logo-container { margin-bottom: 15px; animation: bounceIn 0.8s cubic-bezier(0.4,0,0.2,1) 0.2s backwards; }
@keyframes bounceIn { 0% {transform:scale(0);opacity:0;} 50%{transform:scale(1.1);} 100%{transform:scale(1);opacity:1;} }

.logo-icon { font-size: 48px; margin-bottom: 10px; display: inline-block; animation: rotate 3s ease-in-out infinite; }
@keyframes rotate { 0%,100%{transform:rotate(0deg);} 25%{transform:rotate(-10deg);} 75%{transform:rotate(10deg);} }

.auth-container h2 { color:#2E89F0; margin-bottom:8px; font-size:24px; font-weight:800; animation: fadeIn 0.8s ease 0.3s backwards; }
.subtitle { color:#718096; font-size:13px; margin-bottom:15px; animation: fadeIn 0.8s ease 0.4s backwards; }

.auth-container form { display:flex; flex-direction:column; gap:10px; }

.input-group { position:relative; animation: slideInLeft 0.5s ease backwards; }
.input-group:nth-child(1){animation-delay:0.5s;}
.input-group:nth-child(2){animation-delay:0.6s;}
.input-group:nth-child(3){animation-delay:0.7s;}
.input-group:nth-child(4){animation-delay:0.8s;}
.input-group:nth-child(5){animation-delay:0.9s;}
.input-group:nth-child(6){animation-delay:1s;}

@keyframes slideInLeft { from {opacity:0; transform:translateX(-20px);} to {opacity:1; transform:translateX(0);} }

.input-group input {
    width: 100%;
    padding: 10px 12px;
    padding-left:36px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size:14px;
    font-family: inherit;
    transition: all 0.3s ease;
    background: #f7fafc;
}

.input-group input:focus {
    outline: none;
    border-color:#2E89F0;
    background:white;
    box-shadow: 0 0 0 3px rgba(46,137,240,0.1);
}

.input-group .icon {
    position:absolute; left:12px; top:50%; transform:translateY(-50%);
    font-size:16px; color:#a0aec0; transition:color 0.3s ease;
}

.input-group input:focus + .icon { color:#2E89F0; }

.auth-container button {
    padding:12px;
    border:none;
    background: linear-gradient(135deg, #2E89F0 0%, #1c6ed6  100%);
    color:#0b3b66; font-weight:700; font-size:14px;
    border-radius:12px; cursor:pointer;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
     box-shadow: 0 10px 30px rgba(46, 137, 240, 0.3);
    margin-top:6px;
    animation: slideInLeft 0.5s ease 1.1s backwards;
}

.auth-container button:hover { transform:translateY(-2px);  box-shadow: 0 10px 30px rgba(20, 120, 230, 0.3);; background: linear-gradient(135deg,  #2E89F0 0%, #1c6ed6 ); }
.auth-container button:active { transform:translateY(0); }

.divider { margin:18px 0; display:flex; align-items:center; gap:10px; animation: fadeIn 0.8s ease 1.2s backwards; }
.divider::before,.divider::after { content:''; flex:1; height:1px; background:#e2e8f0; }
.divider span { color:#a0aec0; font-size:13px; }

.auth-container p { margin-top:15px; font-size:13px; color:#4a5568; animation:fadeIn 0.8s ease 1.3s backwards; }
.auth-container a { color:#2E89F0; text-decoration:none; font-weight:600; transition:color 0.3s ease; }
.auth-container a:hover { color:#1c6ed6; text-decoration:underline; }

.message { padding:12px 14px; border-radius:10px; font-size:13px; margin-bottom:15px; display:flex; align-items:center; gap:8px; animation:shake 0.5s ease; }
@keyframes shake { 0%,100%{transform:translateX(0);} 25%{transform:translateX(-10px);} 75%{transform:translateX(10px);} }
.error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }

.password-strength { height:4px; background:#e2e8f0; border-radius:2px; margin-top:6px; overflow:hidden; }
.password-strength-bar { height:100%; width:0%; transition:all 0.3s ease; border-radius:2px; }
.password-strength-bar.weak { width:33%; background:#ef4444; }
.password-strength-bar.medium { width:66%; background:#f59e0b; }
.password-strength-bar.strong { width:100%; background:#10b981; }

@media(max-width:768px){
    .auth-container { padding:20px 15px; margin:10px; }
    .auth-container h2 { font-size:22px; }
    .logo-icon { font-size:40px; }
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
        <h2>Create Your Account</h2>
        <p class="subtitle">Join QuickClean and start booking services today!</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="message error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="registerForm">
        <div class="input-group">
            <input type="text" name="name" id="name" placeholder="Full Name" required>
            <span class="icon">👤</span>
        </div>

        <div class="input-group">
            <input type="email" name="email" id="email" placeholder="Email Address" required>
            <span class="icon">📧</span>
        </div>

        <div class="input-group">
            <input type="text" name="address" id="address" placeholder="Address (Optional)">
            <span class="icon">📍</span>
        </div>

        <div class="input-group">
            <input type="text" name="contact" id="contact" placeholder="Contact Number (Optional)">
            <span class="icon">📱</span>
        </div>

        <div class="input-group">
            <input type="password" name="password" id="password" placeholder="Password (min. 6 characters)" minlength="6" required>
            <span class="icon">🔒</span>
            <div class="password-strength">
                <div class="password-strength-bar" id="strengthBar"></div>
            </div>
        </div>

        <div class="input-group">
            <input type="password" name="confirm" id="confirm" placeholder="Confirm Password" required>
            <span class="icon">🔐</span>
        </div>

        <button type="submit">🚀 Create Account</button>
    </form>

    <div class="divider">
        <span>Already have an account?</span>
    </div>

    <p><a href="login.php">👉 Login here</a></p>
</div>

<script>
// Password strength
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strengthBar');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;

    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;

    strengthBar.className = 'password-strength-bar';
    
    if (strength <= 2) strengthBar.classList.add('weak');
    else if (strength <= 4) strengthBar.classList.add('medium');
    else strengthBar.classList.add('strong');
});

// Confirm password
const form = document.getElementById('registerForm');
const confirmInput = document.getElementById('confirm');

form.addEventListener('submit', function(e) {
    if(passwordInput.value !== confirmInput.value){
        e.preventDefault();
        alert('⚠️ Passwords do not match!');
        confirmInput.focus();
    }
});

// Input animation
const inputs = document.querySelectorAll('.input-group input');
inputs.forEach(input => {
    input.addEventListener('focus', function(){ this.parentElement.style.transform='translateX(5px)'; });
    input.addEventListener('blur', function(){ this.parentElement.style.transform='translateX(0)'; });
});
</script>

</body>
</html>
