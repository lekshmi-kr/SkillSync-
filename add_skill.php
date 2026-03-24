<?php
include "connect.php";

$user_id = $_GET['user_id'];
$skill_name = $_GET['skill_name'];
$description = $_GET['description'];
$level = $_GET['level'];
$age_group = $_GET['age_group'];

$sql = "INSERT INTO skills (user_id, skill_name, description, level, age_group) 
        VALUES ('$user_id', '$skill_name', '$description', '$level', '$age_group')";

if (mysqli_query($conn, $sql)) {
    echo "Skill Added Successfully";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>