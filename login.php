<?php
session_start();
require_once 'db_config.php';

// Handle login form submission
if ($_POST) {
    $userId = trim($_POST['userId'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($userId) || empty($password)) {
        header('Location: login.html?error=' . urlencode('Please enter both User ID and Password'));
        exit;
    }

    try {
        // Get database connection
        $pdo = getDBConnection();
        
        // Find user in database
        $user = getSingleRecord($pdo, 
            "SELECT id, user_id, name, email, password, hostel, registration_completed 
             FROM students WHERE user_id = ?", 
            [$userId]
        );
        
        if ($user) {
            // Debug: Log the attempt
            error_log("Login attempt for user: $userId");
            error_log("User found in database: " . $user['name']);
            error_log("Registration completed: " . ($user['registration_completed'] ? 'Yes' : 'No'));
            
            // Verify password - handle both hashed and plain text
            $passwordMatch = false;
            
            // First try password_verify for properly hashed passwords
            if (isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                $passwordMatch = true;
                error_log("Password verified with password_verify");
            }
            // Also check for plain text passwords (for demo/existing data)
            elseif (isset($user['password']) && $user['password'] === $password) {
                $passwordMatch = true;
                error_log("Password matched as plain text");
            }
            // Special case for demo accounts with password "password"
            elseif (($userId === 'demo1' || $userId === 'demo2') && $password === 'password') {
                $passwordMatch = true;
                error_log("Demo account password matched");
            }
            
            if ($passwordMatch) {
                // Check if registration is completed
                if (!$user['registration_completed']) {
                    logLoginAttempt($pdo, $userId, false, 'Registration not completed');
                    header('Location: login.html?error=' . urlencode('Please complete your registration first'));
                    exit;
                }
                
                // Check if account is active
                if (isset($user['status']) && $user['status'] !== 'active') {
                    logLoginAttempt($pdo, $userId, false, 'Account not active');
                    header('Location: login.html?error=' . urlencode('Your account is not active. Please contact administrator.'));
                    exit;
                }
                
                // Successful login
                session_regenerate_id(true); // Prevent session fixation
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_hostel'] = $user['hostel'];
                $_SESSION['user_authenticated'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['ip_address'] = $ip_address;
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                
                // Handle "Remember Me" functionality
                if ($remember_me) {
                    // Generate secure random token
                    $remember_token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
                    
                    // Store token in database
                    try {
                        // First, check if columns exist and add them if needed
                        $stmt = $pdo->prepare("SHOW COLUMNS FROM students LIKE 'remember_token'");
                        $stmt->execute();
                        if (!$stmt->fetch()) {
                            $pdo->exec("ALTER TABLE students ADD COLUMN remember_token VARCHAR(255) NULL");
                        }
                        
                        $stmt = $pdo->prepare("SHOW COLUMNS FROM students LIKE 'remember_expires'");
                        $stmt->execute();
                        if (!$stmt->fetch()) {
                            $pdo->exec("ALTER TABLE students ADD COLUMN remember_expires DATETIME NULL");
                        }
                        
                        $stmt = $pdo->prepare("UPDATE students SET remember_token = ?, remember_expires = ? WHERE user_id = ?");
                        $stmt->execute([$remember_token, $expires, $user['user_id']]);
                        
                        // Set secure cookie
                        setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                    } catch (Exception $e) {
                        error_log("Remember token error: " . $e->getMessage());
                    }
                }
                
                // Log successful login
                logLoginAttempt($pdo, $userId, true, 'Successful login');
                error_log("Login successful for user: $userId");
                
                header('Location: clean_dashboard.php');
                exit;
            } else {
                // Log failed login attempt
                logLoginAttempt($pdo, $userId, false, 'Invalid password');
                error_log("Invalid password for user: $userId");
                header('Location: login.html?error=' . urlencode('Invalid Password - Please check your password'));
                exit;
            }
        } else {
            // Log failed login attempt
            logLoginAttempt($pdo, $userId, false, 'User not found');
            header('Location: login.html?error=' . urlencode('User ID not found'));
            exit;
        }
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: login.html?error=' . urlencode('Login failed. Please try again.'));
        exit;
    }
}

// Function to log login attempts
function logLoginAttempt($pdo, $userId, $success, $message = '') {
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Check if login_logs table has the required columns
        $stmt = $pdo->prepare("SHOW COLUMNS FROM login_logs LIKE 'success'");
        $stmt->execute();
        $success_column_exists = $stmt->fetch();
        
        if (!$success_column_exists) {
            $pdo->exec("ALTER TABLE login_logs ADD COLUMN success TINYINT(1) DEFAULT 0");
        }
        
        $stmt = $pdo->prepare("SHOW COLUMNS FROM login_logs LIKE 'message'");
        $stmt->execute();
        $message_column_exists = $stmt->fetch();
        
        if (!$message_column_exists) {
            $pdo->exec("ALTER TABLE login_logs ADD COLUMN message VARCHAR(255) NULL");
        }
        
        $stmt = $pdo->prepare("SHOW COLUMNS FROM login_logs LIKE 'attempted_at'");
        $stmt->execute();
        $attempted_at_exists = $stmt->fetch();
        
        if (!$attempted_at_exists) {
            $pdo->exec("ALTER TABLE login_logs ADD COLUMN attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        }
        
        // Insert log record
        $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, success, message, attempted_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $ip_address, $user_agent, $success ? 1 : 0, $message]);
    } catch (Exception $e) {
        // Don't fail login if logging fails
        error_log("Login logging error: " . $e->getMessage());
    }
}

// If no POST request, redirect to login page
header('Location: login.html');
exit;
?>