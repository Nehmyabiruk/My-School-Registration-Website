<?php
// studereg/update_courses.php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['course_name'])) {
    foreach ($_POST['course_name'] as $course_id => $course_name) {
        $course_name = $conn->real_escape_string($course_name);
        $stmt = $conn->prepare("UPDATE courses SET course_name = ? WHERE course_id = ?");
        $stmt->bind_param("si", $course_name, $course_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: manage_courses.php");
exit();
?>