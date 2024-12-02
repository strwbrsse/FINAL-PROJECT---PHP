<?php 

require_once 'DB_Operations.php';
require_once 'Filter.php';

// DEBUG: Handles user account creation after registration
class UserSignUp
{
    private $SQL_Operations;
    private $filters;

    // DEBUG: Initialize database and validation services
    public function __construct($config)
    {
        $this->SQL_Operations = new SQL_Operations($config);
        $this->filters = new Filters();
    }

    // DEBUG: Process signup with username and password validation
    public function signUp($Name, $Pass, $ConPass)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->filters->clearAllErrors();

        // DEBUG: Validate username and check for duplicates
        $this->filters->isValidUsername($Name);
        if ($this->SQL_Operations->check_ExistingUsername($Name)) {
            return [
                "success" => false,
                "errors" => [
                    ["field" => "Username", "message" => "Username already exists"]
                ]
            ];
        }

        // DEBUG: Validate password requirements
        $this->filters->isValidPassword($Pass, $ConPass);
        $validationResult = $this->filters->getErrors();
        
        if (!$validationResult['success']) {
            return $validationResult;
        }

        // DEBUG: Create user account with validated credentials
        try { 
            $result = $this->SQL_Operations->signUp($Name, $Pass, $_SESSION['userData']);
            
            if ($result) {
                unset($_SESSION['userData']);
                return ["success" => true, "message" => "Account created successfully!", "redirect" => "../FrontEnd/dashboard.html"];
            } else {
                return [
                    "success" => false, 
                    "errors" => [
                        ["field" => "general", "message" => "Failed to create account. Please try again."]
                    ]
                ];
            }
        } catch (Exception $e) {
            return [
                "success" => false, 
                "errors" => [
                    ["field" => "general", "message" => "Database error: " . $e->getMessage()]
                ]
            ];
        }
    }

    // DEBUG: Clean up database connection
    public function close()
    {
        $this->SQL_Operations->close();
    }
}

?>