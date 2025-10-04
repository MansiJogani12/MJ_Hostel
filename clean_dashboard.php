<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: new_login.html?error=' . urlencode('Please login to access dashboard'));
    exit;
}

// Check session timeout (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header('Location: new_login.html?error=' . urlencode('Session expired. Please login again'));
    exit;
}

// Update login time
$_SESSION['login_time'] = time();

// Get user data
$userName = $_SESSION['user_name'] ?? 'User';
$userId = $_SESSION['user_id'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userHostel = $_SESSION['user_hostel'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MJ Hostel Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #7c3aed;
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }

        .dashboard-grid.single {
            grid-template-columns: 1fr;
            max-width: 500px;
            margin: 30px auto 0;
        }

        .dashboard-card {
            background: white;
            color: #6b46c1;
            padding: 30px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            min-height: 80px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            color: #6b46c1;
            text-decoration: none;
        }

        .card-icon {
            font-size: 1.8rem;
            min-width: 40px;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 40px 20px;
            }
            
            .header {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🏠 MJ Hostel Dashboard</div>
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>

    <div class="container">
        <div class="dashboard-grid">
            <a href="profile.html" class="dashboard-card">
                <span class="card-icon">👤</span>
                <h3 class="card-title">Profile</h3>
            </a>

            <a href="notification.html" class="dashboard-card">
                <span class="card-icon">🔔</span>
                <h3 class="card-title">Notifications</h3>
            </a>

            <a href="fees.html" class="dashboard-card">
                <span class="card-icon">💳</span>
                <h3 class="card-title">Fees</h3>
            </a>

            <a href="food.html" class="dashboard-card">
                <span class="card-icon">🍽️</span>
                <h3 class="card-title">Food</h3>
            </a>

            <a href="rules.html" class="dashboard-card">
                <span class="card-icon">⚖️</span>
                <h3 class="card-title">Rules</h3>
            </a>

            <a href="about.html" class="dashboard-card">
                <span class="card-icon">ℹ️</span>
                <h3 class="card-title">About</h3>
            </a>
        </div>

        <div class="dashboard-grid single">
            <a href="portal.html" class="dashboard-card">
                <span class="card-icon">🏨</span>
                <h3 class="card-title">Hostel Portal</h3>
            </a>
        </div>

        <div class="footer">
            <p>© 2025 MJ Hostel | Managing Comfort, Ensuring Safety</p>
        </div>
    </div>

    <script>
        function logout() {
            if(confirm("Are you sure you want to logout?")) {
                alert("Logged out successfully!");
                window.location.href = "new_logout.php";
            }
        }
    </script>
</body>
</html>