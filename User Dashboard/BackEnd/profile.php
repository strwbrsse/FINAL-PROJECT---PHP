<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database connection
    $db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'root', ''); // Update with your credentials
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    class ProfileManager {
        private $db;
        
        public function __construct($db) {
            $this->db = $db;
        }

        public function getProfile($userId) {
            try {
                // Debug log
                error_log("Fetching profile for user ID: " . $userId);
                
                $stmt = $this->db->prepare("
                    SELECT 
                        fname, mname, lname, birthday, mail, contact, sex, civilstat, 
                        nationality, employmentstat, employer, profession, address, barangay
                    FROM Personal_Info
                    WHERE personal_id = :user_id
                ");
                
                $stmt->execute(['user_id' => $userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$result) {
                    error_log("No profile found for user ID: " . $userId);
                    return ['error' => 'Profile not found'];
                }
                
                return $result;
            } catch (Exception $e) {
                error_log('Error in getProfile: ' . $e->getMessage());
                return ['error' => 'Database error: ' . $e->getMessage()];
            }
        }

        public function updateProfile($userId, $data) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE Personal_Info SET
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
                    'employer' => $data['employer'],
                    'profession' => $data['profession'],
                    'address' => $data['address'],
                    'barangay' => $data['barangay'],
                    'user_id' => $userId
                ]);
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } catch (Exception $e) {
                error_log('Error updating profile: ' . $e->getMessage());
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
        }
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Handle AJAX requests
    if (isset($_GET['action'])) {
        $profileManager = new ProfileManager($db);
        $response = [];

        switch ($_GET['action']) {
            case 'get_profile':
                error_log("Getting profile for user ID: " . $_SESSION['user_id']);
                $response = $profileManager->getProfile($_SESSION['user_id']);
                break;

            case 'update_profile':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $response = $profileManager->updateProfile($_SESSION['user_id'], $data);
                }
                break;
        }

        echo json_encode($response);
    }

} catch (Exception $e) {
    error_log('Critical error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 