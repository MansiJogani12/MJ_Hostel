<?php
/**
 * Registration Data Validator and Utilities
 * Provides helper functions for data validation and management
 */

class RegistrationValidator {
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (10 digits)
     */
    public static function validatePhone($phone) {
        return preg_match('/^[0-9]{10}$/', $phone);
    }
    
    /**
     * Validate pincode (6 digits)
     */
    public static function validatePincode($pincode) {
        return preg_match('/^[0-9]{6}$/', $pincode);
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequiredFields($data, $requiredFields) {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
    
    /**
     * Check if user ID already exists
     */
    public static function userIdExists($userId, $studentsData) {
        foreach ($studentsData as $student) {
            if (isset($student['userId']) && $student['userId'] === $userId) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    
    /**
     * Generate unique registration ID
     */
    public static function generateRegistrationId() {
        return 'REG_' . date('Ymd') . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
    
    /**
     * Log registration activity
     */
    public static function logActivity($userId, $action, $details = '') {
        $logFile = 'registration_logs.txt';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] User: $userId | Action: $action | Details: $details" . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Send email notification (placeholder - requires mail configuration)
     */
    public static function sendConfirmationEmail($email, $name, $userId) {
        // This is a placeholder function
        // In a real application, you would configure SMTP settings and send actual emails
        
        $subject = "Registration Confirmation - MJ Hostel";
        $message = "Dear $name,\n\n";
        $message .= "Your registration has been successfully completed.\n";
        $message .= "User ID: $userId\n";
        $message .= "Registration Date: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "Please wait for approval from the hostel administration.\n\n";
        $message .= "Best regards,\nMJ Hostel Management";
        
        // For testing purposes, save email content to a file
        $emailFile = "emails/confirmation_" . $userId . "_" . date('Ymd_His') . ".txt";
        @mkdir('emails', 0755, true);
        file_put_contents($emailFile, "To: $email\nSubject: $subject\n\n$message");
        
        return true; // Return true for now, implement actual email sending later
    }
    
    /**
     * Get registration statistics
     */
    public static function getRegistrationStats($studentsData) {
        $stats = [
            'total' => count($studentsData),
            'completed' => 0,
            'incomplete' => 0,
            'by_hostel' => [],
            'recent' => 0 // last 24 hours
        ];
        
        $oneDayAgo = strtotime('-1 day');
        
        foreach ($studentsData as $student) {
            if (isset($student['registrationCompleted']) && $student['registrationCompleted']) {
                $stats['completed']++;
            } else {
                $stats['incomplete']++;
            }
            
            // Count by hostel
            if (isset($student['hostel'])) {
                $hostel = $student['hostel'];
                $stats['by_hostel'][$hostel] = ($stats['by_hostel'][$hostel] ?? 0) + 1;
            }
            
            // Count recent registrations
            if (isset($student['createdAt'])) {
                $createdTime = strtotime($student['createdAt']);
                if ($createdTime > $oneDayAgo) {
                    $stats['recent']++;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Clean old incomplete registrations (older than 24 hours)
     */
    public static function cleanOldIncompleteRegistrations($studentsData) {
        $oneDayAgo = strtotime('-1 day');
        $cleanedData = [];
        $removedCount = 0;
        
        foreach ($studentsData as $student) {
            $shouldKeep = true;
            
            // Remove incomplete registrations older than 24 hours
            if (!isset($student['registrationCompleted']) || !$student['registrationCompleted']) {
                if (isset($student['createdAt'])) {
                    $createdTime = strtotime($student['createdAt']);
                    if ($createdTime < $oneDayAgo) {
                        $shouldKeep = false;
                        $removedCount++;
                    }
                }
            }
            
            if ($shouldKeep) {
                $cleanedData[] = $student;
            }
        }
        
        return ['data' => $cleanedData, 'removed' => $removedCount];
    }
}

/**
 * Data Export Utilities
 */
class DataExporter {
    
    /**
     * Export students data to CSV
     */
    public static function exportToCSV($studentsData, $filename = null) {
        if ($filename === null) {
            $filename = 'students_export_' . date('Ymd_His') . '.csv';
        }
        
        $output = fopen($filename, 'w');
        
        // CSV Headers
        $headers = [
            'User ID', 'Name', 'Email', 'Phone', 'Hostel',
            'Father Name', 'Mother Name', 'Father Phone',
            'Address Line 1', 'Address Line 2', 'City', 'Taluka',
            'Pincode', 'District', 'State', 'Country', 'Room',
            'Registration Step', 'Completed', 'Created At', 'Completed At'
        ];
        fputcsv($output, $headers);
        
        // Data rows
        foreach ($studentsData as $student) {
            $row = [
                $student['userId'] ?? '',
                $student['name'] ?? '',
                $student['email'] ?? '',
                $student['phone'] ?? '',
                $student['hostel'] ?? '',
                $student['parentInfo']['fatherName'] ?? '',
                $student['parentInfo']['motherName'] ?? '',
                $student['parentInfo']['fatherPhone'] ?? '',
                $student['address']['line1'] ?? '',
                $student['address']['line2'] ?? '',
                $student['address']['city'] ?? '',
                $student['address']['taluka'] ?? '',
                $student['address']['pincode'] ?? '',
                $student['address']['district'] ?? '',
                $student['address']['state'] ?? '',
                $student['address']['country'] ?? '',
                $student['address']['room'] ?? '',
                $student['registrationStep'] ?? '',
                ($student['registrationCompleted'] ?? false) ? 'Yes' : 'No',
                $student['createdAt'] ?? '',
                $student['completedAt'] ?? ''
            ];
            fputcsv($output, $row);
        }
        
        fclose($output);
        return $filename;
    }
}
?>