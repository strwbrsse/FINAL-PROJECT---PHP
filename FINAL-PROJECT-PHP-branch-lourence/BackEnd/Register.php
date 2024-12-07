<?php

require_once 'DB_Operations.php';
require_once 'Filter.php';

// Handles user registration process and validation
class UserReg
{
    private $SQL_Operations;
    private $filters;

    // Initialize database and validation services
    public function __construct($config)
    {
        $this->SQL_Operations = new SQL_Operations($config);
        $this->filters = new Filters();
    }

    // Validates and processes personal information registration
    public function register_PersonalInfo(
        $fname, $mname, $lname, $birthday, $mail, $contact,
        $sex, $civStat, $nationality, $empStat, $empl, $profession,
        $address, $barangay, $allergies, $diseases, $allergyCheck, $diseaseCheck
    ) {
        $this->filters->clearAllErrors();

        // Validate all user input fields
        $this->filters->isValidName($fname, $mname, $lname);
        $this->filters->isValidDoB($birthday);
        $this->filters->isValidEmail($mail);
        $this->filters->isValidContact($contact);
        $this->filters->isValidSex($sex);
        $this->filters->isValidCivStat($civStat);
        $this->filters->isValidNationality($nationality);
        $this->filters->isValidEmpStat($empStat);
        $this->filters->isValidEmployer($empl);
        $this->filters->isValidProfession($profession);
        $this->filters->isValidAddress($address);
        $this->filters->isValidBarangay($barangay);
        $this->filters->isValidAllergies($allergies, $allergyCheck);
        $this->filters->isValidDiseases($diseases, $diseaseCheck);

        $validationResult = $this->filters->getErrors();
        
        if (!$validationResult['success']) {
            return $validationResult;
        }

        // Check for duplicate user entries
        if ($this->SQL_Operations->check_ExistingUser($mail)) {
            return ["success" => false, "errors" => [
                ["field" => "Email", "message" => "Email already registered"]
            ]];
        }

        if ($this->SQL_Operations->check_ExistingName($fname, $mname, $lname)) {
            return ["success" => false, "errors" => [
                ["field" => "fname", "message" => "First name already registered"],
                ["field" => "mname", "message" => "Middle name already registered"],
                ["field" => "lname", "message" => "Last name already registered"]
            ]];
        }

        // Store validated user data and prepare for signup
        $userData = $this->SQL_Operations->registerUser(
            $fname, $mname, $lname, $birthday, $mail, $contact,
            $sex, $civStat, $nationality, $empStat, $empl, $profession,
            $address, $barangay, 
            $allergyCheck === 'yes' ? $allergies : null,
            $diseaseCheck === 'yes' ? $diseases : null
        );

        session_start();
        $_SESSION['userData'] = $userData;

        return [
            "success" => true, 
            "message" => "Registration successful", 
            "redirect" => "../FrontEnd/SignUp.html"
        ];
    }

    // Clean up database connection
    public function close()
    {
        $this->SQL_Operations->close();
    }
}
