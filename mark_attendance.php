<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/attendance.php");
    exit();
}

$tutor_id   = intval($_SESSION['user_id']);
$booking_id = intval($_POST['booking_id']);
$status     = $_POST['status'] === 'present' ? 'present' : 'absent';

// Verify this booking belongs to this tutor and is confirmed
$check = mysqli_query($conn, "SELECT student_id FROM bookings
                               WHERE id='$booking_id'
                               AND tutor_id='$tutor_id'
                               AND status='confirmed'");

if (mysqli_num_rows($check) === 0) {
    die("Invalid booking. <a href='pages/attendance.php'>Go back</a>");
}

$student_id = intval(mysqli_fetch_assoc($check)['student_id']);

$sql = "INSERT INTO attendance (booking_id, student_id, tutor_id, status)
        VALUES ('$booking_id', '$student_id', '$tutor_id', '$status')
        ON DUPLICATE KEY UPDATE status = '$status'";

mysqli_query($conn, $sql);
header("Location: pages/attendance.php?marked=1");
exit();
?>
