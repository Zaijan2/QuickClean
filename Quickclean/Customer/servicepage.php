<?php
session_start();

// --- CHECK LOGIN ---
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

// Fetch services
$query = "SELECT * FROM services ORDER BY service_id DESC";
$result = $conn->query($query);

// Fetch user data including profile picture
$stmt = $conn->prepare("SELECT name, profile_pic FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>QuickClean - Services</title>

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

/* ------------------ SERVICES ------------------ */
.services-section { 
  padding: 80px 24px; 
  background: transparent;
}

.services-container {
  max-width: var(--max-content-width);
  margin: 0 auto;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 35px;
  margin-top: 20px;
}

.service-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
  display: flex;
  flex-direction: column;
}

.service-card:hover { 
  transform: translateY(-10px); 
  box-shadow: 0 12px 40px rgba(0,0,0,0.15); 
  border-color: var(--brand-blue);
}

.service-image-wrapper {
  position: relative;
  height: 240px;
  overflow: hidden;
  background: var(--muted-bg);
}

.service-card img { 
  width: 100%; 
  height: 100%; 
  object-fit: cover; 
  transition: transform 0.4s ease;
}

.service-card:hover img {
  transform: scale(1.1);
}

.service-content {
  padding: 24px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.service-card h3 { 
  color: var(--text-blue); 
  margin-bottom: 12px; 
  font-size: 22px;
  font-family: 'Baloo 2';
  font-weight: 700;
}

.service-card p { 
  color: #555; 
  font-size: 15px; 
  line-height: 1.6;
  margin-bottom: 16px;
  flex-grow: 1;
}

.service-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.service-price { 
  color: #2E89F0; 
  font-weight: 700; 
  font-size: 26px;
  font-family: 'Baloo 2';
}

.btn-book { 
  background: var(--nav-yellow); 
  border: none; 
  padding: 12px 28px; 
  border-radius: 25px; 
  font-weight: 700; 
  cursor: pointer; 
  transition: all 0.3s ease; 
  font-size: 15px;
  color: var(--nav-link-color);
  text-decoration: none;
  display: inline-block;
  box-shadow: 0 4px 12px rgba(255, 219, 88, 0.3);
}

.btn-book:hover { 
  background: #ffd700; 
  transform: translateY(-2px); 
  box-shadow: 0 6px 20px rgba(255, 219, 88, 0.4);
}

.no-services {
  text-align: center;
  padding: 80px 20px;
  color: #666;
}

.no-services p {
  font-size: 18px;
  color: #777;
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
  .services-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 28px; }
  .service-image-wrapper { height: 200px; }
  .service-card h3 { font-size: 20px; }
  .service-card p { font-size: 14px; }
  .service-price { font-size: 24px; }
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
  .services-section { padding: 60px 20px; }
  .services-grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
  .service-image-wrapper { height: 180px; }
  .service-card h3 { font-size: 19px; }
  .service-card p { font-size: 14px; }
  .service-price { font-size: 22px; }
  .btn-book { padding: 10px 24px; font-size: 14px; }
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
  .services-section { padding: 50px 16px; }
  .services-grid { 
    grid-template-columns: 1fr; 
    gap: 20px; 
  }
  .service-image-wrapper { height: 200px; }
  .service-content { padding: 20px; }
  .service-card h3 { font-size: 18px; }
  .service-card p { font-size: 13px; }
  .service-price { font-size: 22px; }
  .btn-book { padding: 10px 20px; font-size: 14px; }
  .service-footer { flex-direction: column; gap: 12px; align-items: stretch; }
  .btn-book { width: 100%; text-align: center; }
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
    <li><a href="customer-home.php" class="nav-link">Home</a></li>
    <li><a href="servicepage.php" class="nav-link active">Services</a></li>
    <li><a href="testimonial.php" class="nav-link">Testimonies</a></li>
    <li><a href="aboutus.php" class="nav-link">About Us</a></li>
  </ul>
</nav>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="page-hero-content">
    <h1>Our Services</h1>
    <p>Browse our comprehensive range of professional cleaning services designed to meet all your needs. Quality service, every time.</p>
  </div>
</section>

<!-- SERVICES -->
<section class="services-section">
  <div class="services-container">
    <div class="services-grid">
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="service-card">
            <div class="service-image-wrapper">
              <img src="../Admin/uploads/<?php echo htmlspecialchars($row['image'] ?? 'default.jpg'); ?>" 
                   alt="<?php echo htmlspecialchars($row['service_name']); ?>">
            </div>
            <div class="service-content">
              <h3><?php echo htmlspecialchars($row['service_name']); ?></h3>
              <p><?php echo htmlspecialchars($row['description']); ?></p>
              <div class="service-footer">
                <div class="service-price">₱<?php echo number_format($row['price'],2); ?></div>
                <a href="customer-booking.php?service_id=<?php echo $row['service_id']; ?>" class="btn-book">
                  BOOK NOW
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-services">
          <p>No services available at the moment. Please check back later!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<footer>
  <p>&copy; <?php echo date('Y'); ?> QuickClean. All Rights Reserved. Clean Spaces, Happy Faces.</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>