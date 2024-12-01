<?php
class filters
{
    const minName = 2;
    const maxName = 50;
    const gen_rgx = '/^[a-zA-ZÀ-ÿ\s\'-\.,&()\/]+$/';
    const whiteSpc_rgx = '/^\s+$/';
    private $errors = [];
    private $collectedErrors = [];

    private function addToCollectedErrors()
    {
        if (!empty($this->errors)) {
            $this->collectedErrors = array_merge($this->collectedErrors, $this->errors);
        }
    }

    public function isValidName($fname, $mname, $lname)
    {
        $this->errors = [];  // Clear current errors

        // First check if fields are empty or only whitespace
        if (trim($fname) === '') {
            $this->errors[] = ["field" => "fname", "message" => "First name is required"];
        } else {
            // Only check length and pattern if not empty
            if (strlen($fname) > self::maxName) {
                $this->errors[] = ["field" => "fname", "message" => "First name is too long."];
            } elseif (strlen($fname) < self::minName) {
                $this->errors[] = ["field" => "fname", "message" => "First name is too short."];
            }
            if (!preg_match(self::gen_rgx, $fname)) {
                $this->errors[] = ["field" => "fname", "message" => "Invalid input in First name."];
            }
        }

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

    public function isValidDoB($dob)
    {
        $this->errors = [];  // Clear current errors

        if (trim($dob) === '') {
            $this->errors[] = ["field" => "birthday", "message" => "Date of birth is required"];
        } else {
            $date = date_create($dob);
            if (!$date) {
                $this->errors[] = ["field" => "birthday", "message" => "Invalid date format."];
            } else {
                $today = new DateTime();
                if ($date > $today) {
                    $this->errors[] = ["field" => "birthday", "message" => "Date of birth cannot be in the future."];
                }
            }
        }

        $this->addToCollectedErrors();  // Add current errors to collection
        return empty($this->errors);    // Return boolean for flow control
    }

    public function isValidEmail($email)
    {
        $this->errors = [];  // Clear current errors

        if (trim($email) === '') {
            $this->errors[] = ["field" => "email", "message" => "Email is required"];
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = ["field" => "email", "message" => "Invalid email address"];
        }

        $this->addToCollectedErrors();  // Add current errors to collection
        return empty($this->errors);    // Return boolean for flow control
    }

    public function isValidContact($contact)
    {
        $this->errors = [];  // Clear current errors

        if (trim($contact) === '') {
            $this->errors[] = ["field" => "contact", "message" => "Contact number is required"];
        } elseif (!preg_match('/^[\+]?[0-9]{11,15}$/', $contact)) {
            $this->errors[] = ["field" => "contact", "message" => "Invalid contact number"];
        }

        $this->addToCollectedErrors();  // Add current errors to collection
        return empty($this->errors);    // Return boolean for flow control
    }

    public function isValidSex($sex)
    {
        $this->errors = [];

        if (trim($sex) === '') {
            $this->errors[] = ["field" => "sex", "message" => "Sex is required."];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    public function isValidCivStat($civStat)
    {
        $this->errors = [];

        if (trim($civStat) === '') {
            $this->errors[] = ["field" => "civilstat", "message" => "Civil status is required."];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

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

    public function isValidEmpStat($empStat) {
        $this->errors = [];

        if (trim($empStat) === '') {
            $this->errors[] = ["field" => "employmentstat", "message" => "Employment status is required"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

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

    public function isValidAllergies($allergies, $allergyCheck = 'no') {
        $this->errors = [];
        
        // Only validate if allergyCheck is 'yes'
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

    public function isValidUsername($Name) {
        $this->errors = [];

        if (trim($Name) === '') {
            $this->errors[] = ["field" => "username", "message" => "Username is required"];
        } elseif (strlen($Name) < 2) {
            $this->errors[] = ["field" => "username", "message" => "Username is too short"];
        } elseif (strlen($Name) > 50) {
            $this->errors[] = ["field" => "username", "message" => "Username is too long"];
        } elseif (!preg_match('/^[a-zA-Z0-9_-]{2,50}$/', $Name)) {
            $this->errors[] = ["field" => "username", "message" => "Invalid input in Username"];
        } elseif (!preg_match('/[a-zA-Z]/', $Name)) {
            $this->errors[] = ["field" => "username", "message" => "Username must contain at least one letter"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    public function isValidPassword($Pass, $ConPass) {
        $this->errors = [];

        if (trim($Pass) === '') {
            $this->errors[] = ["field" => "password", "message" => "Password is required"];
        } elseif (strlen($Pass) < 8) {
            $this->errors[] = ["field" => "password", "message" => "Password is too short"];
        } elseif (strlen($Pass) > 30) {
            $this->errors[] = ["field" => "password", "message" => "Password is too long"];
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/', $Pass)) {
            $this->errors[] = ["field" => "password", "message" => "Password must contain at least 8 characters, A-Z, a-z, 0-9, and @$!%*?&#"];
        }elseif (trim($ConPass) === '') {
            $this->errors[] = ["field" => "confirmPassword", "message" => "Confirm password is required"];
        } elseif ($Pass !== $ConPass) { 
            $this->errors[] = ["field" => "confirmPassword", "message" => "Passwords do not match"];
        }

        $this->addToCollectedErrors();
        return empty($this->errors);
    }

    public function getErrors()
    {
        return empty($this->collectedErrors)
            ? ["success" => true]
            : ["success" => false, "errors" => $this->collectedErrors];
    }

    public function clearAllErrors()
    {
        $this->errors = [];
        $this->collectedErrors = [];
    }
}