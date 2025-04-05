
<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'orderadmin');
define('DB_PASSWORD', 'CODENAMEorder@');
define('DB_NAME', 'orderdb');

// Establish database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}
?>
