<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$me   = intval($_SESSION['user_id']);
$with = intval($_GET['with'] ?? 0);

if ($with === 0) { echo "No user specified."; exit(); }

// Get the other person's name
$r = mysqli_query($conn, "SELECT name FROM users WHERE id='$with'");
if (mysqli_num_rows($r) === 0) { echo "User not found."; exit(); }
$other_name = htmlspecialchars(mysqli_fetch_assoc($r)['name']);

// Handle message send via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message'] ?? ''))) {
    $msg = mysqli_real_escape_string($conn, trim($_POST['message']));
    mysqli_query($conn, "INSERT INTO messages (sender_id, receiver_id, message)
                         VALUES ('$me', '$with', '$msg')");
    // Redirect to avoid resubmit on refresh
    header("Location: messages.php?with=$with");
    exit();
}

// Mark incoming messages as read
mysqli_query($conn, "UPDATE messages SET is_read=1
                     WHERE sender_id='$with' AND receiver_id='$me'");

// Fetch all messages
$result = mysqli_query($conn, "SELECT sender_id, message, sent_at
                                FROM messages
                                WHERE (sender_id='$me' AND receiver_id='$with')
                                   OR (sender_id='$with' AND receiver_id='$me')
                                ORDER BY sent_at ASC");
$messages = [];
while ($row = mysqli_fetch_assoc($result)) $messages[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?= $other_name ?> - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .chat-wrapper {
            max-width: 680px; margin: 30px auto; padding: 0 16px;
        }
        .chat-header {
            background: #2c4a9a; color: white;
            padding: 16px 20px; border-radius: 12px 12px 0 0;
            font-size: 16px; font-weight: 700;
            display: flex; align-items: center; gap: 10px;
        }
        .chat-header .avatar {
            width: 36px; height: 36px; background: rgba(255,255,255,0.25);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 16px;
        }
        .chat-box {
            background: #f4f6fb; padding: 20px;
            min-height: 400px; max-height: 500px; overflow-y: auto;
            display: flex; flex-direction: column; gap: 10px;
            border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;
        }
        .bubble {
            max-width: 70%; padding: 10px 14px; border-radius: 16px;
            font-size: 14px; line-height: 1.5; word-break: break-word;
        }
        .bubble.mine {
            background: #2c4a9a; color: white;
            align-self: flex-end; border-bottom-right-radius: 4px;
        }
        .bubble.theirs {
            background: white; color: #111827;
            align-self: flex-start; border-bottom-left-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .bubble .time { font-size: 11px; opacity: 0.65; margin-top: 4px; text-align: right; }
        .chat-input-area {
            display: flex; gap: 8px; padding: 12px 16px;
            background: white; border: 1px solid #e5e7eb;
            border-radius: 0 0 12px 12px;
        }
        .chat-input-area input {
            flex: 1; padding: 10px 14px; border: 1.5px solid #e5e7eb;
            border-radius: 8px; font-size: 14px; outline: none;
        }
        .chat-input-area input:focus { border-color: #2c4a9a; }
        .chat-input-area button {
            padding: 10px 20px; background: #2c4a9a; color: white;
            border: none; border-radius: 8px; font-size: 14px;
            font-weight: 600; cursor: pointer; margin: 0;
            display: inline-block; width: auto;
        }
        .chat-input-area button:hover { background: #1f3575; }
        .empty-chat { text-align: center; color: #9ca3af; margin: auto; font-size: 14px; padding: 40px 0; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="chat-wrapper">
    <div class="chat-header">
        <div class="avatar">💬</div>
        <?= $other_name ?>
    </div>

    <div class="chat-box" id="chatBox">
        <?php if (count($messages) === 0): ?>
            <div class="empty-chat">No messages yet. Say hello!</div>
        <?php else: ?>
            <?php foreach ($messages as $m): ?>
                <div class="bubble <?= intval($m['sender_id']) === $me ? 'mine' : 'theirs' ?>">
                    <?= htmlspecialchars($m['message']) ?>
                    <div class="time"><?= date('h:i A', strtotime($m['sent_at'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form method="POST" class="chat-input-area">
        <input type="text" name="message" placeholder="Type a message..."
               autocomplete="off" required>
        <button type="submit">Send</button>
    </form>
</div>

<script>
    // Scroll to bottom on load
    const box = document.getElementById('chatBox');
    box.scrollTop = box.scrollHeight;
</script>
</body>
</html>
