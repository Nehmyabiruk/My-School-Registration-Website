<?php
// login.php
require_once 'db_connect.php';
session_start();

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION ['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Online Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-control, .form-select {
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #9b59b6;
            box-shadow: 0 0 10px rgba(155, 89, 182, 0.3);
        }
        .btn {
            background: linear-gradient(45deg, #9b59b6, #71b7e6);
            color: white;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .link-btn {
            color: #9b59b6;
            text-decoration: none;
        }
        .link-btn:hover {
            text-decoration: underline;
        }
        .hidden { display: none; }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4"><i class="fas fa-user-lock"></i> Login</h2>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="process_login.php">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="mb-3">
                <select name="usertype" id="usertype" class="form-select" required>
                    <option value="">Select User Type</option>
                    <option value="admin">Admin</option>
                    <option value="student">Student</option>
                    <option value="registeral">Registrar</option>
                </select>
            </div>
            
            <div class="mb-3">
                <select name="department" id="department" class="form-select" required>
                    <option value="">Select Department</option>
                    <option value="Accounting">Accounting</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Marketing">Marketing</option>
                    <?php
                    $stmt = $conn->query("SELECT dept_name FROM departments WHERE dept_name NOT IN ('Accounting', 'Computer Science', 'Marketing')");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($row['dept_name']) . "'>" . htmlspecialchars($row['dept_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
          
            <button type="submit" class="btn w-100">Login</button>
        </form>
        <p class="text-center mt-3">Don't have an account? <a href="register.php" class="link-btn">Register here</a></p>
    </div>

    <script>
        document.getElementById('usertype').addEventListener('change', function() {
            const departmentSelect = document.getElementById('department');
            if (this.value === 'admin' || this.value === 'registeral') {
                departmentSelect.classList.add('hidden');
                departmentSelect.removeAttribute('required');
                departmentSelect.value = '';
            } else {
                departmentSelect.classList.remove('hidden');
                departmentSelect.setAttribute('required', 'required');
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn = null; ?>