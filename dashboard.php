<?php
session_start();

// Check if user is properly logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    // Redirect to login page if not logged in
    header('Location: login.html?error=' . urlencode('Please login first'));
    exit;
}

// Check if session is still valid (optional - session timeout)
if (isset($_SESSION['login_time'])) {
    $sessionTimeout = 30 * 60; // 30 minutes
    if (time() - $_SESSION['login_time'] > $sessionTimeout) {
        session_destroy();
        header('Location: login.html?error=' . urlencode('Session expired. Please login again'));
        exit;
    }
}

// Get user information from session
$userName = $_SESSION['user_name'] ?? 'Student';
$userId = $_SESSION['user_id'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userHostel = $_SESSION['user_hostel'] ?? '';

// Update login time for session management
$_SESSION['login_time'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MJ Hostel – Dashboard</title>

  <link rel="stylesheet" href="style.css" />
</head>

<body class="dashboard-page">
  <header class="dashboard1">
    🏠 MJ Hostel Dashboard - Welcome <?php echo htmlspecialchars($userName); ?>! (ID: <?php echo htmlspecialchars($userId); ?>)
    <button class="logout-btn" onclick="logout()">Logout</button>
  </header>

  <!-- User Info Section -->
  <div style="background: white; margin: 20px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    <h3>👋 Welcome, <?php echo htmlspecialchars($userName); ?>!</h3>
    <p><strong>User ID:</strong> <?php echo htmlspecialchars($userId); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
    <p><strong>Hostel:</strong> <?php echo htmlspecialchars($userHostel); ?></p>
    <p><strong>Login Time:</strong> <?php echo date('d-m-Y H:i:s', $_SESSION['login_time']); ?></p>
  </div>

  <div class="dashboard">
    <a href="profile.html?userId=<?php echo urlencode($userId); ?>" class="card">👤 Profile</a>
    <a href="notification.html" class="card">📩 Notifications</a>
    <a href="fees.html" class="card">💳 Fees</a>
    <a href="food.html" class="card">🍽️ Food</a>
    <a href="rules.html" class="card">⚖️ Rules</a>
    <a href="about.html" class="card">ℹ️ About</a>

    <!-- New card for Hostel Portal -->
    <a href="portal.html" class="card">🏨 Hostel Portal</a>
  </div>

  <footer class="footer">
    <p>&copy; 2025 MJ Hostel | Managing Comfort, Ensuring Safety</p>
  </footer>

  <script>
    function logout() {
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
      }
    }
  </script>
</body>
</html>