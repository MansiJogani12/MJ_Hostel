<?php
session_start();
require_once 'db_config.php';

// Initialize response
$response = ['success' => false, 'message' => '', 'redirect' => ''];

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Determine which form was submitted
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['userId'])) {
        // Step 1: Basic registration from registration1.html
        handleStep1Registration($pdo);
    } elseif (isset($_POST['fatherName']) && isset($_POST['motherName'])) {
        // Step 2: Additional info from registration.html
        handleStep2Registration($pdo);
    } else {
        throw new Exception('Invalid form submission');
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    // For form submissions, redirect back with error
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Location: registration1.html?error=' . urlencode($response['message']));
        exit;
    }
}

function handleStep1Registration($pdo) {
    global $response;
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'userId', 'phone', 'password', 'confirmPassword', 'hostel'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate password match
    if ($_POST['password'] !== $_POST['confirmPassword']) {
        throw new Exception('Passwords do not match');
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate phone number (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
        throw new Exception('Phone number must be exactly 10 digits');
    }
    
    $userId = trim($_POST['userId']);
    
    // Check if user ID already exists
    $existingUser = getSingleRecord($pdo, 
        "SELECT id FROM students WHERE user_id = ?", 
        [$userId]
    );
    
    if ($existingUser) {
        throw new Exception('User ID already exists. Please choose a different one.');
    }
    
    // Check if email already exists
    $existingEmail = getSingleRecord($pdo, 
        "SELECT id FROM students WHERE email = ?", 
        [$_POST['email']]
    );
    
    if ($existingEmail) {
        throw new Exception('Email already registered. Please use a different email.');
    }
    
    // Hash password
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Insert student record
    $sql = "INSERT INTO students (user_id, name, email, phone, password, hostel, registration_step) 
            VALUES (?, ?, ?, ?, ?, ?, 1)";
    
    $studentId = insertRecord($pdo, $sql, [
        $userId,
        trim($_POST['name']),
        trim($_POST['email']),
        trim($_POST['phone']),
        $hashedPassword,
        $_POST['hostel']
    ]);
    
    if ($studentId) {
        // Store user ID in session for step 2
        $_SESSION['registration_userId'] = $userId;
        $_SESSION['registration_studentId'] = $studentId;
        $_SESSION['registration_step1_complete'] = true;
        
        $response['success'] = true;
        $response['message'] = 'Step 1 completed successfully';
        $response['redirect'] = 'registration.html?userId=' . urlencode($userId);
        
        // Redirect to step 2
        header('Location: registration.html?userId=' . urlencode($userId));
        exit;
    } else {
        throw new Exception('Failed to save registration data');
    }
}

function handleStep2Registration($pdo) {
    global $response;
    
    // Get user ID from session or URL parameter
    $userId = $_SESSION['registration_userId'] ?? ($_GET['userId'] ?? '');
    $studentId = $_SESSION['registration_studentId'] ?? null;
    
    if (empty($userId)) {
        throw new Exception('User ID not found. Please start from step 1.');
    }
    
    // If no student ID in session, get it from database
    if (!$studentId) {
        $student = getSingleRecord($pdo, 
            "SELECT id FROM students WHERE user_id = ?", 
            [$userId]
        );
        
        if (!$student) {
            throw new Exception('Student record not found. Please start from step 1.');
        }
        
        $studentId = $student['id'];
    }
    
    // Validate required fields for step 2
    $requiredFields = ['fatherName', 'motherName', 'fatherPhone', 'line1', 'city', 'pincode', 'state', 'country', 'room'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate father's phone number
    if (!preg_match('/^[0-9]{10}$/', $_POST['fatherPhone'])) {
        throw new Exception('Father\'s phone number must be exactly 10 digits');
    }
    
    // Validate pincode
    if (!preg_match('/^[0-9]{6}$/', $_POST['pincode'])) {
        throw new Exception('Pincode must be exactly 6 digits');
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Insert parent info
        $parentSql = "INSERT INTO parent_info (student_id, father_name, mother_name, father_phone) 
                      VALUES (?, ?, ?, ?)";
        
        executeQuery($pdo, $parentSql, [
            $studentId,
            trim($_POST['fatherName']),
            trim($_POST['motherName']),
            trim($_POST['fatherPhone'])
        ]);
        
        // Insert address info
        $addressSql = "INSERT INTO addresses (student_id, line1, line2, city, taluka, pincode, district, state, country, room_number) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        executeQuery($pdo, $addressSql, [
            $studentId,
            trim($_POST['line1']),
            trim($_POST['line2'] ?? ''),
            trim($_POST['city']),
            trim($_POST['taluka'] ?? ''),
            trim($_POST['pincode']),
            trim($_POST['district'] ?? ''),
            trim($_POST['state']),
            trim($_POST['country']),
            (int)$_POST['room']
        ]);
        
        // Update student record as completed
        $updateSql = "UPDATE students SET registration_step = 2, registration_completed = TRUE, updated_at = NOW() 
                      WHERE id = ?";
        
        executeQuery($pdo, $updateSql, [$studentId]);
        
        // Commit transaction
        $pdo->commit();
        
        // Clear session data
        unset($_SESSION['registration_userId']);
        unset($_SESSION['registration_studentId']);
        unset($_SESSION['registration_step1_complete']);
        
        $response['success'] = true;
        $response['message'] = 'Registration completed successfully!';
        $response['redirect'] = 'registration_success.html?userId=' . urlencode($userId);
        
        // Redirect to success page
        header('Location: registration_success.html?userId=' . urlencode($userId));
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
}

// Return JSON response for AJAX calls
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // For regular form submissions, redirect is handled in functions above
    if (!$response['success']) {
        echo '<h2>Registration Status</h2>';
        echo '<p>' . $response['message'] . '</p>';
        echo '<a href="javascript:history.back()">Go Back</a>';
    }
}
?>