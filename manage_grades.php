<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'registeral') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $grade_id = $_POST['grade_id'] ?? null;
        $username = $_POST['username'] ?? '';
        $course_name = $_POST['course_name'] ?? '';
        $semester = $_POST['semester'] ?? '';
        $grade = $_POST['grade'] ?? '';
        $credit_hour = $_POST['credit_hour'] ?? '';

        if ($action === 'update') {
            if (empty($username) || empty($course_name) || empty($semester) || empty($grade) || empty($credit_hour)) {
                throw new Exception("All fields are required.");
            }

            if ($grade_id) {
                // Update existing grade
                $stmt = $conn->prepare("UPDATE grades SET grade = ?, credit_hour = ?, status = 'Draft' WHERE id = ?");
                $stmt->bind_param("ssi", $grade, $credit_hour, $grade_id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['message'] = "Grade updated successfully.";
                $_SESSION['message_type'] = 'success';
            } else {
                // Add new grade
                $stmt = $conn->prepare("INSERT INTO grades (username, course_name, semester, grade, credit_hour, status) 
                                        VALUES (?, ?, ?, ?, ?, 'Draft')");
                $stmt->bind_param("sssss", $username, $course_name, $semester, $grade, $credit_hour);
                $stmt->execute();
                $stmt->close();
                $_SESSION['message'] = "Grade added successfully.";
                $_SESSION['message_type'] = 'success';
            }
        } elseif ($action === 'publish' && $grade_id) {
            // Publish grade
            $stmt = $conn->prepare("UPDATE grades SET status = 'Published' WHERE id = ?");
            $stmt->bind_param("i", $grade_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Grade published successfully.";
            $_SESSION['message_type'] = 'success';
        } else {
            throw new Exception("Invalid action.");
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
}

header("Location: registrar_dashboard.php#grade-management");
exit();
?>