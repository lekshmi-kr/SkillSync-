<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Tutor - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .register-wrapper {
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #e6ecf7 0%, #f4f6fb 100%);
        }

        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(44, 74, 154, 0.12);
            padding: 40px;
            width: 100%;
            max-width: 560px;
        }

        .register-card .card-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .register-card .card-header h1 {
            font-size: 26px;
            color: #1e3a8a;
            margin: 0 0 6px;
        }

        .register-card .card-header p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            padding: 10px 12px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            background: #f9fafb;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #2c4a9a;
            box-shadow: 0 0 0 3px rgba(44, 74, 154, 0.1);
            background: white;
        }

        .form-group input[type="file"] {
            padding: 8px;
            cursor: pointer;
        }

        .divider {
            grid-column: 1 / -1;
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 4px 0;
        }

        .section-label {
            grid-column: 1 / -1;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin-bottom: -4px;
        }

        .submit-btn {
            grid-column: 1 / -1;
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            margin-top: 8px;
            display: block;
        }

        .login-link {
            grid-column: 1 / -1;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .login-link a {
            color: #2c4a9a;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="register-wrapper">
    <div class="register-card">
        <div class="card-header">
            <h1>Become a Tutor</h1>
            <p>Share your skills with learners on SkillSync</p>
        </div>

        <form class="form-grid" method="POST" action="../register.php" enctype="multipart/form-data">

            <span class="section-label">Personal Info</span>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" placeholder="City">
            </div>

            <div class="form-group">
                <label for="experience">Experience (years)</label>
                <input type="number" id="experience" name="experience" placeholder="e.g. 3" min="0">
            </div>

            <hr class="divider">
            <span class="section-label">Skill Details</span>

            <div class="form-group">
                <label for="category">Skill Category</label>
                <select id="category" name="category">
                    <option disabled selected>Select Category</option>
                    <option>Music</option>
                    <option>Dance</option>
                    <option>Art & Craft</option>
                    <option>Sports</option>
                    <option>Technology</option>
                    <option>Languages</option>
                    <option>Fitness & Yoga</option>
                    <option>Photography</option>
                    <option>Public Speaking</option>
                    <option>Design</option>
                    <option>Academics</option>
                    <option>Cooking</option>
                    <option>Handicrafts</option>
                    <option>Drama & Acting</option>
                    <option>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="skill">Specific Skill</label>
                <input type="text" id="skill" name="skill" placeholder="e.g. Guitar">
            </div>

            <hr class="divider">
            <span class="section-label">Account Security</span>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
            </div>

            <div class="form-group full-width">
                <label for="photo">Profile Photo</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>

            <hr class="divider">
            <span class="section-label">Verification</span>

            <div class="form-group full-width">
                <label for="certificate">Certificate / Qualification Proof</label>
                <input type="file" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png" required>
                <span style="font-size:12px;color:#9ca3af;margin-top:3px;">
                    Upload a degree, diploma, or any proof of qualification. PDF or image, max 2MB.
                </span>
            </div>

            <button type="submit" class="submit-btn">Create Tutor Account</button>

            <p class="login-link">Already have an account? <a href="../login.php">Login</a></p>

        </form>
    </div>
</div>

</body>
</html>
