<?php

require_once 'DB_Connect.php';

// Main database operations class that handles all SQL queries and data management
class SQL_Operations
{
    // Database connection instance for managing MySQL connections
    private $conn;

    // Initializes database connection using configuration
    public function __construct($config)
    {
        if ($config instanceof DbConn) {
            $this->conn = $config;
        } else {
            $this->conn = new DbConn($config);
        }
    }

    // Get database connection
    public function getConnection()
    {
        return $this->conn->getConnection();
    }

    // Retrieves user data for login authentication
    public function authenticate($email)
    {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
                ua.password,
                ua.name_id as user_id,
                n.fname,
                n.lname,
                c.email,
                p.sex,
                p.civilstat
                FROM contact c
                JOIN user_name n ON c.name_id = n.name_id
                JOIN User_Auth ua ON n.name_id = ua.name_id
                JOIN personal p ON n.name_id = p.name_id
                WHERE c.email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Verifies if email is already registered in contact table
    public function check_ExistingUser($mail)
    {
        return $this->checkExists('contact', ['email' => $mail]);
    }

    // Searches for exact name match in user_name table
    public function check_ExistingName($fname, $mname, $lname)
    {
        return $this->checkExists('user_name', [
            'fname' => $fname,
            'mname' => $mname,
            'lname' => $lname
        ]);
    }

    // Checks if username is already taken in user_auth table
    public function check_ExistingUsername($Name) {
        return $this->checkExists('user_auth', ['username' => $Name]);
    }

    // Formats and validates user registration data
    public function registerUser(
        $fname, $mname, $lname, $dob, $mail, $num, $sex, 
        $civstat, $nationality, $empstat, $empl, $profession, 
        $address, $barangay, $allergies, $diseases
    ) {
        return [
            'fname' => $fname,
            'mname' => $mname,
            'lname' => $lname,
            'dob' => $dob,
            'mail' => $mail,
            'num' => $num,
            'sex' => $sex,
            'civstat' => $civstat,
            'nationality' => $nationality,
            'empstat' => $empstat,
            'empl' => $empl,
            'profession' => $profession,
            'address' => $address,
            'barangay' => $barangay,
            'allergies' => $allergies,
            'diseases' => $diseases
        ];
    }

    // Manages complete user registration process with transaction safety
    public function signUp($username, $password, $userData)
    {
        if (empty($userData)) {
            throw new Exception("User data is required");
        }

        $conn = $this->conn->getConnection();
        $conn->begin_transaction();

        try {
            // Validate required fields
            $requiredFields = ['fname', 'mname', 'lname', 'sex', 'civstat', 'dob', 'nationality', 
                              'address', 'barangay', 'num', 'mail', 'empstat', 'empl', 'profession'];
            
            foreach ($requiredFields as $field) {
                if (!isset($userData[$field]) || empty($userData[$field])) {
                    throw new Exception("Missing required field: " . $field);
                }
            }

            // Insert into user_name first
            $stmt = $conn->prepare("INSERT INTO user_name (fname, mname, lname) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $userData['fname'], $userData['mname'], $userData['lname']);
            $stmt->execute();
            $name_id = $conn->insert_id;
            $stmt->close();

            // Insert into personal using name_id
            $stmt = $conn->prepare("INSERT INTO personal (name_id, sex, civilstat, birthday, nationality) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $name_id, $userData['sex'], $userData['civstat'], $userData['dob'], $userData['nationality']);
            $stmt->execute();
            $stmt->close();

            // Insert into address
            $stmt = $conn->prepare("INSERT INTO address (name_id, address, barangay) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $userData['address'], $userData['barangay']);
            $stmt->execute();
            $stmt->close();

            // Insert into contact
            $stmt = $conn->prepare("INSERT INTO contact (name_id, contact, email) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $userData['num'], $userData['mail']);
            $stmt->execute();
            $stmt->close();

            // Insert into employment
            $stmt = $conn->prepare("INSERT INTO employment (name_id, employment_stat, employer, profession) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $name_id, $userData['empstat'], $userData['empl'], $userData['profession']);
            $stmt->execute();
            $stmt->close();

            // Insert into Health using name_id
            $stmt = $conn->prepare("INSERT INTO Health (name_id, allergy_description, disease_description) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $userData['allergies'], $userData['diseases']);
            $stmt->execute();
            $stmt->close();

            // Insert into User_Auth using name_id
            $stmt = $conn->prepare("INSERT INTO User_Auth (name_id, username, password) VALUES (?, ?, ?)");
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param('iss', $name_id, $username, $hashedPass);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            
            return [
                'success' => true,
                'user_id' => $name_id,
                'user_name' => $userData['fname'] . ' ' . $userData['lname']
            ];

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    // Protected method to get database connection
    protected function getDbConnection() {
        if (!$this->conn) {
            throw new Exception("Database connection not initialized");
        }
        return $this->conn->getConnection();
    }

    // Standardized error handler
    protected function handleError($e, $operation) {
        error_log("Database error during $operation: " . $e->getMessage());
        if ($this->conn) {
            $this->conn->getConnection()->rollback();
        }
        throw new Exception("Error during $operation: " . $e->getMessage());
    }

    // Consolidated appointment methods
    public function getUpcomingAppointments($nameId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE name_id = ? 
        AND appointment_date >= CURRENT_DATE
        AND status = 'scheduled'
        ORDER BY appointment_date ASC, appointment_time ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $nameId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPastAppointments($nameId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE name_id = ? 
        AND (appointment_date < CURRENT_DATE 
            OR status IN ('completed', 'cancelled'))
        ORDER BY appointment_date DESC, appointment_time DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $nameId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createAppointment($nameId, $vaccineType, $appointmentDate, $appointmentTime) {
        $conn = $this->conn->getConnection();
        try {
            $conn->begin_transaction();
            
            $sql = "INSERT INTO appointments (
                name_id,
                vaccine_type,
                appointment_date,
                appointment_time,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, 'scheduled', NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isss', $nameId, $vaccineType, $appointmentDate, $appointmentTime);
            $stmt->execute();
            
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public function updateAppointmentStatus($appointmentId, $nameId, $status) {
        $conn = $this->conn->getConnection();
        $sql = "UPDATE appointments 
                SET status = ? 
                WHERE id = ? AND name_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $status, $appointmentId, $nameId);
        return $stmt->execute();
    }

    public function getNextAppointment($nameId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE name_id = ? 
        AND appointment_date >= CURRENT_DATE
        AND status = 'scheduled'
        ORDER BY appointment_date ASC, appointment_time ASC
        LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $nameId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getUpcomingDoses($nameId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
                v.vaccine_name,
                COUNT(a.id) as doses_received
                FROM appointments a
                JOIN vaccines v ON a.vaccine_id = v.vaccine_id
                WHERE a.name_id = ?
                AND a.status = 'completed'
                GROUP BY v.vaccine_name";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $nameId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getProfile($nameId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
                n.fname, n.mname, n.lname,
                c.email as mail, c.contact,
                p.sex, p.civilstat, p.birthday,
                a.address, a.barangay,
                h.allergy_description, h.disease_description,
                e.employment_stat as employmentstat, e.employer, e.profession,
                p.nationality
                FROM user_name n
                LEFT JOIN personal p ON p.name_id = n.name_id
                LEFT JOIN contact c ON c.name_id = n.name_id
                LEFT JOIN address a ON a.name_id = n.name_id
                LEFT JOIN employment e ON e.name_id = n.name_id
                LEFT JOIN Health h ON h.name_id = n.name_id
                WHERE n.name_id = ?";
        
        error_log("Executing profile query for name_id: " . $nameId);
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $nameId);
        $stmt->execute();
        $userData = $stmt->get_result()->fetch_assoc();
        
        if ($userData) {
            error_log('Profile found: ' . print_r($userData, true));
            return [
                'success' => true,
                'userData' => $userData
            ];
        } else {
            error_log('Profile not found for name_id: ' . $nameId);
            return [
                'success' => false,
                'message' => 'Profile not found'
            ];
        }
    }

    public function updateProfile($nameId, $data) {
        $conn = $this->conn->getConnection();
        try {
            $conn->begin_transaction();

            // Update user_name table
            $nameQuery = "UPDATE user_name SET fname=?, mname=?, lname=? WHERE name_id=?";
            $nameStmt = $conn->prepare($nameQuery);
            $nameStmt->bind_param('sssi', $data['fname'], $data['mname'], $data['lname'], $nameId);
            $nameStmt->execute();

            // Update contact table
            $contactQuery = "UPDATE contact SET email=?, contact=? WHERE name_id=?";
            $contactStmt = $conn->prepare($contactQuery);
            $contactStmt->bind_param('ssi', $data['email'], $data['contact'], $nameId);
            $contactStmt->execute();

            // Update personal table
            $personalQuery = "UPDATE personal SET birthday=?, sex=?, civilstat=?, address=? WHERE name_id=?";
            $personalStmt = $conn->prepare($personalQuery);
            $personalStmt->bind_param('ssssi', $data['birthday'], $data['sex'], $data['civilstat'], $data['address'], $nameId);
            $personalStmt->execute();

            $conn->commit();
            return ["success" => true, "message" => "Profile updated successfully"];
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public function deleteProfile($nameId) {
        $conn = $this->conn->getConnection();
        try {
            $conn->begin_transaction();
            
            // Delete in reverse order of dependencies
            $tables = ['appointments', 'health', 'contact', 'address', 'employment', 'user_auth', 'personal', 'user_name'];
            
            foreach ($tables as $table) {
                $sql = "DELETE FROM $table WHERE name_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $nameId);
                $stmt->execute();
            }
            
            $conn->commit();
            return ["success" => true, "message" => "Profile deleted successfully"];
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    // Closes active database connection and frees resources
    public function close()
    {
        $this->conn->close();
    }

    // Optimized existence check method
    private function checkExists($table, $conditions) {
        $conn = $this->conn->getConnection();
        $where = [];
        $params = [];
        $types = '';
        
        foreach ($conditions as $field => $value) {
            $where[] = "$field = ?";
            $params[] = $value;
            $types .= is_int($value) ? 'i' : 's';
        }
        
        $sql = "SELECT 1 FROM $table WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows() > 0;
    }

    // Optimized user data retrieval
    private function getUserData($userId, $fields = []) {
        $conn = $this->conn->getConnection();
        $selectedFields = empty($fields) ? '*' : implode(', ', $fields);
        
        $sql = "SELECT $selectedFields 
                FROM user_name n
                LEFT JOIN personal p ON n.name_id = p.name_id
                LEFT JOIN contact c ON n.name_id = c.name_id
                LEFT JOIN address a ON n.name_id = a.name_id
                LEFT JOIN employment e ON n.name_id = e.name_id
                LEFT JOIN health h ON n.name_id = h.name_id
                WHERE n.name_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
