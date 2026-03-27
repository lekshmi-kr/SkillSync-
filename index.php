<?php
session_start();
include "connect.php";

// Fetch 3 featured tutors dynamically
$featured = mysqli_query($conn, "SELECT users.id, users.name, users.email,
                                         skills.skill_name, skills.level
                                  FROM users
                                  JOIN skills ON skills.user_id = users.id
                                  WHERE users.role = 'tutor'
                                  LIMIT 3");
$featured_tutors = [];
while ($r = mysqli_fetch_assoc($featured)) $featured_tutors[] = $r;

$email_img_map = [
    'aniket@skillsync.com' => 'tutor1.jpg',
    'rahul@skillsync.com'  => 'tutor2.jpg',
    'meera@skillsync.com'  => 'tutor3.jpg',
];
$fallback_imgs = ['tutor1.jpg', 'tutor2.jpg', 'tutor3.jpg'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Find the perfect tutor for any skill">
    <title>SkillSync — Find Your Perfect Tutor</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #2c4a9a 40%, #4f7cdb 100%);
            padding: 100px 20px 80px;
            text-align: center;
            color: white;
        }
        .hero h1 {
            font-size: 42px;
            font-weight: 800;
            margin: 0 0 16px;
            line-height: 1.2;
            color: white;
            text-align: center;
        }
        .hero p {
            font-size: 18px;
            opacity: 0.9;
            margin: 0 auto 32px;
            max-width: 560px;
            line-height: 1.6;
        }
        .hero-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-primary {
            padding: 14px 32px; background: white; color: #1e3a8a;
            border: none; border-radius: 8px; font-size: 15px; font-weight: 700;
            cursor: pointer; text-decoration: none; display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s; margin: 0;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .btn-secondary {
            padding: 14px 32px; background: rgba(255,255,255,0.15);
            color: white; border: 2px solid rgba(255,255,255,0.5);
            border-radius: 8px; font-size: 15px; font-weight: 700;
            cursor: pointer; text-decoration: none; display: inline-block;
            transition: background 0.2s; margin: 0;
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.25); }

        /* ── Stats bar ── */
        .stats-bar {
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(8px);
            display: flex; justify-content: center; gap: 0;
            box-shadow: 0 2px 12px rgba(44,74,154,0.08);
        }
        .stat-item {
            padding: 24px 48px; text-align: center;
            border-right: 1px solid #f3f4f6;
        }
        .stat-item:last-child { border-right: none; }
        .stat-item .num { font-size: 28px; font-weight: 800; color: #1e3a8a; }
        .stat-item .lbl { font-size: 13px; color: #6b7280; margin-top: 2px; }

        /* ── Section shared ── */
        .home-section { padding: 64px 20px; }
        .home-section.alt { background: rgba(255,255,255,0.35); }
        .section-heading {
            text-align: center; font-size: 28px; font-weight: 800;
            color: #1e3a8a; margin: 0 0 8px;
        }
        .section-sub {
            text-align: center; color: #6b7280; font-size: 15px;
            margin: 0 auto 40px; max-width: 480px;
        }

        /* ── Skills pills ── */
        .skill-list { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; }
        .skill-list span {
            background: #e6ecff; color: #1e3a8a;
            padding: 9px 18px; border-radius: 20px; font-size: 14px;
            font-weight: 600; cursor: pointer; transition: 0.2s;
        }
        .skill-list span:hover { background: #1e3a8a; color: white; }

        /* ── How it works ── */
        .steps { display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; }
        .step-card {
            background: rgba(255,255,255,0.7); padding: 28px 24px; width: 200px;
            border-radius: 14px; box-shadow: 0 4px 16px rgba(44,74,154,0.08);
            text-align: center; transition: 0.25s;
        }
        .step-card:hover { transform: translateY(-6px); box-shadow: 0 10px 28px rgba(44,74,154,0.13); }
        .step-card .step-icon { font-size: 36px; margin-bottom: 14px; }
        .step-card h3 { color: #1e3a8a; margin: 0 0 8px; font-size: 16px; }
        .step-card p  { color: #6b7280; font-size: 14px; margin: 0; line-height: 1.5; }

        /* ── Featured tutors ── */
        .tutor-grid {
            display: flex; justify-content: center;
            gap: 24px; flex-wrap: wrap;
        }
        .tutor-feat-card {
            background: rgba(255,255,255,0.75); border-radius: 16px; overflow: hidden;
            width: 240px; box-shadow: 0 4px 16px rgba(44,74,154,0.1);
            text-decoration: none; color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            display: block;
        }
        .tutor-feat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(44,74,154,0.15);
        }
        .tutor-feat-card img {
            width: 100%; height: 170px; object-fit: cover;
        }
        .tutor-feat-body { padding: 16px; }
        .tutor-feat-body h3 { margin: 0 0 6px; font-size: 16px; color: #111827; }
        .tutor-feat-body .skill-tag { margin: 0 0 6px; }
        .tutor-feat-body .level-txt { font-size: 13px; color: #6b7280; }
        .view-profile-lnk {
            display: block; margin-top: 12px; padding: 8px;
            background: #2c4a9a; color: white; border-radius: 8px;
            text-align: center; font-size: 13px; font-weight: 600;
            transition: background 0.2s;
        }
        .tutor-feat-card:hover .view-profile-lnk { background: #1f3575; }

        /* ── CTA banner ── */
        .cta-banner {
            background: linear-gradient(135deg, #2c4a9a, #3d5fc4);
            color: white; text-align: center; padding: 64px 20px 80px;
        }
        .cta-banner h2 { font-size: 30px; margin: 0 0 12px; color: white; }
        .cta-banner p  { font-size: 16px; opacity: 0.85; margin: 0 0 28px; }

        /* ── Footer ── */
        footer {
            background: linear-gradient(135deg, #3d5fc4, #2c4a9a);
            color: rgba(255,255,255,0.55);
            text-align: center; padding: 18px;
            font-size: 13px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- Hero -->
<section class="hero">
    <h1>Discover the Perfect<br>Extracurricular Tutor</h1>
    <p>Connect with expert tutors in Music, Dance, Art, Coding, Sports and more — all in one place.</p>
    <div class="hero-btns">
        <a href="pages/tutors.php" class="btn-primary">🔍 Explore Tutors</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="pages/student_signup.php" class="btn-secondary">Join Free</a>
        <?php endif; ?>
    </div>
</section>

<!-- Stats bar -->
<div class="stats-bar">
    <div class="stat-item">
        <div class="num">50+</div>
        <div class="lbl">Expert Tutors</div>
    </div>
    <div class="stat-item">
        <div class="num">15+</div>
        <div class="lbl">Skill Categories</div>
    </div>
    <div class="stat-item">
        <div class="num">200+</div>
        <div class="lbl">Happy Students</div>
    </div>
</div>

<!-- Popular Skills -->
<section class="home-section">
    <h2 class="section-heading">Popular Skills</h2>
    <p class="section-sub">Browse by what you want to learn</p>
    <div class="skill-list">
        <?php
        $skills = ['🎵 Music','💃 Dance','🎨 Art','💻 Coding','⚽ Sports',
                   '🌍 Languages','📷 Photography','🧘 Yoga','🎤 Public Speaking','🍳 Cooking'];
        foreach ($skills as $s):
        ?>
            <span onclick="window.location.href='pages/tutors.php?skill=<?= urlencode(trim(preg_replace('/[^\w\s]/u','',$s))) ?>'"><?= $s ?></span>
        <?php endforeach; ?>
    </div>
</section>

<!-- How it works -->
<section class="home-section alt">
    <h2 class="section-heading">How It Works</h2>
    <p class="section-sub">Get started in three simple steps</p>
    <div class="steps">
        <div class="step-card">
            <div class="step-icon">🔍</div>
            <h3>Search</h3>
            <p>Find tutors based on your skill and level</p>
        </div>
        <div class="step-card">
            <div class="step-icon">👤</div>
            <h3>Explore</h3>
            <p>View profiles, ratings and certifications</p>
        </div>
        <div class="step-card">
            <div class="step-icon">📅</div>
            <h3>Book</h3>
            <p>Request a demo class at your preferred time</p>
        </div>
        <div class="step-card">
            <div class="step-icon">💬</div>
            <h3>Connect</h3>
            <p>Chat directly with your tutor</p>
        </div>
    </div>
</section>

<!-- Featured Tutors -->
<?php if (count($featured_tutors) > 0): ?>
<section class="home-section">
    <h2 class="section-heading">Featured Tutors</h2>
    <p class="section-sub">Meet some of our top-rated instructors</p>
    <div class="tutor-grid">
        <?php foreach ($featured_tutors as $i => $t):
            $img = $email_img_map[$t['email']] ?? $fallback_imgs[$i % 3];
        ?>
            <a href="profile.php?id=<?= $t['id'] ?>" class="tutor-feat-card">
                <img src="images/<?= $img ?>" alt="<?= htmlspecialchars($t['name']) ?>">
                <div class="tutor-feat-body">
                    <h3><?= htmlspecialchars($t['name']) ?></h3>
                    <span class="skill-tag"><?= htmlspecialchars($t['skill_name']) ?></span>
                    <p class="level-txt">📊 <?= ucfirst($t['level']) ?></p>
                    <span class="view-profile-lnk">View Profile</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<?php if (!isset($_SESSION['user_id'])): ?>
<section class="cta-banner">
    <h2>Ready to start learning?</h2>
    <p>Join SkillSync today and find the perfect tutor for your goals.</p>
    <a href="pages/student_signup.php" class="btn-primary">Create Free Account</a>
</section>
<?php endif; ?>

<!-- Footer -->
<footer>
    &copy; <?= date('Y') ?> SkillSync. All rights reserved.
</footer>

</body>
</html>
