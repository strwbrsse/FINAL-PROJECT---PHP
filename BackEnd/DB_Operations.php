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
                JOIN Personal_Info pi ON ua.user_id = pi.personal_id 
                WHERE pi.mail = ?";

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

    public function close()
    {
        $this->conn->close();
    }

    public function register_PersonalInfo(
        $fname,
        $mname,
        $lname,
        $birthday,
        $mail,
        $contact,
        $sex,
        $civStat,
        $nationality,
        $empStat,
        $empl,
        $profession,
        $address,
        $barangay,
        $allergies,
        $dieseases
    ) {
        $conn = $this->conn->getConnection();
        $sql = "INSERT INTO personal_info 
                (fname, mname, lname, birthday,
                mail, contact, sex, civilstat, 
                nationality, employmentstat,
                employer, profession, address,
                barangay) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssssssssss',
            $fname,
            $mname,
            $lname,
            $birthday,
            $mail,
            $contact,
            $sex,
            $civStat,
            $nationality,
            $empStat,
            $empl,
            $profession,
            $address,
            $barangay
        );
        $stmt->execute();

        if ($stmt->execute()) {
            $insertedID = $stmt->insert_id;
            $stmt->close();
            return $insertedID;
        } else {
            $stmt->close();
            throw new Exception("Execution failed" . $stmt->error);
        }
    }
}
