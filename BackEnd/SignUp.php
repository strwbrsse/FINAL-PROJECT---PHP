<?php 

require_once 'DB_Operations.php';
require_once 'Filter.php';

class UserSignUp
{
    private $SQL_Operations;
    private $filters;

    public function __construct($config)
    {
        $this->SQL_Operations = new SQL_Operations($config);
        $this->filters = new Filters();
    }

    public function signUp($Name, $Pass, $ConPass)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->filters->clearAllErrors();

        $this->filters->isValidUsername($Name);

        if ($this->SQL_Operations->check_ExistingUsername($Name)) {
            return [
                "success" => false,
                "errors" => [
                    ["field" => "Username", "message" => "Username already exists"]
                ]
            ];
        }

        $this->filters->isValidPassword($Pass, $ConPass);

        $validationResult = $this->filters->getErrors();
        
        if (!$validationResult['success']) {
            return $validationResult;
        }

        

        try { 
            $result = $this->SQL_Operations->signUp($Name, $Pass, $_SESSION['userData']);
            
            if ($result) {
                unset($_SESSION['userData']);
                return ["success" => true, "message" => "Account created successfully!", "redirect" => "../FrontEnd/dashboard.html"
                ];
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
    public function close()
    {
        $this->SQL_Operations->close();
    }
}

?>