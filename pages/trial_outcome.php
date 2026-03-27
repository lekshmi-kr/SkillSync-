<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$booking_id = intval($_GET['booking_id'] ?? 0);
$user_id    = intval($_SESSION['user_id']);

$sql = "SELECT demo_class.*, 
               s.name AS student_name,
               t.name AS tutor_name,
               bookings.session_date
        FROM demo_class
        JOIN bookings ON bookings.id = demo_class.booking_id
        JOIN users s ON s.id = demo_class.student_id
        JOIN users t ON t.id = demo_class.instructor_id
        WHERE demo_class.booking_id='$booking_id'
          AND (demo_class.student_id='$user_id' OR demo_class.instructor_id='$user_id')";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    die("No outcome found for this session. <a href='my_bookings.php'>Go back</a>");
}
$data = mysqli_fetch_assoc($result);

// Icon and color based on student feedback
$feedback_style = [
    'Interested'          => ['icon' => '👍', 'color' => '#065f46', 'bg' => '#d1fae5'],
    'Not Suitable'        => ['icon' => '❌', 'color' => '#991b1b', 'bg' => '#fee2e2'],
    'Need Different Level'=> ['icon' => '🔄', 'color' => '#1d4ed8', 'bg' => '#dbeafe'],
];
$level_style = [
    'Beginner'     => ['color' => '#065f46', 'bg' => '#d1fae5'],
    'Intermediate' => ['color' => '#92400e', 'bg' => '#fef3c7'],
    'Advanced'     => ['color' => '#991b1b', 'bg' => '#fee2e2'],
];
$sf = $data['student_feedback'] ? ($feedback_style[$data['student_feedback']] ?? null) : null;
$tf = $data['teacher_feedback'] ? ($level_style[$data['teacher_feedback']] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Class Outcome - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .outcome-wrapper { max-width: 560px; margin: 40px auto; padding: 0 20px; }
        .outcome-card {
            background: white; border-radius: 16px;
            padding: 32px; box-shadow: 0 4px 20px rgba(44,74,154,0.09);
        }
        .outcome-card h2 { color: #1e3a8a; margin: 0 0 4px; font-size: 22px; }
        .outcome-card .sub { color: #6b7280; font-size: 14px; margin-bottom: 28px; }
        hr { border: none; border-top: 1px solid #f3f4f6; margin: 20px 0; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase;
                         letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 10px; }
        .feedback-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 15px;
        }
        .pending-pill {
            background: #f3f4f6; color: #9ca3af;
            padding: 8px 16px; border-radius: 20px; font-size: 14px;
        }
        .recommendation-box {
            background: linear-gradient(135deg, #eff6ff, #e6ecf7);
            border-left: 4px solid #2c4a9a;
            border-radius: 10px; padding: 18px 20px; margin-top: 4px;
        }
        .recommendation-box p { margin: 0; font-size: 15px; color: #1e3a8a; font-weight: 600; line-height: 1.5; }
        .waiting-box {
            background: #fef3c7; border-left: 4px solid #f59e0b;
            border-radius: 10px; padding: 16px 20px; margin-top: 4px;
        }
        .waiting-box p { margin: 0; font-size: 14px; color: #92400e; }
        .back-link { display: inline-block; margin-bottom: 16px; font-size: 14px; color: #2c4a9a; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="outcome-wrapper">
    <a href="my_bookings.php" class="back-link">← Back to My Bookings</a>

    <div class="outcome-card">
        <h2>Trial Class Outcome</h2>
        <p class="sub">
            <?= htmlspecialchars($data['student_name']) ?> with <?= htmlspecialchars($data['tutor_name']) ?>
            — <?= $data['session_date'] ?>
        </p>

        <!-- Student Feedback -->
        <div class="section-title">Student Feedback</div>
        <?php if ($data['student_feedback'] && $sf): ?>
            <span class="feedback-pill" style="color:<?= $sf['color'] ?>;background:<?= $sf['bg'] ?>">
                <?= $sf['icon'] ?> <?= $data['student_feedback'] ?>
            </span>
        <?php else: ?>
            <span class="pending-pill">⏳ Awaiting student feedback</span>
        <?php endif; ?>

        <hr>

        <!-- Teacher Assessment -->
        <div class="section-title">Instructor Assessment</div>
        <?php if ($data['teacher_feedback'] && $tf): ?>
            <span class="feedback-pill" style="color:<?= $tf['color'] ?>;background:<?= $tf['bg'] ?>">
                Student Level: <?= $data['teacher_feedback'] ?>
            </span>
        <?php else: ?>
            <span class="pending-pill">⏳ Awaiting instructor assessment</span>
        <?php endif; ?>

        <hr>

        <!-- Recommendation -->
        <div class="section-title">Recommendation</div>
        <?php if ($data['recommendation']): ?>
            <div class="recommendation-box">
                <p>💡 <?= htmlspecialchars($data['recommendation']) ?></p>
            </div>
        <?php else: ?>
            <div class="waiting-box">
                <p>⏳ Recommendation will appear once both student and instructor have submitted their feedback.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
