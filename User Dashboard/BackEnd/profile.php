<?php
session_start();

$uploadDir = '../uploads/profile_pictures/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Database connection
$db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'username', 'password');

class ProfileManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserProfile() {
        $stmt = $this->db->prepare("
            SELECT 
                pi.*,
                ua.username,
                h.allergy_description,
                h.disease_description
            FROM Personal_Info pi
            LEFT JOIN User_Auth ua ON pi.personal_id = ua.user_id
            LEFT JOIN Health h ON pi.personal_id = h.user_id
            WHERE pi.personal_id = :user_id
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePersonalInfo($data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE Personal_Info 
                SET 
                    fname = :fname,
                    mname = :mname,
                    lname = :lname,
                    birthday = :birthday,
                    mail = :mail,
                    contact = :contact,
                    sex = :sex,
                    civilstat = :civilstat,
                    nationality = :nationality,
                    employmentstat = :employmentstat,
                    employer = :employer,
                    profession = :profession,
                    address = :address,
                    barangay = :barangay
                WHERE personal_id = :user_id
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'fname' => $data['fname'],
                'mname' => $data['mname'],
                'lname' => $data['lname'],
                'birthday' => $data['birthday'],
                'mail' => $data['mail'],
                'contact' => $data['contact'],
                'sex' => $data['sex'],
                'civilstat' => $data['civilstat'],
                'nationality' => $data['nationality'],
                'employmentstat' => $data['employmentstat'],
                'employer' => $data['employer'] ?? null,
                'profession' => $data['profession'] ?? null,
                'address' => $data['address'],
                'barangay' => $data['barangay']
            ]);

            return ['success' => true, 'message' => 'Personal information updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updatePassword($currentPassword, $newPassword) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("
                SELECT password 
                FROM User_Auth 
                WHERE user_id = :user_id
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE User_Auth 
                SET password = :password 
                WHERE user_id = :user_id
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'password' => $hashedPassword
            ]);

            return ['success' => true, 'message' => 'Password updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateHealthInfo($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Health (
                    user_id,
                    allergy_description,
                    disease_description
                ) VALUES (
                    :user_id,
                    :allergy_description,
                    :disease_description
                ) ON DUPLICATE KEY UPDATE
                    allergy_description = VALUES(allergy_description),
                    disease_description = VALUES(disease_description)
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'allergy_description' => $data['allergy_description'],
                'disease_description' => $data['disease_description']
            ]);

            return ['success' => true, 'message' => 'Health information updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function uploadProfilePicture($file) {
        try {
            $targetDir = "uploads/profile_pictures/";
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = "profile_" . $_SESSION['user_id'] . "." . $fileExtension;
            $targetFile = $targetDir . $newFileName;

            // Check file type
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            if (!in_array($fileExtension, $allowedTypes)) {
                throw new Exception('Only JPG, JPEG & PNG files are allowed');
            }

            // Check file size (5MB max)
            if ($file['size'] > 5000000) {
                throw new Exception('File is too large (max 5MB)');
            }

            // Upload file
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                // Update database with new profile picture path
                $stmt = $this->db->prepare("
                    UPDATE Personal_Info 
                    SET profile_picture = :profile_picture 
                    WHERE personal_id = :user_id
                ");
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'profile_picture' => $newFileName
                ]);

                return [
                    'success' => true, 
                    'message' => 'Profile picture updated successfully',
                    'filename' => $newFileName
                ];
            } else {
                throw new Exception('Failed to upload file');
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteAccount() {
        try {
            $this->db->beginTransaction();

            // Delete related records first
            $tables = ['Health', 'Vaccine', 'User_Settings', 'User_Auth'];
            foreach ($tables as $table) {
                $stmt = $this->db->prepare("DELETE FROM $table WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $_SESSION['user_id']]);
            }

            // Finally delete personal info
            $stmt = $this->db->prepare("DELETE FROM Personal_Info WHERE personal_id = :user_id");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Account deleted successfully'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Handle profile picture upload
if ($_FILES['profile_picture']) {
    $file = $_FILES['profile_picture'];
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'filename' => $fileName
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload file'
        ]);
    }
}


// Get profile data
if ($_GET['action'] === 'get_profile') {
    // Replace with your actual database connection and query
    $profile = [
        'fname' => 'John',
        'mname' => '',
        'lname' => 'Doe',
        'birthday' => '1990-01-01',
        'mail' => 'john@example.com',
        'contact' => '1234567890',
        'sex' => 'Male',
        'profile_picture' => 'default.png'
    ];
    
    echo json_encode($profile);
}
?>

// Handle AJAX requests
if (isset($_GET['action'])) {
    $profileManager = new ProfileManager($db);
    $response = [];

    switch ($_GET['action']) {
        case 'get_profile':
            $response = $profileManager->getUserProfile();
            break;

        case 'update_personal_info':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $profileManager->updatePersonalInfo($data);
            }
            break;

        case 'update_password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $profileManager->updatePassword(
                    $data['current_password'],
                    $data['new_password']
                );
            }
            break;

        case 'update_health_info':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $profileManager->updateHealthInfo($data);
            }
            break;

        case 'upload_profile_picture':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
                $response = $profileManager->uploadProfilePicture($_FILES['profile_picture']);
            }
            break;

        case 'delete_account':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $response = $profileManager->deleteAccount();
                if ($response['success']) {
                    session_destroy();
                }
            }
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 