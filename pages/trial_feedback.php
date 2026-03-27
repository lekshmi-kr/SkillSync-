<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$booking_id = intval($_GET['booking_id'] ?? 0);
$student_id = intval($_SESSION['user_id']);

// Verify this is a confirmed booking for this student
$b = mysqli_query($conn, "SELECT bookings.*, users.name AS tutor_name
                           FROM bookings
                           JOIN users ON users.id = bookings.tutor_id
                           WHERE bookings.id='$booking_id'
                           AND bookings.student_id='$student_id'
                           AND bookings.status='confirmed'");
if (mysqli_num_rows($b) === 0) {
    die("Booking not found or not confirmed. <a href='my_bookings.php'>Go back</a>");
}
$booking = mysqli_fetch_assoc($b);

// Check if student already submitted
$existing = mysqli_query($conn, "SELECT student_feedback FROM demo_class
                                  WHERE booking_id='$booking_id' AND student_feedback IS NOT NULL");
$already_submitted = mysqli_num_rows($existing) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Class Feedback - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .feedback-wrapper { max-width: 520px; margin: 40px auto; padding: 0 20px; }
        .feedback-card {
            background: white; border-radius: 16px;
            padding: 32px; box-shadow: 0 4px 20px rgba(44,74,154,0.09);
        }
        .feedback-card h2 { color: #1e3a8a; margin: 0 0 6px; font-size: 22px; }
        .feedback-card .sub { color: #6b7280; font-size: 14px; margin-bottom: 28px; }
        .option-group { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
        .option-label {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px; border: 2px solid #e5e7eb;
            border-radius: 10px; cursor: pointer; transition: border-color 0.2s, background 0.2s;
        }
        .option-label:hover { border-color: #2c4a9a; background: #f4f6fb; }
        .option-label input[type="radio"] { margin-top: 2px; accent-color: #2c4a9a; }
        .option-label input:checked ~ .option-text { color: #1e3a8a; }
        .option-label:has(input:checked) { border-color: #2c4a9a; background: #eff6ff; }
        .option-text strong { display: block; font-size: 15px; color: #111827; }
        .option-text span { font-size: 13px; color: #6b7280; }
        .submit-btn { width: 100%; padding: 12px; font-size: 15px; font-weight: 600; border-radius: 8px; }
        .already-done {
            background: #d1fae5; color: #065f46;
            padding: 16px; border-radius: 10px; text-align: center; font-weight: 600;
        }
        .back-link { display: inline-block; margin-bottom: 16px; font-size: 14px; color: #2c4a9a; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="feedback-wrapper">
    <a href="my_bookings.php" class="back-link">← Back to My Bookings</a>

    <div class="feedback-card">
        <h2>How was your trial class?</h2>
        <p class="sub">With <?= htmlspecialchars($booking['tutor_name']) ?> on <?= $booking['session_date'] ?></p>

        <?php if ($already_submitted): ?>
            <div class="already-done">
                ✅ You've already submitted feedback for this session.
                <br><a href="trial_outcome.php?booking_id=<?= $booking_id ?>" style="color:#065f46;font-weight:700;">View Recommendation →</a>
            </div>
        <?php else: ?>
            <form method="POST" action="../submit_trial_feedback.php">
                <input type="hidden" name="booking_id" value="<?= $booking_id ?>">

                <div class="option-group">
                    <label class="option-label">
                        <input type="radio" name="student_feedback" value="Interested" required>
                        <div class="option-text">
                            <strong>👍 Interested</strong>
                            <span>I enjoyed the class and want to continue</span>
                        </div>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="student_feedback" value="Not Suitable">
                        <div class="option-text">
                            <strong>❌ Not Suitable</strong>
                            <span>This instructor isn't the right fit for me</span>
                        </div>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="student_feedback" value="Need Different Level">
                        <div class="option-text">
                            <strong>🔄 Need Different Level</strong>
                            <span>The class level doesn't match my current skill</span>
                        </div>
                    </label>
                </div>

                <button type="submit" class="submit-btn">Submit Feedback</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
