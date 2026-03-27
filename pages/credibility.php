<?php
session_start();
include "../connect.php";
include "../includes/credibility.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    header("Location: ../login.php");
    exit();
}

$tutor_id = intval($_SESSION['user_id']);

// Recalculate fresh on every visit
$score = calculate_credibility($conn, $tutor_id);
[$label, $color, $bg, $icon] = credibility_label($score);

// Fetch component data for breakdown display
$rq  = mysqli_query($conn, "SELECT ROUND(AVG(rating),1) AS avg_r, COUNT(*) AS total_ratings FROM ratings WHERE tutor_id='$tutor_id'");
$rat = mysqli_fetch_assoc($rq);

$cq   = mysqli_query($conn, "SELECT certificate FROM users WHERE id='$tutor_id'");
$cert = mysqli_fetch_assoc($cq);

$bq = mysqli_query($conn, "SELECT COUNT(*) AS total, SUM(status='confirmed') AS confirmed
                             FROM bookings WHERE tutor_id='$tutor_id'");
$bk = mysqli_fetch_assoc($bq);

$rating_pts     = $rat['avg_r']   ? round(($rat['avg_r'] / 5) * 40) : 0;
$cert_pts       = !empty($cert['certificate']) ? 30 : 0;
$completion_pts = ($bk['total'] > 0) ? round(($bk['confirmed'] / $bk['total']) * 30) : 0;
$completion_pct = ($bk['total'] > 0) ? round(($bk['confirmed'] / $bk['total']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credibility Score - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .cred-wrapper { max-width: 680px; margin: 40px auto; padding: 0 20px 60px; }

        /* Score hero card */
        .score-hero {
            background: linear-gradient(135deg, #2c4a9a, #4f7cdb);
            border-radius: 16px; padding: 36px 32px;
            text-align: center; color: white; margin-bottom: 28px;
        }
        .score-circle {
            width: 110px; height: 110px; border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: 4px solid rgba(255,255,255,0.4);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 36px; font-weight: 800; color: white;
        }
        .score-hero h2 { margin: 0 0 6px; font-size: 22px; color: white; }
        .score-badge {
            display: inline-block; padding: 6px 18px;
            border-radius: 20px; font-size: 14px; font-weight: 700;
            margin-top: 8px;
        }

        /* Breakdown cards */
        .section-title { font-size: 17px; font-weight: 700; color: #1e3a8a; margin-bottom: 16px; }
        .breakdown-card {
            background: white; border-radius: 12px;
            padding: 20px 24px; margin-bottom: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .breakdown-top {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 10px;
        }
        .breakdown-top h4 { margin: 0; font-size: 15px; color: #111827; }
        .pts { font-size: 20px; font-weight: 800; color: #2c4a9a; }
        .pts span { font-size: 13px; font-weight: 400; color: #9ca3af; }
        .bar-bg { background: #f3f4f6; border-radius: 99px; height: 10px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #2c4a9a, #4f7cdb); transition: width 0.6s; }
        .breakdown-meta { font-size: 13px; color: #6b7280; margin-top: 8px; }

        /* Tips */
        .tip-card {
            background: #eff6ff; border-left: 4px solid #2c4a9a;
            border-radius: 8px; padding: 14px 16px; margin-bottom: 10px;
            font-size: 14px; color: #1e3a8a;
        }
        .tip-card.done { background: #f0fdf4; border-color: #059669; color: #065f46; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="cred-wrapper">

    <!-- Score hero -->
    <div class="score-hero">
        <div class="score-circle"><?= $score ?></div>
        <h2>Your Credibility Score</h2>
        <p style="opacity:0.85;margin:0;font-size:14px;">Based on ratings, certification, and demo completions</p>
        <span class="score-badge" style="background:<?= $bg ?>;color:<?= $color ?>">
            <?= $icon ?> <?= $label ?>
        </span>
    </div>

    <!-- Breakdown -->
    <div class="section-title">Score Breakdown</div>

    <!-- Rating component -->
    <div class="breakdown-card">
        <div class="breakdown-top">
            <h4>⭐ Average Rating</h4>
            <div class="pts"><?= $rating_pts ?> <span>/ 40 pts</span></div>
        </div>
        <div class="bar-bg">
            <div class="bar-fill" style="width:<?= round(($rating_pts/40)*100) ?>%"></div>
        </div>
        <p class="breakdown-meta">
            <?php if ($rat['avg_r']): ?>
                <?= $rat['avg_r'] ?> stars average across <?= $rat['total_ratings'] ?> review<?= $rat['total_ratings'] != 1 ? 's' : '' ?>
            <?php else: ?>
                No ratings yet — encourage students to rate you after sessions
            <?php endif; ?>
        </p>
    </div>

    <!-- Certificate component -->
    <div class="breakdown-card">
        <div class="breakdown-top">
            <h4>📄 Certificate Uploaded</h4>
            <div class="pts"><?= $cert_pts ?> <span>/ 30 pts</span></div>
        </div>
        <div class="bar-bg">
            <div class="bar-fill" style="width:<?= $cert_pts > 0 ? 100 : 0 ?>%"></div>
        </div>
        <p class="breakdown-meta">
            <?= !empty($cert['certificate']) ? '✔ Certificate on file' : 'No certificate uploaded — add one via your profile' ?>
        </p>
    </div>

    <!-- Demo completion component -->
    <div class="breakdown-card">
        <div class="breakdown-top">
            <h4>📅 Demo Completion Rate</h4>
            <div class="pts"><?= $completion_pts ?> <span>/ 30 pts</span></div>
        </div>
        <div class="bar-bg">
            <div class="bar-fill" style="width:<?= round(($completion_pts/30)*100) ?>%"></div>
        </div>
        <p class="breakdown-meta">
            <?php if ($bk['total'] > 0): ?>
                <?= $bk['confirmed'] ?> confirmed out of <?= $bk['total'] ?> total requests (<?= $completion_pct ?>%)
            <?php else: ?>
                No booking requests yet
            <?php endif; ?>
        </p>
    </div>

    <!-- Tips to improve -->
    <div class="section-title" style="margin-top:28px;">How to Improve</div>

    <div class="tip-card <?= !empty($cert['certificate']) ? 'done' : '' ?>">
        <?= !empty($cert['certificate']) ? '✅' : '💡' ?>
        <?= !empty($cert['certificate']) ? 'Certificate uploaded — +30 pts earned' : 'Upload a certificate or qualification proof to earn +30 pts' ?>
    </div>
    <div class="tip-card <?= $rat['total_ratings'] >= 5 ? 'done' : '' ?>">
        <?= $rat['total_ratings'] >= 5 ? '✅' : '💡' ?>
        <?= $rat['total_ratings'] >= 5 ? 'Good number of ratings — keep collecting reviews' : 'Ask students to rate you after sessions to boost your rating score' ?>
    </div>
    <div class="tip-card <?= $completion_pct >= 80 ? 'done' : '' ?>">
        <?= $completion_pct >= 80 ? '✅' : '💡' ?>
        <?= $completion_pct >= 80 ? 'High completion rate — great job accepting bookings' : 'Confirm more booking requests to improve your completion rate' ?>
    </div>

</div>
</body>
</html>
