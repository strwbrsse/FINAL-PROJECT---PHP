<?php

require_once 'DB_Operations.php';

// DEBUG: Handles user authentication and login verification
class userAuth
{
    // DEBUG: Database operations instance
    private $SQL_Operations;

    // DEBUG: Initialize with database configuration
    public function __construct($config)
    {
        $this->SQL_Operations = new SQL_Operations($config);
    }

    // DEBUG: Authenticate user login attempt
    public function authenticate($email, $password)
    {
        // DEBUG: Get hashed password from database for email
        $hashedPass = $this->SQL_Operations->authenticate($email);

        // DEBUG: Validate email presence
        if (empty($email)) {
            return ["success" => false, "message" => "Email is required"];
        }

        // DEBUG: Verify password if user exists
        if ($hashedPass !== null) {
            // DEBUG: Compare provided password with stored hash
            if (password_verify($password, $hashedPass)) {
                return ["success" => true, "message" => "Access granted"];
            } else {
                return ["success" => false, "message" => "Sign in failed: Incorrect Password"];
            }
        } else {
            return ["success" => false, "message" => "Sign in failed: User not found"];
        }
    }

    // DEBUG: Clean up database connection
    public function close()
    {
        $this->SQL_Operations->close();
    }
}
