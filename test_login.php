<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login Credentials - MJ Hostel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .test-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }
        .credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .btn {
            padding: 12px 24px;
            background: #764ba2;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover { background: #5b3a8a; }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Test Login Credentials</h1>
        
        <?php
        require_once 'db_config.php';
        
        if ($_POST && isset($_POST['create_test_user'])) {
            try {
                $pdo = getDBConnection();
                
                // Create a simple test user
                $testUserId = 'test' . rand(100, 999);
                $testPassword = 'test123';
                $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO students (user_id, name, email, phone, password, hostel, registration_completed) 
                        VALUES (?, ?, ?, ?, ?, ?, TRUE)";
                
                executeQuery($pdo, $sql, [
                    $testUserId,
                    'Test User ' . rand(1, 99),
                    'test' . rand(1, 999) . '@example.com',
                    '98765' . rand(10000, 99999),
                    $hashedPassword,
                    'hostel' . rand(1, 6)
                ]);
                
                echo "<div class='status success'>✅ Test user created successfully!</div>";
                echo "<div class='credentials'>";
                echo "<strong>New Test Credentials:</strong><br>";
                echo "User ID: <strong>$testUserId</strong><br>";
                echo "Password: <strong>test123</strong>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='status error'>❌ Error: " . $e->getMessage() . "</div>";
            }
        }
        ?>
        
        <div class="credentials">
            <h3>📋 Available Test Credentials:</h3>
            
            <p><strong>Option 1 - Demo Account:</strong></p>
            <ul>
                <li>User ID: <strong>demo1</strong></li>
                <li>Password: <strong>password</strong></li>
            </ul>
            
            <p><strong>Option 2 - Simple Test Account:</strong></p>
            <ul>
                <li>User ID: <strong>test1</strong></li>
                <li>Password: <strong>test123</strong></li>
            </ul>
        </div>
        
        <form method="POST">
            <button type="submit" name="create_test_user" class="btn">Create New Test User</button>
        </form>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="login.html" class="btn">Try Login</a>
            <a href="registration1.html" class="btn">Register New User</a>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 8px;">
            <h4>🔍 Debug Information:</h4>
            <?php
            try {
                $pdo = getDBConnection();
                $users = getMultipleRecords($pdo, "SELECT user_id, name, registration_completed FROM students LIMIT 5");
                
                echo "<p><strong>Users in Database:</strong></p>";
                echo "<ul>";
                foreach ($users as $user) {
                    $status = $user['registration_completed'] ? '✅' : '⏳';
                    echo "<li>{$user['user_id']} - {$user['name']} $status</li>";
                }
                echo "</ul>";
            } catch (Exception $e) {
                echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>