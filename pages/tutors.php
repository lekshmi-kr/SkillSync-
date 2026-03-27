<?php
session_start();
include "../connect.php";

// Search filters
$where = "users.role = 'tutor'";
$skill = '';
$level = '';
$age_group = '';

if (!empty($_GET['skill'])) {
    $skill = mysqli_real_escape_string($conn, trim($_GET['skill']));
    $where .= " AND skills.skill_name LIKE '%$skill%'";
}
if (!empty($_GET['level'])) {
    $level = mysqli_real_escape_string($conn, $_GET['level']);
    $where .= " AND skills.level = '$level'";
}
if (!empty($_GET['age_group'])) {
    $age_group = mysqli_real_escape_string($conn, $_GET['age_group']);
    $where .= " AND skills.age_group = '$age_group'";
}

$sql = "SELECT users.id, users.name, users.email, skills.skill_name, skills.level, skills.age_group
        FROM users
        JOIN skills ON skills.user_id = users.id
        WHERE $where
        ORDER BY users.name ASC";

$result = mysqli_query($conn, $sql);
$tutors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tutors[] = $row;
}

$tutor_imgs = ['tutor1.jpg', 'tutor2.jpg', 'tutor3.jpg'];
// Map tutor email to image for demo tutors
$email_img_map = [
    'aniket@skillsync.com' => 'tutor1.jpg',
    'rahul@skillsync.com'  => 'tutor2.jpg',
    'meera@skillsync.com'  => 'tutor3.jpg',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutors - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Search bar */
        .search-section {
            background: linear-gradient(135deg, #2c4a9a, #1e3a8a);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .search-section h2 { margin: 0 0 20px; font-size: 26px; }
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            max-width: 700px;
            margin: 0 auto;
        }
        .search-form input,
        .search-form select {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255,255,255,0.15);
            color: white;
            outline: none;
            min-width: 160px;
        }
        .search-form input::placeholder { color: rgba(255,255,255,0.75); }
        .search-form select option { color: #111; background: white; }
        .search-form input:focus,
        .search-form select:focus { background: rgba(255,255,255,0.25); }
        .search-form button {
            padding: 10px 24px;
            background: white;
            color: #1e3a8a;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            margin: 0;
            display: inline-block;
            width: auto;
        }
        .search-form button:hover { background: #e6ecf7; }
        .clear-link {
            color: rgba(255,255,255,0.75);
            font-size: 13px;
            text-decoration: underline;
            cursor: pointer;
            align-self: center;
        }

        /* Tutors grid */
        .tutors-section { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .tutors-section h3 { color: #1e3a8a; margin-bottom: 20px; font-size: 20px; }
        .tutor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .tutor-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: center;
        }
        .tutor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(44,74,154,0.13);
        }
        .tutor-card img {
            width: 100%; height: 160px; object-fit: cover;
        }
        .tutor-card-body { padding: 16px; }
        .tutor-card-body h4 { margin: 0 0 6px; font-size: 16px; color: #111827; }
        .tutor-card-body .skill-tag { margin: 0 0 8px; display: inline-block; }
        .meta { font-size: 13px; color: #6b7280; margin: 3px 0; }
        .view-profile-btn {
            display: block;
            margin: 12px 0 0;
            padding: 9px;
            background: #2c4a9a;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .view-profile-btn:hover { background: #1f3575; }
        .no-results {
            text-align: center; color: #9ca3af;
            padding: 60px 0; font-size: 15px;
            grid-column: 1 / -1;
        }
        .result-count { font-size: 14px; color: #6b7280; margin-bottom: 16px; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<!-- Search -->
<div class="search-section">
    <h2>Find Your Tutor</h2>
    <form class="search-form" method="GET">
        <input type="text" name="skill" placeholder="Search skill (e.g. Guitar)"
               value="<?= htmlspecialchars($skill) ?>">
        <select name="level">
            <option value="">All Levels</option>
            <option value="beginner"     <?= $level === 'beginner'     ? 'selected' : '' ?>>Beginner</option>
            <option value="intermediate" <?= $level === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
            <option value="advanced"     <?= $level === 'advanced'     ? 'selected' : '' ?>>Advanced</option>
        </select>
        <select name="age_group">
            <option value="">All Ages</option>
            <option value="kids"   <?= $age_group === 'kids'   ? 'selected' : '' ?>>Kids</option>
            <option value="teens"  <?= $age_group === 'teens'  ? 'selected' : '' ?>>Teens</option>
            <option value="adults" <?= $age_group === 'adults' ? 'selected' : '' ?>>Adults</option>
        </select>
        <button type="submit">Search</button>
        <?php if ($skill || $level || $age_group): ?>
            <a href="tutors.php" class="clear-link">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Results -->
<div class="tutors-section">
    <h3>Available Tutors</h3>

    <?php if (count($tutors) > 0): ?>
        <p class="result-count"><?= count($tutors) ?> tutor<?= count($tutors) !== 1 ? 's' : '' ?> found</p>
        <div class="tutor-grid">
            <?php foreach ($tutors as $i => $t):
                $img = $email_img_map[$t['email']] ?? $tutor_imgs[$i % count($tutor_imgs)];
            ?>
                <div class="tutor-card">
                    <img src="../images/<?= $img ?>"
                         alt="<?= htmlspecialchars($t['name']) ?>">
                    <div class="tutor-card-body">
                        <h4><?= htmlspecialchars($t['name']) ?></h4>
                        <span class="skill-tag"><?= htmlspecialchars($t['skill_name']) ?></span>
                        <p class="meta">📊 <?= ucfirst($t['level']) ?></p>
                        <p class="meta">👥 <?= ucfirst($t['age_group']) ?></p>
                        <a href="../profile.php?id=<?= $t['id'] ?>" class="view-profile-btn">View Profile</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="tutor-grid">
            <div class="no-results">
                No tutors found<?= $skill ? " for \"$skill\"" : '' ?>.
                <?php if ($skill || $level || $age_group): ?>
                    <br><a href="tutors.php" style="color:#2c4a9a;font-weight:600;">Clear filters</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="../js/script.js"></script>
</body>
</html>
