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
$db = "quickclean";

// Establishing the database connection
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set default timezone
date_default_timezone_set('Asia/Manila'); 

// --- FLEXIBLE DATE LOGIC ---
$today_date = date('Y-m-d'); 
$current_display_date_str = isset($_GET['date']) ? $_GET['date'] : $today_date;
$current_display_timestamp = strtotime($current_display_date_str);
$current_display_date = date('Y-m-d', $current_display_timestamp); 

// Calculate previous and next day
$prev_date = date('Y-m-d', strtotime('-1 day', $current_display_timestamp));
$next_date = date('Y-m-d', strtotime('+1 day', $current_display_timestamp));

// Get all bookings
$sql = "SELECT b.*, t.status AS transaction_status
        FROM bookings b
        LEFT JOIN transactions t ON b.booking_id = t.booking_id
        WHERE b.date = ? 
        AND b.status IN ('pending', 'accepted')
        ORDER BY b.date, b.time";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $current_display_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$current_timestamp = time();
$bookings_by_date = [];

while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['date'];
    
    // --- EXPIRY LOGIC ---
    $booking_datetime_str = $row['date'] . ' ' . $row['time'];
    $booking_timestamp = strtotime($booking_datetime_str);
    $is_expired = ($booking_timestamp < $current_timestamp);

    if ($is_expired && $row['transaction_status'] !== 'completed' && $row['transaction_status'] !== 'on the way') {
        $row['status_display'] = 'expired';
    } else {
        $row['status_display'] = $row['transaction_status'] ?? $row['status'];
    }

    if (!isset($bookings_by_date[$date])) {
        $bookings_by_date[$date] = [];
    }
    $bookings_by_date[$date][] = $row;
}

mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - <?php echo date('F j, Y', $current_display_timestamp); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --bg-gradient: linear-gradient(135deg, #217edbff 0%, #05a6d6ff 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-dark: #1a202c;
            --text-gray: #718096;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* --- OVERLAY FOR MOBILE --- */
        .overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 90;
            backdrop-filter: blur(4px);
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--glass-border);
            padding: 30px 20px;
            color: white;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
            height: 100vh;
            position: sticky;
            top: 0;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* --- CONTENT AREA --- */
        .content {
            flex: 1;
            padding: 30px 40px;
            width: 100%;
        }

        .header {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .menu-toggle {
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

        /* --- CALENDAR HEADER --- */
        .calendar-header {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 24px 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .calendar-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
        }

        .calendar-nav {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .calendar-nav a, .calendar-nav button {
            height: 40px;
            border: none;
            background: #f0f4ff;
            color: var(--primary);
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .calendar-nav a:hover, .calendar-nav button:hover {
            background: var(--primary);
            color: white;
        }

        .nav-arrow { width: 40px; }
        .date-picker-icon { width: 40px; }
        .today-btn { padding: 0 16px; font-size: 14px; }
        .hidden-date-input { position: absolute; opacity: 0; pointer-events: none; }

        /* --- SCHEDULE GRID --- */
        .schedule-day {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .schedule-day-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #bfb010ff 0%, #c4bc20ff 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-bookings { padding: 20px; }

        /* --- BOOKING CARD --- */
        .schedule-booking {
            display: flex;
            gap: 16px;
            padding: 16px;
            margin-bottom: 12px;
            background: #f7fafc;
            border-radius: 12px;
            border-left: 4px solid var(--primary);
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s;
        }

        .schedule-booking:not(.expired-booking):hover {
            background: #edf2f7;
            transform: translateX(4px);
        }

        .schedule-booking.expired-booking {
            opacity: 0.6;
            border-left-color: #cbd5e0;
            pointer-events: none;
        }

        .booking-time {
            flex-shrink: 0;
            width: 80px;
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .booking-time-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        .booking-details { flex: 1; }
        .booking-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2d3748;
        }

        .booking-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 14px;
            color: var(--text-gray);
        }

        .booking-info-item { display: flex; align-items: center; gap: 5px; }

        /* --- BADGES --- */
        .booking-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .booking-status.pending { background: #f59e0b; }
        .booking-status.accepted { background: #8b5cf6; }
        .booking-status.ontheway { background: #3b82f6; }
        .booking-status.completed { background: #10b981; }
        .booking-status.expired { background: #a0aec0; }

        /* --- EMPTY STATE --- */
        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .empty-state-icon { font-size: 60px; margin-bottom: 15px; opacity: 0.5; }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media (max-width: 900px) {
            .calendar-header { flex-direction: column; align-items: stretch; text-align: center; }
            .calendar-nav { justify-content: center; }
            .content { padding: 20px; }
        }

        @media (max-width: 768px) {
            /* Sidebar Logic */
            .sidebar {
                position: fixed;
                left: -100%; /* Hide completely */
                width: 280px;
                background: #2d3748; /* Solid dark bg for mobile readability */
                box-shadow: 4px 0 15px rgba(0,0,0,0.2);
            }
            .sidebar.active { left: 0; }
            .overlay.active { display: block; }
            .menu-toggle, .close-sidebar { display: block; }
            
            .header h1 { font-size: 24px; }

            /* Booking Card Mobile Layout */
            .schedule-booking { flex-direction: column; gap: 10px; }
            .booking-time {
                width: 100%;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 8px 15px;
            }
            .booking-time-value { font-size: 16px; margin-bottom: 0; }
            .booking-info { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <div class="wrapper">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                QuickClean
                <button class="close-sidebar" onclick="toggleSidebar()">&times;</button>
            </div>
            <div class="nav-item"><a href="dashboard.php">📊 Dashboard</a></div>
            <div class="nav-item"><a href="assigned.php">📋 My Bookings</a></div>
            <div class="nav-item"><a href="schedule.php" class="active">📅 Schedule</a></div>
            <div class="nav-item"><a href="earnings.php">💰 Earnings</a></div>
            <div class="nav-item"><a href="cleaner_messages.php">💬 Messages</a></div>
            <div class="nav-item"><a href="cleaner-notification.php">🔔 Notifications</a></div>
            <div class="nav-item"><a href="profile.php">👤 Profile</a></div>
            <div class="nav-item"><a href="logout.php">Logout</a></div>
        </div>

        <div class="content">
            <div class="header">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <h1>Daily Schedule</h1>
            </div>

            <div class="calendar-header">
                <div class="calendar-title">
                    <?php 
                    if ($current_display_date == $today_date) {
                        echo "Today";
                    } elseif ($current_display_date == date('Y-m-d', strtotime('-1 day', strtotime($today_date)))) {
                        echo "Yesterday";
                    } elseif ($current_display_date == date('Y-m-d', strtotime('+1 day', strtotime($today_date)))) {
                        echo "Tomorrow";
                    } else {
                        echo date('D, M j', $current_display_timestamp);
                    }
                    ?>
                </div>
                <div class="calendar-nav">
                    <a href="?date=<?php echo $prev_date; ?>" class="nav-arrow">←</a>
                    
                    <button id="date-picker-button" class="date-picker-icon">📅</button>
                    <input type="date" 
                           id="date-picker-input" 
                           class="hidden-date-input" 
                           value="<?php echo $current_display_date; ?>"
                           onchange="redirectToDate(this.value)"> 
                    
                    <a href="schedule.php" class="today-btn">Today</a> 
                    <a href="?date=<?php echo $next_date; ?>" class="nav-arrow">→</a>
                </div>
            </div>

            <?php if (count($bookings_by_date) > 0): ?>
                <div class="schedule-grid">
                <?php foreach ($bookings_by_date as $date => $bookings): ?>
                <div class="schedule-day">
                    <div class="schedule-day-header">
                        <div class="schedule-date"><?php echo date('l, F j', strtotime($date)); ?></div>
                        <div class="schedule-count"><?php echo count($bookings); ?> bookings</div>
                    </div>
                    <div class="schedule-bookings">
                        <?php foreach ($bookings as $booking): ?>
                        <a href="bookingdetails.php?booking=<?php echo $booking['booking_id']; ?>" 
                            class="schedule-booking <?php echo ($booking['status_display'] == 'expired' ? 'expired-booking' : ''); ?>">
                            
                            <div class="booking-time">
                                <?php
                                $time_parts = explode(':', $booking['time']);
                                $hour = (int)$time_parts[0];
                                $minute = $time_parts[1];
                                $period = $hour >= 12 ? 'PM' : 'AM';
                                $display_hour = $hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour);
                                ?>
                                <div class="booking-time-value"><?php echo sprintf('%02d:%s', $display_hour, $minute); ?></div>
                                <div class="booking-time-period"><?php echo $period; ?></div>
                            </div>

                            <div class="booking-details">
                                <div class="booking-title"><?php echo htmlspecialchars($booking['service_name'] . ' - ' . $booking['name']); ?></div>
                                <div class="booking-info">
                                    <div class="booking-info-item">
                                        <span>📍</span> <?php echo htmlspecialchars($booking['address']); ?>
                                    </div>
                                    <div class="booking-info-item">
                                        <span>💰</span> ₱<?php echo number_format($booking['price'], 2); ?>
                                    </div>
                                    <div class="booking-info-item">
                                        <?php
                                        $status_display = $booking['status_display'];
                                        $status_text = ''; $status_class = '';
                                        
                                        switch ($status_display) {
                                            case 'completed': $status_text = '✓ Completed'; $status_class = 'completed'; break;
                                            case 'on the way': $status_text = '🚗 On the way'; $status_class = 'ontheway'; break;
                                            case 'accepted': $status_text = 'Accepted'; $status_class = 'accepted'; break;
                                            case 'pending': $status_text = 'Pending'; $status_class = 'pending'; break;
                                            case 'expired': $status_text = '❌ Expired'; $status_class = 'expired'; break;
                                            default: $status_text = ucfirst($status_display); $status_class = 'pending';
                                        }
                                        ?>
                                        <span class="booking-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <div class="empty-state-title">No bookings</div>
                    <div class="empty-state-text">No schedule found for this date.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // --- Sidebar Logic ---
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Close sidebar when clicking overlay
        document.getElementById('overlay').addEventListener('click', toggleSidebar);

        // --- Date Picker Logic ---
        function redirectToDate(selectedDate) {
            if (selectedDate) {
                window.location.href = 'schedule.php?date=' + selectedDate;
            }
        }

        const dateButton = document.getElementById('date-picker-button');
        const dateInput = document.getElementById('date-picker-input');

        dateButton.addEventListener('click', () => {
            dateInput.showPicker();
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>