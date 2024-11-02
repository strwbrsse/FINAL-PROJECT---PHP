<?php
$destination = isset($_GET['destination']) ? $_GET['destination'] : 'index.html';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading</title>
    <link rel="stylesheet" href="styles.css">
    <meta http-equiv="refresh" content="3;url=<?php echo htmlspecialchars($destination); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div action="loading-screen" class="loading-screen">
        <div class="loader"></div>
        <h1>Loading, Please wait...</h1>
    </div>
</body>
</html>