<?php
include "connect.php";

$id = intval($_GET['id']); // safer

$sql = "SELECT users.name, skills.skill_name, skills.description, skills.level, skills.age_group
        FROM skills
        JOIN users ON skills.user_id = users.id
        WHERE users.id = '$id' AND users.role = 'tutor'";

$result = mysqli_query($conn, $sql);

// safety check
if(mysqli_num_rows($result) > 0){
    $row = mysqli_fetch_assoc($result);
} else {
    echo "Tutor not found";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tutor Profile</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f7fb;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        hr {
            margin: 20px 0;
        }

        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            background: #2d3e87;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #1f2c5c;
        }
    </style>

</head>

<body>

<div class="container">

    <h2><?php echo $row['name']; ?></h2>

    <p><strong>Skill:</strong> <?php echo $row['skill_name']; ?></p>
    <p><strong>Level:</strong> <?php echo $row['level']; ?></p>
    <p><strong>Age Group:</strong> <?php echo $row['age_group']; ?></p>
    <p><strong>Description:</strong> <?php echo $row['description']; ?></p>

    <hr>

    <h3>Certifications</h3>
    <p>✔ Certified Trainer (Demo)</p>
    <p>✔ 5+ Years Experience</p>

    <hr>

    <h3>Request Demo Class</h3>

    <form action="request_demo.php" method="POST">

        <input type="text" name="student_name" placeholder="Your Name" required>

        <input type="date" name="date" required>

        <!-- hidden tutor id -->
        <input type="hidden" name="tutor_id" value="<?php echo $id; ?>">

        <button type="submit">Request</button>

    </form>

    <hr>

    <h3>Attendance Record</h3>
    <p>✔ 95% Attendance</p>

</div>

</body>
</html>