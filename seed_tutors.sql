-- Seed 3 demo tutors into skillsync_db
-- Password for all three: demo123
-- Run this in phpMyAdmin SQL tab on skillsync_db

-- Step 1: Insert tutor accounts
-- (Skip any that already exist)
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Aniket Sharma', 'aniket@skillsync.com', '$2y$10$kCmcucaRM1ycYSRHx29LquMw6MVI8DKLet6uULTuPtSVjHTrJpB2i', 'tutor'),
('Rahul Verma',   'rahul@skillsync.com',  '$2y$10$kCmcucaRM1ycYSRHx29LquMw6MVI8DKLet6uULTuPtSVjHTrJpB2i', 'tutor'),
('Meera Nair',    'meera@skillsync.com',  '$2y$10$kCmcucaRM1ycYSRHx29LquMw6MVI8DKLet6uULTuPtSVjHTrJpB2i', 'tutor');

-- Step 2: Insert skills using INSERT INTO ... SELECT (works in MySQL)
INSERT IGNORE INTO skills (user_id, skill_name, description, level, age_group)
SELECT id, 'Dance',
    'Trained Bharatanatyam and contemporary dance instructor with 6 years of performance and teaching experience.',
    'intermediate', 'teens'
FROM users WHERE email = 'aniket@skillsync.com';

INSERT IGNORE INTO skills (user_id, skill_name, description, level, age_group)
SELECT id, 'Guitar',
    'Acoustic and electric guitar coach specializing in Bollywood, classical, and rock styles. Beginner-friendly approach.',
    'beginner', 'kids'
FROM users WHERE email = 'rahul@skillsync.com';

INSERT IGNORE INTO skills (user_id, skill_name, description, level, age_group)
SELECT id, 'Painting',
    'Fine arts graduate with expertise in watercolour, acrylic, and sketch. Teaches creative thinking alongside technical skills.',
    'advanced', 'adults'
FROM users WHERE email = 'meera@skillsync.com';
