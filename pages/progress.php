<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = intval($_SESSION['user_id']);

// Fetch existing progress entries
$result = mysqli_query($conn, "SELECT * FROM learning_progress
                                WHERE student_id='$student_id'
                                ORDER BY updated_at DESC");
$entries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $entries[] = $row;
}

// Derive status from progress %
function get_status($p) {
    if ($p == 0)   return ['Not Started',   '#9ca3af', '#f3f4f6'];
    if ($p < 50)   return ['In Progress',   '#92400e', '#fef3c7'];
    if ($p < 100)  return ['Almost There',  '#1d4ed8', '#dbeafe'];
    return              ['Completed',       '#065f46', '#d1fae5'];
}

$skills = ['Music','Dance','Art & Craft','Sports','Technology','Languages',
           'Fitness & Yoga','Photography','Public Speaking','Design',
           'Academics','Cooking','Handicrafts','Drama & Acting','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Progress - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .progress-wrapper { max-width: 780px; margin: 40px auto; padding: 0 20px; }

        /* Add / Edit form card */
        .add-card {
            background: white;
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 4px 20px rgba(44,74,154,0.09);
            margin-bottom: 36px;
        }
        .add-card h3 { color: #1e3a8a; margin: 0 0 20px; font-size: 18px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
        .form-group select,
        .form-group input[type="number"] {
            padding: 10px 12px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: #f9fafb;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group select:focus,
        .form-group input[type="number"]:focus { border-color: #2c4a9a; background: white; }

        /* Progress slider */
        .slider-wrap { display: flex; align-items: center; gap: 12px; }
        .slider-wrap input[type="range"] {
            flex: 1; accent-color: #2c4a9a; height: 6px; cursor: pointer;
        }
        .slider-val {
            min-width: 42px; text-align: center;
            font-weight: 700; color: #2c4a9a; font-size: 15px;
        }

        .save-btn {
            margin-top: 20px; width: 100%; padding: 12px;
            font-size: 15px; font-weight: 600; border-radius: 8px;
            display: block;
        }

        /* Toast messages */
        .toast {
            padding: 12px 18px; border-radius: 8px;
            font-size: 14px; font-weight: 600; margin-bottom: 20px;
        }
        .toast.success { background: #d1fae5; color: #065f46; }
        .toast.error   { background: #fee2e2; color: #991b1b; }

        /* Progress cards */
        .section-title { font-size: 18px; font-weight: 700; color: #1e3a8a; margin-bottom: 16px; }
        .progress-card {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .card-top h4 { margin: 0; font-size: 16px; color: #111827; }
        .level-tag {
            font-size: 12px; font-weight: 600;
            padding: 3px 10px; border-radius: 20px;
            background: #e6ecf7; color: #1e3a8a;
            text-transform: capitalize;
        }
        .status-badge {
            font-size: 12px; font-weight: 700;
            padding: 4px 12px; border-radius: 20px;
        }
        .progress-bar-bg {
            background: #f3f4f6;
            border-radius: 99px;
            height: 10px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #2c4a9a, #4f7cdb);
            transition: width 0.6s ease;
        }
        .card-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 12px;
            color: #9ca3af;
        }
        .edit-link {
            font-size: 13px; color: #2c4a9a;
            text-decoration: none; font-weight: 600;
        }
        .edit-link:hover { text-decoration: underline; }
        .empty { text-align: center; color: #9ca3af; padding: 30px 0; font-size: 15px; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="progress-wrapper">

    <?php if (isset($_GET['saved'])): ?>
        <div class="toast success">✅ Progress saved successfully!</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="toast error">❌ Something went wrong. Please try again.</div>
    <?php endif; ?>

    <!-- Add / Update form -->
    <div class="add-card">
        <h3>📘 Track a Skill</h3>
        <form method="POST" action="../save_progress.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="skill_name">Skill</label>
                    <select id="skill_name" name="skill_name" required>
                        <option disabled selected>Select a skill</option>
                        <?php foreach ($skills as $s): ?>
                            <option value="<?= $s ?>"><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="level">Level</label>
                    <select id="level" name="level" required>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label for="progress">Progress</label>
                    <div class="slider-wrap">
                        <input type="range" id="progress" name="progress"
                               min="0" max="100" value="0"
                               oninput="document.getElementById('pval').textContent = this.value + '%'">
                        <span class="slider-val" id="pval">0%</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="save-btn">Save Progress</button>
        </form>
    </div>

    <!-- Existing progress entries -->
    <div class="section-title">My Learning Path</div>

    <?php if (count($entries) > 0): ?>
        <?php foreach ($entries as $e):
            [$label, $color, $bg] = get_status($e['progress']);
        ?>
        <div class="progress-card">
            <div class="card-top">
                <div style="display:flex;align-items:center;gap:10px;">
                    <h4><?= htmlspecialchars($e['skill_name']) ?></h4>
                    <span class="level-tag"><?= $e['level'] ?></span>
                </div>
                <span class="status-badge" style="color:<?= $color ?>;background:<?= $bg ?>">
                    <?= $label ?>
                </span>
            </div>

            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width:<?= $e['progress'] ?>%"></div>
            </div>

            <div class="card-footer">
                <span><?= $e['progress'] ?>% complete</span>
                <a href="progress.php?edit=<?= $e['id'] ?>&skill=<?= urlencode($e['skill_name']) ?>&level=<?= $e['level'] ?>&progress=<?= $e['progress'] ?>"
                   class="edit-link">✏️ Update</a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">No skills tracked yet. Add one above to get started.</div>
    <?php endif; ?>
</div>

<script>
// Pre-fill form if editing
const params = new URLSearchParams(window.location.search);
if (params.get('edit')) {
    const skill    = params.get('skill');
    const level    = params.get('level');
    const progress = params.get('progress');

    const skillSel = document.getElementById('skill_name');
    for (let o of skillSel.options) {
        if (o.value === skill) { o.selected = true; break; }
    }
    document.getElementById('level').value    = level;
    document.getElementById('progress').value = progress;
    document.getElementById('pval').textContent = progress + '%';

    document.querySelector('.add-card h3').textContent = '✏️ Update Skill Progress';
    document.querySelector('.save-btn').textContent = 'Update Progress';
}
</script>
</body>
</html>
