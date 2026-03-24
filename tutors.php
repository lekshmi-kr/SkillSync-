<?php
include "connect.php";

$result = mysqli_query($conn, "SELECT * FROM skills");

while($row = mysqli_fetch_assoc($result)) {
    echo "<h3>" . $row['skill_name'] . "</h3>";
    echo "<p>" . $row['description'] . "</p><hr>";
}
?>
