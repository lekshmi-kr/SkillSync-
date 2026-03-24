<?php
include "connect.php";

$result = mysqli_query($conn, "SELECT * FROM skills");

while($row = mysqli_fetch_assoc($result)) {
    echo "Skill: " . $row['skill_name'] . "<br>";
    echo "Description: " . $row['description'] . "<br><br>";
}
?>
