<?php
// Instead of creating a NEW connection here, just use the one you already made.
require_once 'db.php'; 

// CRITICAL CHECK: Variable Mapping
// Your previous code uses "$_db"
// But your database.php might use "$conn" or "$pdo".
// You must map them so the rest of your site doesn't break.

if (isset($conn)) {
    $_db = $conn; 
} elseif (isset($pdo)) {
    $_db = $pdo;
}
?>