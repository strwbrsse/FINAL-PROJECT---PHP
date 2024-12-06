<?php
include('DB_Connect.php');

$response = "";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}else{
    if (isset($_POST['userId'])) {
        $userId = $_POST['userId'];
        $sql = "SELECT un.fname, un.mname, un.lname, p.birthday, c.email, c.contact, a.address, a.barangay, p.sex, 
        p.civilstat, e.employment_stat, e.employer, e.profession, h.allergy_description, h.disease_description  FROM user_name as un join personal as p on un.name_id = p.name_id 
        join contact as c on p.name_id = c.name_id join address as a on c.name_id = a.name_id join employment as e on a.name_id = e.name_id 
         join health as h on e.name_id = h.name_id
        WHERE un.name_id = $userId";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            $response = json_encode($row); // Send the value as JSON
        }
    }
}

echo $response;
mysqli_close($conn);
?>