<?php
class filters
{
    const minName = 2;
    const maxName = 50;
    const gen_rgx = '/^[a-zA-Z\s]+$/';
    const whiteSpc_rgx = '/^[\s]+$/';

    public function isValidName($fname, $mname, $lname)
    {
        $errors = [];
        
        // Check for whitespace-only names
        if (preg_match(self::whiteSpc_rgx, $fname) || preg_match(self::whiteSpc_rgx, $lname)) {
            $errors[] = "Name cannot be empty";
        }
        
        // Check for letters only
        if (!preg_match(self::gen_rgx, $fname)) {
            $errors[] = "Only letters are allowed in First Name";
        }
        if (!preg_match(self::gen_rgx, $mname)) {
            $errors[] = "Only letters are allowed in Middle Name";
        }
        if (!preg_match(self::gen_rgx, $lname)) {
            $errors[] = "Only letters are allowed in Last Name";
        }

        // Check name lengths
        if (strlen($fname) < self::minName) {
            $errors[] = "First name is too short";
        } elseif (strlen($fname) > self::maxName) {
            $errors[] = "First name is too long";
        }

        if (strlen($mname) > self::maxName) {  // Fixed the middle name condition
            $errors[] = "Middle name is too long";
        }

        if (strlen($lname) < self::minName) {
            $errors[] = "Last name is too short";
        } elseif (strlen($lname) > self::maxName) {
            $errors[] = "Last name is too long";
        }

        // Return results
        if (!empty($errors)) {
            $message = (count($errors) === 2) 
                ? implode(" & ", $errors) 
                : implode(", ", $errors);
            return ["success" => false, "message" => $message];
        }
    }

    public function isValidMail($mail)
    {
        if (preg_match(self::whiteSpc_rgx, $mail)) {
            return ["success" => false, "message" => "Email cannot be empty"];
        }
        
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $mail)) {
            return ["success" => false, "message" => "Invalid Email format"];
        }
    }

    public function isValidPhone($num)
    {
        if (preg_match(self::whiteSpc_rgx, $num)) {
            return ["success" => false, "message" => "Phone number cannot be empty"];
        }

        if (!preg_match('/^\+?[\d]+$/', $num)) {
            return ["success" => false, "message" => "Invalid Contact number format"];
        }
    }

    public function isValidNationality($nationality)
    {
        if (preg_match(self::whiteSpc_rgx, $nationality)) {
            return ["success" => false, "message" => "Nationality cannot be empty"];
        }

        if (!preg_match(self::gen_rgx, $nationality)) {
            return ["success" => false, "message" => "Only letters are allowed in Nationality field"];
        }
    }

    public function isValidEmployer($empl)
    {
        if (preg_match(self::whiteSpc_rgx, $empl)) {
            return ["success" => false, "message" => "Employer cannot be empty"];
        }

        if (!preg_match(self::gen_rgx, $empl)) {
            return ["success" => false, "message" => "Only letters are allowed in Employer field"];
        }
    }

    public function isValidProfession($profession)
    {
        if (preg_match(self::whiteSpc_rgx, $profession)) {
            return ["success" => false, "message" => "Profession cannot be empty"];
        }

        if (!preg_match(self::gen_rgx, $profession)) {
            return ["success" => false, "message" => "Only letters are allowed in Profession field"];
        }
    }

    public function isValidAddress($address, $barangay)
    {
        if (preg_match(self::whiteSpc_rgx, $address)) {
            return ["success" => false, "message" => "Address cannot be empty"];
        }
        if (!preg_match('/^[a-zA-Z0-9.,-/\'&#@:\s]+$/', $address)) {
            return ["success" => false, "message" => "Invalid Address format"];
        }

        if (!preg_match('/^[a-zA-Z0-9.,-/\'&#@:\s]+$/', $barangay)) {
            return ["success" => false, "message" => "Invalid Barangay format"];
        }
    }

    public function isValidHealth($allergies, $diseases)
    {
        if (preg_match(self::whiteSpc_rgx, $allergies)) {
            return ["success" => false, "message" => "Allergies cannot consist only of whitespace."];
        }
        if (!preg_match('/^[a-zA-Z0-9,.&/-\'\s]+$/', $allergies)) {
            return ["success" => false, "message" => "Invalid Allergies format"];
        }

        if (preg_match(self::whiteSpc_rgx, $diseases)) {
            return ["success" => false, "message" => "Diseases cannot consists only of whitespaces"];
        }

        if (!preg_match('/^[a-zA-Z0-9,.&/-\'\s]+$/', $diseases)) {
            return ["success" => false, "message" => "Invalid Diseases format"];
        }
    }

    public function isValidFullName($Name)
    {
        if (preg_match(self::whiteSpc_rgx, $Name)) {
            return ["success" => false, "message" => "Name cannot be empty"];
        }

        if (!preg_match(self::gen_rgx, $Name)) {
            return ["success" => false, "message" => "Only letters are allowed in the Name field"];
        }

        if (strlen($Name) < self::minName){
            return ["success" => false, "message" => "Name is too short"];
        } elseif (strlen($Name) > self::maxName){
            return ["success" => false, "message" => "Name is too long"];
        }
    }

    public function isValidPassword($Pass) {
        if (preg_match(self::whiteSpc_rgx, $Pass)) {
            return ["success" => false, "message" => "Password cannot be empty"];
        }

        if (strlen($Pass) < 8) {
            return ["success" => false, "message" => "Password must be at least 8 characters long"];
        }

        if (!preg_match('/[a-z]/', $Pass)) {
            return ["success" => false, "message" => "Password must contain at least one lowercase letter"];
        }

        if (!preg_match('/[A-Z]/', $Pass)) {
            return ["success" => false, "message" => "Password must contain at least one uppercase letter"];
        }

        if (!preg_match('/[0-9]/', $Pass)) {
            return ["success" => false, "message" => "Password must contain at least one number"];
        }

    
        if (!preg_match('/[@$!%*?&#]/', $Pass)) {
            return ["success" => false, "message" => "Password must contain at least one special character (@$!%*?&#)"];
        }
    }

    public function isValidConfirmPass($ConPass, $Pass) {
        if ($ConPass !== $Pass) {
            return ["success" => false, "message" => "Passwords do not match"];
        }
    }
}
