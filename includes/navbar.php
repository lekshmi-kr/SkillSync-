<?php
// Pass $base = '../' from pages/, leave empty from root
$base = isset($base) ? $base : '';
?>
<header>
    <h1 class="logo">SkillSync</h1>
    <nav>
        <a href="<?= $base ?>index.php">Home</a>
        <a href="<?= $base ?>pages/tutors.php">Tutors</a>
        <a href="<?= $base ?>pages/signup.php">Become a Tutor</a>

        <?php if (isset($_SESSION['user'])): ?>
            <a href="<?= $base ?>pages/dashboard.php">Dashboard</a>
            <a href="<?= $base ?>logout.php">Logout (<?= htmlspecialchars($_SESSION['user']) ?>)</a>
        <?php else: ?>
            <a href="<?= $base ?>login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
