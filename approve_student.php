<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_id'], $_POST['action'])) {
    $registration_id = $_POST['registration_id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'Approved' : 'Denied';

    try {
        $stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $registration_id]);
        $_SESSION['message'] = "Registration $status successfully.";
        $_SESSION['message_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = 'error';
}

header("Location: registrar.php#approve-students");
exit();
?>