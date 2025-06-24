<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department = $conn->real_escape_string($_POST['department']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $usertype = 'student';

    $sql = "INSERT INTO users (firstname, lastname, dob, email, phone, username, password, department, usertype, semester, registered_at) 
            VALUES ('$firstname', '$lastname', '$dob', '$email', '$phone', '$username', '$password', '$department', '$usertype', '$semester', NOW())";
    if ($conn->query($sql)) {
        header("Location: manage_students.php");
        exit();
    } else {
        $error = "Error adding student: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background: #3498db;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .return-btn {
            background: #e74c3c;
        }
        .return-btn:hover {
            background: #c0392b;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Add New Student</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="firstname" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lastname" required>
        </div>
        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="dob" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Department</label>
            <select name="department" required>
                <option value="">Select Department</option>
                <option value="Accounting">Accounting</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Marketing">Marketing</option>
                <option value="PE">PE</option>
                <option value="nursing">Nursing</option>
                <option value="pharmacist">Pharmacist</option>
            </select>
        </div>
        <div class="form-group">
            <label>Semester</label>
            <select name="semester" required>
                <option value="">Select Semester</option>
                <option value="Semester 1">Semester 1</option>
                <option value="Semester 2">Semester 2</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Add Student</button>
            <a href="manage_students.php" class="btn return-btn">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>