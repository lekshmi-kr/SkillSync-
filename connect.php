<?php
$host   = "sql310.infinityfree.com"; // MySQL Host Name from IF panel
$user   = "if0_41490108";            // MySQL User Name
$pass   = "SkillSync";      // your InfinityFree account password
$dbname = "if0_41490108_skillsync";  // MySQL DB Name

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
