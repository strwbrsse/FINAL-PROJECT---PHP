<?php
class filters
{
    public function isValidEmail($mail)
    {
        return (filter_var($mail, FILTER_VALIDATE_EMAIL) !== false);
    }

    public function isValidName($fname, $mname, $lname)
    {

        if (strlen($fname) > 50) {
            return ["success" => false, "message" => "First name should not exceed 50 characters."];
        } elseif (strlen($fname) < 1) {
            return ["success" => false, "message" => "First name should be at least 1 character."];
        }

        if (strlen($mname) > 50) {
            return ["success" => false, "message" => "Middle name should not exceed 50 character"];
        }

        if (strlen($lname) > 50) {
            return ["success" => false, "message" => "Last name should not exceed 50 characters."];
        } elseif (strlen($lname) < 1) {
            return ["success" => false, "message" => "Last name should be at least 1 character."];
        }
    }

    public function isValidPassword($password)
    {
        if (strlen($password) < 8) {
            return ["success" => false, "message" => "Password should be at least 8 characters."];
        } elseif (strlen($password) > 25) {
            return ["success" => false, "message" => "Password should not exceed 25 characters."];
        }
    }

    public function  isValidContact($contact)
    {
        if (!preg_match('/^[\d+]+$/', $contact)) {
            // ^ - start of string
            // [\d+]+ - one or more digits
            // + - one or more of the preceding element
            // $ - end of string
            return ["success" => false, "message" => "Invalid contact number."];
        }
    }
}
