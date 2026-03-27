-- =============================================
-- SkillSync Full Database Setup
-- Run this in phpMyAdmin on your hosting panel
-- =============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'tutor') DEFAULT 'student',
    certificate VARCHAR(255) DEFAULT NULL,
    credibility_score INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    description TEXT,
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    age_group ENUM('kids', 'teens', 'adults') DEFAULT 'adults',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (tutor_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS learning_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    progress INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_skill (student_id, skill_name),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS demo_class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    instructor_id INT NOT NULL,
    booking_id INT DEFAULT NULL,
    student_feedback ENUM('Interested', 'Not Suitable', 'Need Different Level') DEFAULT NULL,
    teacher_feedback ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT NULL,
    recommendation VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (instructor_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

CREATE TABLE IF NOT EXISTS learning_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    instructor_id INT NOT NULL,
    learning_style ENUM('Visual', 'Practical', 'Theory') DEFAULT NULL,
    learning_pace  ENUM('Slow', 'Medium', 'Fast') DEFAULT NULL,
    teaching_style ENUM('Visual', 'Practical', 'Theory') DEFAULT NULL,
    teaching_pace  ENUM('Slow', 'Medium', 'Fast') DEFAULT NULL,
    compatibility_score INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pair (student_id, instructor_id),
    FOREIGN KEY (student_id)    REFERENCES users(id),
    FOREIGN KEY (instructor_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY one_rating_per_pair (student_id, tutor_id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (tutor_id)   REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_booking_attendance (booking_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (tutor_id)   REFERENCES users(id)
);

-- ── Seed 3 demo tutors (password: demo123) ──

INSERT IGNORE INTO users (name, email, password, role) VALUES
('Aniket Sharma', 'aniket@skillsync.com', '$2y$10$kCmcucaRM1ycYSRHx29LquMw6MVI8DKLet6uULTuPtSVjHTrJpB2i', 'tutor'),
('Rahul Verma',   'rahul@skillsync.com',  '$2y$10$kCmcucaRM1ycYSRHx29LquMw6MVI8DKLet6uULTuPtSVjHTrJpB2i', 'tutor'),
('Meera Nair',    'meera@skillsync.com',  '$2y$10$kCmcucaRM1ycYSRHx29LquMw6MVI8DKLet6uULTuPtSVjHTrJpB2i', 'tutor');

INSERT INTO skills (user_id, skill_name, description, level, age_group)
SELECT id, 'Dance',
    'Trained Bharatanatyam and contemporary dance instructor with 6 years of performance and teaching experience.',
    'intermediate', 'teens'
FROM users WHERE email = 'aniket@skillsync.com';

INSERT INTO skills (user_id, skill_name, description, level, age_group)
SELECT id, 'Guitar',
    'Acoustic and electric guitar coach specializing in Bollywood, classical, and rock styles. Beginner-friendly approach.',
    'beginner', 'kids'
FROM users WHERE email = 'rahul@skillsync.com';

INSERT INTO skills (user_id, skill_name, description, level, age_group)
SELECT id, 'Painting',
    'Fine arts graduate with expertise in watercolour, acrylic, and sketch. Teaches creative thinking alongside technical skills.',
    'advanced', 'adults'
FROM users WHERE email = 'meera@skillsync.com';
