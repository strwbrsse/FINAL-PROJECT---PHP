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

    // Retrieves user data for login authentication
    public function authenticate($email)
    {
        $conn = $this->conn->getConnection();
        $sql = "SELECT ua.password, ua.name_id as user_id, n.fname, n.lname, 
                c.email, p.sex, p.civilstat
                FROM User_Auth ua 
                JOIN user_name n ON ua.name_id = n.name_id
                JOIN contact c ON n.name_id = c.name_id 
                JOIN personal p ON n.name_id = p.name_id
                WHERE c.email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    // Verifies if email is already registered in contact table
    public function check_ExistingUser($mail)
    {
        $conn = $this->conn->getConnection();
        $sql = "select * from contact where email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $mail);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows() > 0;
    }

    // Searches for exact name match in user_name table
    public function check_ExistingName($fname, $mname, $lname)
    {
        $conn = $this->conn->getConnection();
        $sql = "SELECT * FROM user_name WHERE fname = ? AND mname = ? AND lname = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $fname, $mname, $lname);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows() > 0;
    }

    // Checks if username is already taken in user_auth table
    public function check_ExistingUsername($Name) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT * FROM user_auth WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $Name);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows() > 0;
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

    // Add these new methods for appointment operations
    public function createAppointment($userId, $vaccineType, $appointmentDate, $appointmentTime) {
        $conn = $this->conn->getConnection();
        
        try {
            $conn->begin_transaction();
            
            $sql = "INSERT INTO appointments (
                user_id,
                vaccine_type,
                appointment_date,
                appointment_time,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, 'scheduled', NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isss', $userId, $vaccineType, $appointmentDate, $appointmentTime);
            $stmt->execute();
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public function getUpcomingAppointments($userId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE user_id = ? 
        AND appointment_date >= CURRENT_DATE
        AND status = 'scheduled'
        ORDER BY appointment_date ASC, appointment_time ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getNextAppointment($userId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE user_id = ? 
        AND appointment_date >= CURRENT_DATE
        AND status = 'scheduled'
        ORDER BY appointment_date ASC, appointment_time ASC
        LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getUpcomingDoses($userId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
            vs.vaccine_name,
            COUNT(v.vaccine_id) as doses_received,
            vs.total_doses,
            CASE 
                WHEN COUNT(v.vaccine_id) < vs.total_doses THEN vs.total_doses - COUNT(v.vaccine_id)
                ELSE 0 
            END as doses_remaining
        FROM vaccine_schedule vs
        LEFT JOIN vaccine v ON v.vaccine_name = vs.vaccine_name AND v.name_id = ?
        GROUP BY vs.vaccine_name, vs.total_doses
        HAVING doses_remaining > 0
        ORDER BY vs.vaccine_name";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateAppointmentStatus($appointmentId, $userId, $status) {
        $conn = $this->conn->getConnection();
        
        try {
            $sql = "UPDATE appointments 
                    SET status = ? 
                    WHERE id = ? AND user_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sii', $status, $appointmentId, $userId);
            $stmt->execute();
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getPastAppointments($userId) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE user_id = ? 
        AND (appointment_date < CURRENT_DATE 
            OR status IN ('completed', 'cancelled'))
        ORDER BY appointment_date DESC, appointment_time DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Closes active database connection and frees resources
    public function close()
    {
        $this->conn->close();
    }
}
