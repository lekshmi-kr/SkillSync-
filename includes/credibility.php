<?php
/**
 * Calculates and saves a tutor's credibility score (0–100).
 *
 * Breakdown:
 *   - Average rating (1–5 stars) → up to 40 pts
 *   - Certificate uploaded        → 30 pts
 *   - Demo completion rate        → up to 30 pts
 *
 * @param mysqli $conn
 * @param int    $tutor_id
 * @return int   Final score 0–100
 */
function calculate_credibility($conn, $tutor_id) {
    $score = 0;

    // 1. Rating score (max 40pts)
    $rq  = mysqli_query($conn, "SELECT AVG(rating) AS avg_r FROM ratings WHERE tutor_id='$tutor_id'");
    $rat = mysqli_fetch_assoc($rq);
    if ($rat['avg_r']) {
        // avg_r is 1–5, map to 0–40
        $score += round(($rat['avg_r'] / 5) * 40);
    }

    // 2. Certificate uploaded (30pts)
    $cq   = mysqli_query($conn, "SELECT certificate FROM users WHERE id='$tutor_id'");
    $cert = mysqli_fetch_assoc($cq);
    if (!empty($cert['certificate'])) {
        $score += 30;
    }

    // 3. Demo completion rate (max 30pts)
    // = confirmed bookings / total bookings requested
    $bq  = mysqli_query($conn, "SELECT
                                    COUNT(*) AS total,
                                    SUM(status = 'confirmed') AS confirmed
                                 FROM bookings WHERE tutor_id='$tutor_id'");
    $bk  = mysqli_fetch_assoc($bq);
    if ($bk['total'] > 0) {
        $rate  = $bk['confirmed'] / $bk['total'];
        $score += round($rate * 30);
    }

    $score = min(100, max(0, $score));

    // Save to DB
    mysqli_query($conn, "UPDATE users SET credibility_score='$score' WHERE id='$tutor_id'");

    return $score;
}

/**
 * Returns label + colors for a credibility score.
 */
function credibility_label($score) {
    if ($score >= 80) return ['Highly Credible',     '#065f46', '#d1fae5', '⭐'];
    if ($score >= 55) return ['Moderately Credible', '#92400e', '#fef3c7', '👍'];
    return                   ['Building Credibility','#1d4ed8', '#dbeafe', '📈'];
}
?>
