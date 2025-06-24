<?php
include "db.php";

// New password for registral
$new_password = "123"; // 👈 You can change this to anything you like
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Registral user type ID is 3 (as defined in your user_types table)
$username = "registral"; // Make sure this matches the username for the registral

// Check if the registral user exists
$check = $conn->prepare("SELECT id FROM users WHERE username = ? AND user_type_id = 3");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Update existing password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND user_type_id = 3");
    $stmt->bind_param("ss", $hashed_password, $username);
    if ($stmt->execute()) {
        echo "✅ Registral password updated successfully!";
    } else {
        echo "❌ Error updating password: " . $conn->error;
    }
    $stmt->close();
} else {
    // Insert new registral account if it doesn't exist
    $stmt = $conn->prepare("INSERT INTO users (username, password, user_type_id, department_id) VALUES (?, ?, 3, NULL)");
    $stmt->bind_param("ss", $username, $hashed_password);
    if ($stmt->execute()) {
        echo "✅ Registral account created successfully!";
    } else {
        echo "❌ Error creating account: " . $conn->error;
    }
    $stmt->close();
}

$check->close();
$conn->close();
?>
