<?php
session_start();
include "../connect.php";
include "../includes/compatibility.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    header("Location: ../login.php");
    exit();
}

$instructor_id = intval($_SESSION['user_id']);

// Current saved preferences
$pref_result = mysqli_query($conn, "SELECT teaching_style, teaching_pace
                                     FROM learning_preferences
                                     WHERE instructor_id='$instructor_id'
                                     LIMIT 1");
$saved = mysqli_fetch_assoc($pref_result);

// Compatibility scores with all students
$scores = mysqli_query($conn, "SELECT lp.compatibility_score, lp.learning_style, lp.learning_pace,
                                       u.name AS student_name
                                FROM learning_preferences lp
                                JOIN users u ON u.id = lp.student_id
                                WHERE lp.instructor_id='$instructor_id'
                                ORDER BY lp.compatibility_score DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Teaching Style - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .style-wrapper { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .style-card {
            background: white; border-radius: 16px;
            padding: 28px 32px; box-shadow: 0 4px 20px rgba(44,74,154,0.09);
            margin-bottom: 28px;
        }
        .style-card h3 { color: #1e3a8a; margin: 0 0 20px; font-size: 18px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
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
        .style-option input:checked + label { border-color: #2c4a9a; background: #eff6ff; color: #1e3a8a; }
        .style-option label:hover { border-color: #2c4a9a; background: #f4f6fb; }
        .save-btn { width: 100%; padding: 12px; font-size: 15px; font-weight: 600; border-radius: 8px; }
        .toast { padding: 12px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
        .toast.success { background: #d1fae5; color: #065f46; }
        .toast.error   { background: #fee2e2; color: #991b1b; }
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
        .score-bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #2c4a9a, #4f7cdb); }
        .no-scores { color: #9ca3af; font-size: 14px; padding: 20px 0; }
        .pending-score { color: #9ca3af; font-size: 13px; font-style: italic; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="style-wrapper">

    <?php if (isset($_GET['saved'])): ?>
        <div class="toast success">✅ Teaching preferences saved!</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="toast error">❌ Invalid input. Please try again.</div>
    <?php endif; ?>

    <div class="style-card">
        <h3>🎓 My Teaching Preferences</h3>
        <form method="POST" action="../save_preferences.php">

            <div class="form-group">
                <label>Teaching Style</label>
                <div class="option-grid">
                    <div class="style-option">
                        <input type="radio" name="teaching_style" id="ts_visual" value="Visual"
                               <?= ($saved['teaching_style'] ?? '') === 'Visual' ? 'checked' : '' ?> required>
                        <label for="ts_visual"><span class="icon">👁️</span>Visual</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="teaching_style" id="ts_practical" value="Practical"
                               <?= ($saved['teaching_style'] ?? '') === 'Practical' ? 'checked' : '' ?>>
                        <label for="ts_practical"><span class="icon">🛠️</span>Practical</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="teaching_style" id="ts_theory" value="Theory"
                               <?= ($saved['teaching_style'] ?? '') === 'Theory' ? 'checked' : '' ?>>
                        <label for="ts_theory"><span class="icon">📖</span>Theory</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Teaching Pace</label>
                <div class="option-grid">
                    <div class="style-option">
                        <input type="radio" name="teaching_pace" id="tp_slow" value="Slow"
                               <?= ($saved['teaching_pace'] ?? '') === 'Slow' ? 'checked' : '' ?> required>
                        <label for="tp_slow"><span class="icon">🐢</span>Slow</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="teaching_pace" id="tp_medium" value="Medium"
                               <?= ($saved['teaching_pace'] ?? '') === 'Medium' ? 'checked' : '' ?>>
                        <label for="tp_medium"><span class="icon">🚶</span>Medium</label>
                    </div>
                    <div class="style-option">
                        <input type="radio" name="teaching_pace" id="tp_fast" value="Fast"
                               <?= ($saved['teaching_pace'] ?? '') === 'Fast' ? 'checked' : '' ?>>
                        <label for="tp_fast"><span class="icon">🚀</span>Fast</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-btn">Save Preferences</button>
        </form>
    </div>

    <!-- Compatibility with students -->
    <div class="section-title">Compatibility with Your Students</div>

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
                <h4><?= htmlspecialchars($r['student_name']) ?></h4>
                <?php if ($r['learning_style'] && $r['learning_pace']): ?>
                    <p>Learns: <?= $r['learning_style'] ?> · <?= $r['learning_pace'] ?> pace</p>
                <?php else: ?>
                    <p class="pending-score">Student hasn't set preferences yet</p>
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
        <p class="no-scores">No students to compare yet.</p>
    <?php endif; ?>

</div>
</body>
</html>
