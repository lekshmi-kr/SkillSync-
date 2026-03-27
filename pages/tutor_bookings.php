<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$tutor_id = intval($_SESSION['user_id']);

// Handle confirm / cancel actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $action = $_GET['action'] === 'confirm' ? 'confirmed' : 'cancelled';
    mysqli_query($conn, "UPDATE bookings SET status='$action' WHERE id='$booking_id' AND tutor_id='$tutor_id'");
    header("Location: tutor_bookings.php");
    exit();
}

$sql = "SELECT bookings.id, bookings.session_date, bookings.session_time, bookings.status,
               users.name AS student_name
        FROM bookings
        JOIN users ON bookings.student_id = users.id
        WHERE bookings.tutor_id = '$tutor_id'
        ORDER BY bookings.session_date ASC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Requests - SkillSync</title>
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
        .actions { display: flex; gap: 8px; }
        .btn-confirm { background: #059669; color: white; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .btn-cancel  { background: #dc2626; color: white; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .badge.pending   { background: #fef3c7; color: #92400e; }
        .badge.confirmed { background: #d1fae5; color: #065f46; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .empty { text-align: center; color: #9ca3af; margin-top: 60px; font-size: 16px; }
        h2 { color: #1e3a8a; margin-bottom: 24px; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="bookings-wrapper">
    <h2>Booking Requests</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="booking-card">
                <div class="booking-info">
                    <h3><?= htmlspecialchars($row['student_name']) ?></h3>
                    <p>📅 <?= htmlspecialchars($row['session_date']) ?> at <?= htmlspecialchars($row['session_time']) ?></p>
                </div>
                <?php if ($row['status'] === 'pending'): ?>
                    <div class="actions">
                        <a href="tutor_bookings.php?action=confirm&id=<?= $row['id'] ?>" class="btn-confirm">Confirm</a>
                        <a href="tutor_bookings.php?action=cancel&id=<?= $row['id'] ?>"  class="btn-cancel">Cancel</a>
                    </div>
                <?php else: ?>
                    <div class="actions">
                        <span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                        <?php if ($row['status'] === 'confirmed'): ?>
                            <a href="tutor_trial_feedback.php?booking_id=<?= $row['id'] ?>"
                               style="font-size:13px;color:#2c4a9a;font-weight:600;text-decoration:none;padding:7px 12px;background:#eff6ff;border-radius:6px;">
                                📋 Assess Student
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty"><p>No booking requests yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
