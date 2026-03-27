<?php
session_start();
include "connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$me   = intval($_SESSION['user_id']);
$with = intval($_GET['with'] ?? 0);

if ($with === 0) {
    echo json_encode(['error' => 'Missing user']);
    exit();
}

// Mark messages from the other person as read
mysqli_query($conn, "UPDATE messages SET is_read=1
                     WHERE sender_id='$with' AND receiver_id='$me'");

$sql = "SELECT sender_id, message, sent_at
        FROM messages
        WHERE (sender_id='$me' AND receiver_id='$with')
           OR (sender_id='$with' AND receiver_id='$me')
        ORDER BY sent_at ASC";

$result = mysqli_query($conn, $sql);
$msgs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $msgs[] = [
        'mine'    => intval($row['sender_id']) === $me,
        'message' => htmlspecialchars($row['message']),
        'time'    => date('h:i A', strtotime($row['sent_at']))
    ];
}

echo json_encode($msgs);
?>
