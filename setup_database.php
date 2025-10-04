<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - MJ Hostel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .setup-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
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
        .code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            border-left: 4px solid #007bff;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🏠 MJ Hostel - Database Setup</h1>
        
        <?php
        require_once 'db_config.php';
        
        $message = '';
        $messageType = '';
        
        if ($_POST && isset($_POST['setup_db'])) {
            try {
                // Create database if it doesn't exist
                createDatabaseIfNotExists();
                
                // Execute SQL setup file
                if (file_exists('database_setup.sql')) {
                    if (executeSQLFile('database_setup.sql')) {
                        $message = 'Database setup completed successfully! All tables have been created.';
                        $messageType = 'success';
                    } else {
                        $message = 'Database setup failed. Please check the logs.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'database_setup.sql file not found.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
        
        // Test connection
        $connectionStatus = testDBConnection();
        ?>
        
        <div class="info">
            <h3>📋 Setup Instructions:</h3>
            <ol>
                <li>Make sure XAMPP MySQL is running</li>
                <li>Click "Setup Database" to create tables</li>
                <li>Start using the registration and login system</li>
            </ol>
        </div>
        
        <?php if ($message): ?>
        <div class="status <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="status <?php echo $connectionStatus ? 'success' : 'error'; ?>">
            <strong>Database Connection:</strong> 
            <?php echo $connectionStatus ? '✅ Connected' : '❌ Failed'; ?>
        </div>
        
        <?php if ($connectionStatus): ?>
        <form method="POST">
            <button type="submit" name="setup_db" class="btn">Setup Database</button>
        </form>
        <?php else: ?>
        <div class="error">
            <strong>Connection Failed!</strong> Please check:
            <ul>
                <li>XAMPP MySQL service is running</li>
                <li>Database credentials in db_config.php</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="code">
            <strong>Database Configuration:</strong><br>
            Host: <?php echo DB_HOST; ?><br>
            Database: <?php echo DB_NAME; ?><br>
            Username: <?php echo DB_USERNAME; ?><br>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="registration1.html" class="btn">Go to Registration</a>
            <a href="login.html" class="btn">Go to Login</a>
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
        </div>
    </div>
</body>
</html>