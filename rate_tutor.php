<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/tutors.php");
    exit();
}

$student_id = intval($_SESSION['user_id']);
$tutor_id   = intval($_POST['tutor_id']);
$rating     = intval($_POST['rating']);
$review     = mysqli_real_escape_string($conn, trim($_POST['review'] ?? ''));

if ($rating < 1 || $rating > 5 || $tutor_id === 0) {
    die("Invalid rating. <a href='javascript:history.back()'>Go back</a>");
}

if ($student_id === $tutor_id) {
    die("You cannot rate yourself.");
}

$review_val = !empty($review) ? "'$review'" : "NULL";

// INSERT or UPDATE if already rated
$sql = "INSERT INTO ratings (student_id, tutor_id, rating, review)
        VALUES ('$student_id', '$tutor_id', '$rating', $review_val)
        ON DUPLICATE KEY UPDATE
            rating = '$rating',
            review = $review_val";

if (mysqli_query($conn, $sql)) {
    header("Location: profile.php?id=$tutor_id&rated=1");
} else {
    echo "Error: " . mysqli_error($conn);
}
exit();
?>
