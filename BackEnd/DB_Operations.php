<?php

require_once 'DB_Connect.php';

class SQL_Operations
{
    private $conn;

    public function __construct($config)
    {
        $this->conn = new DbConn($config);
    }

    public function authenticate($email)
    {
        $conn = $this->conn->getConnection();
        $sql = "SELECT ua.password 
        FROM User_Auth ua 
        JOIN personal p ON ua.user_id = p.personal_id 
        JOIN contact c ON p.name_id = c.name_id 
        WHERE c.email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows() > 0) {
            $stmt->bind_result($hashedPass);
            $stmt->fetch();
            return $hashedPass; // Return the hashed password

        } else {
            return null; // No user found with that email
        }
    }

    public function check_ExistingUser($mail)
    {
        $conn = $this->conn->getConnection();
        $sql = "select * from contact where email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $mail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows() > 0) {
            return true; // User already exists
        } else {
            return false; // User does not exist
        }
    }

    public function check_ExistingName($fname, $mname, $lname)
    {
        $conn = $this->conn->getConnection();
        $sql = "SELECT * FROM user_name WHERE fname = ? AND mname = ? AND lname = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $fname, $mname, $lname);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows() > 0) {
            return true; // User already exists
        } else {
            return false; // User does not exist
        }
    }

    public function check_ExistingUsername($Name) {
        $conn = $this->conn->getConnection();
        $sql = "SELECT * FROM user_auth WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $Name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows() > 0) {
            return true; // User already exists
        } else {
            return false; // User does not exist
        }
    }

    public function registerUser(
        $fname, $mname, $lname, $dob, $mail, $num, $sex, 
        $civstat, $nationality, $empstat, $empl, $profession, 
        $address, $barangay, $allergies, $diseases
    ) {
        // Store the data in class properties or return as array instead of inserting
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

    public function signUp($username, $password, $userData)
    {
        $conn = $this->conn->getConnection();
        $conn->begin_transaction();

        try {
            // First insert into user_name table
            $stmt = $conn->prepare("INSERT INTO user_name (fname, mname, lname) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $userData['fname'], $userData['mname'], $userData['lname']);
            $stmt->execute();
            $name_id = $conn->insert_id;
            $stmt->close();

            // Then insert into personal table with name_id
            $stmt = $conn->prepare("INSERT INTO personal (name_id, sex, civilstat, birthday, nationality) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $name_id, $userData['sex'], $userData['civstat'], $userData['dob'], $userData['nationality']);
            $stmt->execute();
            $personal_id = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO address (name_id, address, barangay) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $userData['address'], $userData['barangay']);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO contact (name_id, contact, email) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $userData['num'], $userData['mail']);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO employment (name_id, employment_stat, employer, profession) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $name_id, $userData['empstat'], $userData['empl'], $userData['profession']);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO Health (user_id, allergy_description, disease_description) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $personal_id, $userData['allergies'], $userData['diseases']);
            $stmt->execute();
            $stmt->close();

            // Insert into user_auth table
            $stmt = $conn->prepare("INSERT INTO user_auth (user_id, username, password) VALUES (?, ?, ?)");
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param('iss', $personal_id, $username, $hashedPass);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public function close()
    {
        $this->conn->close();
    }
}
