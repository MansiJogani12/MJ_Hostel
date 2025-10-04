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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            overflow-x: hidden;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); }
            25% { background: linear-gradient(135deg, #764ba2 0%, #f093fb 50%, #667eea 100%); }
            50% { background: linear-gradient(135deg, #f093fb 0%, #667eea 50%, #764ba2 100%); }
            75% { background: linear-gradient(135deg, #667eea 0%, #f093fb 50%, #764ba2 100%); }
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .header {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 25px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
        }

        .logo::before {
            content: "🏠";
            font-size: 2rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-5px); }
            60% { transform: translateY(-3px); }
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            color: white;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
            background: linear-gradient(135deg, #ee5a52, #ff6b6b);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 50px 30px;
        }

        .welcome-section {
            text-align: center;
            color: white;
            margin-bottom: 60px;
            animation: fadeInUp 1s ease-out;
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

        .welcome-title {
            font-size: 3.5rem;
            margin-bottom: 15px;
            font-weight: 300;
            letter-spacing: -1px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .welcome-subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 20px 30px;
            border-radius: 15px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 35px;
            margin-top: 50px;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px 35px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out forwards;
            opacity: 0;
        }

        .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
        .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
        .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
        .dashboard-card:nth-child(4) { animation-delay: 0.4s; }
        .dashboard-card:nth-child(5) { animation-delay: 0.5s; }
        .dashboard-card:nth-child(6) { animation-delay: 0.6s; }
        .dashboard-card:nth-child(7) { animation-delay: 0.7s; }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s;
        }

        .dashboard-card:hover::before {
            left: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-15px) scale(1.05);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            background: white;
            border-color: rgba(255, 255, 255, 0.6);
        }

        .card-icon {
            font-size: 4rem;
            margin-bottom: 25px;
            display: block;
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            letter-spacing: -0.3px;
        }

        .card-description {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
            font-weight: 400;
        }

        .footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 80px;
            font-size: 1rem;
            font-weight: 300;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 968px) {
            .dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 25px;
            }
            
            .welcome-title {
                font-size: 2.5rem;
            }
            
            .container {
                padding: 30px 20px;
            }
            
            .stats-bar {
                gap: 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (max-width: 600px) {
            .welcome-title {
                font-size: 2rem;
            }
            
            .dashboard-card {
                padding: 30px 25px;
            }
            
            .card-icon {
                font-size: 3rem;
            }
            
            .stats-bar {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="particles">
        <div class="particle" style="left: 10%; width: 10px; height: 10px; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; width: 15px; height: 15px; animation-delay: 2s;"></div>
        <div class="particle" style="left: 30%; width: 8px; height: 8px; animation-delay: 4s;"></div>
        <div class="particle" style="left: 40%; width: 12px; height: 12px; animation-delay: 6s;"></div>
        <div class="particle" style="left: 50%; width: 6px; height: 6px; animation-delay: 1s;"></div>
        <div class="particle" style="left: 60%; width: 14px; height: 14px; animation-delay: 3s;"></div>
        <div class="particle" style="left: 70%; width: 9px; height: 9px; animation-delay: 5s;"></div>
        <div class="particle" style="left: 80%; width: 11px; height: 11px; animation-delay: 7s;"></div>
        <div class="particle" style="left: 90%; width: 13px; height: 13px; animation-delay: 2.5s;"></div>
    </div>

    <div class="header">
        <div class="header-content">
            <div class="logo">MJ Hostel Dashboard</div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
                <span>Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome Back!</h1>
            <p class="welcome-subtitle">Manage your hostel experience with our comprehensive dashboard</p>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number"><?php echo htmlspecialchars($userHostel); ?></span>
                    <div class="stat-label">Your Hostel</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo htmlspecialchars($userId); ?></span>
                    <div class="stat-label">Student ID</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo date('Y'); ?></span>
                    <div class="stat-label">Academic Year</div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <a href="profile.html" class="dashboard-card">
                <span class="card-icon">👤</span>
                <h3 class="card-title">Profile</h3>
                <p class="card-description">View and manage your personal information, contact details, and account settings</p>
            </a>

            <a href="notification.html" class="dashboard-card">
                <span class="card-icon">�</span>
                <h3 class="card-title">Notifications</h3>
                <p class="card-description">Stay updated with important announcements, alerts, and hostel news</p>
            </a>

            <a href="fees.html" class="dashboard-card">
                <span class="card-icon">💳</span>
                <h3 class="card-title">Fees</h3>
                <p class="card-description">Check fee status, payment history, and manage your financial obligations</p>
            </a>

            <a href="food.html" class="dashboard-card">
                <span class="card-icon">🍽️</span>
                <h3 class="card-title">Food</h3>
                <p class="card-description">View mess menu, meal timings, and special dietary arrangements</p>
            </a>

            <a href="rules.html" class="dashboard-card">
                <span class="card-icon">⚖️</span>
                <h3 class="card-title">Rules</h3>
                <p class="card-description">Review hostel rules, regulations, and important guidelines for residents</p>
            </a>

            <a href="about.html" class="dashboard-card">
                <span class="card-icon">ℹ️</span>
                <h3 class="card-title">About</h3>
                <p class="card-description">Learn more about our hostel facilities, history, and management team</p>
            </a>

            <a href="portal.html" class="dashboard-card">
                <span class="card-icon">�</span>
                <h3 class="card-title">Hostel Portal</h3>
                <p class="card-description">Access additional resources, forms, and administrative services</p>
            </a>
        </div>

        <div class="footer">
            <p>© 2025 MJ Hostel | Managing Comfort, Ensuring Safety</p>
            <p>Your secure student living experience starts here</p>
        </div>
    </div>

    <script>
        // Logout functionality (matching original dashboard.js)
        function logout() {
            if(confirm("Are you sure you want to logout?")) {
                alert("Logged out successfully!");
                window.location.href = "new_login.html";
            }
        }

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Create additional floating particles
            const particlesContainer = document.querySelector('.particles');
            for(let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = (Math.random() * 10 + 5) + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
                particlesContainer.appendChild(particle);
            }

            // Add click effect to cards
            document.querySelectorAll('.dashboard-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    const ripple = document.createElement('div');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255,255,255,0.6)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.left = (e.clientX - card.offsetLeft) + 'px';
                    ripple.style.top = (e.clientY - card.offsetTop) + 'px';
                    ripple.style.width = ripple.style.height = '20px';
                    card.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>