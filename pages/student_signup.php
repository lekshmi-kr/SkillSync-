<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sign Up - SkillSync</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .register-wrapper {
            min-height: calc(100vh - 70px);
            display: flex; align-items: center; justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #e6ecf7 0%, #f4f6fb 100%);
        }
        .register-card {
            background: white; border-radius: 16px;
            box-shadow: 0 8px 32px rgba(44,74,154,0.12);
            padding: 40px; width: 100%; max-width: 460px;
        }
        .card-header { text-align: center; margin-bottom: 28px; }
        .card-header h1 { font-size: 24px; color: #1e3a8a; margin: 0 0 6px; }
        .card-header p  { color: #6b7280; font-size: 14px; margin: 0; }
        .form-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
        .form-group input {
            padding: 10px 12px; border: 1.5px solid #e5e7eb;
            border-radius: 8px; font-size: 14px; background: #f9fafb;
            outline: none; transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: #2c4a9a;
            box-shadow: 0 0 0 3px rgba(44,74,154,0.1);
            background: white;
        }
        .submit-btn { width: 100%; padding: 12px; font-size: 15px; font-weight: 600; border-radius: 8px; margin-top: 4px; }
        .bottom-links { text-align: center; margin-top: 16px; font-size: 13px; color: #6b7280; }
        .bottom-links a { color: #2c4a9a; font-weight: 600; text-decoration: none; }
        .bottom-links a:hover { text-decoration: underline; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
    </style>
</head>
<body>
<?php $base = '../'; include '../includes/navbar.php'; ?>

<div class="register-wrapper">
    <div class="register-card">
        <div class="card-header">
            <h1>Create Student Account</h1>
            <p>Find and connect with the best tutors on SkillSync</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">
                <?php
                $msgs = [
                    'mismatch' => 'Passwords do not match.',
                    'exists'   => 'An account with this email already exists.',
                    'failed'   => 'Registration failed. Please try again.',
                ];
                echo $msgs[$_GET['error']] ?? 'Something went wrong.';
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../register_student.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Your full name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <div class="bottom-links">
            Already have an account? <a href="../login.php">Login</a>
            <br><br>
            Want to teach? <a href="signup.php">Become a Tutor</a>
        </div>
    </div>
</div>
</body>
</html>
