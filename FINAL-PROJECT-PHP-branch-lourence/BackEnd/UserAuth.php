<?php

require_once 'DB_Operations.php';

// Handles user authentication and login verification
class userAuth
{
    private $SQL_Operations;

    // Initialize with database configuration
    public function __construct($config)
    {
        $this->SQL_Operations = new SQL_Operations($config);
    }

    // Authenticate user login attempt
    public function authenticate($email, $password)
    {
        $userData = $this->SQL_Operations->authenticate($email);

        if (empty($email)) {
            return ["success" => false, "message" => "Email is required"];
        }

        if ($userData !== null) {
            if (password_verify($password, $userData['password'])) {
                session_start();
                $_SESSION['name_id'] = $userData['user_id'];
                
                return [
                    "success" => true, 
                    "message" => "Access granted",
                    "redirect" => "../FrontEnd/dashboard.html",
                    "name_id" => $userData['user_id']
                ];
            } else {
                return ["success" => false, "message" => "Sign in failed: Incorrect Password"];
            }
        } else {
            return ["success" => false, "message" => "Sign in failed: User not found"];
        }
    }

    // Clean up database connection
    public function close()
    {
        $this->SQL_Operations->close();
    }

    public function updateProfile($userId, $data) {
        try {
            $mysqli = $this->SQL_Operations->getConnection();
            
            // Add validation here as needed 
            $query = "UPDATE users SET 
                      fname = ?, 
                      mname = ?,
                      lname = ?,
                      email = ?,
                      contact = ?,
                      birthday = ?,
                      sex = ?,
                      civilstat = ?,
                      address = ?
                      WHERE user_id = ?";
                      
            $stmt = mysqli_prepare($mysqli, $query);
            mysqli_stmt_bind_param($stmt, "sssssssssi", 
                $data['fname'],
                $data['mname'],
                $data['lname'],
                $data['email'],
                $data['contact'],
                $data['birthday'],
                $data['sex'],
                $data['civilstat'],
                $data['address'],
                $userId
            );
            
            if (mysqli_stmt_execute($stmt)) {
                return ["success" => true, "message" => "Profile updated successfully"];
            }
            
            return ["success" => false, "message" => "Failed to update profile"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

    public function deleteProfile($userId) {
        try {
            $mysqli = $this->SQL_Operations->getConnection();
            
            // Start transaction
            mysqli_begin_transaction($mysqli);
            
            // Delete related records first (appointments, etc.)
            $queries = [
                "DELETE FROM appointments WHERE user_id = ?",
                "DELETE FROM user_vaccines WHERE user_id = ?",
                "DELETE FROM users WHERE user_id = ?"
            ];
            
            foreach ($queries as $query) {
                $stmt = mysqli_prepare($mysqli, $query);
                mysqli_stmt_bind_param($stmt, "i", $userId);
                if (!mysqli_stmt_execute($stmt)) {
                    mysqli_rollback($mysqli);
                    return ["success" => false, "message" => "Failed to delete profile"];
                }
            }
            
            mysqli_commit($mysqli);
            return ["success" => true, "message" => "Profile deleted successfully"];
        } catch (Exception $e) {
            mysqli_rollback($mysqli);
            return ["success" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

    public function getProfile($userId) {
        try {
            $mysqli = $this->SQL_Operations->getConnection();
            
            $query = "SELECT user_id, fname, mname, lname, email, contact, birthday, sex, civilstat, address 
                      FROM users 
                      WHERE user_id = ?";
            
            $stmt = mysqli_prepare($mysqli, $query);
            mysqli_stmt_bind_param($stmt, "i", $userId);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $userData = mysqli_fetch_assoc($result);
                
                if ($userData) {
                    return [
                        "success" => true,
                        "userData" => [
                            "user_id" => $userData['user_id'],
                            "fname" => $userData['fname'],
                            "mname" => $userData['mname'],
                            "lname" => $userData['lname'],
                            "email" => $userData['email'],
                            "contact" => $userData['contact'],
                            "birthday" => $userData['birthday'],
                            "sex" => $userData['sex'],
                            "civilstat" => $userData['civilstat'],
                            "address" => $userData['address']
                        ]
                    ];
                }
            }
            
            return ["success" => false, "message" => "Failed to fetch profile data"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Error: " . $e->getMessage()];
        }
    }
}
