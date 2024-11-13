<?php

abstract class Database {
    protected $conn;

    public function __construct($dbConfig) {
        $this->conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    abstract public function close();
}
class UserAuth extends Database {
    public function authenticate($username, $email, $password) {
        $sql = "SELECT password FROM user_auth WHERE username = ? AND mail = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            if ($password === $hashedPassword) {
                return ["success" => true, "message" => "Access granted"];
            } else {
                return ["success" => false, "message" => "Access denied: Invalid password"];
            }
        } else {
            return ["success" => false, "message" => "Access denied: User not found"];
        }
    }

    public function close() {
        $this->conn->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['Username'];
    $email = $_POST['Email'];
    $password = $_POST['Pass'];

    $dbConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'shotsafe_db'
    ];

    $userAuth = new UserAuth($dbConfig);
    $result = $userAuth->authenticate($username, $email, $password);

    echo json_encode($result);

    $userAuth->close();
}
?>