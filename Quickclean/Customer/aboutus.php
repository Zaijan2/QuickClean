<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only allow customers
if ($_SESSION['role'] !== 'customer') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin-dashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// --- DATABASE CONNECTION ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch user data including profile picture
$stmt = $conn->prepare("SELECT name, profile_pic FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$current_page = basename($_SERVER['PHP_SELF']); // e.g., "customer-home.php"

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QuickClean - About & Contact</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Baloo+2&display=swap" rel="stylesheet">
<style>
:root{
  --brand-blue: #6DAFF2;
  --nav-yellow: #FFDB58;
  --cta-yellow: #FFD54A;
  --text-blue: #2E89F0;
  --muted-bg: #F0F4F5;
  --nav-link-color: #0b3b66;
  --header-height: 110px;
  --nav-height: 64px;
  --max-content-width: 1360px;
}
* { box-sizing: border-box; }
html,body { height:100%; margin:0; font-family:"Poppins",sans-serif; background:#fff; color:#123; }

/* ------------------ HEADER ------------------ */
.site-header, .nav-bar {
  position: fixed;
  left: 0;
  width: 100%;
  z-index: 999;
}

.site-header {
  background: var(--brand-blue);
  height: var(--header-height);
  display: flex;
  align-items: center;
  top: 0;
  padding: 0 15px;
}

.header-inner {
  width: 100%;
  max-width: var(--max-content-width);
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo { height: 75px; max-height:75px; }
.tagline { font-family:"Baloo 2"; color:#fff; font-weight:600; font-size:20px; }

/* PROFILE */
.profile-link { text-decoration: none; }
.profile {
  width: 40px;
  height: 40px;
  background: #f9fafb;
  color: #005fcc;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}
.profile img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.profile:hover { 
  background: #005fcc; 
  color:white;
  transform: scale(1.1);
}

/* ------------------ NAV ------------------ */
.nav-bar{
  background: var(--nav-yellow);
  height: var(--nav-height);
  display: flex;
  align-items: center;
  top: var(--header-height);
}
.nav-list{
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 20px;
  list-style: none;
  width: 100%;
  margin: 0;
  padding: 0;
}
.nav-link{
  color: var(--nav-link-color);
  text-decoration: none;
  font-weight: 600;
  font-size: 18px;
  padding: 8px 12px;
}
.nav-link.active{
  text-decoration: underline;
  text-underline-offset: 6px;
  font-weight: 800;
}

/* ------------------ BODY PADDING ------------------ */
body {
  padding-top: calc(var(--header-height) + var(--nav-height));
  background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
}

/* ------------------ PAGE HERO ------------------ */
.page-hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 60px 24px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.page-hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
  opacity: 0.3;
}

.page-hero-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: 0 auto;
}

.page-hero h1 {
  color: #fff;
  font-family: 'Baloo 2';
  font-size: 56px;
  margin-bottom: 16px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
  font-weight: 800;
}

.page-hero p {
  color: rgba(255,255,255,0.95);
  font-size: 18px;
  line-height: 1.6;
  max-width: 600px;
  margin: 0 auto;
}

/* ------------------ ABOUT SECTION ------------------ */
.about-section {
  max-width: 1100px;
  margin: 80px auto;
  padding: 0 24px;
}

.about-card {
  background: #fff;
  border-radius: 20px;
  padding: 50px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  margin-bottom: 40px;
}

.about-card h2 {
  font-size: 36px;
  color: var(--text-blue);
  margin-bottom: 24px;
  font-family: 'Baloo 2';
  font-weight: 800;
}

.about-card p {
  color: #555;
  line-height: 1.8;
  font-size: 16px;
  margin-bottom: 20px;
}

.about-card p:last-child {
  margin-bottom: 0;
}

.about-card strong {
  color: var(--text-blue);
  font-weight: 600;
}

/* ------------------ CONTACT SECTION ------------------ */
.contact-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 80px 24px;
  margin-top: 60px;
}

.contact-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: start;
}

.contact-info {
  color: #fff;
}

.contact-info h2 {
  font-family: 'Baloo 2';
  font-size: 42px;
  margin-bottom: 24px;
  font-weight: 800;
}

.contact-info > p {
  font-size: 18px;
  line-height: 1.7;
  margin-bottom: 40px;
  opacity: 0.95;
}

.contact-details {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 16px;
  background: rgba(255,255,255,0.1);
  padding: 16px 20px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.contact-item:hover {
  background: rgba(255,255,255,0.2);
  transform: translateX(5px);
}

.contact-icon {
  font-size: 24px;
  min-width: 30px;
  text-align: center;
}

/* CONTACT FORM */
.contact-form {
  background: #fff;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.contact-form h3 {
  font-family: 'Baloo 2';
  font-size: 28px;
  color: var(--text-blue);
  margin-bottom: 24px;
  font-weight: 700;
}

.contact-form form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: #555;
}

.contact-form input,
.contact-form select {
  padding: 14px 18px;
  border-radius: 10px;
  border: 2px solid #e0e0e0;
  background: #f8f9fa;
  font-size: 15px;
  color: #333;
  transition: all 0.3s ease;
  font-family: 'Poppins', sans-serif;
}

.contact-form input:focus,
.contact-form select:focus {
  outline: none;
  border-color: var(--brand-blue);
  background: #fff;
}

.contact-form button {
  padding: 14px 40px;
  border: none;
  background: var(--nav-yellow);
  color: var(--nav-link-color);
  font-weight: 700;
  cursor: pointer;
  border-radius: 25px;
  transition: all 0.3s ease;
  align-self: center;
  font-size: 16px;
  margin-top: 10px;
  box-shadow: 0 4px 15px rgba(255, 219, 88, 0.3);
}

.contact-form button:hover {
  background: #ffd700;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 219, 88, 0.4);
}

/* ------------------ FOOTER ------------------ */
.footer {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  padding: 40px 24px;
  text-align: center;
  margin-top: 60px;
}

.footer p {
  margin: 0;
  font-size: 16px;
}

/* ------------------ MEDIA QUERIES ------------------ */
@media (max-width: 1024px) {
  .logo { height: 55px; }
  .tagline { font-size: 16px; }
  .nav-link { font-size: 15px; padding: 6px 10px; }
  .page-hero h1 { font-size: 48px; }
  .about-card { padding: 40px; }
  .about-card h2 { font-size: 32px; }
  .contact-container { gap: 40px; }
  .contact-info h2 { font-size: 36px; }
}

@media (max-width: 768px) {
  .logo { height: 50px; }
  .tagline { font-size: 15px; }
  .nav-list { flex-wrap: wrap; gap: 12px; }
  .nav-link { font-size: 14px; padding: 5px 8px; }
  .page-hero { padding: 50px 20px; }
  .page-hero h1 { font-size: 40px; }
  .page-hero p { font-size: 16px; }
  .about-section { margin: 60px auto; padding: 0 20px; }
  .about-card { padding: 32px; }
  .about-card h2 { font-size: 28px; }
  .about-card p { font-size: 15px; }
  .contact-section { padding: 60px 20px; }
  .contact-container {
    grid-template-columns: 1fr;
    gap: 50px;
  }
  .contact-info h2 { font-size: 32px; }
  .contact-form { padding: 32px; }
}

@media (max-width: 480px) {
  .logo { height: 45px; }
  .tagline { font-size: 14px; }
  .nav-bar { flex-direction: column; height: auto; padding: 10px 0; }
  .nav-list { flex-direction: column; gap: 10px; }
  .nav-link { font-size: 14px; padding: 8px 12px; }
  .page-hero { padding: 40px 16px; }
  .page-hero h1 { font-size: 32px; }
  .page-hero p { font-size: 15px; }
  .about-section { margin: 50px auto; padding: 0 16px; }
  .about-card { padding: 24px; }
  .about-card h2 { font-size: 24px; }
  .about-card p { font-size: 14px; }
  .contact-section { padding: 50px 16px; }
  .contact-info h2 { font-size: 28px; }
  .contact-info > p { font-size: 16px; }
  .contact-item { font-size: 14px; padding: 12px 16px; }
  .contact-form { padding: 24px; }
  .contact-form h3 { font-size: 24px; }
  .contact-form input,
  .contact-form select { padding: 12px 16px; font-size: 14px; }
  .contact-form button { padding: 12px 32px; font-size: 15px; }
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <div class="header-inner">
    <img src="logo.png" alt="QuickClean logo" class="logo">

    <div class="tagline">QuickClean: Clean Spaces, Happy Faces.</div>

    <a href="user_page.php" class="profile-link">
      <div class="profile">
        <?php
          $pic = $userData['profile_pic'];
          $path = "uploads/" . $pic;

          if (!empty($pic) && file_exists($path)) {
              echo '<img src="'.$path.'" 
                        style="width:40px;height:40px;border-radius:50%;object-fit:cover;" 
                        alt="Profile">';
          } else {
              echo htmlspecialchars(substr($userData['name'], 0, 1));
          }
        ?>
      </div>
    </a>

  </div>
</header>

<nav class="nav-bar">
  <ul class="nav-list">
    <li><a href="customer-home.php" class="nav-link <?php if($current_page=='customer-home.php'){echo 'active';} ?>">Home</a></li>
    <li><a href="servicepage.php" class="nav-link <?php if($current_page=='servicepage.php'){echo 'active';} ?>">Services</a></li>
    <li><a href="testimonial.php" class="nav-link <?php if($current_page=='testimonial.php'){echo 'active';} ?>">Testimonies</a></li>
    <li><a href="aboutus.php" class="nav-link <?php if($current_page=='aboutus.php'){echo 'active';} ?>">About Us</a></li>
  </ul>
</nav>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="page-hero-content">
    <h1>About QuickClean</h1>
    <p>Learn more about our mission, values, and the story behind our commitment to delivering exceptional cleaning services.</p>
  </div>
</section>

<!-- ABOUT US SECTION -->
<section class="about-section">
  <div class="about-card">
    <h2>About Us</h2>
    <p>At <strong>QuickClean</strong>, we believe that a clean home is the foundation of comfort and well-being. Our mission is to make cleaning easy, reliable, and accessible for every household. We specialize in professional home cleaning and post-construction cleaning services tailored to your needs.</p>
    <p>Our team is trained, trustworthy, and passionate about delivering spotless results every time. With just a few clicks, you can book our services online and enjoy hassle-free scheduling.</p>
    <p>At QuickClean, we don't just clean—we create fresh, welcoming spaces where you can truly relax and feel at home.</p>
  </div>

  <div class="about-card">
    <h2>Our History</h2>
    <p>QuickClean was founded with a simple goal: to make home cleaning faster, easier, and more reliable for busy households. What started as a small idea to help families maintain clean and comfortable homes has grown into a trusted service platform, offering professional cleaning solutions for every need.</p>
    <p>We've built our reputation on dedication, quality, and customer satisfaction. Every home we clean tells a story of transformation and care.</p>
    <p><strong>QuickClean: Clean Spaces, Happy Faces.</strong></p>
  </div>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section">
  <div class="contact-container">
    <div class="contact-info">
      <h2>Get In Touch</h2>
      <p>Need a spotless home? Connect with QuickClean today. Cleanliness is our promise, and we're always ready to serve you.</p>
      <div class="contact-details">
        <div class="contact-item">
          <span class="contact-icon">📞</span>
          <span>+63 923456789</span>
        </div>
        <div class="contact-item">
          <span class="contact-icon">📧</span>
          <span>support@quickclean.com</span>
        </div>
        <div class="contact-item">
          <span class="contact-icon">📍</span>
          <span>123 Clean Street, Quezon City, Philippines</span>
        </div>
      </div>
    </div>

    <div class="contact-form">
      <h3>Request a Quote</h3>
      <form>
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" placeholder="Enter your full name" required>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="text" id="phone" placeholder="Enter your phone number" required>
        </div>
        <div class="form-group">
          <label for="service">Service Needed</label>
          <select id="service" required>
            <option value="">Select a service</option>
            <option value="Deep Cleaning">Deep Cleaning</option>
            <option value="Regular Cleaning">Regular Cleaning</option>
            <option value="Post-Construction Cleaning">Post-Construction Cleaning</option>
            <option value="Move-in/Move-out Cleaning">Move-in/Move-out Cleaning</option>
            <option value="Upholstery Cleaning">Upholstery Cleaning</option>
          </select>
        </div>
        <button type="submit">Get Your Quote</button>
      </form>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <p>&copy; <?php echo date('Y'); ?> QuickClean. All rights reserved. Clean Spaces, Happy Faces.</p>
</footer>

</body>
</html>