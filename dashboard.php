<?
session_start();

//Check if the user is logged in
if (!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit;
}

echo "Welcome, " . $_SESSION['username'] . "You are logged in to ShotSafe!";
?>

<a href="logout.php">Logout</a>