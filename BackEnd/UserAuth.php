<?php

require_once 'DB_Operations.php';

Class userAuth {
    private $SQL_Operations;

    public function __construct($config) {
        $this->SQL_Operations = new SQL_Operations($config);
    }

    public function authenticate($email, $password) {
        $hashedPass = $this->SQL_Operations->authenticate($email);

        if ($hashedPass !== null) {
            if ($password === $hashedPass) {
                return ["success" => true, "message" => "Access granted"];
            } else {
                return ["success" => false, "message" => "Access denied: Invalid Password"];
            }
        } else {
            return ["success" => false, "message" => "Access denied: User not found"];
        }
    }
    public function close() {
        $this->SQL_Operations->close();
    }

}

?>