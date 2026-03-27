<?php
session_start();
include "connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$sender_id   = intval($_SESSION['user_id']);
$receiver_id = intval($_POST['receiver_id']);
$message     = trim(mysqli_real_escape_string($conn, $_POST['message']));

if (empty($message) || $receiver_id === 0) {
    echo json_encode(['error' => 'Missing fields']);
    exit();
}

if ($sender_id === $receiver_id) {
    echo json_encode(['error' => 'Cannot message yourself']);
    exit();
}

$sql = "INSERT INTO messages (sender_id, receiver_id, message)
        VALUES ('$sender_id', '$receiver_id', '$message')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => mysqli_error($conn)]);
}
?>
