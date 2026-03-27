<?php
session_start();
include "../connect.php";
include "../includes/compatibility.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = intval($_SESSION['user_id']);

// Current saved preferences (first instructor row or standalone)
$pref_result = mysqli_query($conn, "SELECT learning_style, learning_pace
                                     FROM learning_preferences
                                     WHERE student_id='$student_id'
                                     LIMIT 1");
$saved = mysqli_fetch_assoc($pref_result);

// Compatibility scores with all booked instructors
$scores = mysqli_query($conn, "SELECT lp.compatibility_score, lp.teaching_style, lp.teaching_pace,
                                       u.name AS instructor_name, u.id AS instructor_id
                                FROM learning_preferences lp
                                JOIN users u ON u.id = lp.instructor_id
                                WHERE lp.student_id='$student_id'
                                ORDER BY lp.compatibility_score DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Learning Style - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .style-wrapper { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .style-card {
            background: white; border-radius: 16px;
            padding: 28px 32px; box-shadow: 0 4px 20px rgba(44,74,154,0.09);
            margin-bottom: 28px;
        }
        .style-card h3 { color: #1e3a8a; margin: 0 0 20px; font-size: 18px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 700; color: #374151; }
        .option-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .style-option input[type="radio"] { display: none; }
        .style-option label {
            display: flex; flex-direction: column; align-items: center;
            padding: 14px 10px; border: 2px solid #e5e7eb;
            border-radius: 10px; cursor: pointer; font-size: 13px;
            font-weight: 600; color: #374151; text-align: center;
            transition: border-color 0.2s, background 0.2s;
        }
        .style-option label .icon { font-size: 22px; margin-bottom: 6px; }
        .style-option input:checked + label {
            border-color: #2c4a9a; background: #eff6ff; color: #1e3a8a;
        }
        .style-option label:hover { border-color: #2c4a9a; background: #f4f6fb; }
        .save-btn { width: 100%; margin-top: 20px; padding: 12px; font-size: 15px; font-weight: 600; border-radius: 8px; }
        .toast { padding: 12px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
        .toast.success { background: #d1fae5; color: #065f46; }
        .toast.error   { background: #fee2e2; color: #991b1b; }

        /* Compatibility results */
        .section-title { font-size: 18px; font-weight: 700; color: #1e3a8a; margin-bottom: 16px; }
        .compat-card {
            background: white; border-radius: 12px;
            padding: 18px 22px; margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        }
        .compat-score {
            width: 58px; height: 58px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; flex-shrink: 0;
        }
        .compat-info { flex: 1; }
        .compat-info h4 { margin: 0 0 4px; font-size: 15px; color: #111827; }
        .compat-info p  { margin: 0; font-size: 13px; color: #6b7280; }
        .compat-badge { padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
        .score-bar-bg { background: #f3f4f6; border-radius: 99px; height: 8px; margin-top: 8px; overflow: hidden; }
        .score-bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #2c4a9a, #4f7cdb); transition: width 0.6s; }
        .no-scores { color: #9ca3af; font-size: 14px; padding: 20px 0; }
        .pending-score { color: #9ca3af; font-size: 13px; font-style: italic; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="style-wrapper">

    <?php if (isset($_GET['saved'])): ?>
        <div class="toast success">✅ Learning preferences saved!</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="toast error">❌ Invalid input. Please try again.</div>
    <?php endif; ?>

    <!-- Preferences Form -->
    <div class="style-card">
        <h3>🎯 My Learning Preferences</h3>
        <form method="POST" action="../save_preferences.php">

            <div class="form-group" style="margin-bottom:20px;">
                <label>Learning Style</label>
                <div class="option-grid">
                    <div class="style-option">
                        <input type="radio" name="learning_style" id="ls_visual" value="Visual"
                               <?= ($saved['learning_style'] ?? '') === 'Visual' ? 'checked' : '' ?> required>
                        <label for="ls_visual"><span class="icon">👁️</span>Visual</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="learning_style" id="ls_practical" value="Practical"
                               <?= ($saved['learning_style'] ?? '') === 'Practical' ? 'checked' : '' ?>>
                        <label for="ls_practical"><span class="icon">🛠️</span>Practical</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="learning_style" id="ls_theory" value="Theory"
                               <?= ($saved['learning_style'] ?? '') === 'Theory' ? 'checked' : '' ?>>
                        <label for="ls_theory"><span class="icon">📖</span>Theory</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Learning Pace</label>
                <div class="option-grid">
                    <div class="style-option">
                        <input type="radio" name="learning_pace" id="lp_slow" value="Slow"
                               <?= ($saved['learning_pace'] ?? '') === 'Slow' ? 'checked' : '' ?> required>
                        <label for="lp_slow"><span class="icon">🐢</span>Slow</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="learning_pace" id="lp_medium" value="Medium"
                               <?= ($saved['learning_pace'] ?? '') === 'Medium' ? 'checked' : '' ?>>
                        <label for="lp_medium"><span class="icon">🚶</span>Medium</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="learning_pace" id="lp_fast" value="Fast"
                               <?= ($saved['learning_pace'] ?? '') === 'Fast' ? 'checked' : '' ?>>
                        <label for="lp_fast"><span class="icon">🚀</span>Fast</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-btn">Save Preferences</button>
        </form>
    </div>

    <!-- Compatibility Results -->
    <div class="section-title">Compatibility with Your Instructors</div>

    <?php
    $rows = [];
    while ($r = mysqli_fetch_assoc($scores)) $rows[] = $r;

    if (count($rows) > 0):
        foreach ($rows as $r):
            $score = $r['compatibility_score'];
            if ($score !== null):
                [$label, $color, $bg] = compatibility_label(intval($score));
            endif;
    ?>
        <div class="compat-card">
            <?php if ($score !== null): ?>
                <div class="compat-score" style="background:<?= $bg ?>;color:<?= $color ?>">
                    <?= $score ?>
                </div>
            <?php else: ?>
                <div class="compat-score" style="background:#f3f4f6;color:#9ca3af">—</div>
            <?php endif; ?>

            <div class="compat-info">
                <h4><?= htmlspecialchars($r['instructor_name']) ?></h4>
                <?php if ($r['teaching_style'] && $r['teaching_pace']): ?>
                    <p>Teaches: <?= $r['teaching_style'] ?> · <?= $r['teaching_pace'] ?> pace</p>
                <?php else: ?>
                    <p class="pending-score">Instructor hasn't set preferences yet</p>
                <?php endif; ?>
                <?php if ($score !== null): ?>
                    <div class="score-bar-bg">
                        <div class="score-bar-fill" style="width:<?= $score ?>%"></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($score !== null): ?>
                <span class="compat-badge" style="color:<?= $color ?>;background:<?= $bg ?>">
                    <?= $label ?>
                </span>
            <?php endif; ?>
        </div>
    <?php
        endforeach;
    else: ?>
        <p class="no-scores">No instructors to compare yet. Book a session first to see compatibility scores.</p>
    <?php endif; ?>

</div>
</body>
</html>
