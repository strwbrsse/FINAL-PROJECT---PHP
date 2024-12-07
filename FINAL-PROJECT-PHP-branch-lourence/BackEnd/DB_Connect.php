<?php

// DEBUG: Database connection handler class
class DBConn {
    // DEBUG: Store database connection instance
    protected $conn;
    // DEBUG: Track connection state to prevent multiple close attempts
    private $isClosed = false;

    // DEBUG: Initialize database connection with config parameters
    public function __construct($dbConnect) {
        // DEBUG: Create new MySQL connection with error handling
        $this->conn = new mysqli(
            $dbConnect['host'], 
            $dbConnect['username'], 
            $dbConnect['password'], 
            $dbConnect['dbname']
        );

        // DEBUG: Throw exception if connection fails
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    // DEBUG: Get active database connection instance
    public function getConnection() {
        return $this->conn;
    }

    // DEBUG: Safely close database connection if still open
    public function close() {
        if (!$this->isClosed) {
            $this->conn->close();
            $this->isClosed = true;
        }
    }

    // DEBUG: Ensure connection is closed when object is destroyed
    public function __destruct()
    {
        $this->close();
    }
}

?>