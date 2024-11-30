<?php

$host = "localhost";
$dbname = "vaccination";
$username = "root";
$password = "";

$conn = mysqli_connect(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname);

if (!$conn) {
    echo "Connection failed!";
}

?>