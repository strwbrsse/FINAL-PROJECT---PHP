<?php
require_once 'DB_Connect.php';

session_start();
header('Content-Type: application/json');

// $userId = $_SESSION['user_id'];
$userId = "1";

// $query = "SELECT fname, mname, lname, birthday, mail, contact, address, barangay, sex, civilstat, employmentstat, employer, profession FROM Personal_Info WHERE personal_id = ?";
$query = "SELECT un.fname, un.mname, un.lname, p.birthday, c.mail, c.contact, a.address, a.barangay p.sex, p.civilstat, e.employment_stat, e.employer, e.profession  FROM user_name as un 
join personal as p
on un.name_id = p.name_id 
join contact as c
on p.name_id = c.name_id
join address as a
on c.name_id = a.name_id
join employment as e
on a.name_id = e.name_id
WHERE personal_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "User not found."]);
}

$stmt->close();
$conn->close();
?>