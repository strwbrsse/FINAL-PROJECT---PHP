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
        $sql = "select * from User_Auth where mail = ?";
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

    public function registerUser(
        $fname,
        $mname,
        $lname,
        $dob,
        $mail,
        $num,
        $sex,
        $civstat,
        $nationality,
        $empstat,
        $empl,
        $profession,
        $address,
        $barangay,
        $allergies,
        $diseases
    ) {
        $conn = $this->conn->getConnection();
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO user_name (fname, mname, lname) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $fname, $mname, $lname);
            $stmt->execute();
            $name_id = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO personal (name_id, sex, civilstat, birthday, nationality) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $name_id, $sex, $civstat, $dob, $nationality);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO address (name_id, address, barangay) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $address, $barangay);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO contact (name_id, contact, email) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $num, $mail);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO employment (name_id, employment_stat, employer, profession) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $name_id, $empstat, $empl, $profession);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO health (name_id, allergies, diseases) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $name_id, $allergies, $diseases);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public function signUp($username, $password)
    {
        $conn = $this->conn->getConnection();
        $stmt = $conn->prepare("INSERT INTO user_auth (name_id, username, password) VALUES (?, ?, ?)");
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param('iss', $name_id, $username, $hashedPass);
        $stmt->execute();
        $stmt->close();
    }

    public function close()
    {
        $this->conn->close();
    }
}
