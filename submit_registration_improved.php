<?php
session_start();

// Define file paths
$studentsFile = 'students.json';
$registrationsFile = 'registrations.json';

// Initialize response
$response = ['success' => false, 'message' => '', 'redirect' => ''];

try {
    // Load existing data
    $studentsData = [];
    $registrationsData = [];
    
    if (file_exists($studentsFile)) {
        $json = file_get_contents($studentsFile);
        $studentsData = json_decode($json, true) ?? [];
    }
    
    if (file_exists($registrationsFile)) {
        $json = file_get_contents($registrationsFile);
        $registrationsData = json_decode($json, true) ?? [];
    }

    // Determine which form was submitted
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['userId'])) {
        // Step 1: Basic registration from registration1.html
        handleStep1Registration();
    } elseif (isset($_POST['fatherName']) && isset($_POST['motherName'])) {
        // Step 2: Additional info from registration.html
        handleStep2Registration();
    } else {
        throw new Exception('Invalid form submission');
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

function handleStep1Registration() {
    global $studentsData, $studentsFile, $response;
    
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
    
    // Check if user ID already exists
    $userId = trim($_POST['userId']);
    foreach ($studentsData as $student) {
        if (isset($student['userId']) && $student['userId'] === $userId) {
            throw new Exception('User ID already exists. Please choose a different one.');
        }
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate phone number (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
        throw new Exception('Phone number must be exactly 10 digits');
    }
    
    // Create student record for step 1
    $studentRecord = [
        'userId' => $userId,
        'name' => trim($_POST['name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'hostel' => $_POST['hostel'],
        'registrationStep' => 1,
        'createdAt' => date('Y-m-d H:i:s'),
        'updatedAt' => date('Y-m-d H:i:s')
    ];
    
    // Add to students array
    $studentsData[] = $studentRecord;
    
    // Save to file
    if (file_put_contents($studentsFile, json_encode($studentsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Store user ID in session for step 2
        $_SESSION['registration_userId'] = $userId;
        $_SESSION['registration_step1_complete'] = true;
        
        $response['success'] = true;
        $response['message'] = 'Step 1 completed successfully';
        $response['redirect'] = 'registration.html?userId=' . urlencode($userId);
    } else {
        throw new Exception('Failed to save registration data');
    }
}

function handleStep2Registration() {
    global $studentsData, $registrationsData, $studentsFile, $registrationsFile, $response;
    
    // Get user ID from session or URL parameter
    $userId = $_SESSION['registration_userId'] ?? ($_GET['userId'] ?? '');
    
    if (empty($userId)) {
        throw new Exception('User ID not found. Please start from step 1.');
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
    
    // Find and update the student record
    $studentFound = false;
    for ($i = 0; $i < count($studentsData); $i++) {
        if (isset($studentsData[$i]['userId']) && $studentsData[$i]['userId'] === $userId) {
            // Update existing record with additional info
            $studentsData[$i]['parentInfo'] = [
                'fatherName' => trim($_POST['fatherName']),
                'motherName' => trim($_POST['motherName']),
                'fatherPhone' => trim($_POST['fatherPhone'])
            ];
            
            $studentsData[$i]['address'] = [
                'line1' => trim($_POST['line1']),
                'line2' => trim($_POST['line2'] ?? ''),
                'city' => trim($_POST['city']),
                'taluka' => trim($_POST['taluka'] ?? ''),
                'pincode' => trim($_POST['pincode']),
                'district' => trim($_POST['district'] ?? ''),
                'state' => trim($_POST['state']),
                'country' => trim($_POST['country']),
                'room' => (int)$_POST['room']
            ];
            
            $studentsData[$i]['registrationStep'] = 2;
            $studentsData[$i]['registrationCompleted'] = true;
            $studentsData[$i]['updatedAt'] = date('Y-m-d H:i:s');
            $studentsData[$i]['completedAt'] = date('Y-m-d H:i:s');
            
            $studentFound = true;
            break;
        }
    }
    
    if (!$studentFound) {
        throw new Exception('Student record not found. Please start from step 1.');
    }
    
    // Also save to registrations.json for backup/tracking
    $registrationRecord = [
        'userId' => $userId,
        'parentInfo' => $studentsData[$i]['parentInfo'],
        'address' => $studentsData[$i]['address'],
        'submittedOn' => date('Y-m-d H:i:s')
    ];
    $registrationsData[] = $registrationRecord;
    
    // Save both files
    $studentsSuccess = file_put_contents($studentsFile, json_encode($studentsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $registrationsSuccess = file_put_contents($registrationsFile, json_encode($registrationsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($studentsSuccess && $registrationsSuccess) {
        // Clear session data
        unset($_SESSION['registration_userId']);
        unset($_SESSION['registration_step1_complete']);
        
        $response['success'] = true;
        $response['message'] = 'Registration completed successfully!';
        $response['redirect'] = 'registration_success.html?userId=' . urlencode($userId);
    } else {
        throw new Exception('Failed to save registration data');
    }
}

// Return JSON response for AJAX calls or redirect for form submissions
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // For regular form submissions
    if ($response['success'] && !empty($response['redirect'])) {
        header('Location: ' . $response['redirect']);
        exit;
    } else {
        echo '<h2>Registration Status</h2>';
        echo '<p>' . $response['message'] . '</p>';
        if (!$response['success']) {
            echo '<a href="javascript:history.back()">Go Back</a>';
        }
    }
}
?>