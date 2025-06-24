<!-- studereg/admin.php -->
<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="home">
    <div class="overlay">
        <h1>Welcome, Admin</h1>
        <p>Manage students, courses, and departments</p>

        <a href="manage_students.php" class="btn">Manage Students</a><br>
        <a href="manage_courses.php" class="btn">Manage Courses</a><br>
        <a href="manage_department.php" class="btn">Manage Departments</a><br>
        <a href="admin_reports.php" class="btn">View Reports</a><br>
        <a href="password_control.php" class="btn">Password Control</a><br>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</body>
</html>
