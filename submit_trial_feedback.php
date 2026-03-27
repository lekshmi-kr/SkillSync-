<?php
session_start();
include "connect.php";
include "includes/recommendation.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/my_bookings.php");
    exit();
}

$role       = $_SESSION['role'] ?? 'student';
$user_id    = intval($_SESSION['user_id']);
$booking_id = intval($_POST['booking_id']);

// Verify booking exists and involves this user
$b = mysqli_query($conn, "SELECT * FROM bookings WHERE id='$booking_id'
                           AND (student_id='$user_id' OR tutor_id='$user_id')
                           AND status='confirmed'");
if (mysqli_num_rows($b) === 0) {
    die("Invalid booking. <a href='pages/my_bookings.php'>Go back</a>");
}
$booking = mysqli_fetch_assoc($b);
$student_id    = intval($booking['student_id']);
$instructor_id = intval($booking['tutor_id']);

// Check if a demo_class record already exists for this booking
$existing = mysqli_query($conn, "SELECT * FROM demo_class WHERE booking_id='$booking_id'");

if ($role === 'student') {
    $allowed = ['Interested', 'Not Suitable', 'Need Different Level'];
    $student_feedback = $_POST['student_feedback'];
    if (!in_array($student_feedback, $allowed)) {
        die("Invalid feedback.");
    }
    $student_feedback = mysqli_real_escape_string($conn, $student_feedback);

    if (mysqli_num_rows($existing) === 0) {
        // Create record with student feedback only
        mysqli_query($conn, "INSERT INTO demo_class (student_id, instructor_id, booking_id, student_feedback)
                             VALUES ('$student_id', '$instructor_id', '$booking_id', '$student_feedback')");
    } else {
        $row = mysqli_fetch_assoc($existing);
        $teacher_feedback = $row['teacher_feedback'];

        // If teacher already submitted, generate recommendation now
        $recommendation = '';
        if ($teacher_feedback) {
            $recommendation = mysqli_real_escape_string($conn, generate_recommendation($student_feedback, $teacher_feedback));
        }

        mysqli_query($conn, "UPDATE demo_class
                             SET student_feedback='$student_feedback', recommendation='$recommendation'
                             WHERE booking_id='$booking_id'");
    }

    header("Location: pages/trial_outcome.php?booking_id=$booking_id");
    exit();
}

if ($role === 'tutor') {
    $allowed = ['Beginner', 'Intermediate', 'Advanced'];
    $teacher_feedback = $_POST['teacher_feedback'];
    if (!in_array($teacher_feedback, $allowed)) {
        die("Invalid feedback.");
    }
    $teacher_feedback = mysqli_real_escape_string($conn, $teacher_feedback);

    if (mysqli_num_rows($existing) === 0) {
        // Create record with teacher feedback only
        mysqli_query($conn, "INSERT INTO demo_class (student_id, instructor_id, booking_id, teacher_feedback)
                             VALUES ('$student_id', '$instructor_id', '$booking_id', '$teacher_feedback')");
    } else {
        $row = mysqli_fetch_assoc($existing);
        $student_feedback = $row['student_feedback'];

        // If student already submitted, generate recommendation now
        $recommendation = '';
        if ($student_feedback) {
            $recommendation = mysqli_real_escape_string($conn, generate_recommendation($student_feedback, $teacher_feedback));
        }

        mysqli_query($conn, "UPDATE demo_class
                             SET teacher_feedback='$teacher_feedback', recommendation='$recommendation'
                             WHERE booking_id='$booking_id'");
    }

    header("Location: pages/tutor_bookings.php?feedback=1");
    exit();
}
?>
