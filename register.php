<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Online Student Registration</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function toggleDepartment() {
            var userType = document.getElementById("usertype").value;
            var departmentDropdown = document.getElementById("department");

            if (userType === "admin" || userType === "registeral") {
                departmentDropdown.style.display = "none";
            } else {
                departmentDropdown.style.display = "block";
            }
        }
    </script>
</head>
<style>
    /* Animated Background */
@keyframes moveBackground {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

body.login {
    background: linear-gradient(-45deg, #1e3c72, #2a5298, #ff7300, #f09);
    background-size: 400% 400%;
    animation: moveBackground 10s ease infinite;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: 'Poppins', sans-serif;
    color: white;
}

/* Registration Container */
.login-container {
    background: rgba(0, 0, 0, 0.8);
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    width: 380px;
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
    transition: all 0.4s ease-in-out;
}

/* Input Fields */
.login-container input,
.login-container select {
    width: 90%;
    padding: 12px;
    margin: 10px 0;
    border: 2px solid #FFD700;
    border-radius: 8px;
    background-color: #333;
    color: white;
    font-size: 16px;
    transition: all 0.3s ease-in-out;
}

.login-container input:focus,
.login-container select:focus {
    border-color: #FFA500;
    box-shadow: 0 0 10px rgba(255, 165, 0, 0.6);
    outline: none;
}

/* Buttons */
.btn {
    display: inline-block;
    padding: 12px 24px;
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: black;
    border-radius: 8px;
    font-weight: bold;
    box-shadow: 3px 3px 10px rgba(255, 215, 0, 0.4);
    transition: all 0.3s ease;
}

.btn:hover {
    background: linear-gradient(45deg, #FFA500, #FF4500);
    box-shadow: 0px 5px 15px rgba(255, 165, 0, 0.6);
    transform: scale(1.1);
}

/* Dropdown Styling */
.select-box {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 2px solid #FFD700;
    background: #333;
    color: white;
    font-size: 16px;
    box-shadow: 2px 2px 10px rgba(255, 215, 0, 0.3);
    transition: all 0.3s ease-in-out;
}

.select-box:hover,
.select-box:focus {
    border-color: #FFA500;
    box-shadow: 0 0 10px rgba(255, 165, 0, 0.6);
    outline: none;
}

.select-box option {
    background: #222;
    color: white;
    padding: 8px;
}

/* Links */
.link-btn {
    color: #FFD700;
    text-decoration: none;
    font-weight: bold;
}

.link-btn:hover {
    color: #FFA500;
}
</style>
<body class="login">
    <div class="login-container">
        <h2>Create an Account</h2>
        <form method="POST" action="process_register.php">
            <input type="text" name="firstname" placeholder="First Name" required><br>
            <input type="text" name="lastname" placeholder="Last Name" required><br>
            <input type="date" name="dob" placeholder="Date of Birth" required><br>
            <input type="email" name="email" placeholder="Email Address" required><br>
            <input type="tel" name="phone" placeholder="Phone Number" required><br>
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="password" name="confirmpassword" placeholder="Confirm Password" required><br>
                
           
            <select name="usertype" id="usertype" onchange="toggleDepartment()" required>
                <option value="">Select User Type</option>
                <option value="student">Student</option>
               
            </select><br>
           
           
           
           
           
           
           
           
           <?php
            // Fetch departments from the database
            $dept_result = $conn->query("SELECT dept_name FROM departments");
            $database_departments = [];
            while ($row = $dept_result->fetch_assoc()) {
                $database_departments[] = $row['dept_name'];
            }
            $dept_result->free();

            // Define hardcoded departments
            $hardcoded_departments = [
                'Accounting',
                'Computer Science',
                'Marketing'
            ];

            // Combine and remove duplicates, then sort
            $all_departments = array_unique(array_merge($database_departments, $hardcoded_departments));
            sort($all_departments);
            ?>

            <select name="department" id="department" required>
                <option value="">Select Department</option>
                <?php foreach ($all_departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                <?php endforeach; ?>
            </select><br>

        

            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php" class="link-btn">Login here</a></p>
    </div>
</body>
</html>
<?php $conn->close(); ?>