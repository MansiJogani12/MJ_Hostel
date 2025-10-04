<?php
$file = 'students.json';
$data = [];

// Load existing JSON data
if(file_exists($file)) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
}

// Determine which step
$step = $_POST['step'] ?? '';

if($step == '1') {
    // Step 1: Basic info
    $userId = $_POST['userId'];
    $data[$userId] = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'hostel' => $_POST['hostel']
    ];

    // Save to JSON
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

    // Redirect to registration1.html
    header("Location: registration1.html?userId=".$userId);
    exit;

} elseif($step == '2') {
    // Step 2: Additional info
    $userId = $_GET['userId'] ?? '';

    if(isset($data[$userId])) {
        $data[$userId]['fatherName'] = $_POST['fatherName'];
        $data[$userId]['motherName'] = $_POST['motherName'];
        $data[$userId]['fatherPhone'] = $_POST['fatherPhone'];
        $data[$userId]['address'] = [
            'line1' => $_POST['line1'],
            'line2' => $_POST['line2'],
            'city' => $_POST['city'],
            'taluka' => $_POST['taluka'],
            'pincode' => $_POST['pincode'],
            'district' => $_POST['district'],
            'state' => $_POST['state'],
            'country' => $_POST['country'],
            'room' => $_POST['room']
        ];

        // Save final JSON
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

        // Redirect to dashboard
        header("Location: dashboard.html");
        exit;
    } else {
        echo "User ID not found. Please start from the first registration page.";
    }

} else {
    echo "Invalid submission.";
}
?>
