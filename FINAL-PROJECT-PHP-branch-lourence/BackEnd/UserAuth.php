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
                $_SESSION['user_id'] = $userData['user_id'];
                $_SESSION['user_name'] = $userData['fname'] . ' ' . $userData['lname'];
                
                return [
                    "success" => true, 
                    "message" => "Access granted",
                    "redirect" => "dashboard.html",
                    "userData" => [
                        "userId" => $userData['user_id'],
                        "userName" => $userData['fname'] . ' ' . $userData['lname']
                    ]
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
}
