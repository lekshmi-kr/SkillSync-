<?php
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/signup.php");
    exit();
}

$name    = mysqli_real_escape_string($conn, $_POST['name']);
$email   = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];
$confirm  = $_POST['confirm_password'];
$role     = "tutor";

if ($password !== $confirm) {
    die("Passwords do not match. <a href='pages/signup.php'>Go back</a>");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

// Handle certificate upload
$certificate = null;
if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $file_type     = $_FILES['certificate']['type'];
    $file_size     = $_FILES['certificate']['size'];

    if (!in_array($file_type, $allowed_types)) {
        die("Invalid file type. Only PDF, JPG, PNG allowed. <a href='pages/signup.php'>Go back</a>");
    }
    if ($file_size > 2 * 1024 * 1024) { // 2MB limit
        die("File too large. Max 2MB. <a href='pages/signup.php'>Go back</a>");
    }

    $ext      = pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION);
    $filename = 'cert_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest     = 'uploads/certificates/' . $filename;

    if (!is_dir('uploads/certificates')) {
        mkdir('uploads/certificates', 0755, true);
    }

    if (move_uploaded_file($_FILES['certificate']['tmp_name'], $dest)) {
        $certificate = mysqli_real_escape_string($conn, $filename);
    }
}

$cert_val = $certificate ? "'$certificate'" : "NULL";

$sql = "INSERT INTO users (name, email, password, role, certificate)
        VALUES ('$name', '$email', '$hashed', '$role', $cert_val)";

if (mysqli_query($conn, $sql)) {
    header("Location: login.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
