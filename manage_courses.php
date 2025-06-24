<?php
// studereg/manage_courses.php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle course addition
if (isset($_POST['add_course'])) {
    $course_name = $conn->real_escape_string($_POST['course_name']);
    $department = $conn->real_escape_string($_POST['department']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $stmt = $conn->prepare("INSERT INTO courses (course_name, department, semester) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $course_name, $department, $semester);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_courses.php");
    exit();
}

// Fetch departments from the database
$dept_result = $conn->query("SELECT dept_name FROM departments");
$database_departments = [];
while ($row = $dept_result->fetch_assoc()) {
    $database_departments[] = $row['dept_name'];
}
$dept_result->free();

// Define hardcoded departments
$hardcoded_departments = [
    'Computer Science',
    'Accounting',
    'Marketing',
    'PE',
    'nursing',
    'pharmacist'
];

// Combine and remove duplicates, then sort
$all_departments = array_unique(array_merge($database_departments, $hardcoded_departments));
sort($all_departments);

$departmentFilter = $_GET['department'] ?? '';
$courses = [];

if ($departmentFilter) {
    $stmt = $conn->prepare("
        SELECT c.course_id, c.course_name, c.department, c.semester, COUNT(r.id) as student_count
        FROM courses c
        LEFT JOIN registrations r ON c.course_id = r.course_id
        WHERE c.department = ?
        GROUP BY c.course_id, c.course_name, c.department, c.semester
    ");
    $stmt->bind_param("s", $departmentFilter);
    $stmt->execute();
    $courses = $stmt->get_result();
    $stmt->close();
} else {
    $courses = $conn->query("
        SELECT c.course_id, c.course_name, c.department, c.semester, COUNT(r.id) as student_count
        FROM courses c
        LEFT JOIN registrations r ON c.course_id = r.course_id
        GROUP BY c.course_id, c.course_name, c.department, c.semester
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .management-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: #28a745;
            color: white;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="management-container">
        <h2 class="text-center mb-4">Manage Courses</h2>

        <!-- Add Course Form -->
        <form method="post" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="course_name" class="form-control" placeholder="Course Name" required>
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select" required>
                        <option value="">Select Department</option>
                        <?php foreach ($all_departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="semester" class="form-select" required>
                        <option value="">Select Semester</option>
                        <option value="Semester 1">Semester 1</option>
                        <option value="Semester 2">Semester 2</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_course" class="btn btn-custom w-100">Add Course</button>
                </div>
            </div>
        </form>

        <!-- Filter Form -->
        <form method="get" class="mb-4">
            <div class="row g-3">
                <div class="col-md-10">
                    <select name="department" class="form-select">
                        <option value="">-- All Departments --</option>
                        <?php foreach ($all_departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $departmentFilter == $dept ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <?php if ($courses && $courses->num_rows > 0): ?>
            <form method="post" action="update_courses.php">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Course Name</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Students</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><input type="text" name="course_name[<?php echo $row['course_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($row['course_name']); ?>"></td>
                                <td><?php echo htmlspecialchars($row['department']); ?></td>
                                <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                <td><?php echo $row['student_count']; ?></td>
                                <td>
                                    <a href="delete_course.php?id=<?php echo $row['course_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this course?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-custom mt-3">Save Changes</button>
                <a href="admin.php" class="btn btn-secondary mt-3">Return to Admin Panel</a>
            </form>
        <?php else: ?>
            <p class="text-center text-muted">No courses found.</p>
            <a href="admin.php" class="btn btn-secondary">Return to Admin Panel</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>