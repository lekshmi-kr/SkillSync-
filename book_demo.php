<?php
session_start();
include "connect.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/tutors.php");
    exit();
}

$student_id   = intval($_SESSION['user_id']);
$tutor_id     = intval($_POST['tutor_id']);
$session_date = mysqli_real_escape_string($conn, $_POST['session_date']);
$session_time = mysqli_real_escape_string($conn, $_POST['session_time']);

// Validate date is not in the past
if (strtotime($session_date) < strtotime('today')) {
    die("Please select a future date. <a href='javascript:history.back()'>Go back</a>");
}

// Prevent booking own profile
if ($student_id === $tutor_id) {
    die("You cannot book yourself. <a href='javascript:history.back()'>Go back</a>");
}

$sql = "INSERT INTO bookings (student_id, tutor_id, session_date, session_time)
        VALUES ('$student_id', '$tutor_id', '$session_date', '$session_time')";

if (mysqli_query($conn, $sql)) {
    header("Location: pages/my_bookings.php?booked=1");
    exit();
} else {
    echo "Booking failed: " . mysqli_error($conn);
}
?>
