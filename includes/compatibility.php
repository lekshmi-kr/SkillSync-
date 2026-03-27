<?php
/**
 * Calculates compatibility score (0-100) between student and instructor.
 *
 * @param string $learning_style  Visual | Practical | Theory
 * @param string $learning_pace   Slow | Medium | Fast
 * @param string $teaching_style  Visual | Practical | Theory
 * @param string $teaching_pace   Slow | Medium | Fast
 * @return int  Score between 0 and 100
 */
function calculate_compatibility($learning_style, $learning_pace, $teaching_style, $teaching_pace) {
    $score = 0;

    // Style match: exact = 50pts, mismatch = 20pts
    if ($learning_style === $teaching_style) {
        $score += 50;
    } else {
        $score += 20;
    }

    // Pace match: exact = 50pts, adjacent = 30pts, opposite = 10pts
    $pace_order = ['Slow' => 1, 'Medium' => 2, 'Fast' => 3];
    $diff = abs(($pace_order[$learning_pace] ?? 2) - ($pace_order[$teaching_pace] ?? 2));

    if ($diff === 0) {
        $score += 50;
    } elseif ($diff === 1) {
        $score += 30;
    } else {
        $score += 10;
    }

    return min(100, max(0, $score));
}

/**
 * Returns label and color for a given compatibility score.
 *
 * @param int $score
 * @return array [label, color, bg]
 */
function compatibility_label($score) {
    if ($score >= 80) return ['Highly Compatible',    '#065f46', '#d1fae5'];
    if ($score >= 60) return ['Moderately Compatible','#92400e', '#fef3c7'];
    return                   ['Low Compatibility',    '#991b1b', '#fee2e2'];
}
?>
