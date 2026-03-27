<?php
// ─────────────────────────────────────────────
// Update these values with your InfinityFree
// database credentials from their control panel
// ─────────────────────────────────────────────
$host   = "sql300.infinityfree.com"; // from IF panel → MySQL Databases
$user   = "your_db_username";        // e.g. if0_12345678
$pass   = "your_db_password";
$dbname = "your_db_name";            // e.g. if0_12345678_skillsync

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
