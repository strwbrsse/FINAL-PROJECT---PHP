<?php
    include('DB_Connect.php');

    session_start();
    $response = "";

    if ($_POST['userId'] && isset($_POST['fname']) && isset($_POST['mname']) && isset($_POST['lname']) && isset($_POST['birthday'])
        && isset($_POST['mail']) && isset($_POST['contact']) && isset($_POST['address']) && isset($_POST['barangay'])
        && isset($_POST['sex']) && isset($_POST['civilstat']) && isset($_POST['employmentstat']) && isset($_POST['employer'])
        && isset($_POST['profession']) && isset($_POST['userId'])) {

        $userId = $_POST['userId'];
        $fname = $_POST['fname'];
        $mname = $_POST['mname'];
        $lname = $_POST['lname'];
        $birthday = $_POST['birthday'];
        $mail = $_POST['mail'];
        $contact = $_POST['contact'];
        $address = $_POST['address'];
        $barangay = $_POST['barangay'];
        $sex = $_POST['sex'];
        $civilstat = $_POST['civilstat'];
        $employmentstat = $_POST['employmentstat'];
        $employer = $_POST['employer'];
        $profession = $_POST['profession'];

        $sql = "UPDATE user_name as un join personal as p on un.name_id = p.name_id 
            join contact as c on p.name_id = c.name_id 
            join address as a on c.name_id = a.name_id 
            join employment as e on a.name_id = e.name_id 
            set un.fname = '$fname', un.mname = '$mname', un.lname = '$lname', p.birthday = '$birthday', c.email = '$mail',
            c.contact = '$contact', a.address = '$address', a.barangay = '$barangay', p.sex = '$sex', 
            p.civilstat = '$civilstat', e.employment_stat = '$employmentstat', e.employer = '$employer', e.profession = '$profession'
            WHERE p.personal_id = $userId";

        if(mysqli_query($conn, $sql)){
            $response = "success";
        }
        else{
            $response = $sql;
        }
    }

echo $response;
mysqli_close($conn);
?>