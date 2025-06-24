<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_id'], $_POST['action'])) {
    try {
        $document_id = $_POST['document_id'];
        $action = $_POST['action'];
        $status = ($action === 'verify') ? 'Verified' : 'Rejected';

        $stmt = $conn->prepare("UPDATE documents SET status = ? WHERE id = ?");
        $stmt->execute([$status, $document_id]);

        $_SESSION['message'] = "Document $status successfully.";
        $_SESSION['message_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = 'error';
}

header("Location: registrar.php#verify-documents");
exit();
?>