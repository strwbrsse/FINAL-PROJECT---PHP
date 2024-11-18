<?php

class DBConn {

    protected $conn;
    private $isClosed = false;

    public function __construct($dbConnect) {
        $this->conn = new mysqli($dbConnect['host'], $dbConnect['username'], $dbConnect['password'], $dbConnect['dbname']);

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function close() {
        if (!$this->isClosed) { // Only close if not already closed
            $this->conn->close();
            $this->isClosed = true; // Mark as closed
        }
    }

    public function __destruct()
    {
        $this->close();
    }

}

?>