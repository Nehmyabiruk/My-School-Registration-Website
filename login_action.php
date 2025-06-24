<?php
include 'db.php';
session_start();

$username = $_POST['username'];
$password = $_POST['password'];
$usertype = $_POST['usertype'];

// Check if department is set
$department = isset($_POST['department']) ? $_POST['department'] : '';

if ($usertype == 'admin') {
    // Admin login (no department needed)
    if ($username == 'admin' && $password == 'admin123') {
        $_SESSION['username'] = $username;
        $_SESSION['usertype'] = $usertype;
        header("Location: admin.php");
        exit();
    } else {
        echo "Invalid Admin credentials!";
    }
} elseif ($usertype == 'registeral') {
    // Registral login (no department needed)
    if ($username == 'registral' && $password == 'registral123') {
        $_SESSION['username'] = $username;
        $_SESSION['usertype'] = $usertype;
        header("Location: registrar.php");
        exit();
    } else {
        echo "Invalid Registral credentials!";
    }
} else {
    // Student login (department required)
    $sql = "SELECT * FROM users WHERE username='$username' AND department='$department' AND usertype='$usertype'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['usertype'] = $usertype;
            header("Location: student.php");
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Invalid login details!";
    }
}
?>