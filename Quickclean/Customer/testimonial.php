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
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>QuickClean - Testimonials</title>

  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;800&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

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

html, body {
  height:100%;
  margin:0;
  font-family:"Poppins",sans-serif;
  background:#fff;
  color:#123;
}

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

/* ------------------ TESTIMONIALS ------------------ */
.testimonials-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 80px 24px;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 35px;
  margin-top: 20px;
}

.testimonial-card {
  background: #fff;
  border-radius: 20px;
  padding: 35px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
  position: relative;
}

.testimonial-card::before {
  content: '"';
  position: absolute;
  top: 20px;
  left: 25px;
  font-size: 80px;
  color: rgba(109, 175, 242, 0.15);
  font-family: Georgia, serif;
  line-height: 1;
}

.testimonial-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
  border-color: var(--brand-blue);
}

.testimonial-content {
  position: relative;
  z-index: 1;
}

.testimonial-text {
  font-size: 16px;
  line-height: 1.8;
  color: #444;
  margin-bottom: 24px;
  font-style: italic;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 16px;
  padding-top: 20px;
  border-top: 2px solid #f0f0f0;
}

.author-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--brand-blue) 0%, var(--text-blue) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 20px;
  font-family: 'Baloo 2';
  flex-shrink: 0;
}

.author-info {
  text-align: left;
}

.author-name {
  font-weight: 700;
  color: var(--text-blue);
  font-size: 18px;
  margin-bottom: 4px;
  font-family: 'Baloo 2';
}

.author-label {
  font-size: 14px;
  color: #777;
}

.rating {
  color: #ffc107;
  font-size: 18px;
  margin-top: 8px;
}

/* ------------------ FOOTER ------------------ */
footer { 
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  text-align: center; 
  padding: 40px 24px; 
  margin-top: 60px; 
  font-size: 16px;
}

footer p {
  margin: 0;
}

/* ------------------ MEDIA QUERIES ------------------ */

/* Tablets */
@media (max-width: 1024px) {
  .logo { height: 55px; }
  .tagline { font-size: 16px; }
  .nav-link { font-size: 15px; padding: 6px 10px; }
  .page-hero h1 { font-size: 48px; }
  .page-hero p { font-size: 17px; }
  .testimonials-grid { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px; }
  .testimonial-card { padding: 30px; }
  .testimonial-text { font-size: 15px; }
}

/* Small tablets / large phones */
@media (max-width: 768px) {
  .logo { height: 50px; }
  .tagline { font-size: 15px; }
  .nav-list { flex-wrap: wrap; gap: 12px; }
  .nav-link { font-size: 14px; padding: 5px 8px; }
  .page-hero { padding: 50px 20px; }
  .page-hero h1 { font-size: 40px; }
  .page-hero p { font-size: 16px; }
  .testimonials-section { padding: 60px 20px; }
  .testimonials-grid { grid-template-columns: 1fr; gap: 24px; }
  .testimonial-card { padding: 28px; }
  .testimonial-text { font-size: 15px; }
  .author-name { font-size: 17px; }
}

/* Phones */
@media (max-width: 480px) {
  .logo { height: 45px; }
  .tagline { font-size: 14px; }
  .nav-bar { flex-direction: column; height: auto; padding: 10px 0; }
  .nav-list { flex-direction: column; gap: 10px; }
  .nav-link { font-size: 14px; padding: 8px 12px; }
  .page-hero { padding: 40px 16px; }
  .page-hero h1 { font-size: 32px; }
  .page-hero p { font-size: 15px; }
  .testimonials-section { padding: 50px 16px; }
  .testimonial-card { padding: 24px; }
  .testimonial-card::before { font-size: 60px; top: 15px; left: 20px; }
  .testimonial-text { font-size: 14px; line-height: 1.7; }
  .author-avatar { width: 45px; height: 45px; font-size: 18px; }
  .author-name { font-size: 16px; }
  .author-label { font-size: 13px; }
  .rating { font-size: 16px; }
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


  <!-- NAVIGATION -->
  <nav class="nav-bar">
    <ul class="nav-list">
      <li><a href="customer-home.php" class="nav-link">Home</a></li>
      <li><a href="servicepage.php" class="nav-link">Services</a></li>
      <li><a href="testimonial.php" class="nav-link active">Testimonies</a></li>
      <li><a href="aboutus.php" class="nav-link">About Us</a></li>
    </ul>
  </nav>

  <!-- PAGE HERO -->
  <section class="page-hero">
    <div class="page-hero-content">
      <h1>Client Testimonials</h1>
      <p>Hear from our satisfied customers about their experience with QuickClean's professional cleaning services.</p>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="testimonials-section">
    <div class="testimonials-grid">
      
      <div class="testimonial-card">
        <div class="testimonial-content">
          <p class="testimonial-text">QuickClean saved me so much time! Unlike other cleaners who rush the job, their team really pays attention to every detail. From now on, QuickClean will be our go-to home cleaning service. Thank you so much!</p>
          <div class="testimonial-author">
            <div class="author-avatar">A</div>
            <div class="author-info">
              <div class="author-name">Angela M.</div>
              <div class="author-label">Verified Customer</div>
              <div class="rating">★★★★★</div>
            </div>
          </div>
        </div>
      </div>

      <div class="testimonial-card">
        <div class="testimonial-content">
          <p class="testimonial-text">I'm beyond satisfied with the outcome of QuickClean's service. The team was professional, thorough, and very focused on their work. Every corner of the house was spotless, and it truly exceeded my expectations. Definitely a 5-star experience—highly recommended!</p>
          <div class="testimonial-author">
            <div class="author-avatar">R</div>
            <div class="author-info">
              <div class="author-name">Robert D.</div>
              <div class="author-label">Verified Customer</div>
              <div class="rating">★★★★★</div>
            </div>
          </div>
        </div>
      </div>

      <div class="testimonial-card">
        <div class="testimonial-content">
          <p class="testimonial-text">10/10 for dedication and consistency! The cleaners worked quietly but efficiently, making sure our home looked brand new after a stressful renovation. We've tried other services before, but QuickClean went above and beyond what we expected.</p>
          <div class="testimonial-author">
            <div class="author-avatar">S</div>
            <div class="author-info">
              <div class="author-name">Stephanie L.</div>
              <div class="author-label">Verified Customer</div>
              <div class="rating">★★★★★</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <p>&copy; <?php echo date('Y'); ?> QuickClean. All Rights Reserved. Clean Spaces, Happy Faces.</p>
  </footer>
</body>
</html>