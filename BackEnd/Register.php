<?php

require_once 'DB_Operations.php';

class Register
{
    private $SQL_Operations;

    public function __construct($config)
    {
        $this->SQL_Operations = new SQL_Operations($config);
    }

    public function register_PersonalInfo(
        $fname,
        $mname,
        $lname,
        $dob,
        $mail,
        $num,
        $sex,
        $civstat,
        $nationality,
        $empstat,
        $empl,
        $profession,
        $address,
        $barangay,
        $allergies,
        $diseases
    ) {

    }
}
