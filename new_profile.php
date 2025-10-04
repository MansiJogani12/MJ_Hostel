<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: new_login.html?error=' . urlencode('Please login to access your profile'));
    exit;
}

// Database configuration
$host = 'localhost';
$dbname = 'mj_hostel';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get user data from database
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT s.*, p.father_name, p.mother_name, p.father_phone,
           a.line1, a.line2, a.city, a.taluka, a.pincode, a.district, a.state, a.country, a.room_number
    FROM students s
    LEFT JOIN parent_info p ON s.id = p.student_id
    LEFT JOIN addresses a ON s.id = a.student_id
    WHERE s.user_id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: new_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MJ Hostel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 20px;
        }

        .profile-name {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-id {
            color: #666;
            font-size: 1rem;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section h3 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            background: #f8f9fc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #764ba2;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .info-value {
            color: #333;
            font-size: 1rem;
        }

        .no-data {
            color: #999;
            font-style: italic;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #764ba2;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 968px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>👤 My Profile</h1>
            <a href="new_dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
    </div>

    <div class="container">
        <div class="profile-grid">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="profile-id">ID: <?php echo htmlspecialchars($user['user_id']); ?></div>
                </div>

                <div class="info-section">
                    <h3>Quick Stats</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo htmlspecialchars($user['hostel']); ?></div>
                            <div class="stat-label">Hostel</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $user['room_number'] ?: 'N/A'; ?></div>
                            <div class="stat-label">Room No.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="info-section">
                    <h3>Personal Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Registration Date</div>
                            <div class="info-value"><?php echo date('d M Y', strtotime($user['created_at'])); ?></div>
                        </div>
                    </div>
                </div>

                <?php if ($user['father_name'] || $user['mother_name'] || $user['father_phone']): ?>
                <div class="info-section">
                    <h3>Parent Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Father's Name</div>
                            <div class="info-value"><?php echo $user['father_name'] ? htmlspecialchars($user['father_name']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Mother's Name</div>
                            <div class="info-value"><?php echo $user['mother_name'] ? htmlspecialchars($user['mother_name']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Father's Phone</div>
                            <div class="info-value"><?php echo $user['father_phone'] ? htmlspecialchars($user['father_phone']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($user['line1'] || $user['city'] || $user['state']): ?>
                <div class="info-section">
                    <h3>Address Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Address Line 1</div>
                            <div class="info-value"><?php echo $user['line1'] ? htmlspecialchars($user['line1']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Address Line 2</div>
                            <div class="info-value"><?php echo $user['line2'] ? htmlspecialchars($user['line2']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">City</div>
                            <div class="info-value"><?php echo $user['city'] ? htmlspecialchars($user['city']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">State</div>
                            <div class="info-value"><?php echo $user['state'] ? htmlspecialchars($user['state']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Pincode</div>
                            <div class="info-value"><?php echo $user['pincode'] ? htmlspecialchars($user['pincode']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Country</div>
                            <div class="info-value"><?php echo $user['country'] ? htmlspecialchars($user['country']) : '<span class="no-data">Not provided</span>'; ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>