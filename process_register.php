<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];
    $department = $_POST['department'];
    $usertype = $_POST['usertype'];

    // Check if passwords match
    if ($password != $confirmpassword) {
        die("Passwords do not match!");
    }

    // Check if the user is trying to register as admin or registeral
    if ($usertype == 'admin' || $usertype == 'registeral') {
        die("You cannot register as an Admin or Registeral. Please select a valid user type.");
    }

    // Hash the password before saving
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the database
    $sql = "INSERT INTO users (firstname, lastname, dob, email, phone, username, password, department, usertype)
            VALUES ('$firstname', '$lastname', '$dob', '$email', '$phone', '$username', '$hashed_password', '$department', '$usertype')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to login page after successful registration
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
