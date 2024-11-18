<?php

require_once 'DB_Connect.php';

class SQL_Operations {
    private $conn;

    public function __construct($config) {
        $this->conn = new DbConn($config);
    }

    public function authenticate($email) {
        $conn = $this->conn->getConnection();
        $sql = "Select password from user_auth where mail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
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