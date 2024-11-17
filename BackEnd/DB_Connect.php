<?php

class DBConn {

    protected $conn;

    public function __construct($dbConnect) {
        $this->conn = new mysqli($dbConnect['host'], $dbConnect['username'], $dbConnect['password'], $dbConnect['dbname']);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function close() {
        return $this->conn->close();
    }

    public function __destruct()
    {
        $this->close();
    }

}

?>