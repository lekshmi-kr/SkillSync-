<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = intval($_SESSION['user_id']);

$sql = "SELECT bookings.id, bookings.session_date, bookings.session_time, bookings.status,
               users.name AS tutor_name, skills.skill_name
        FROM bookings
        JOIN users ON bookings.tutor_id = users.id
        LEFT JOIN skills ON skills.user_id = users.id
        WHERE bookings.student_id = '$student_id'
        ORDER BY bookings.session_date DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .bookings-wrapper { max-width: 750px; margin: 40px auto; padding: 0 20px; }
        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .booking-info h3 { margin: 0 0 4px; color: #1e3a8a; }
        .booking-info p  { margin: 2px 0; font-size: 14px; color: #555; }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge.pending   { background: #fef3c7; color: #92400e; }
        .badge.confirmed { background: #d1fae5; color: #065f46; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .empty { text-align: center; color: #9ca3af; margin-top: 60px; font-size: 16px; }
        .success-msg {
            background: #d1fae5; color: #065f46;
            padding: 12px 20px; border-radius: 8px;
            margin-bottom: 20px; font-weight: 600;
        }
        h2 { color: #1e3a8a; margin-bottom: 24px; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="bookings-wrapper">
    <h2>My Bookings</h2>

    <?php if (isset($_GET['booked'])): ?>
        <div class="success-msg">✅ Demo class booked successfully!</div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="booking-card">
                <div class="booking-info">
                    <h3><?= htmlspecialchars($row['tutor_name']) ?></h3>
                    <p>📚 <?= htmlspecialchars($row['skill_name'] ?? 'N/A') ?></p>
                    <p>📅 <?= htmlspecialchars($row['session_date']) ?> at <?= htmlspecialchars($row['session_time']) ?></p>
                </div>
                <span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                <?php if ($row['status'] === 'confirmed'): ?>
                    <a href="trial_feedback.php?booking_id=<?= $row['id'] ?>"
                       style="font-size:13px;color:#2c4a9a;font-weight:600;text-decoration:none;">
                        📝 Give Feedback
                    </a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty">
            <p>No bookings yet.</p>
            <a href="tutors.php" class="view-btn" style="display:inline-block;margin-top:12px;">Find a Tutor</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
