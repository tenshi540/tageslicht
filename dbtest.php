<?php
require_once ('dbaccess.php'); // Retrieve connection details
$db_obj = new mysqli($host, $user, $password, $database);

if ($db_obj->connect_error) {
    die("Connection Error: " . $db_obj->connect_error);
}

// The database connection is established here. This file no longer fetches or outputs user data.
?>
