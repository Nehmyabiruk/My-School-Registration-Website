<?php
// studereg/password_control.php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle password update
if (isset($_POST['update_id']) && isset($_POST['passwords'])) {
    $user_id = (int)$_POST['update_id'];
    $new_password = password_hash($_POST['passwords'][$user_id], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND usertype = 'student'");
    $stmt->bind_param("si", $new_password, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: password_control.php");
    exit();
}

$search = $_GET['search'] ?? '';
$department = $_GET['department'] ?? '';
$students = [];

$where = ["usertype = 'student'"];
$params = [];
$types = '';

if ($search) {
    $where[] = "(username LIKE ?)";
    $params[] = "%$search%";
    $types .= 's';
}
if ($department) {
    $where[] = "department = ?";
    $params[] = $department;
    $types .= 's';
}

$query = "SELECT id, username, department FROM users";
if ($where) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Control</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .btn { margin: 5px; padding: 8px 16px; text-decoration: none; color: white; background: #4CAF50; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid white; text-align: left; color: white; }
        th { background: #4CAF50; }
    </style>
</head>
<body class="home">
    <div class="overlay">
        <h2>Control Student Passwords</h2>

        <!-- Search Form -->
        <form method="get">
            <input type="text" name="search" placeholder="Search by student name" value="<?= htmlspecialchars($search) ?>">
            <select name="department">
                <option value="">All Departments</option>
                <option value="Computer Science" <?= $department == 'Computer Science' ? 'selected' : '' ?>>Computer Science</option>
                <option value="Accounting" <?= $department == 'Accounting' ? 'selected' : '' ?>>Accounting</option>
                <option value="Marketing" <?= $department == 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                <option value="PE" <?= $department == 'PE' ? 'selected' : '' ?>>PE</option>
                <option value="nursing" <?= $department == 'nursing' ? 'selected' : '' ?>>Nursing</option>
                <option value="pharmacist" <?= $department == 'pharmacist' ? 'selected' : '' ?>>Pharmacist</option>
            </select>
            <button type="submit" class="btn">Search</button>
        </form>

        <?php if ($students && $students->num_rows > 0): ?>
            <form method="post">
                <table>
                    <tr><th>ID</th><th>Name</th><th>Department</th><th>New Password</th><th>Action</th></tr>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><input type="password" name="passwords[<?= $row['id'] ?>]" required></td>
                            <td><button type="submit" name="update_id" value="<?= $row['id'] ?>" class="btn">Reset Password</button></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <a href="admin.php" class="btn">Return to Admin Panel</a>
            </form>
        <?php else: ?>
            <p>No students found.</p>
            <a href="admin.php" class="btn">Return to Admin Panel</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>