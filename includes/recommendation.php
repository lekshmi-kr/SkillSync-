<?php
/**
 * Generates a recommendation based on student and teacher feedback.
 *
 * @param string $student_feedback  "Interested" | "Not Suitable" | "Need Different Level"
 * @param string $teacher_feedback  "Beginner" | "Intermediate" | "Advanced"
 * @return string  The recommendation text
 */
function generate_recommendation($student_feedback, $teacher_feedback) {

    // Primary decision: student's own feeling
    if ($student_feedback === 'Interested') {
        // Even if interested, flag a level mismatch so they can grow faster
        if ($teacher_feedback === 'Beginner') {
            return "Continue with same instructor. Consider starting from foundational topics.";
        } elseif ($teacher_feedback === 'Advanced') {
            return "Continue with same instructor. Student shows strong potential — move to advanced track.";
        } else {
            return "Continue with same instructor at current level.";
        }
    }

    if ($student_feedback === 'Not Suitable') {
        return "Suggest a different instructor with a different teaching style.";
    }

    if ($student_feedback === 'Need Different Level') {
        if ($teacher_feedback === 'Beginner') {
            return "Suggest a beginner-level instructor for foundational learning.";
        } elseif ($teacher_feedback === 'Advanced') {
            return "Suggest an advanced instructor to match student's high skill level.";
        } else {
            return "Suggest an intermediate-level instructor for better skill alignment.";
        }
    }

    // Fallback
    return "Please review feedback manually.";
}
?>
