<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['department'], $_POST['semester'])) {
    try {
        $student_id = $_POST['student_id'];
        $department = $_POST['department'];
        $semester = $_POST['semester'] ?: null;
        $stmt = $conn->prepare("UPDATE users SET department = ?, semester = ? WHERE id = ?");
        $stmt->execute([$department, $semester, $student_id]);
        $_SESSION['message'] = "Student record updated successfully.";
        $_SESSION['message_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = 'error';
}

header("Location: registrar.php#student-management");
exit();
?>