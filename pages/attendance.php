<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$role    = $_SESSION['role'];

if ($role === 'tutor') {
    // Tutor sees all their confirmed sessions + attendance status
    $sql = "SELECT b.id AS booking_id, b.session_date, b.session_time,
                   u.name AS student_name,
                   a.status AS attendance_status
            FROM bookings b
            JOIN users u ON u.id = b.student_id
            LEFT JOIN attendance a ON a.booking_id = b.id
            WHERE b.tutor_id = '$user_id' AND b.status = 'confirmed'
            ORDER BY b.session_date DESC";
} else {
    // Student sees their own attendance record
    $sql = "SELECT b.id AS booking_id, b.session_date, b.session_time,
                   u.name AS tutor_name,
                   a.status AS attendance_status
            FROM bookings b
            JOIN users u ON u.id = b.tutor_id
            LEFT JOIN attendance a ON a.booking_id = b.id
            WHERE b.student_id = '$user_id' AND b.status = 'confirmed'
            ORDER BY b.session_date DESC";
}

$result = mysqli_query($conn, $sql);
$rows = [];
while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;

// Stats for student
$total    = count($rows);
$present  = count(array_filter($rows, fn($r) => $r['attendance_status'] === 'present'));
$pct      = $total > 0 ? round(($present / $total) * 100) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .att-wrapper { max-width: 760px; margin: 40px auto; padding: 0 20px; }
        h2 { color: #1e3a8a; margin-bottom: 6px; }
        .sub { color: #6b7280; font-size: 14px; margin-bottom: 24px; }

        /* Student summary card */
        .att-summary {
            background: linear-gradient(135deg, #2c4a9a, #4f7cdb);
            color: white; border-radius: 14px; padding: 24px 28px;
            display: flex; align-items: center; gap: 24px;
            margin-bottom: 28px; flex-wrap: wrap;
        }
        .att-summary .big-pct { font-size: 48px; font-weight: 800; line-height: 1; }
        .att-summary .summary-info p { margin: 4px 0; font-size: 14px; opacity: 0.85; }
        .att-summary .summary-info strong { font-size: 16px; }

        /* Table */
        .att-card {
            background: white; border-radius: 12px;
            padding: 20px 24px; margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 10px;
        }
        .att-card h4 { margin: 0 0 3px; color: #111827; font-size: 15px; }
        .att-card p  { margin: 0; font-size: 13px; color: #6b7280; }
        .badge-present  { background: #d1fae5; color: #065f46; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
        .badge-absent   { background: #fee2e2; color: #991b1b; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
        .badge-unmarked { background: #f3f4f6; color: #9ca3af; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        /* Tutor mark buttons */
        .mark-form { display: flex; gap: 8px; }
        .btn-present { background: #059669; color: white; padding: 7px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 600; margin: 0; display: inline-block; width: auto; }
        .btn-absent  { background: #dc2626; color: white; padding: 7px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 600; margin: 0; display: inline-block; width: auto; }
        .btn-present:hover { background: #047857; }
        .btn-absent:hover  { background: #b91c1c; }

        .toast { background: #d1fae5; color: #065f46; padding: 12px 18px; border-radius: 8px; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        .empty { color: #9ca3af; font-size: 14px; text-align: center; padding: 40px 0; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="att-wrapper">

    <?php if (isset($_GET['marked'])): ?>
        <div class="toast">✅ Attendance marked successfully.</div>
    <?php endif; ?>

    <h2><?= $role === 'tutor' ? 'Student Attendance' : 'My Attendance' ?></h2>
    <p class="sub"><?= $role === 'tutor' ? 'Mark attendance for your confirmed sessions.' : 'Your attendance record across all sessions.' ?></p>

    <?php if ($role === 'student' && $pct !== null): ?>
        <div class="att-summary">
            <div class="big-pct"><?= $pct ?>%</div>
            <div class="summary-info">
                <strong>Overall Attendance</strong>
                <p><?= $present ?> present out of <?= $total ?> sessions</p>
                <p><?= $pct >= 75 ? '✅ Good standing' : '⚠️ Below 75% — try to attend more sessions' ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (count($rows) > 0): ?>
        <?php foreach ($rows as $row): ?>
            <div class="att-card">
                <div>
                    <h4><?= htmlspecialchars($role === 'tutor' ? $row['student_name'] : $row['tutor_name']) ?></h4>
                    <p>📅 <?= $row['session_date'] ?> at <?= $row['session_time'] ?></p>
                </div>

                <?php if ($role === 'tutor'): ?>
                    <?php if ($row['attendance_status']): ?>
                        <span class="badge-<?= $row['attendance_status'] ?>">
                            <?= ucfirst($row['attendance_status']) ?>
                        </span>
                    <?php else: ?>
                        <form method="POST" action="../mark_attendance.php" class="mark-form">
                            <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                            <button type="submit" name="status" value="present" class="btn-present">✔ Present</button>
                            <button type="submit" name="status" value="absent"  class="btn-absent">✘ Absent</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($row['attendance_status']): ?>
                        <span class="badge-<?= $row['attendance_status'] ?>">
                            <?= ucfirst($row['attendance_status']) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge-unmarked">Not marked yet</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">No confirmed sessions found.</div>
    <?php endif; ?>

</div>
</body>
</html>
