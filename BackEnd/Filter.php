<?php
class filters
{
    // Validation constants for name fields
    private const minName = 2;
    private const maxName = 50;
    // Regex for general text validation (letters, accented chars, spaces, basic punctuation)
    private const gen_rgx = '/^[a-zA-ZÀ-ÿ\s\'-\.,&()\/]+$/';
    // Arrays to track current validation errors and accumulated errors
    private $errors = [];
    private $collectedErrors = [];
    // Password requirements: 8-30 chars, uppercase, lowercase, number, special char
    private const PASSWORD_PATTERN = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,30}$/';
    // Username requirements: start with letter, alphanumeric with underscore/hyphen, 2-50 chars
    private const USERNAME_PATTERN = '/^[a-zA-Z][a-zA-Z0-9_-]{1,49}$/';

    // Merges current validation errors into collected errors array
    private function addToCollectedErrors()
    {
        if (!empty($this->errors)) {
            $this->collectedErrors = array_merge($this->collectedErrors, $this->errors);
        }
    }

    // DEBUG: Validates first, middle, and last name fields
    public function isValidName($fname, $mname, $lname)
    {
        $this->errors = [];  // Reset error array for new validation

        // DEBUG: First name validation
        if (trim($fname) === '') {
            $this->errors[] = ["field" => "fname", "message" => "First name is required"];
        } else {
            // DEBUG: Check fname length and pattern after trimming
            if (strlen($fname) > self::maxName) {
                $this->errors[] = ["field" => "fname", "message" => "First name is too long."];
            } elseif (strlen($fname) < self::minName) {
                $this->errors[] = ["field" => "fname", "message" => "First name is too short."];
            }
            if (!preg_match(self::gen_rgx, $fname)) {
                $this->errors[] = ["field" => "fname", "message" => "Invalid input in First name."];
            }
        }

        // DEBUG: Middle name validation (no minimum length requirement)
        if (trim($mname) === '') {
            $this->errors[] = ["field" => "mname", "message" => "Middle name is required"];
        } else {
            if (strlen($mname) > self::maxName) {
                $this->errors[] = ["field" => "mname", "message" => "Middle name is too long."];
            }
            if (!preg_match(self::gen_rgx, $mname)) {
                $this->errors[] = ["field" => "mname", "message" => "Invalid input in Middle name."];
            }
        }

        // DEBUG: Last name validation
        if (trim($lname) === '') {
            $this->errors[] = ["field" => "lname", "message" => "Last name is required"];
        } else {
            if (strlen($lname) > self::maxName) {
                $this->errors[] = ["field" => "lname", "message" => "Last name is too long."];
            } elseif (strlen($lname) < self::minName) {
                $this->errors[] = ["field" => "lname", "message" => "Last name is too short."];
            }
            if (!preg_match(self::gen_rgx, $lname)) {
                $this->errors[] = ["field" => "lname", "message" => "Invalid input in Last name."];
            }
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates date of birth (must be in past)
    public function isValidDoB($dob)
    {
        $this->errors = [];

        if (trim($dob) === '') {
            $this->errors[] = ["field" => "birthday", "message" => "Date of birth is required"];
        } else {
            // DEBUG: Attempt to create DateTime object from input
            $date = date_create($dob);
            if (!$date) {
                $this->errors[] = ["field" => "birthday", "message" => "Invalid date format."];
            } else {
                // DEBUG: Ensure date is not in future
                $today = new DateTime();
                if ($date > $today) {
                    $this->errors[] = ["field" => "birthday", "message" => "Date of birth cannot be in future."];
                }
            }
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates email format using PHP's built-in filter
    public function isValidEmail($email)
    {
        $this->errors = [];

        if (trim($email) === '') {
            $this->errors[] = ["field" => "email", "message" => "Email is required"];
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = ["field" => "email", "message" => "Invalid email address"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates contact number (11-15 digits, optional + prefix)
    public function isValidContact($contact)
    {
        $this->errors = [];

        if (trim($contact) === '') {
            $this->errors[] = ["field" => "contact", "message" => "Contact number is required"];
        } elseif (strlen($contact) < 11) {
            $this->errors[] = ["field" => "contact", "message" => "Contact number is too short"];
        } elseif (strlen($contact) > 15) {
            $this->errors[] = ["field" => "contact", "message" => "Contact number is too long"];
        } elseif (!preg_match('/^[\+]?[0-9]{11,15}$/', $contact)) {
            $this->errors[] = ["field" => "contact", "message" => "Invalid contact number"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates sex field (must not be empty)
    public function isValidSex($sex)
    {
        $this->errors = [];

        if (trim($sex) === '') {
            $this->errors[] = ["field" => "sex", "message" => "Sex is required."];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates civil status (must not be empty)
    public function isValidCivStat($civStat)
    {
        $this->errors = [];

        if (trim($civStat) === '') {
            $this->errors[] = ["field" => "civilstat", "message" => "Civil status is required."];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates nationality (2-50 chars, matches general regex)
    public function isValidNationality($nationality) {
        $this->errors = [];

        if (trim($nationality) === '') {
            $this->errors[] = ["field" => "nationality", "message" => "Nationality is required"];
        } elseif(strlen($nationality) < 2) {
            $this->errors[] = ["field" => "nationality", "message" => "Nationality is too short"];
        } elseif (strlen($nationality) > 50) {
            $this->errors[] = ["field" => "nationality", "message" => "Nationality is too long"];
        } elseif (!preg_match(self::gen_rgx, $nationality)) {
            $this->errors[] = ["field" => "nationality", "message" => "Invalid input in Nationality"];
        }
        
        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates employment status (must not be empty)
    public function isValidEmpStat($empStat) {
        $this->errors = [];

        if (trim($empStat) === '') {
            $this->errors[] = ["field" => "employmentstat", "message" => "Employment status is required"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates employer name (2-50 chars, matches general regex)
    public function isValidEmployer($empl) {
        $this->errors = [];

        if (trim($empl) === '') {
            $this->errors[] = ["field" => "employer", "message" => "Employer is required"];
        } elseif(strlen($empl) < 2) {
            $this->errors[] = ["field" => "employer", "message" => "Employer is too short"];
        } elseif (strlen($empl) > 50) {
            $this->errors[] = ["field" => "employer", "message" => "Employer is too long"];
        } elseif (!preg_match(self::gen_rgx, $empl)) {
            $this->errors[] = ["field" => "employer", "message" => "Invalid input in Employer"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates profession (2-100 chars, matches general regex)
    public function isValidProfession($profession) {
        $this->errors = [];

        if (trim($profession) === '') {
            $this->errors[] = ["field" => "profession", "message" => "Profession is required"];
        } elseif(strlen($profession) < 2) {
            $this->errors[] = ["field" => "profession", "message" => "Profession is too short"];
        } elseif (strlen($profession) > 100) {
            $this->errors[] = ["field" => "profession", "message" => "Profession is too long"];
        } elseif (!preg_match(self::gen_rgx, $profession)) {
            $this->errors[] = ["field" => "profession", "message" => "Invalid input in Profession"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates address (2-150 chars, matches general regex)
    public function isValidAddress($address) {
        $this->errors = [];

        if (trim($address) === '') {
            $this->errors[] = ["field" => "address", "message" => "Address is required"];
        } elseif(strlen($address) < 2) {
            $this->errors[] = ["field" => "address", "message" => "Address is too short"];
        } elseif (strlen($address) > 150) {
            $this->errors[] = ["field" => "address", "message" => "Address is too long"];
        } elseif (!preg_match(self::gen_rgx, $address)) {
            $this->errors[] = ["field" => "address", "message" => "Invalid input in Address"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates barangay (2-50 chars, matches general regex)
    public function isValidBarangay($barangay) {
        $this->errors = [];

        if (trim($barangay) === '') {
            $this->errors[] = ["field" => "barangay", "message" => "Barangay is required"];
        } elseif(strlen($barangay) < 2) {
            $this->errors[] = ["field" => "barangay", "message" => "Barangay is too short"];
        } elseif (strlen($barangay) > 50) {
            $this->errors[] = ["field" => "barangay", "message" => "Barangay is too long"];
        } elseif (!preg_match(self::gen_rgx, $barangay)) {
            $this->errors[] = ["field" => "barangay", "message" => "Invalid input in Barangay"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates allergies (only if allergyCheck is 'yes')
    public function isValidAllergies($allergies, $allergyCheck = 'no') {
        $this->errors = [];
        
        if ($allergyCheck === 'yes') {
            if (trim($allergies) === '') {
                $this->errors[] = ["field" => "allergy", "message" => "Please list your allergies"];
            } elseif (!preg_match(self::gen_rgx, $allergies)) {
                $this->errors[] = ["field" => "allergy", "message" => "Invalid input in Allergies"];
            }
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates diseases (only if diseaseCheck is 'yes')
    public function isValidDiseases($diseases, $diseaseCheck = 'no') {
        $this->errors = [];

        if ($diseaseCheck === 'yes') {
            if (trim($diseases) === '') {
                $this->errors[] = ["field" => "disease", "message" => "Please list your diseases"];
            } elseif (!preg_match(self::gen_rgx, $diseases)) {
                $this->errors[] = ["field" => "disease", "message" => "Invalid input in Diseases"];
            }
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates username (2-50 chars, must start with letter, alphanumeric with _ -)
    public function isValidUsername($Name) {
        $this->errors = [];

        if (trim($Name) === '') {
            $this->errors[] = ["field" => "username", "message" => "Username is required"];
        } elseif (strlen($Name) < 2) {
            $this->errors[] = ["field" => "username", "message" => "Username is too short"];
        } elseif (strlen($Name) > 50) {
            $this->errors[] = ["field" => "username", "message" => "Username is too long"];
        } elseif (!preg_match(self::USERNAME_PATTERN, $Name)) {
            $this->errors[] = ["field" => "username", "message" => "Invalid input in Username"];
        } elseif (!preg_match('/[a-zA-Z]/', $Name)) {
            $this->errors[] = ["field" => "username", "message" => "Username must contain at least one letter"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Validates password and confirmation (8-30 chars, complex requirements)
    public function isValidPassword($Pass, $ConPass) {
        $this->errors = [];

        if (trim($Pass) === '') {
            $this->errors[] = ["field" => "password", "message" => "Password is required"];
        } elseif (strlen($Pass) < 8) {
            $this->errors[] = ["field" => "password", "message" => "Password is too short"];
        } elseif (strlen($Pass) > 30) {
            $this->errors[] = ["field" => "password", "message" => "Password cannot exceed 30 characters"];
        } elseif (!preg_match(self::PASSWORD_PATTERN, $Pass)) {
            $this->errors[] = ["field" => "password", "message" => "Password must include A-Z, a-z, 0-9, and @$!%*?&#"];
        } elseif (trim($ConPass) === '') {
            $this->errors[] = ["field" => "confirmPassword", "message" => "Please confirm your password"];
        } elseif ($Pass !== $ConPass) {
            $this->errors[] = ["field" => "confirmPassword", "message" => "Passwords do not match"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    // DEBUG: Returns all collected errors or success status
    public function getErrors()
    {
        return empty($this->collectedErrors)
            ? ["success" => true]
            : ["success" => false, "errors" => $this->collectedErrors];
    }

    // DEBUG: Resets all error arrays
    public function clearAllErrors()
    {
        $this->errors = [];
        $this->collectedErrors = [];
    }
}