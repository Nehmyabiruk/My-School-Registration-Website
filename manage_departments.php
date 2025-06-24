<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add' && isset($_POST['dept_name'])) {
            $dept_name = $_POST['dept_name'];
            $dept_code = $_POST['dept_code'] ?: null;
            $stmt = $conn->prepare("INSERT INTO departments (dept_name, dept_code) VALUES (?, ?)");
            $stmt->execute([$dept_name, $dept_code]);
            $_SESSION['message'] = "Department added successfully.";
            $_SESSION['message_type'] = 'success';
        } elseif ($_POST['action'] === 'update' && isset($_POST['dept_id'], $_POST['dept_name'])) {
            $dept_id = $_POST['dept_id'];
            $dept_name = $_POST['dept_name'];
            $dept_code = $_POST['dept_code'] ?: null;
            $stmt = $conn->prepare("UPDATE departments SET dept_name = ?, dept_code = ? WHERE id = ?");
            $stmt->execute([$dept_name, $dept_code, $dept_id]);
            $_SESSION['message'] = "Department updated successfully.";
            $_SESSION['message_type'] = 'success';
        } else {
            throw new Exception("Invalid action or missing fields.");
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = 'error';
}

header("Location: registrar.php#department-management");
exit();
?>