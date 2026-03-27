<?php
session_start();
include "connect.php";
include "includes/credibility.php";

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: pages/tutors.php");
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT users.id, users.name, users.email, users.certificate,
               skills.skill_name, skills.description,
               skills.level, skills.age_group
        FROM users
        JOIN skills ON skills.user_id = users.id
        WHERE users.id = '$id' AND users.role = 'tutor'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    // Tutor not found — show friendly error
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Tutor Not Found - SkillSync</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
    <?php include 'includes/navbar.php'; ?>
    <div style="text-align:center;margin-top:80px;">
        <h2 style="color:#1e3a8a;">Tutor not found</h2>
        <p style="color:#6b7280;">This profile doesn't exist or the tutor hasn't set up their skills yet.</p>
        <a href="pages/tutors.php" style="color:#2c4a9a;font-weight:600;">← Browse all tutors</a>
    </div>
    </body>
    </html>
    <?php
    exit();
}

$tutor = mysqli_fetch_assoc($result);

// Map demo tutor emails to their images
$email_img_map = [
    'aniket@skillsync.com' => 'tutor1.jpg',
    'rahul@skillsync.com'  => 'tutor2.jpg',
    'meera@skillsync.com'  => 'tutor3.jpg',
];
$tutor_img = $email_img_map[$tutor['email']] ?? null;

// Calculate (and cache) credibility score
$cred_score = calculate_credibility($conn, $id);
[$cred_label, $cred_color, $cred_bg, $cred_icon] = credibility_label($cred_score);

// Fetch average rating and total count
$rq  = mysqli_query($conn, "SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total
                              FROM ratings WHERE tutor_id='$id'");
$rat = mysqli_fetch_assoc($rq);
$avg_rating  = $rat['avg_rating'] ?? null;
$total_ratings = intval($rat['total']);

// Fetch all reviews to display
$reviews_result = mysqli_query($conn, "SELECT ratings.rating, ratings.review, ratings.created_at,
                                               users.name AS student_name
                                        FROM ratings
                                        JOIN users ON users.id = ratings.student_id
                                        WHERE ratings.tutor_id='$id'
                                        ORDER BY ratings.created_at DESC");

// Check if logged-in student has a confirmed booking with this tutor
$has_confirmed_booking = false;
$confirmed_booking_id  = null;
$user_existing_rating  = null;

if (isset($_SESSION['user_id'])) {
    $student_id = intval($_SESSION['user_id']);
    $bq = mysqli_query($conn, "SELECT id FROM bookings
                                WHERE student_id='$student_id'
                                AND tutor_id='$id'
                                AND status='confirmed'
                                LIMIT 1");
    if ($bq && mysqli_num_rows($bq) > 0) {
        $has_confirmed_booking = true;
        $confirmed_booking_id  = mysqli_fetch_assoc($bq)['id'];
    }
    // Check if student already rated this tutor
    $er = mysqli_query($conn, "SELECT rating, review FROM ratings
                                WHERE student_id='$student_id' AND tutor_id='$id'");
    if (mysqli_num_rows($er) > 0) {
        $user_existing_rating = mysqli_fetch_assoc($er);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tutor['name']) ?> - SkillSync</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-wrapper {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px 60px;
        }
        .back-link {
            display: inline-block; margin-bottom: 18px;
            font-size: 14px; color: #2c4a9a; text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }

        /* Header banner */
        .profile-banner {
            background: linear-gradient(135deg, #2c4a9a, #4f7cdb);
            border-radius: 16px 16px 0 0;
            padding: 32px 28px 24px;
            display: flex; align-items: center; gap: 20px;
        }
        .profile-avatar {
            width: 76px; height: 76px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.5);
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: white; font-weight: 800; flex-shrink: 0;
            overflow: hidden;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-banner-info h2 {
            margin: 0 0 6px; color: white; font-size: 22px;
        }
        .profile-banner-info .skill-tag {
            background: rgba(255,255,255,0.2);
            color: white; border: 1px solid rgba(255,255,255,0.4);
        }

        /* Card body */
        .profile-body {
            background: white;
            border-radius: 0 0 16px 16px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .info-row {
            display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .info-item {
            background: #f4f6fb; padding: 12px 16px;
            border-radius: 10px; font-size: 14px; flex: 1; min-width: 110px;
        }
        .info-item strong {
            display: block; color: #9ca3af; font-size: 11px;
            text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;
        }
        .info-item span { color: #111827; font-weight: 600; }
        .description {
            font-size: 14px; color: #555; line-height: 1.7;
            background: #f9fafb; border-radius: 8px; padding: 14px 16px;
            margin-bottom: 20px;
        }
        hr { border: none; border-top: 1px solid #f3f4f6; margin: 22px 0; }
        .section-title {
            font-size: 15px; font-weight: 700; color: #1e3a8a; margin-bottom: 12px;
        }
        .cert-item { font-size: 14px; color: #374151; margin: 6px 0; }

        /* Booking form */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
        .form-group input {
            padding: 10px 12px; border: 1.5px solid #e5e7eb;
            border-radius: 8px; font-size: 14px; background: #f9fafb;
            outline: none; transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: #2c4a9a; background: white; }
        .submit-btn {
            width: 100%; margin-top: 14px; padding: 12px;
            font-size: 15px; font-weight: 600; border-radius: 8px;
        }

        /* Action buttons */
        .action-btn {
            display: block; text-align: center; padding: 11px;
            border-radius: 8px; font-weight: 600;
            text-decoration: none; font-size: 14px;
            transition: background 0.2s; margin-top: 10px;
        }
        .action-btn.chat    { background: #f4f6fb; color: #2c4a9a; }
        .action-btn.chat:hover { background: #e6ecf7; }
        .action-btn.trial   { background: #fef3c7; color: #92400e; }
        .action-btn.trial:hover { background: #fde68a; }

        .login-notice {
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 8px; padding: 14px 16px;
            font-size: 14px; color: #1e40af; text-align: center;
        }
        .login-notice a { color: #1e3a8a; font-weight: 700; }

        /* Ratings */
        .avg-rating { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
        .stars-display { color: #fbbf24; font-size: 18px; letter-spacing: 2px; }
        .avg-num { color: white; font-size: 15px; font-weight: 700; }
        .total-ratings { color: rgba(255,255,255,0.7); font-size: 13px; }

        .star-selector { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; margin: 8px 0; }
        .star-selector input { display: none; }
        .star-selector label { font-size: 28px; color: #d1d5db; cursor: pointer; transition: color 0.15s; }
        .star-selector label:hover,
        .star-selector label:hover ~ label,
        .star-selector input:checked ~ label { color: #fbbf24; }

        .review-input {
            width: 100%; padding: 10px 12px; border: 1.5px solid #e5e7eb;
            border-radius: 8px; font-size: 14px; background: #f9fafb;
            outline: none; resize: vertical; min-height: 70px;
            transition: border-color 0.2s; box-sizing: border-box;
        }
        .review-input:focus { border-color: #2c4a9a; background: white; }
        .rate-btn {
            margin-top: 10px; padding: 10px 24px;
            background: #fbbf24; color: #111; border: none;
            border-radius: 8px; font-weight: 700; font-size: 14px;
            cursor: pointer; display: inline-block; width: auto;
        }
        .rate-btn:hover { background: #f59e0b; }

        .review-card {
            background: #f9fafb; border-radius: 10px;
            padding: 14px 16px; margin-bottom: 10px;
        }
        .review-card .reviewer { font-weight: 700; font-size: 14px; color: #111827; }
        .review-card .review-stars { color: #fbbf24; font-size: 15px; }
        .review-card .review-text { font-size: 14px; color: #555; margin-top: 4px; }
        .review-card .review-date { font-size: 12px; color: #9ca3af; margin-top: 4px; }
        .success-toast {
            background: #d1fae5; color: #065f46; padding: 12px 16px;
            border-radius: 8px; font-weight: 600; font-size: 14px; margin-bottom: 16px;
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="profile-wrapper">
    <a href="pages/tutors.php" class="back-link">← Back to Tutors</a>

    <!-- Banner -->
    <div class="profile-banner">
        <div class="profile-avatar">
            <?php if ($tutor_img): ?>
                <img src="images/<?= $tutor_img ?>" alt="<?= htmlspecialchars($tutor['name']) ?>">
            <?php else: ?>
                <?= strtoupper(substr($tutor['name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="profile-banner-info">
            <h2><?= htmlspecialchars($tutor['name']) ?></h2>
            <span class="skill-tag"><?= htmlspecialchars($tutor['skill_name']) ?></span>
            <?php if ($avg_rating): ?>
                <div class="avg-rating">
                    <span class="stars-display"><?= str_repeat('★', round($avg_rating)) ?><?= str_repeat('☆', 5 - round($avg_rating)) ?></span>
                    <span class="avg-num"><?= $avg_rating ?></span>
                    <span class="total-ratings">(<?= $total_ratings ?> review<?= $total_ratings !== 1 ? 's' : '' ?>)</span>
                </div>
            <?php else: ?>
                <div style="color:rgba(255,255,255,0.6);font-size:13px;margin-top:6px;">No ratings yet</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Body -->
    <div class="profile-body">

        <!-- Info chips -->
        <div class="info-row">
            <div class="info-item">
                <strong>Level</strong>
                <span><?= ucfirst(htmlspecialchars($tutor['level'])) ?></span>
            </div>
            <div class="info-item">
                <strong>Age Group</strong>
                <span><?= ucfirst(htmlspecialchars($tutor['age_group'])) ?></span>
            </div>
            <div class="info-item">
                <strong>Skill</strong>
                <span><?= htmlspecialchars($tutor['skill_name']) ?></span>
            </div>
            <div class="info-item" style="background:<?= $cred_bg ?>;">
                <strong style="color:<?= $cred_color ?>;">Credibility</strong>
                <span style="color:<?= $cred_color ?>;"><?= $cred_icon ?> <?= $cred_score ?>/100</span>
            </div>
        </div>

        <!-- Description -->
        <?php if (!empty($tutor['description'])): ?>
            <div class="description">
                <?= htmlspecialchars($tutor['description']) ?>
            </div>
        <?php endif; ?>

        <!-- Certifications -->
        <div class="section-title">Certifications</div>
        <?php if (!empty($tutor['certificate'])): ?>
            <?php
            $cert_file = $tutor['certificate'];
            $cert_path = 'uploads/certificates/' . $cert_file;
            $ext       = strtolower(pathinfo($cert_file, PATHINFO_EXTENSION));
            ?>
            <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                <img src="<?= $cert_path ?>" alt="Certificate"
                     style="max-width:100%;border-radius:8px;border:1px solid #e5e7eb;margin-bottom:10px;">
            <?php else: ?>
                <a href="<?= $cert_path ?>" target="_blank"
                   style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;background:#f4f6fb;border-radius:8px;color:#1e3a8a;font-weight:600;text-decoration:none;font-size:14px;">
                    📄 View Certificate (PDF)
                </a>
            <?php endif; ?>
        <?php else: ?>
            <p class="cert-item">✔ Certified Trainer</p>
            <p class="cert-item">✔ 5+ Years Experience</p>
        <?php endif; ?>

        <hr>

        <!-- Booking + Chat + Trial Feedback -->
        <div class="section-title">Book a Demo Class</div>

        <?php if (isset($_SESSION['user_id'])): ?>

            <form method="POST" action="book_demo.php">
                <input type="hidden" name="tutor_id" value="<?= $tutor['id'] ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="session_date">Date</label>
                        <input type="date" id="session_date" name="session_date"
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="session_time">Time</label>
                        <input type="time" id="session_time" name="session_time" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Request Demo Session</button>
            </form>

            <a href="pages/chat.php?with=<?= $tutor['id'] ?>" class="action-btn chat">
                💬 Chat with <?= htmlspecialchars($tutor['name']) ?>
            </a>

            <?php if ($has_confirmed_booking): ?>
                <a href="pages/trial_feedback.php?booking_id=<?= $confirmed_booking_id ?>"
                   class="action-btn trial">
                    📝 Submit Trial Class Feedback
                </a>
            <?php endif; ?>

        <?php else: ?>
            <div class="login-notice">
                Please <a href="login.php">login</a> to book a demo class or chat.
            </div>
        <?php endif; ?>

        <hr>

        <!-- Ratings & Reviews -->
        <div class="section-title">Ratings & Reviews</div>

        <?php if (isset($_GET['rated'])): ?>
            <div class="success-toast">⭐ Your rating has been submitted!</div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'tutor'): ?>
            <div style="margin-bottom:20px;">
                <p style="font-size:14px;color:#374151;margin:0 0 8px;font-weight:600;">
                    <?= $user_existing_rating ? 'Update your rating' : 'Rate this tutor' ?>
                </p>
                <form method="POST" action="rate_tutor.php">
                    <input type="hidden" name="tutor_id" value="<?= $tutor['id'] ?>">
                    <div class="star-selector">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <input type="radio" name="rating" id="star<?= $s ?>" value="<?= $s ?>"
                                   <?= ($user_existing_rating['rating'] ?? 0) == $s ? 'checked' : '' ?> required>
                            <label for="star<?= $s ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <textarea name="review" class="review-input"
                              placeholder="Write a short review (optional)"><?= htmlspecialchars($user_existing_rating['review'] ?? '') ?></textarea>
                    <button type="submit" class="rate-btn">⭐ Submit Rating</button>
                </form>
            </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <p style="font-size:14px;color:#6b7280;"><a href="login.php" style="color:#2c4a9a;font-weight:600;">Login</a> to rate this tutor.</p>
        <?php endif; ?>

        <!-- All reviews -->
        <?php if ($total_ratings > 0): ?>
            <?php while ($rev = mysqli_fetch_assoc($reviews_result)): ?>
                <div class="review-card">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                        <span class="reviewer"><?= htmlspecialchars($rev['student_name']) ?></span>
                        <span class="review-stars"><?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5 - $rev['rating']) ?></span>
                    </div>
                    <?php if (!empty($rev['review'])): ?>
                        <p class="review-text"><?= htmlspecialchars($rev['review']) ?></p>
                    <?php endif; ?>
                    <p class="review-date"><?= date('d M Y', strtotime($rev['created_at'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="font-size:14px;color:#9ca3af;">No reviews yet. Be the first!</p>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
