<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/progress.php");
    exit();
}

$student_id = intval($_SESSION['user_id']);
$skill_name = mysqli_real_escape_string($conn, trim($_POST['skill_name']));
$level      = mysqli_real_escape_string($conn, $_POST['level']);
$progress   = max(0, min(100, intval($_POST['progress'])));

$allowed_levels = ['beginner', 'intermediate', 'advanced'];
if (!in_array($level, $allowed_levels) || empty($skill_name)) {
    header("Location: pages/progress.php?error=1");
    exit();
}

// INSERT or UPDATE if skill already exists for this student
$sql = "INSERT INTO learning_progress (student_id, skill_name, level, progress)
        VALUES ('$student_id', '$skill_name', '$level', '$progress')
        ON DUPLICATE KEY UPDATE
            level    = '$level',
            progress = '$progress'";

if (mysqli_query($conn, $sql)) {
    header("Location: pages/progress.php?saved=1");
} else {
    header("Location: pages/progress.php?error=1");
}
exit();
?>
