<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'mj_hostel';
$username = 'root';
$password = '';

function redirectWithError($message) {
    header('Location: new_forgot_password.html?error=' . urlencode($message));
    exit;
}

function redirectWithSuccess($message) {
    header('Location: new_forgot_password.html?success=' . urlencode($message));
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Invalid request method');
}

// Get form data
$userId = trim($_POST['userId'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validate input
if (empty($userId) || empty($email)) {
    redirectWithError('Both User ID and email address are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Please enter a valid email address');
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Find user by User ID and email
    $stmt = $pdo->prepare("SELECT id, user_id, name, email FROM students WHERE user_id = ? AND email = ?");
    $stmt->execute([$userId, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithError('No account found with this User ID and email combination');
    }

    // Generate a temporary password (in production, use a secure token system)
    $tempPassword = 'MJ' . rand(1000, 9999) . '!';
    $hashedTempPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Update user's password to temporary password
    $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
    $stmt->execute([$hashedTempPassword, $user['id']]);

    // Log the password reset attempt (handle missing action column gracefully)
    try {
        $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, login_time, ip_address, user_agent, action) VALUES (?, NOW(), ?, ?, 'password_reset')");
        $stmt->execute([
            $user['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $logError) {
        // If action column doesn't exist, try without it
        $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, login_time, ip_address, user_agent) VALUES (?, NOW(), ?, ?)");
        $stmt->execute([
            $user['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    // In production, you would send this via email
    // For demonstration, we'll show the temporary password
    redirectWithSuccess('Your temporary password is: ' . $tempPassword . ' - Please login and change your password immediately. (In production, this would be sent to your email)');

} catch (Exception $e) {
    error_log("Password recovery error: " . $e->getMessage());
    redirectWithError('System error occurred. Please try again later.');
}
?>