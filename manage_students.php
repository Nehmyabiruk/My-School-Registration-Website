<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle deletion
if (isset($_POST['delete_ids'])) {
    $ids = implode(",", array_map('intval', $_POST['delete_ids']));
    $conn->query("DELETE FROM users WHERE id IN ($ids) AND usertype='student'");
}

// Handle updates
if (isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $firstname = $conn->real_escape_string($_POST['edit_firstname']);
    $lastname = $conn->real_escape_string($_POST['edit_lastname']);
    $email = $conn->real_escape_string($_POST['edit_email']);
    $dept = $conn->real_escape_string($_POST['edit_dept']);
    $semester = $conn->real_escape_string($_POST['edit_semester']);
    $password = !empty($_POST['edit_password']) ? password_hash($_POST['edit_password'], PASSWORD_DEFAULT) : null;

    $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', email='$email', department='$dept', semester='$semester'";
    if ($password) {
        $sql .= ", password='$password'";
    }
    $sql .= " WHERE id=$id AND usertype='student'";
    $conn->query($sql);
}

$students = $conn->query("SELECT * FROM users WHERE usertype='student'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2, h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .actions, .filters {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .actions button, .filters select, .filters input, .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background: #3498db;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .actions button:hover, .btn:hover {
            background: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: #f9f9f9;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #3498db;
            color: white;
        }
        .edit-form input, .edit-form select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .edit-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .edit-actions button {
            padding: 10px 20px;
        }
        .return-btn {
            background: #e74c3c;
        }
        .return-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Students</h2>
    <form method="POST">
        <div class="actions">
            <button type="button" onclick="location.href='add_student.php'">Add New Student</button>
            <button type="submit">Delete Selected Student(s)</button>
            <a href="admin.php" class="btn return-btn">Return to Dashboard</a>
        </div>
        <div class="filters">
            <input type="text" placeholder="Search by name or ID..." id="searchInput" onkeyup="searchStudents()">
            <select id="departmentFilter" onchange="filterByDepartment()">
                <option value="">All Departments</option>
                <option value="Accounting">Accounting</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Marketing">Marketing</option>
                <option value="PE">PE</option>
                <option value="nursing">Nursing</option>
                <option value="pharmacist">Pharmacist</option>
            </select>
        </div>

        <table id="studentsTable">
            <thead>
            <tr>
                <th>Select</th>
                <th>Student ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Semester</th>
            </tr>
            </thead>
            <tbody>
            <?php while($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" name="delete_ids[]" value="<?= $row['id'] ?>"></td>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['firstname']) ?></td>
                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </form>

    <h3>Edit Student Info</h3>
    <form class="edit-form" method="POST">
        <input type="text" name="edit_id" placeholder="Student ID" required>
        <input type="text" name="edit_firstname" placeholder="First Name" required>
        <input type="text" name="edit_lastname" placeholder="Last Name" required>
        <input type="email" name="edit_email" placeholder="Email" required>
        <select name="edit_dept" required>
            <option value="">Select Department</option>
            <option value="Accounting">Accounting</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Marketing">Marketing</option>
            <option value="PE">PE</option>
            <option value="nursing">Nursing</option>
            <option value="pharmacist">Pharmacist</option>
        </select>
        <select name="edit_semester" required>
            <option value="">Select Semester</option>
            <option value="Semester 1">Semester 1</option>
            <option value="Semester 2">Semester 2</option>
        </select>
        <input type="password" name="edit_password" placeholder="New Password (optional)">
        <div class="edit-actions">
            <button type="submit">Save Changes</button>
            <button type="reset">Cancel</button>
        </div>
    </form>
</div>
<script>
function searchStudents() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let table = document.getElementById('studentsTable');
    let tr = table.getElementsByTagName('tr');
    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let match = td[1].textContent.toLowerCase().includes(input) ||
                    td[2].textContent.toLowerCase().includes(input) ||
                    td[3].textContent.toLowerCase().includes(input);
        tr[i].style.display = match ? '' : 'none';
    }
}
function filterByDepartment() {
    let filter = document.getElementById('departmentFilter').value.toLowerCase();
    let table = document.getElementById('studentsTable');
    let tr = table.getElementsByTagName('tr');
    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let dept = td[5].textContent.toLowerCase();
        tr[i].style.display = filter === '' || dept === filter ? '' : 'none';
    }
}
</script>
</body>
</html>