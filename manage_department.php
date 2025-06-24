<?php
// studereg/manage_department.php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$successMessage = '';
$errorMessage = '';

// Handle department addition
if (isset($_POST['add_dept'])) {
    $dept_name = $conn->real_escape_string(trim($_POST['dept_name']));
    
    // Check for duplicate department
    $checkStmt = $conn->prepare("SELECT id FROM departments WHERE dept_name = ?");
    $checkStmt->bind_param("s", $dept_name);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $errorMessage = "Department '$dept_name' already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO departments (dept_name) VALUES (?)");
        $stmt->bind_param("s", $dept_name);
        if ($stmt->execute()) {
            $successMessage = "Department '$dept_name' added successfully!";
        } else {
            $errorMessage = "Failed to add department: " . $conn->error;
        }
        $stmt->close();
    }
    $checkStmt->close();
}

// Handle department deletion
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    // Check if department is used in courses or users
    $check = $conn->query("SELECT COUNT(*) as count FROM courses WHERE department = (SELECT dept_name FROM departments WHERE id = $delete_id)");
    $row = $check->fetch_assoc();
    if ($row['count'] > 0) {
        $errorMessage = "Cannot delete department: it is used in courses.";
    } else {
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $successMessage = "Department deleted successfully!";
        } else {
            $errorMessage = "Failed to delete department: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Departments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            font-family: 'Poppins', sans-serif;
            padding: 40px;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            margin: 50px auto;
            max-width: 800px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn {
            background: linear-gradient(45deg, #4CAF50, #388E3C);
            color: white;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: linear-gradient(45deg, #4CAF50, #388E3C);
            color: white;
        }
        tr:hover {
            background: #e0f7fa;
        }
        .alert {
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <div class="overlay">
        <h2 class="text-center mb-4"><i class="fas fa-building"></i> Manage Departments</h2>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Add Department Form -->
        <form method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" name="dept_name" class="form-control" placeholder="Department Name" required>
                <button type="submit" name="add_dept" class="btn"><i class="fas fa-plus"></i> Add Department</button>
            </div>
        </form>

        <!-- List of Departments -->
        <h5>Existing Departments</h5>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM departments");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>" . htmlspecialchars($row['dept_name']) . "</td>
                        <td>
                            <a href='?delete={$row['id']}' class='btn btn-danger' onclick=\"return confirm('Delete this department?')\"><i class='fas fa-trash'></i> Delete</a>
                        </td>
                    </tr>";
                }
                $result->free();
                ?>
            </tbody>
        </table>
        <a href="admin.php" class="btn mt-3 w-100"><i class="fas fa-arrow-left"></i> Return to Admin Panel</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>