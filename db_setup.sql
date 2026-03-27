-- Run this in your skillsync_db database

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
    progress INT DEFAULT 0 CHECK (progress >= 0 AND progress <= 100),
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

-- Add certificate column to users table (run if not already present)
ALTER TABLE users ADD COLUMN IF NOT EXISTS certificate VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
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

-- Add credibility score column to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS credibility_score INT DEFAULT 0;
