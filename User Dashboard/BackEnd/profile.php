<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = mysqli_connect('localhost', 'root', '', 'shotsafe_data');
    if (!$db) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    class ProfileManager {
        private $db;

        public function __construct($db) {
            $this->db = $db;
        }

        private function validateCSRFToken($token) {
            if (empty($token) || $token !== $_SESSION['csrf_token']) {
                throw new Exception('CSRF token mismatch');
            }
        }

        public function getProfile($userId) {
            $query = "
                SELECT 
                    u.fname, u.mname, u.lname,
                    p.birthday, p.sex, p.civilstat, p.nationality,
                    e.employment_stat, e.employer, e.profession,
                    a.address, a.barangay,
                    c.email, c.contact
                FROM user_name u
                INNER JOIN personal p ON u.name_id = p.name_id
                INNER JOIN address a ON u.name_id = a.name_id
                INNER JOIN contact c ON u.name_id = c.name_id
                LEFT JOIN employment e ON u.name_id = e.name_id
                WHERE u.name_id = ?
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $profile = $result->fetch_assoc();

            if (!$profile) {
                throw new Exception('Profile not found');
            }

            return $profile;
        }

        public function updateProfile($userId, $data) {
            $this->validateCSRFToken($data['csrf_token']);

            $this->db->begin_transaction();

            try {
                // Update personal info
                $stmt = $this->db->prepare("
                    UPDATE personal
                    SET birthday = ?, sex = ?, civilstat = ?, nationality = ?
                    WHERE name_id = ?
                ");
                $stmt->bind_param("ssssi", 
                    $data['birthday'],
                    $data['sex'],
                    $data['civilstat'],
                    $data['nationality'],
                    $userId
                );
                $stmt->execute();

                // Update address
                $stmt = $this->db->prepare("
                    UPDATE address
                    SET address = ?, barangay = ?
                    WHERE name_id = ?
                ");
                $stmt->bind_param("ssi",
                    $data['address'],
                    $data['barangay'],
                    $userId
                );
                $stmt->execute();

                // Update contact
                $stmt = $this->db->prepare("
                    UPDATE contact
                    SET contact = ?, email = ?
                    WHERE name_id = ?
                ");
                $stmt->bind_param("ssi",
                    $data['contact'],
                    $data['email'],
                    $userId
                );
                $stmt->execute();

                if (!empty($data['employment_stat'])) {
                    $stmt = $this->db->prepare("
                        UPDATE employment
                        SET employment_stat = ?, employer = ?, profession = ?
                        WHERE name_id = ?
                    ");
                    $stmt->bind_param("sssi",
                        $data['employment_stat'],
                        $data['employer'],
                        $data['profession'],
                        $userId
                    );
                    $stmt->execute();
                }

                $this->db->commit();

                return ['success' => true, 'message' => 'Profile updated successfully'];
            } catch (Exception $e) {
                $this->db->rollback();
                error_log('Error updating profile: ' . $e->getMessage());
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
        }
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    $userId = $_SESSION['user_id'];
    $profileManager = new ProfileManager($db);
    $response = [];

    // Handle AJAX actions
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get_profile':
                $response = ['success' => true, 'data' => $profileManager->getProfile($userId)];
                break;

            case 'update_profile':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $response = $profileManager->updateProfile($userId, $data);
                }
                break;

            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('No action specified');
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log('Critical error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
