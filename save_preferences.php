<?php
session_start();
include "connect.php";
include "includes/compatibility.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/my_style.php");
    exit();
}

$role    = $_SESSION['role'] ?? 'student';
$user_id = intval($_SESSION['user_id']);

$valid_styles = ['Visual', 'Practical', 'Theory'];
$valid_paces  = ['Slow', 'Medium', 'Fast'];

if ($role === 'student') {
    $learning_style = $_POST['learning_style'];
    $learning_pace  = $_POST['learning_pace'];

    if (!in_array($learning_style, $valid_styles) || !in_array($learning_pace, $valid_paces)) {
        header("Location: pages/my_style.php?error=1");
        exit();
    }

    $ls = mysqli_real_escape_string($conn, $learning_style);
    $lp = mysqli_real_escape_string($conn, $learning_pace);

    // Get all instructors this student has booked
    $booked = mysqli_query($conn, "SELECT DISTINCT tutor_id FROM bookings WHERE student_id='$user_id'");

    while ($b = mysqli_fetch_assoc($booked)) {
        $instructor_id = intval($b['tutor_id']);

        // Fetch instructor's teaching preferences if they exist
        $pref = mysqli_query($conn, "SELECT teaching_style, teaching_pace FROM learning_preferences
                                     WHERE student_id='$user_id' AND instructor_id='$instructor_id'");
        $existing = mysqli_fetch_assoc($pref);

        $ts    = $existing['teaching_style'] ?? null;
        $tp    = $existing['teaching_pace']  ?? null;
        $score = ($ts && $tp) ? calculate_compatibility($learning_style, $learning_pace, $ts, $tp) : null;
        $score_val = $score !== null ? "'$score'" : "NULL";

        mysqli_query($conn, "INSERT INTO learning_preferences
                             (student_id, instructor_id, learning_style, learning_pace, compatibility_score)
                             VALUES ('$user_id', '$instructor_id', '$ls', '$lp', $score_val)
                             ON DUPLICATE KEY UPDATE
                                learning_style      = '$ls',
                                learning_pace       = '$lp',
                                compatibility_score = $score_val");
    }

    header("Location: pages/my_style.php?saved=1");
    exit();
}

if ($role === 'tutor') {
    $teaching_style = $_POST['teaching_style'];
    $teaching_pace  = $_POST['teaching_pace'];

    if (!in_array($teaching_style, $valid_styles) || !in_array($teaching_pace, $valid_paces)) {
        header("Location: pages/tutor_style.php?error=1");
        exit();
    }

    $ts = mysqli_real_escape_string($conn, $teaching_style);
    $tp = mysqli_real_escape_string($conn, $teaching_pace);

    // Get all students who booked this tutor
    $students = mysqli_query($conn, "SELECT DISTINCT student_id FROM bookings WHERE tutor_id='$user_id'");

    while ($s = mysqli_fetch_assoc($students)) {
        $student_id = intval($s['student_id']);

        // Fetch student's learning preferences if they exist
        $pref = mysqli_query($conn, "SELECT learning_style, learning_pace FROM learning_preferences
                                     WHERE student_id='$student_id' AND instructor_id='$user_id'");
        $existing = mysqli_fetch_assoc($pref);

        $ls    = $existing['learning_style'] ?? null;
        $lp    = $existing['learning_pace']  ?? null;
        $score = ($ls && $lp) ? calculate_compatibility($ls, $lp, $teaching_style, $teaching_pace) : null;
        $score_val = $score !== null ? "'$score'" : "NULL";

        mysqli_query($conn, "INSERT INTO learning_preferences
                             (student_id, instructor_id, teaching_style, teaching_pace, compatibility_score)
                             VALUES ('$student_id', '$user_id', NULL, NULL, $score_val)
                             ON DUPLICATE KEY UPDATE
                                teaching_style      = '$ts',
                                teaching_pace       = '$tp',
                                compatibility_score = $score_val");
    }

    header("Location: pages/tutor_style.php?saved=1");
    exit();
}
?>
