<?php
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/student_signup.php");
    exit();
}

$name     = mysqli_real_escape_string($conn, trim($_POST['name']));
$email    = mysqli_real_escape_string($conn, trim($_POST['email']));
$password = $_POST['password'];
$confirm  = $_POST['confirm_password'];
$role     = "student";

if ($password !== $confirm) {
    header("Location: pages/student_signup.php?error=mismatch");
    exit();
}

// Check if email already exists
$check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
if (mysqli_num_rows($check) > 0) {
    header("Location: pages/student_signup.php?error=exists");
    exit();
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password, role)
        VALUES ('$name', '$email', '$hashed', '$role')";

if (mysqli_query($conn, $sql)) {
    header("Location: login.php");
    exit();
} else {
    header("Location: pages/student_signup.php?error=failed");
    exit();
}
?>
