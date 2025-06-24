<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle student deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $username = $_POST['username'] ?? '';
        if (empty($username)) {
            throw new Exception("Username is required.");
        }

        // Delete student (cascading deletes handle grades and registrations)
        $stmt = $conn->prepare("DELETE FROM users WHERE username = ? AND usertype = 'student'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Student deleted successfully.";
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }

    header("Location: admin_reports.php");
    exit();
}

// Fetch students for deletion section
$sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.department, u.semester 
        FROM users u 
        WHERE u.usertype = 'student'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Reports Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .report-card {
            background: #f9f9f9;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .report-card h3 {
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .message-error {
            color: red;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message-success {
            color: green;
            padding: 10px;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn:hover {
            background: #45a049;
        }
        .delete-btn {
            background: #f44336;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Admin Reports Dashboard</h2>
        <a href="admin.php" class="btn">Back to Admin Panel</a>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type'] === 'success' ? 'message-success' : 'message-error'; ?>">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Student Deletion Section -->
        <div class="report-card">
            <h3>Manage Students</h3>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Username</th>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>Action</th>
                </tr>
                <?php if (empty($students)): ?>
                    <tr><td colspan="5">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['semester'] ?? 'N/A'); ?></td>
                            <td>
                                <form action="admin_reports.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($student['username']); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>

        <!-- Student Registration Statistics -->
        <div class="report-card">
            <h3>Student Registration Statistics</h3>
            <form class="filter-form" method="GET">
                <select name="semester">
                    <option value="">All Semesters</option>
                    <option value="Semester 1" <?php echo isset($_GET['semester']) && $_GET['semester'] === 'Semester 1' ? 'selected' : ''; ?>>Semester 1</option>
                    <option value="Semester 2" <?php echo isset($_GET['semester']) && $_GET['semester'] === 'Semester 2' ? 'selected' : ''; ?>>Semester 2</option>
                </select>
                <select name="department">
                    <option value="">All Departments</option>
                    <option value="Accounting" <?php echo isset($_GET['department']) && $_GET['department'] === 'Accounting' ? 'selected' : ''; ?>>Accounting</option>
                    <option value="Computer Science" <?php echo isset($_GET['department']) && $_GET['department'] === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                    <option value="Marketing" <?php echo isset($_GET['department']) && $_GET['department'] === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                    <option value="PE" <?php echo isset($_GET['department']) && $_GET['department'] === 'PE' ? 'selected' : ''; ?>>PE</option>
                    <option value="nursing" <?php echo isset($_GET['department']) && $_GET['department'] === 'nursing' ? 'selected' : ''; ?>>Nursing</option>
                    <option value="pharmacist" <?php echo isset($_GET['department']) && $_GET['department'] === 'pharmacist' ? 'selected' : ''; ?>>Pharmacist</option>
                </select>
                <button type="submit">Filter</button>
            </form>
            <?php
            $where = [];
            $params = [];
            $types = '';

            if (isset($_GET['semester']) && $_GET['semester'] != '') {
                $where[] = "semester = ?";
                $params[] = $_GET['semester'];
                $types .= 's';
            }
            if (isset($_GET['department']) && $_GET['department'] != '') {
                $where[] = "department = ?";
                $params[] = $_GET['department'];
                $types .= 's';
            }
            $where_clause = count($where) > 0 ? ' AND ' . implode(' AND ', $where) : '';

            $sql = "SELECT department, semester, COUNT(*) as student_count 
                    FROM users 
                    WHERE usertype = 'student' $where_clause 
                    GROUP BY department, semester";

            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $_SESSION['message'] = "Error preparing query: " . $conn->error;
                $_SESSION['message_type'] = 'error';
                error_log("SQL Error: " . $conn->error);
                echo "<p>Error preparing query.</p>";
            } else {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
            }
            ?>
            <table>
                <tr>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>Student Count</th>
                </tr>
                <?php if (isset($result) && $result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['department'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['semester'] ?: 'N/A'); ?></td>
                        <td><?php echo $row['student_count']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No student statistics available.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <?php if (isset($stmt)) $stmt->close(); ?>
        </div>

        <!-- Course Enrollment Report -->
        <div class="report-card">
            <h3>Course Enrollment Report</h3>
            <?php
            $sql = "SELECT c.course_name, c.department, COUNT(r.username) as enrolled_students
                    FROM courses c
                    LEFT JOIN registrations r ON c.course_id = r.course_id
                    GROUP BY c.course_name, c.department";
            $result = $conn->query($sql);
            ?>
            <table>
                <tr>
                    <th>Course Name</th>
                    <th>Department</th>
                    <th>Enrolled Students</th>
                </tr>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><?php echo $row['enrolled_students']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No enrollment data available.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Grade Distribution Report -->
        <div class="report-card">
            <h3>Grade Distribution Report</h3>
            <?php
            $sql = "SELECT course_name, grade, COUNT(*) as grade_count
                    FROM grades
                    WHERE status = 'Published'
                    GROUP BY course_name, grade
                    ORDER BY course_name, grade";
            $result = $conn->query($sql);
            ?>
            <table>
                <tr>
                    <th>Course Name</th>
                    <th>Grade</th>
                    <th>Number of Students</th>
                </tr>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['grade']); ?></td>
                        <td><?php echo $row['grade_count']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No grade data available.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>