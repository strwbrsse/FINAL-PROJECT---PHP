<?php

require_once 'DB_Connect.php';

class SQL_Operations {
    private $conn;

    public function __construct($config) {
        $this->conn = new dbConn($config);
    }

    public function authenticate($email) {
        $conn = $this->conn->getConnection();
        $sql = "Select password from users where email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->bind_param('s', $email);
        $stmt->store_result();

        if ($stmt->num_rows() > 0) {
            $stmt->bind_result($hashedPass);
            $stmt->fetch();
            return $hashedPass;
        } else {
            return null;
        }
    }
    public function close() {
        $this->conn->close();
    }
}

?>