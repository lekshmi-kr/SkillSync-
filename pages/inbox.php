<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$me = intval($_SESSION['user_id']);

// Get all unique conversations with latest message
$sql = "SELECT
            u.id, u.name,
            m.message AS last_message,
            m.sent_at,
            SUM(m.is_read = 0 AND m.receiver_id = '$me') AS unread
        FROM messages m
        JOIN users u ON u.id = IF(m.sender_id = '$me', m.receiver_id, m.sender_id)
        WHERE m.sender_id = '$me' OR m.receiver_id = '$me'
        GROUP BY u.id, u.name, m.message, m.sent_at
        ORDER BY m.sent_at DESC";

// Deduplicate by user using PHP since MySQL GROUP BY on non-aggregates varies
$result = mysqli_query($conn, $sql);
$seen = [];
$conversations = [];
while ($row = mysqli_fetch_assoc($result)) {
    if (!isset($seen[$row['id']])) {
        $seen[$row['id']] = true;
        $conversations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .inbox-wrapper { max-width: 640px; margin: 40px auto; padding: 0 20px; }
        h2 { color: #1e3a8a; margin-bottom: 20px; }
        .convo-card {
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: box-shadow 0.2s;
        }
        .convo-card:hover { box-shadow: 0 4px 16px rgba(44,74,154,0.12); }
        .convo-card .avatar {
            width: 42px; height: 42px;
            background: #e6ecf7;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .convo-info { flex: 1; margin-left: 14px; }
        .convo-info h4 { margin: 0 0 3px; font-size: 15px; color: #111827; }
        .convo-info p  { margin: 0; font-size: 13px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px; }
        .unread-badge {
            background: #2c4a9a; color: white;
            border-radius: 50%; width: 20px; height: 20px;
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .empty { text-align: center; color: #9ca3af; margin-top: 60px; font-size: 15px; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="inbox-wrapper">
    <h2>💬 Messages</h2>

    <?php if (count($conversations) > 0): ?>
        <?php foreach ($conversations as $c): ?>
            <a href="chat.php?with=<?= $c['id'] ?>" class="convo-card">
                <div class="avatar">👤</div>
                <div class="convo-info">
                    <h4><?= htmlspecialchars($c['name']) ?></h4>
                    <p><?= htmlspecialchars($c['last_message']) ?></p>
                </div>
                <?php if ($c['unread'] > 0): ?>
                    <div class="unread-badge"><?= $c['unread'] ?></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">
            <p>No conversations yet.</p>
            <a href="tutors.php" class="view-btn" style="display:inline-block;margin-top:12px;">Find a Tutor to Chat</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
