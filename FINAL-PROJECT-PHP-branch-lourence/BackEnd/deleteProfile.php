<?php
    include('DB_Connect.php');
    session_start();
    $response = "";
    if (isset($_POST['userId'])) {
        $userId = $_POST['userId'];
        
        $sql = "DELETE from vaccine WHERE name_id = $userId";
        $sql1 = "DELETE from health WHERE name_id = $userId";
        $sql2 = "DELETE from contact WHERE name_id = $userId";
        $sql3 = "DELETE from address WHERE name_id= $userId";
        $sql4 = "DELETE from employment WHERE name_id = $userId";
        $sql5 = "DELETE from user_auth WHERE name_id = $userId";
        $sql6 = "DELETE from personal WHERE name_id = $userId";
        $sql7 = "DELETE from user_name WHERE name_id = $userId";
        

        if(mysqli_query($conn, $sql) && mysqli_query($conn, $sql1) && mysqli_query($conn, $sql2) && mysqli_query($conn, $sql3) 
        && mysqli_query($conn, $sql4) && mysqli_query($conn, $sql5) && mysqli_query($conn, $sql6) && mysqli_query($conn, $sql7)){
            $response = "success";
        }
        else{
            $response = $sql;
        }
    }
echo $response;
mysqli_close($conn);
?>
