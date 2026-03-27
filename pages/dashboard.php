<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../connect.php";

$user_id = intval($_SESSION['user_id']);
$role    = $_SESSION['role'] ?? 'student';
$name    = htmlspecialchars($_SESSION['user']);

// Fetch recent bookings
if ($role === 'tutor') {
    $sql = "SELECT bookings.session_date, bookings.session_time, bookings.status, users.name AS other_name
            FROM bookings
            JOIN users ON bookings.student_id = users.id
            WHERE bookings.tutor_id = '$user_id'
            ORDER BY bookings.session_date DESC LIMIT 5";
} else {
    $sql = "SELECT bookings.session_date, bookings.session_time, bookings.status, users.name AS other_name
            FROM bookings
            JOIN users ON bookings.tutor_id = users.id
            WHERE bookings.student_id = '$user_id'
            ORDER BY bookings.session_date DESC LIMIT 5";
}
$bookings = mysqli_query($conn, $sql);

// Unread message count
$uq = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM messages
                             WHERE receiver_id='$user_id' AND is_read=0");
$unread = intval(mysqli_fetch_assoc($uq)['cnt']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dash-wrapper { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .welcome-card {
            background: linear-gradient(135deg, #2c4a9a, #1e3a8a);
            color: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 28px;
        }
        .welcome-card h2 { margin: 0 0 6px; font-size: 26px; }
        .welcome-card p  { margin: 0; opacity: 0.85; font-size: 15px; }
        .quick-links { display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap; }
        .quick-link {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .quick-link:hover { background: rgba(255,255,255,0.28); }
        .quick-link .badge {
            display: inline-block;
            background: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 700;
            border-radius: 99px;
            padding: 1px 7px;
            margin-left: 5px;
            vertical-align: middle;
        }
        .section-title { font-size: 18px; font-weight: 700; color: #1e3a8a; margin-bottom: 16px; }
        .booking-row {
            background: white;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .booking-row p { margin: 0; font-size: 14px; color: #555; }
        .booking-row h4 { margin: 0 0 3px; color: #111827; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge.pending   { background: #fef3c7; color: #92400e; }
        .badge.confirmed { background: #d1fae5; color: #065f46; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .empty { color: #9ca3af; font-size: 14px; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="dash-wrapper">
    <div class="welcome-card">
        <h2>Welcome back, <?= $name ?> 👋</h2>
        <p><?= $role === 'tutor' ? 'Manage your sessions and students.' : 'Find tutors and manage your bookings.' ?></p>
        <div class="quick-links">
            <?php if ($role === 'tutor'): ?>
                <a href="tutor_bookings.php" class="quick-link">📋 Booking Requests</a>
                <a href="inbox.php" class="quick-link">💬 Messages<?php if ($unread > 0): ?><span class="badge"><?= $unread ?></span><?php endif; ?></a>
                <a href="tutor_style.php" class="quick-link">🎓 Teaching Style</a>
                <a href="attendance.php" class="quick-link">📊 Attendance</a>
                <a href="credibility.php" class="quick-link">🏅 Credibility Score</a>
            <?php else: ?>
                <a href="tutors.php" class="quick-link">🔍 Find Tutors</a>
                <a href="my_bookings.php" class="quick-link">📅 My Bookings</a>
                <a href="inbox.php" class="quick-link">💬 Messages<?php if ($unread > 0): ?><span class="badge"><?= $unread ?></span><?php endif; ?></a>
                <a href="progress.php" class="quick-link">📈 My Progress</a>
                <a href="my_style.php" class="quick-link">🎯 Learning Style</a>
                <a href="attendance.php" class="quick-link">📊 Attendance</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-title">Recent Bookings</div>

    <?php if (mysqli_num_rows($bookings) > 0): ?>
        <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
            <div class="booking-row">
                <div>
                    <h4><?= htmlspecialchars($b['other_name']) ?></h4>
                    <p>📅 <?= $b['session_date'] ?> at <?= $b['session_time'] ?></p>
                </div>
                <span class="badge <?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty">No bookings yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
