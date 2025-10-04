<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => null, 'message' => ''];

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get user ID from session, URL parameter, or use latest registration
    $userId = $_SESSION['user_id'] ?? ($_GET['userId'] ?? '');
    $studentData = null;
    
    if ($userId) {
        // Get specific student data with related information
        $sql = "SELECT 
                    s.id, s.user_id, s.name, s.email, s.phone, s.hostel, 
                    s.registration_step, s.registration_completed, s.created_at, s.updated_at,
                    p.father_name, p.mother_name, p.father_phone,
                    a.line1, a.line2, a.city, a.taluka, a.pincode, 
                    a.district, a.state, a.country, a.room_number
                FROM students s
                LEFT JOIN parent_info p ON s.id = p.student_id
                LEFT JOIN addresses a ON s.id = a.student_id
                WHERE s.user_id = ?";
        
        $studentData = getSingleRecord($pdo, $sql, [$userId]);
        
        if (!$studentData) {
            throw new Exception('Student not found');
        }
        
        // Restructure data to match the expected format
        $formattedData = [
            'userId' => $studentData['user_id'],
            'name' => $studentData['name'],
            'email' => $studentData['email'],
            'phone' => $studentData['phone'],
            'hostel' => $studentData['hostel'],
            'registrationStep' => $studentData['registration_step'],
            'registrationCompleted' => (bool)$studentData['registration_completed'],
            'createdAt' => $studentData['created_at'],
            'updatedAt' => $studentData['updated_at'],
            'parentInfo' => [
                'fatherName' => $studentData['father_name'],
                'motherName' => $studentData['mother_name'],
                'fatherPhone' => $studentData['father_phone']
            ],
            'address' => [
                'line1' => $studentData['line1'],
                'line2' => $studentData['line2'],
                'city' => $studentData['city'],
                'taluka' => $studentData['taluka'],
                'pincode' => $studentData['pincode'],
                'district' => $studentData['district'],
                'state' => $studentData['state'],
                'country' => $studentData['country'],
                'room' => $studentData['room_number']
            ]
        ];
        
        $response['success'] = true;
        $response['data'] = $formattedData;
        $response['message'] = 'Student data loaded successfully';
        
    } else {
        // Get the latest completed registration if no specific user requested
        $sql = "SELECT 
                    s.id, s.user_id, s.name, s.email, s.phone, s.hostel, 
                    s.registration_step, s.registration_completed, s.created_at, s.updated_at,
                    p.father_name, p.mother_name, p.father_phone,
                    a.line1, a.line2, a.city, a.taluka, a.pincode, 
                    a.district, a.state, a.country, a.room_number
                FROM students s
                LEFT JOIN parent_info p ON s.id = p.student_id
                LEFT JOIN addresses a ON s.id = a.student_id
                WHERE s.registration_completed = TRUE
                ORDER BY s.created_at DESC
                LIMIT 1";
        
        $studentData = getSingleRecord($pdo, $sql);
        
        if (!$studentData) {
            // If no completed registrations, get the latest student
            $sql = "SELECT 
                        s.id, s.user_id, s.name, s.email, s.phone, s.hostel, 
                        s.registration_step, s.registration_completed, s.created_at, s.updated_at,
                        p.father_name, p.mother_name, p.father_phone,
                        a.line1, a.line2, a.city, a.taluka, a.pincode, 
                        a.district, a.state, a.country, a.room_number
                    FROM students s
                    LEFT JOIN parent_info p ON s.id = p.student_id
                    LEFT JOIN addresses a ON s.id = a.student_id
                    ORDER BY s.created_at DESC
                    LIMIT 1";
            
            $studentData = getSingleRecord($pdo, $sql);
        }
        
        if (!$studentData) {
            throw new Exception('No students registered yet');
        }
        
        // Format data same as above
        $formattedData = [
            'userId' => $studentData['user_id'],
            'name' => $studentData['name'],
            'email' => $studentData['email'],
            'phone' => $studentData['phone'],
            'hostel' => $studentData['hostel'],
            'registrationStep' => $studentData['registration_step'],
            'registrationCompleted' => (bool)$studentData['registration_completed'],
            'createdAt' => $studentData['created_at'],
            'updatedAt' => $studentData['updated_at'],
            'parentInfo' => [
                'fatherName' => $studentData['father_name'],
                'motherName' => $studentData['mother_name'],
                'fatherPhone' => $studentData['father_phone']
            ],
            'address' => [
                'line1' => $studentData['line1'],
                'line2' => $studentData['line2'],
                'city' => $studentData['city'],
                'taluka' => $studentData['taluka'],
                'pincode' => $studentData['pincode'],
                'district' => $studentData['district'],
                'state' => $studentData['state'],
                'country' => $studentData['country'],
                'room' => $studentData['room_number']
            ]
        ];
        
        $response['success'] = true;
        $response['data'] = $formattedData;
        $response['message'] = 'Student data loaded successfully';
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Get student data error: " . $e->getMessage());
}

echo json_encode($response);
?>