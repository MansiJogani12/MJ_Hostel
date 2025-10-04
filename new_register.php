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
    header('Location: new_register.html');
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$userId = trim($_POST['userId'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$hostel = $_POST['hostel'] ?? '';

// Parent Information
$fatherName = trim($_POST['fatherName'] ?? '');
$motherName = trim($_POST['motherName'] ?? '');
$fatherPhone = trim($_POST['fatherPhone'] ?? '');

// Address Information
$addressLine1 = trim($_POST['addressLine1'] ?? '');
$addressLine2 = trim($_POST['addressLine2'] ?? '');
$city = trim($_POST['city'] ?? '');
$taluka = trim($_POST['taluka'] ?? '');
$district = trim($_POST['district'] ?? '');
$state = trim($_POST['state'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');
$country = trim($_POST['country'] ?? '');
$roomNumber = trim($_POST['roomNumber'] ?? '');

// Validate required fields
$missingFields = [];
if (empty($name)) $missingFields[] = 'name';
if (empty($email)) $missingFields[] = 'email';
if (empty($userId)) $missingFields[] = 'userId';
if (empty($phone)) $missingFields[] = 'phone';
if (empty($password)) $missingFields[] = 'password';
if (empty($hostel)) $missingFields[] = 'hostel';
if (empty($addressLine1)) $missingFields[] = 'addressLine1';
if (empty($city)) $missingFields[] = 'city';
if (empty($district)) $missingFields[] = 'district';
if (empty($state)) $missingFields[] = 'state';
if (empty($pincode)) $missingFields[] = 'pincode';
if (empty($country)) $missingFields[] = 'country';

if (!empty($missingFields)) {
    redirectWithError('Missing required fields: ' . implode(', ', $missingFields));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Please enter a valid email address');
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    redirectWithError('Phone number must be exactly 10 digits');
}

if (!empty($fatherPhone) && !preg_match('/^[0-9]{10}$/', $fatherPhone)) {
    redirectWithError('Father\'s phone number must be exactly 10 digits');
}

if (!empty($pincode) && !preg_match('/^[0-9]{6}$/', $pincode)) {
    redirectWithError('Pincode must be exactly 6 digits');
}

if (strlen($password) < 6) {
    redirectWithError('Password must be at least 6 characters long');
}

if ($password !== $confirmPassword) {
    redirectWithError('Passwords do not match');
}

try {
    // Check if user ID already exists
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetch()) {
        redirectWithError('User ID already exists. Please choose a different one.');
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        redirectWithError('Email already registered. Please use a different email.');
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $pdo->beginTransaction();

    // Insert user into students table
    $stmt = $pdo->prepare("
        INSERT INTO students (user_id, name, email, phone, password, hostel, registration_completed, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, TRUE, NOW())
    ");
    
    $stmt->execute([$userId, $name, $email, $phone, $hashedPassword, $hostel]);
    $studentId = $pdo->lastInsertId();

    // Insert parent information if provided
    if (!empty($fatherName) || !empty($motherName) || !empty($fatherPhone)) {
        $stmt = $pdo->prepare("
            INSERT INTO parent_info (student_id, father_name, mother_name, father_phone) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $fatherName, $motherName, $fatherPhone]);
    }

    // Insert address information
    $stmt = $pdo->prepare("
        INSERT INTO addresses (student_id, line1, line2, city, taluka, district, state, pincode, country, room_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$studentId, $addressLine1, $addressLine2, $city, $taluka, $district, $state, $pincode, $country, $roomNumber]);

    // Commit transaction
    $pdo->commit();

    // Registration successful
    redirectWithSuccess('Account created successfully! You can now login with your credentials.');

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Registration error: " . $e->getMessage());
    redirectWithError('Registration failed. Please try again.');
}

function redirectWithError($message) {
    header('Location: new_register.html?error=' . urlencode($message));
    exit;
}

function redirectWithSuccess($message) {
    header('Location: new_login.html?success=' . urlencode($message));
    exit;
}
?>