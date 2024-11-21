<?php

require_once 'DB_Operations.php';
require_once 'Filters.php';

class UserReg
{
    private $SQL_Operations;
    private $filters;

    public function __construct($config)
    {
        $this->SQL_Operations = new SQL_Operations($config);
    }

    public function register_PersonalInfo(
        $fname,
        $mname,
        $lname,
        $birthday,
        $mail,
        $contact,
        $sex,
        $civStat,
        $nationality,
        $empStat,
        $empl,
        $profession,
        $address,
        $barangay
    ) {
        if ($this->SQL_Operations->check_ExistingUser($mail)) {
            return ["success" => false, "message" => "Registration denied: Email already exists."];
        }

        if (!$this->filters->isValidEmail($mail)) {
            return ["success" => false, "message" => "Registration denied: Invalid email."];
        }

        $this->filters->isValidPassword($fname, $mname, $lname);

        $this->filters->isValidContact($contact);
    }
}
