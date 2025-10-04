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
    <title>Dashboard - MJ Hostel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logout-btn {
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #7c3aed;
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 120px;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            text-decoration: none;
            color: #333;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            transition: left 0.5s;
        }

        .dashboard-card:hover::before {
            left: 100%;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-description {
            color: #666;
            font-size: 0.9rem;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            border-top: 1px solid #eee;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .user-details {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <h1>🏠 MJ Hostel Dashboard</h1>
            </div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>👋 Welcome to Your Dashboard</h2>
            <p>Manage your hostel activities and access all services from here.</p>
            
            <div class="user-details">
                <div class="detail-item">
                    <strong>User ID</strong>
                    <?php echo htmlspecialchars($userId); ?>
                </div>
                <div class="detail-item">
                    <strong>Name</strong>
                    <?php echo htmlspecialchars($userName); ?>
                </div>
                <div class="detail-item">
                    <strong>Email</strong>
                    <?php echo htmlspecialchars($userEmail); ?>
                </div>
                <div class="detail-item">
                    <strong>Hostel</strong>
                    <?php echo htmlspecialchars($userHostel); ?>
                </div>
                <div class="detail-item">
                    <strong>Login Time</strong>
                    <?php echo date('d M Y, h:i A', $_SESSION['login_time']); ?>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <a href="new_profile.php" class="dashboard-card">
                <span class="card-icon">👤</span>
                <div class="card-title">My Profile</div>
                <div class="card-description">View and edit your personal information, contact details, and preferences</div>
            </a>

            <a href="notifications.html" class="dashboard-card">
                <span class="card-icon">📢</span>
                <div class="card-title">Notifications</div>
                <div class="card-description">Check important announcements, updates, and messages from hostel management</div>
            </a>

            <a href="fees.html" class="dashboard-card">
                <span class="card-icon">💳</span>
                <div class="card-title">Fee Management</div>
                <div class="card-description">View fee structure, payment history, and pending dues</div>
            </a>

            <a href="food.html" class="dashboard-card">
                <span class="card-icon">🍽️</span>
                <div class="card-title">Mess & Food</div>
                <div class="card-description">Check daily menu, meal timings, and food-related announcements</div>
            </a>

            <a href="rules.html" class="dashboard-card">
                <span class="card-icon">📋</span>
                <div class="card-title">Hostel Rules</div>
                <div class="card-description">Read hostel guidelines, regulations, and important policies</div>
            </a>

            <a href="about.html" class="dashboard-card">
                <span class="card-icon">ℹ️</span>
                <div class="card-title">About Hostel</div>
                <div class="card-description">Learn about hostel facilities, contact information, and services</div>
            </a>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 MJ Hostel Management System. All rights reserved.</p>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'new_logout.php';
            }
        }

        // Auto refresh session every 5 minutes
        setInterval(function() {
            fetch('refresh_session.php')
                .catch(error => console.log('Session refresh failed'));
        }, 300000);
    </script>
</body>
</html>