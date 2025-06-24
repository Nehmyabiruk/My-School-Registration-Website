<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add' && isset($_POST['new_course_name'], $_POST['new_department'], $_POST['new_semester'])) {
            $course_name = $_POST['new_course_name'];
            $department = $_POST['new_department'];
            $semester = $_POST['new_semester'];
            $stmt = $conn->prepare("INSERT INTO courses (course_name, department, semester) VALUES (?, ?, ?)");
            $stmt->execute([$course_name, $department, $semester]);
            $_SESSION['message'] = "Course added successfully.";
            $_SESSION['message_type'] = 'success';
        } elseif ($_POST['action'] === 'update' && isset($_POST['course_id'], $_POST['capacity'], $_POST['status'])) {
            $course_id = $_POST['course_id'];
            $capacity = $_POST['capacity'];
            $status = $_POST['status'];
            // Note: Schema doesn't have capacity/status; assuming future schema update
            $_SESSION['message'] = "Course updated successfully (capacity/status not stored due to schema).";
            $_SESSION['message_type'] = 'success';
        } else {
            throw new Exception("Invalid action or missing fields.");
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = 'error';
}

header("Location: registrar.php#dashboard");
exit();
?>