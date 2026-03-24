<?php
include "connect.php";

$skill = $_GET['skill'];
$level = $_GET['level'];
$age = $_GET['age_group'];

$sql = "SELECT users.name, skills.skill_name, skills.description, skills.level, skills.age_group
        FROM skills
        JOIN users ON skills.user_id = users.id
        WHERE skill_name LIKE '%$skill%'
        AND level='$level'
        AND age_group='$age'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<h3>" . $row['skill_name'] . "</h3>";
        echo "<p>By: " . $row['name'] . "</p>";
        echo "<p>Level: " . $row['level'] . "</p>";
        echo "<p>Age Group: " . $row['age_group'] . "</p>";
        echo "<p>" . $row['description'] . "</p><hr>";
    }
} else {
    echo "No matching tutors found";
}
?>