<?php
// studereg/view_registration_status.php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT course_name, registered_at FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Status</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="home">
    <div class="overlay">
        <h2>Your Registration Status</h2>
        <?php if ($result->num_rows > 0): ?>
            <ul style="text-align: left;">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>You are registered in: <strong><?= htmlspecialchars($row['course_name']) ?></strong> on <em><?= htmlspecialchars($row['registered_at']) ?></em></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No courses registered yet.</p>
        <?php endif; ?>

        <a href="student.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>
