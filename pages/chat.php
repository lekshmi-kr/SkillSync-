<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$me   = intval($_SESSION['user_id']);
$with = intval($_GET['with'] ?? 0);

if ($with === 0) {
    echo "No user specified.";
    exit();
}

// Get the other person's name
$r = mysqli_query($conn, "SELECT name FROM users WHERE id='$with'");
if (mysqli_num_rows($r) === 0) {
    echo "User not found.";
    exit();
}
$other = mysqli_fetch_assoc($r);
$other_name = htmlspecialchars($other['name']);
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
            max-width: 680px;
            margin: 30px auto;
            padding: 0 16px;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
        }
        .chat-header {
            background: #2c4a9a;
            color: white;
            padding: 16px 20px;
            border-radius: 12px 12px 0 0;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .chat-header .avatar {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .chat-box {
            flex: 1;
            background: #f4f6fb;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
        }
        .bubble {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        .bubble.mine {
            background: #2c4a9a;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .bubble.theirs {
            background: white;
            color: #111827;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .bubble .time {
            font-size: 11px;
            opacity: 0.65;
            margin-top: 4px;
            text-align: right;
        }
        .chat-input-area {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 12px 12px;
        }
        .chat-input-area input {
            flex: 1;
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .chat-input-area input:focus { border-color: #2c4a9a; }
        .chat-input-area button {
            padding: 10px 20px;
            background: #2c4a9a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 0;
            display: inline-block;
            width: auto;
        }
        .chat-input-area button:hover { background: #1f3575; }
        .empty-chat { text-align: center; color: #9ca3af; margin: auto; font-size: 14px; }
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
        <div class="empty-chat" id="emptyMsg">No messages yet. Say hello!</div>
    </div>

    <div class="chat-input-area">
        <input type="text" id="msgInput" placeholder="Type a message..." autocomplete="off">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
const WITH = <?= $with ?>;
const BASE = '../';

function renderMessages(msgs) {
    const box = document.getElementById('chatBox');
    const empty = document.getElementById('emptyMsg');

    if (msgs.length === 0) {
        empty.style.display = 'block';
        return;
    }
    empty.style.display = 'none';

    // Only re-render if count changed (avoid flicker)
    if (box.querySelectorAll('.bubble').length === msgs.length) return;

    box.querySelectorAll('.bubble').forEach(b => b.remove());

    msgs.forEach(m => {
        const div = document.createElement('div');
        div.className = 'bubble ' + (m.mine ? 'mine' : 'theirs');
        div.innerHTML = `${m.message}<div class="time">${m.time}</div>`;
        box.appendChild(div);
    });

    box.scrollTop = box.scrollHeight;
}

function fetchMessages() {
    fetch(`${BASE}fetch_messages.php?with=${WITH}`)
        .then(r => r.json())
        .then(data => {
            if (!data.error) renderMessages(data);
        });
}

function sendMessage() {
    const input = document.getElementById('msgInput');
    const msg = input.value.trim();
    if (!msg) return;

    const form = new FormData();
    form.append('receiver_id', WITH);
    form.append('message', msg);

    fetch(`${BASE}send_message.php`, { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                fetchMessages();
            }
        });
}

// Send on Enter key
document.getElementById('msgInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') sendMessage();
});

// Poll every 3 seconds
fetchMessages();
setInterval(fetchMessages, 3000);
</script>
</body>
</html>
