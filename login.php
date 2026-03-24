<?php
include "connect.php";

$email = $_GET['email'];
$password = $_GET['password'];

$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    $name = $user['name'];

    // redirect to dashboard with name
    header("Location: http://127.0.0.1:5500/pages/dashboard.html?name=$name");
    exit();
} else {
    echo "Invalid Email or Password";
}
?>