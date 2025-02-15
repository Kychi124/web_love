<?php
// config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'seafood');

// เชื่อมต่อกับฐานข้อมูล
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// ตรวจสอบการเชื่อมต่อ
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>