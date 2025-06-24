<?php
// process_login.php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $usertype = $_POST['usertype'];
    $department = isset($_POST['department']) ? $_POST['department'] : '';

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND usertype = :usertype");
        $stmt->execute(['username' => $username, 'usertype' => $usertype]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($usertype === 'student' && $user['department'] !== $department) {
                $_SESSION['message'] = 'Invalid department for student.';
                $_SESSION['message_type'] = 'error';
                header("Location: login.php");
                exit();
            }

            $_SESSION['username'] = $username;
            $_SESSION['usertype'] = $usertype;

            if ($usertype === 'admin') {
                header("Location: admin.php");
            } elseif ($usertype === 'registeral') {
                header("Location: registrar.php");
            } elseif ($usertype === 'student') {
                header("Location: student.php");
            }
            exit();
        } else {
            $_SESSION['message'] = 'Invalid username, password, or user type.';
            $_SESSION['message_type'] = 'error';
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit();
    }
}
?>