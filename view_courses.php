<?php
// studereg/view_courses.php
session_start();
include 'db.php'; // Ensure student_header.php is included if needed

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$errorMessage = '';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verify database connection
if ($conn->connect_error) {
    $errorMessage = "Database connection failed: " . $conn->connect_error;
}

// Fetch registered courses with course_name, department, semester, and registered_at
$query = "
    SELECT c.course_name, c.department, r.semester, r.registered_at
    FROM registrations r
    JOIN courses c ON r.course_id = c.course_id
    WHERE r.username = ?
    ORDER BY r.registered_at DESC, c.course_name
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $errorMessage = "Query preparation failed: " . $conn->error;
} else {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        $errorMessage = "Query execution failed: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registered Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            padding: 40px;
            overflow-x: hidden;
        }
        .table-container {
            max-width: 900px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .table th, .table td {
            vertical-align: middle;
            transition: background 0.3s;
        }
        .table tr:hover {
            background: #e0f7fa;
        }
        .btn-custom {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .alert {
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        .empty-state {
            text-align: center;
            padding: 20px;
        }
        .empty-state img {
            max-width: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <h2 class="text-center mb-4"><i class="fas fa-book"></i> My Registered Courses</h2>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if (!$errorMessage && $result): ?>
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Course Name</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['department']); ?></td>
                                <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                <td><?php echo htmlspecialchars($row['registered_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <img src="https://via.placeholder.com/200?text=No+Courses" alt="No courses">
                    <p class="text-muted">No courses registered yet. Head to <a href="course_registration.php">Course Registration</a> to enroll!</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">Unable to load courses. Please try again later.</div>
        <?php endif; ?>

        <a href="student.php" class="btn btn-custom w-100 mt-3"><i class="fas fa-arrow-left"></i> Return to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate table rows on load
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('.table tr');
            rows.forEach((row, index) => {
                row.style.animation = `fadeIn 0.5s ease-in ${index * 0.1}s forwards`;
                row.style.opacity = 0;
            });
        });
    </script>
</body>
</html>
<?php if ($result) $result->free(); $conn->close(); ?>