<?php 
session_reset();
session_start();
include('db_conn.php');

if (isset($_POST['name']) && isset($_POST['gender']) && isset($_POST['age']) && isset($_POST['birthdate'])
	&& isset($_POST['address']) && isset($_POST['mobilenumber']) && isset($_POST['dosageseq']) && isset($_POST['dateofvaccine']) && isset($_POST['brand'])) {

	function validate($data){
       $data = trim($data);
	   return $data;
	}

	$name = validate($_POST['name']);
	$gender = validate($_POST['gender']);
	$age = validate($_POST['age']);
	$birthdate = validate($_POST['birthdate']);
	$address = validate($_POST['address']);
	$mobilenumber = validate($_POST['mobilenumber']);
	$dosageseq = validate($_POST['dosageseq']);
    $dateofvaccine = validate($_POST['dateofvaccine']);
	$brand = validate($_POST['brand']);
	
	// $user = ($_SESSION['uname']);
	$response = "false";
	
	if(!empty($name && $gender && $age && $birthdate && $address && $mobilenumber && $dosageseq && $dateofvaccine && $brand)){
		$sqlselect = "Select max(id) as id from applicants_family";
		$resultID = mysqli_query($conn, $sqlselect);

		if (mysqli_num_rows($resultID) === 1) {
			$row = mysqli_fetch_assoc($resultID);
			$id = $row['id'] + 1;
		}else{
			$id = 1;
		}
		
		$sqlInsert = "INSERT INTO applicants_family VALUES ($id, '$applicant_id', '$fullname', '$relationship', '$familyAge',
		'$familyCivilstatus', '$highesteducation', '$occupation', '$income', '$user', NOW(), '$user', NOW())";

		if(mysqli_query($conn, $sqlInsert)){
			$response = "success";
		}
		else{
			$response = $sqlInsert;
		}

	}
	
}
// Close connection
echo $response;
mysqli_close($conn);

?>

