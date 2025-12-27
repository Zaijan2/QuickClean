<?php
session_start();

// ------------------ LOGIN VALIDATION ------------------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? "Admin";

// --- DATABASE CONNECTION ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickclean";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Connection failed: " . mysqli_connect_error());
error_reporting(0);

// ---------------- ADD SERVICE ----------------
if (isset($_POST['add_service'])) {
    $name = $_POST['service_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);

    $image = $_FILES["image"]["name"];
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);

    $conn->query("INSERT INTO services (service_name, image, description, price) VALUES ('$name','$image','$desc','$price')");
    header("Location: services.php");
    exit();
}

// ---------------- DELETE SERVICE ----------------
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $res = $conn->query("SELECT image FROM services WHERE service_id=$id");
    $row = $res->fetch_assoc();
    if ($row && file_exists("uploads/".$row['image'])) unlink("uploads/".$row['image']);
    $conn->query("DELETE FROM services WHERE service_id=$id");
    header("Location: services.php");
    exit();
}

// ---------------- UPDATE SERVICE ----------------
if (isset($_POST['update_service'])) {
    $id = $_POST['service_id'];
    $name = $_POST['service_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    if (!empty($_FILES["image"]["name"])) {
        $image = $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/".$image);
        $conn->query("UPDATE services SET service_name='$name', description='$desc', price='$price', image='$image' WHERE service_id=$id");
    } else {
        $conn->query("UPDATE services SET service_name='$name', description='$desc', price='$price' WHERE service_id=$id");
    }
    header("Location: services.php");
    exit();
}

// ---------------- FETCH SERVICES ----------------
$result = $conn->query("SELECT * FROM services ORDER BY service_id DESC");
$total_services = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Services - QuickClean</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}

:root {
    --primary: #4F46E5;
    --primary-dark: #4338CA;
    --secondary: #10B981;
    --danger: #EF4444;
    --warning: #F59E0B;
    --info: #3B82F6;
    --dark: #1F2937;
    --light: #F9FAFB;
    --border: #E5E7EB;
    --text-primary: #111827;
    --text-secondary: #6B7280;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

body { 
    font-family: "Inter", sans-serif; 
    background: var(--light);
    color: var(--text-primary);
    line-height: 1.6;
}

/* Sidebar */
.sidebar { 
    width: 260px; 
    background: white;
    border-right: 1px solid var(--border);
    display: flex; 
    flex-direction: column; 
    position: fixed; 
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.sidebar-header {
    padding: 24px 20px;
    border-bottom: 1px solid var(--border);
}

.sidebar-header h2 { 
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-header h2 i {
    font-size: 28px;
}

.sidebar-menu { 
    list-style: none; 
    padding: 16px 12px;
    flex: 1;
}

.sidebar-menu li { 
    margin-bottom: 4px;
}

.sidebar-menu li a { 
    text-decoration: none; 
    color: var(--text-secondary);
    font-size: 15px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.sidebar-menu li a i {
    width: 20px;
    font-size: 18px;
}

.sidebar-menu li a:hover { 
    background: var(--light);
    color: var(--primary);
}

.sidebar-menu li a.active { 
    background: var(--primary);
    color: white;
}

/* Mobile menu toggle */
.menu-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 12px;
    cursor: pointer;
    box-shadow: var(--shadow);
}

.menu-toggle i {
    font-size: 20px;
    color: var(--text-primary);
}

/* Main Content */
.main-content { 
    margin-left: 260px; 
    min-height: 100vh;
    background: var(--light);
}

/* Topbar */
.topbar { 
    background: white;
    padding: 20px 32px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
}

.topbar-left h1 { 
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.topbar-left h1 i {
    color: var(--primary);
}

.topbar-left p {
    font-size: 14px;
    color: var(--text-secondary);
}

.admin-profile { 
    display: flex; 
    align-items: center; 
    gap: 12px;
    padding: 8px 16px;
    background: var(--light);
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.admin-profile:hover {
    background: var(--border);
}

.admin-profile img { 
    width: 40px; 
    height: 40px; 
    border-radius: 50%;
    border: 2px solid var(--primary);
}

.admin-profile-info {
    display: flex;
    flex-direction: column;
}

.admin-profile-info span {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
}

.admin-profile-info small {
    font-size: 12px;
    color: var(--text-secondary);
}

/* Content Container */
.content-container {
    padding: 32px;
    max-width: 1400px;
}

/* Stats Card */
.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: #FEF3C7;
    color: var(--warning);
}

.stat-info h3 {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 4px;
}

.stat-info p {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
}

/* Action Bar */
.action-bar {
    background: white;
    padding: 20px 24px;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    font-size: 16px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.add-btn {
    background: var(--primary);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.add-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Services Grid */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.service-card {
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.service-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: var(--light);
}

.service-content {
    padding: 20px;
}

.service-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}

.service-name {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.service-price {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
}

.service-description {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 16px;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.service-actions {
    display: flex;
    gap: 8px;
}

.btn {
    flex: 1;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-edit {
    background: #FEF3C7;
    color: var(--warning);
}

.btn-edit:hover {
    background: var(--warning);
    color: white;
}

.btn-delete {
    background: #FEE2E2;
    color: var(--danger);
    text-decoration: none;
}

.btn-delete:hover {
    background: var(--danger);
    color: white;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
}

.close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 20px;
    color: var(--text-secondary);
}

.close:hover {
    background: var(--light);
    color: var(--text-primary);
}

.modal-body {
    padding: 24px;
}

form .form-group {
    margin-bottom: 20px;
}

form label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 8px;
}

form input,
form textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
}

form input:focus,
form textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

form textarea {
    resize: vertical;
    min-height: 100px;
}

form input[type="file"] {
    padding: 10px 12px;
    cursor: pointer;
}

form button[type="submit"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: var(--primary);
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
}

form button[type="submit"]:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.no-services {
    text-align: center;
    padding: 64px 24px;
    color: var(--text-secondary);
}

.no-services i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.no-services p {
    font-size: 16px;
    margin-bottom: 24px;
}

/* Responsive */
@media (max-width: 768px) {
    .menu-toggle {
        display: block;
    }

    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }

    .topbar {
        padding: 16px 20px;
    }

    .content-container {
        padding: 20px;
    }

    .admin-profile-info {
        display: none;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }

    .action-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .add-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>

<!-- Mobile Menu Toggle -->
<div class="menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2><i class="fas fa-sparkles"></i> QuickClean</h2>
  </div>
  <ul class="sidebar-menu">
    <li><a href="admindashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="customers.php"><i class="fas fa-users"></i> Users</a></li>
    <li><a href="services.php" class="active"><i class="fas fa-concierge-bell"></i> Services</a></li>
    <li><a href="cleaners.php"><i class="fas fa-user-tie"></i> Cleaners</a></li>
    <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
    <li><a href="booking.php"><i class="fas fa-clipboard-list"></i> Bookings</a></li>
    <li><a href="transactions.php"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-left">
      <h1><i class="fas fa-concierge-bell"></i> Services Management</h1>
      <p>Manage your cleaning services</p>
    </div>
    <div class="admin-profile">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin">
      <div class="admin-profile-info">
        <span><?= htmlspecialchars($admin_name) ?></span>
        <small>Administrator</small>
      </div>
    </div>
  </div>

  <!-- Content Container -->
  <div class="content-container">

    <!-- Stats Card -->
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-concierge-bell"></i>
      </div>
      <div class="stat-info">
        <h3>Total Services</h3>
        <p><?= number_format($total_services) ?></p>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search services...">
      </div>
      <button class="add-btn" onclick="openModal('addModal')">
        <i class="fas fa-plus"></i> Add New Service
      </button>
    </div>

    <!-- Services Grid -->
    <div class="services-grid" id="servicesGrid">
      <?php if ($total_services > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="service-card" data-name="<?= strtolower($row['service_name']) ?>" data-desc="<?= strtolower($row['description']) ?>">
          <img src="uploads/<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['service_name']) ?>" class="service-image">
          <div class="service-content">
            <div class="service-header">
              <div>
                <h3 class="service-name"><?= htmlspecialchars($row['service_name']) ?></h3>
                <p class="service-price">₱<?= number_format($row['price'], 2) ?></p>
              </div>
            </div>
            <p class="service-description"><?= htmlspecialchars($row['description']) ?></p>
            <div class="service-actions">
              <button class="btn btn-edit" onclick='openEditModal(<?= json_encode($row) ?>)'>
                <i class="fas fa-edit"></i> Edit
              </button>
              <a href="?delete=<?= $row['service_id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this service?')">
                <i class="fas fa-trash"></i> Delete
              </a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-services">
          <i class="fas fa-concierge-bell"></i>
          <p>No services available yet</p>
          <button class="add-btn" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Add Your First Service
          </button>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- ADD SERVICE MODAL -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Add New Service</h2>
      <span class="close" onclick="closeModal('addModal')">
        <i class="fas fa-times"></i>
      </span>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label>Service Name *</label>
          <input type="text" name="service_name" placeholder="e.g., Deep House Cleaning" required>
        </div>
        <div class="form-group">
          <label>Service Image *</label>
          <input type="file" name="image" accept="image/*" required>
        </div>
        <div class="form-group">
          <label>Description *</label>
          <textarea name="description" placeholder="Describe the service..." required></textarea>
        </div>
        <div class="form-group">
          <label>Price (₱) *</label>
          <input type="number" name="price" placeholder="0.00" step="0.01" min="0" required>
        </div>
        <button type="submit" name="add_service">
          <i class="fas fa-save"></i> Save Service
        </button>
      </form>
    </div>
  </div>
</div>

<!-- EDIT SERVICE MODAL -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Service</h2>
      <span class="close" onclick="closeModal('editModal')">
        <i class="fas fa-times"></i>
      </span>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="service_id" id="editServiceId">
        <div class="form-group">
          <label>Service Name *</label>
          <input type="text" name="service_name" id="editServiceName" required>
        </div>
        <div class="form-group">
          <label>Service Image (Leave empty to keep current)</label>
          <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group">
          <label>Description *</label>
          <textarea name="description" id="editDescription" required></textarea>
        </div>
        <div class="form-group">
          <label>Price (₱) *</label>
          <input type="number" name="price" id="editPrice" step="0.01" min="0" required>
        </div>
        <button type="submit" name="update_service">
          <i class="fas fa-check"></i> Update Service
        </button>
      </form>
    </div>
  </div>
</div>

<script>
// Toggle Sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});

// Modal Functions
function openModal(id) {
    document.getElementById(id).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Edit Modal
function openEditModal(service) {
    document.getElementById('editServiceId').value = service.service_id;
    document.getElementById('editServiceName').value = service.service_name;
    document.getElementById('editDescription').value = service.description;
    document.getElementById('editPrice').value = service.price;
    openModal('editModal');
}

// Search Functionality
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', function() {
    const keyword = this.value.toLowerCase();
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        const name = card.dataset.name;
        const desc = card.dataset.desc;
        
        if (name.includes(keyword) || desc.includes(keyword)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

</body>
</html>