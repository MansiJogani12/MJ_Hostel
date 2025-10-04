<?php
session_start();
require_once 'db_config.php';

// Function to validate login credentials
function validateLogin($userId, $password) {
    try {
        $pdo = getDBConnection();
        
        // Find user in database
        $user = getSingleRecord($pdo, 
            "SELECT id, user_id, name, email, password, hostel, registration_completed 
             FROM students WHERE user_id = ?", 
            [$userId]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'User ID not found'];
        }
        
        // Verify password
        $passwordMatch = false;
        
        if (password_verify($password, $user['password'])) {
            $passwordMatch = true;
        } elseif ($user['password'] === $password) {
            // Handle plain text passwords for existing data
            $passwordMatch = true;
        }
        
        if (!$passwordMatch) {
            return ['success' => false, 'message' => 'Invalid password'];
        }
        
        // Check if registration is completed
        if (!$user['registration_completed']) {
            return ['success' => false, 'message' => 'Please complete your registration first'];
        }
        
        // Login successful - set session data
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_hostel'] = $user['hostel'];
        $_SESSION['login_time'] = time();
        
        // Log successful login
        logLoginAttempt($pdo, $user['user_id'], 'success');
        
        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        
    } catch (Exception $e) {
        error_log("Login validation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'System error. Please try again.'];
    }
}

// Function to log login attempts
function logLoginAttempt($pdo, $userId, $status) {
    try {
        $sql = "INSERT INTO login_logs (user_id, login_time, ip_address, user_agent) 
                VALUES (?, NOW(), ?, ?)";
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        executeQuery($pdo, $sql, [$userId . '_' . $status, $ipAddress, $userAgent]);
    } catch (Exception $e) {
        error_log("Login logging error: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = trim($_POST['userId'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Debug logging
    error_log("Login attempt - User ID: $userId");
    
    // Basic validation
    if (empty($userId) || empty($password)) {
        error_log("Login failed - Empty credentials");
        header('Location: login.html?error=' . urlencode('Please enter both User ID and Password'));
        exit;
    }
    
    // Validate login
    $result = validateLogin($userId, $password);
    
    error_log("Login result: " . json_encode($result));
    
    if ($result['success']) {
        // Successful login - redirect to dashboard
        error_log("Login successful - redirecting to dashboard");
        header('Location: dashboard.php');
        exit;
    } else {
        // Failed login - redirect back with error
        error_log("Login failed - " . $result['message']);
        header('Location: login.html?error=' . urlencode($result['message']));
        exit;
    }
} else {
    // If not POST request, redirect to login page
    error_log("No POST data - redirecting to login");
    header('Location: login.html');
    exit;
}
?>