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

// Fetch active services
$services = $conn->query("SELECT * FROM services WHERE status='active'");

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
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>QuickClean - Customer Home</title>

<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

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

html,body {
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

.logo { height: 75px; max-height: 75px; }
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
}

/* ------------------ HERO ------------------ */
.hero{ 
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 100px 24px 80px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
  opacity: 0.3;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 1000px;
  margin: 0 auto;
}

.hero h1{ 
  font-family:"Baloo 2"; 
  color: #fff;
  font-size: 64px; 
  margin-bottom: 20px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
  animation: fadeInUp 0.8s ease;
}

.hero p{ 
  font-size: 20px; 
  color: rgba(255,255,255,0.95);
  margin-bottom: 40px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

.cta-btn{ 
  background: var(--cta-yellow);
  border: none;
  padding: 16px 40px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  color: var(--nav-link-color);
}

.cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  background: #ffd700;
}

/* HERO VIDEO */
.video-container {
  margin-top: 50px;
  position: relative;
  display: inline-block;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  max-width: 700px;
  width: 100%;
}

.hero video {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 16px;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ------------------ SERVICES ------------------ */
.services-section { 
  padding: 80px 24px;
  background: #fff;
}

.services-header {
  text-align: center;
  margin-bottom: 60px;
}

.services-header h2 {
  color: var(--text-blue);
  font-family: 'Baloo 2';
  font-size: 48px;
  margin-bottom: 16px;
  font-weight: 800;
}

.services-header p {
  color: #666;
  font-size: 18px;
  max-width: 600px;
  margin: 0 auto;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 40px;
  max-width: var(--max-content-width);
  margin: 0 auto;
}

.service-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  overflow: hidden;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.service-card:hover { 
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
  border-color: var(--brand-blue);
}

.service-image-container {
  position: relative;
  overflow: hidden;
  height: 220px;
}

.service-card img { 
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.service-card:hover img {
  transform: scale(1.1);
}

.service-card-content { 
  padding: 24px;
}

.service-card-content h3 { 
  color: var(--text-blue);
  margin-bottom: 12px;
  font-size: 22px;
  font-weight: 600;
  font-family: 'Baloo 2';
}

.service-card-content p { 
  font-size: 15px;
  color: #555;
  line-height: 1.6;
  margin-bottom: 16px;
}

.price { 
  font-weight: 700;
  color: #2E89F0;
  font-size: 24px;
  display: block;
  margin-top: 12px;
}

.no-services {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
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

/* Tablets */
@media (max-width: 1024px) {
  .logo { height: 55px; }
  .tagline { font-size: 16px; }
  .nav-link { font-size: 15px; padding: 6px 10px; }
  .hero h1 { font-size: 48px; }
  .hero p { font-size: 18px; }
  .services-header h2 { font-size: 40px; }
  .service-image-container { height: 180px; }
  .service-card-content h3 { font-size: 20px; }
  .service-card-content p { font-size: 14px; }
  .price { font-size: 22px; }
}

/* Small tablets / large phones */
@media (max-width: 768px) {
  .logo { height: 50px; }
  .tagline { font-size: 15px; }
  .nav-list { flex-wrap: wrap; gap: 12px; }
  .nav-link { font-size: 14px; padding: 5px 8px; }
  .hero { padding: 80px 24px 60px; }
  .hero h1 { font-size: 40px; }
  .hero p { font-size: 16px; }
  .services-header h2 { font-size: 36px; }
  .services-grid { gap: 30px; }
  .service-image-container { height: 160px; }
  .service-card-content h3 { font-size: 19px; }
  .service-card-content p { font-size: 14px; }
  .price { font-size: 20px; }
}

/* Phones */
@media (max-width: 480px) {
  .logo { height: 45px; }
  .tagline { font-size: 14px; }
  .nav-bar { flex-direction: column; height: auto; padding: 10px 0; }
  .nav-list { flex-direction: column; gap: 10px; }
  .nav-link { font-size: 14px; padding: 8px 12px; }
  .hero { padding: 60px 20px 50px; }
  .hero h1 { font-size: 32px; }
  .hero p { font-size: 15px; }
  .cta-btn { padding: 14px 32px; font-size: 16px; }
  .services-section { padding: 60px 20px; }
  .services-header h2 { font-size: 32px; }
  .services-header p { font-size: 16px; }
  .services-grid { 
    grid-template-columns: 1fr;
    gap: 24px;
  }
  .service-image-container { height: 180px; }
  .service-card-content { padding: 20px; }
  .service-card-content h3 { font-size: 18px; }
  .service-card-content p { font-size: 13px; }
  .price { font-size: 20px; }
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


<!-- NAV -->
<nav class="nav-bar">
  <ul class="nav-list">
    <li><a href="customer-home.php" class="nav-link active">Home</a></li>
    <li><a href="servicepage.php" class="nav-link">Services</a></li>
    <li><a href="testimonial.php" class="nav-link">Testimonies</a></li>
    <li><a href="aboutus.php" class="nav-link">About Us</a></li>
  </ul>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
    <p>Experience professional cleaning services that transform your space. We're here to make your life easier and your home spotless.</p>
    
    <div class="video-container">
      <video width="100%" autoplay loop muted playsinline>
        <source src="ads.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="services-section">
  <div class="services-header">
    <h2>Our Services</h2>
    <p>Discover our range of professional cleaning solutions tailored to meet your needs</p>
  </div>
  
  <div class="services-grid">
    <?php if ($services && $services->num_rows > 0): ?>
      <?php while($row = $services->fetch_assoc()): ?>
        <div class="service-card">
          <div class="service-image-container">
            <img src="../Admin/uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['service_name']); ?>">
          </div>
          <div class="service-card-content">
            <h3><?php echo htmlspecialchars($row['service_name']); ?></h3>
            <p><?php echo htmlspecialchars($row['description']); ?></p>
            <span class="price">₱<?php echo number_format($row['price'], 2); ?></span>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="no-services">
        <p>No services available at the moment. Please check back later!</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <p>&copy; <?php echo date('Y'); ?> QuickClean. All rights reserved. Clean Spaces, Happy Faces.</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>