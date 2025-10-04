<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'mj_hostel';
$username = 'root';
$password = '';

function redirectWithError($message) {
    header('Location: new_forgot_userid.html?error=' . urlencode($message));
    exit;
}

function redirectWithSuccess($message) {
    header('Location: new_forgot_userid.html?success=' . urlencode($message));
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Invalid request method');
}

// Get form data
$email = trim($_POST['email'] ?? '');

// Validate input
if (empty($email)) {
    redirectWithError('Email address is required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Please enter a valid email address');
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Find user by email
    $stmt = $pdo->prepare("SELECT user_id, name FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithError('No account found with this email address');
    }

    // In a real application, you would send an email here
    // For now, we'll just show the User ID (for demonstration purposes)
    
    // Log the recovery attempt (handle missing action column gracefully)
    try {
        $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, login_time, ip_address, user_agent, action) VALUES (?, NOW(), ?, ?, 'userid_recovery')");
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

    // For demonstration, we'll show the User ID
    // In production, this should be sent via email
    redirectWithSuccess('Your User ID is: ' . $user['user_id'] . ' (In production, this would be sent to your email)');

} catch (Exception $e) {
    error_log("User ID recovery error: " . $e->getMessage());
    redirectWithError('System error occurred. Please try again later.');
}
?>