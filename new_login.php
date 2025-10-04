<?php
session_start();

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

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: new_login.html');
    exit;
}

$userId = trim($_POST['userId'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($userId) || empty($password)) {
    redirectWithError('Please enter both User ID and Password');
}

try {
    // Find user in database
    $stmt = $pdo->prepare("SELECT id, user_id, name, email, password, hostel, registration_completed FROM students WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithError('Invalid User ID or Password');
    }

    // Verify password
    $passwordValid = false;
    
    // Try hashed password first
    if (password_verify($password, $user['password'])) {
        $passwordValid = true;
    } 
    // Try plain text password (for existing data)
    elseif ($user['password'] === $password) {
        $passwordValid = true;
    }

    if (!$passwordValid) {
        redirectWithError('Invalid User ID or Password');
    }

    // Check if registration is completed
    if (!$user['registration_completed']) {
        redirectWithError('Please complete your registration process first');
    }

    // Login successful - create session
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_hostel'] = $user['hostel'];
    $_SESSION['login_time'] = time();

    // Log login activity
    $logStmt = $pdo->prepare("INSERT INTO login_logs (user_id, login_time, ip_address, user_agent) VALUES (?, NOW(), ?, ?)");
    $logStmt->execute([
        $user['user_id'],
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    header('Location: clean_dashboard.php');
    exit;

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    redirectWithError('System error. Please try again.');
}

function redirectWithError($message) {
    header('Location: new_login.html?error=' . urlencode($message));
    exit;
}
?>