<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'registeral') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all students for registrations
$sql = "SELECT u.id AS user_id, u.firstname, u.lastname, u.username, u.department, c.course_name, r.semester, r.registered_at 
        FROM users u 
        LEFT JOIN registrations r ON u.username = r.username 
        LEFT JOIN courses c ON r.course_id = c.course_id 
        WHERE u.usertype = 'student'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$registrations = [];
while ($row = $result->fetch_assoc()) {
    $registrations[] = $row;
}
$stmt->close();

// Fetch students for grade review
$sql = "SELECT u.id AS user_id, u.firstname, u.lastname, u.username, c.course_name, r.semester, g.grade, g.id AS grade_id, g.credit_hour, g.status 
        FROM users u 
        LEFT JOIN registrations r ON u.username = r.username 
        LEFT JOIN courses c ON r.course_id = c.course_id 
        LEFT JOIN grades g ON u.username = g.username AND c.course_name = g.course_name AND r.semester = g.semester 
        WHERE u.usertype = 'student'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$registered_students = [];
while ($row = $result->fetch_assoc()) {
    $registered_students[] = $row;
}
$stmt->close();

// Skip documents
$documents = [];

// Fetch students for student management
$sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.department, u.semester, COUNT(r.id) AS registration_count 
        FROM users u 
        LEFT JOIN registrations r ON u.username = r.username 
        WHERE u.usertype = 'student' 
        GROUP BY u.id, u.firstname, u.lastname, u.username, u.department, u.semester";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Fetch departments for course and department management from users and courses
$sql = "SELECT DISTINCT department AS dept_name FROM (
    SELECT department FROM users WHERE department IS NOT NULL AND department != ''
    UNION
    SELECT department FROM courses WHERE department IS NOT NULL AND department != ''
) AS combined_departments ORDER BY dept_name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}
$stmt->close();

// Fetch departments for reports
$sql = "SELECT DISTINCT department AS dept_name FROM courses ORDER BY dept_name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$departments_report = [];
while ($row = $result->fetch_assoc()) {
    $departments_report[] = $row;
}
$stmt->close();

// Check for messages
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: #f9f9f9;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h3 {
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
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-open {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            width: filled;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        .btn:hover {
            background: #45a049;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-btn {
            background: #2196F3;
            color: white;
        }
        .edit-btn:hover {
            background: #1976D2;
        }
        .publish-btn {
            background: #4CAF50;
            color: white;
        }
        .publish-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Registrar Dashboard</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Overview -->
        <div class="card">
            <h3>Overview</h3>
            <p>Manage student registrations, grades, academic records, and more efficiently.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="#approve-students" class="btn">View Registrations</a>
                <a href="#grade-management" class="btn">Manage Grades</a>
                <a href="#student-management" class="btn">Manage Students</a>
                <a href="#department-management" class="btn">Manage Departments</a>
                <a href="#reports" class="btn">View Reports</a>
                <a href="logout.php" class="btn" style="background: #f44336;">Logout</a>
            </div>
        </div>

        <!-- View Registrations Section -->
        <section id="approve-students" class="card">
            <h3>Student Registrations</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Username</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr><td colspan="6" class="text-center">No students found.</td></tr>
                    <?php else: ?>
                        <?php
                        $grouped_registrations = [];
                        foreach ($registrations as $reg) {
                            $key = $reg['username'];
                            if (!isset($grouped_registrations[$key])) {
                                $grouped_registrations[$key] = [
                                    'firstname' => $reg['firstname'],
                                    'lastname' => $reg['lastname'],
                                    'username' => $reg['username'],
                                    'department' => $reg['department'],
                                    'courses' => []
                                ];
                            }
                            if ($reg['course_name']) {
                                $grouped_registrations[$key]['courses'][] = [
                                    'course_name' => $reg['course_name'],
                                    'semester' => $reg['semester'],
                                    'registered_at' => $reg['registered_at']
                                ];
                            }
                        }
                        ?>
                        <?php foreach ($grouped_registrations as $reg): ?>
                            <?php if (empty($reg['courses'])): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reg['firstname'] . ' ' . $reg['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['username']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['department'] ?? 'N/A'); ?></td>
                                    <td>No registrations</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reg['courses'] as $index => $course): ?>
                                    <tr>
                                        <?php if ($index === 0): ?>
                                            <td rowspan="<?php echo count($reg['courses']); ?>">
                                                <?php echo htmlspecialchars($reg['firstname'] . ' ' . $reg['lastname']); ?>
                                            </td>
                                            <td rowspan="<?php echo count($reg['courses']); ?>">
                                                <?php echo htmlspecialchars($reg['username']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($reg['department'] ?? 'N/A'); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['semester']); ?></td>
                                        <td><?php echo htmlspecialchars($course['registered_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Grade Management Section -->
        <section id="grade-management" class="card">
            <h3>Grade Management</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Username</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Grade</th>
                        <th>Credit Hours</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registered_students)): ?>
                        <tr><td colspan="8" class="text-center">No students found.</td></tr>
                    <?php else: ?>
                        <?php
                        $grouped_students = [];
                        foreach ($registered_students as $student) {
                            $key = $student['username'];
                            if (!isset($grouped_students[$key])) {
                                $grouped_students[$key] = [
                                    'firstname' => $student['firstname'],
                                    'lastname' => $student['lastname'],
                                    'username' => $student['username'],
                                    'courses' => []
                                ];
                            }
                            if ($student['course_name']) {
                                $grouped_students[$key]['courses'][] = [
                                    'course_name' => $student['course_name'],
                                    'semester' => $student['semester'],
                                    'grade' => $student['grade'] ?? 'Not Assigned',
                                    'credit_hour' => $student['credit_hour'] ?? 'N/A',
                                    'grade_id' => $student['grade_id'] ?? null,
                                    'status' => $student['status'] ?? 'Draft'
                                ];
                            }
                        }
                        ?>
                        <?php foreach ($grouped_students as $student): ?>
                            <?php if (empty($student['courses'])): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                                    <td>No registrations</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($student['courses'] as $index => $course): ?>
                                    <tr>
                                        <?php if ($index === 0): ?>
                                            <td rowspan="<?php echo count($student['courses']); ?>">
                                                <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
                                            </td>
                                            <td rowspan="<?php echo count($student['courses']); ?>">
                                                <?php echo htmlspecialchars($student['username']); ?>
                                            </td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['semester']); ?></td>
                                        <td><?php echo htmlspecialchars($course['grade']); ?></td>
                                        <td><?php echo htmlspecialchars($course['credit_hour']); ?></td>
                                        <td><?php echo htmlspecialchars($course['status']); ?></td>
                                        <td>
                                            <button class="action-btn edit-btn" 
                                                    onclick="openGradeModal(<?php echo $course['grade_id'] ?? 'null'; ?>, '<?php echo addslashes($course['grade'] === 'Not Assigned' ? '' : $course['grade']); ?>', '<?php echo addslashes($student['username']); ?>', '<?php echo addslashes($course['course_name']); ?>', '<?php echo addslashes($course['semester']); ?>', '<?php echo addslashes($course['credit_hour'] === 'N/A' ? '' : $course['credit_hour']); ?>')">Edit/Add</button>
                                            <?php if ($course['grade_id'] && $course['status'] === 'Draft'): ?>
                                                <form action="manage_grades.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="grade_id" value="<?php echo $course['grade_id']; ?>">
                                                    <input type="hidden" name="action" value="publish">
                                                    <button type="submit" class="action-btn publish-btn">Publish</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Student Management Section -->
        <section id="student-management" class="card">
            <h3>Student Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Username</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Registrations</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="6" class="text-center">No students found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['semester'] ?? 'Not Assigned'); ?></td>
                                <td><?php echo $student['registration_count']; ?> course(s)</td>
                                <td>
                                    <button class="action-btn edit-btn" 
                                            onclick="openStudentModal(<?php echo $student['id']; ?>, '<?php echo addslashes($student['department'] ?? ''); ?>', '<?php echo addslashes($student['semester'] ?? ''); ?>')">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Department Management Section -->
        <section id="department-management" class="card">
            <h3>Departments</h3>
            <button class="btn" onclick="openModal('addDepartmentModal')">Add Department</button>
            <table>
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($departments)): ?>
                        <tr><td colspan="2" class="text-center">No departments found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['dept_name']); ?></td>
                                <td>
                                    <button class="action-btn edit-btn" 
                                            onclick="openDepartmentModal(<?php echo $dept['id'] ?? 0; ?>, '<?php echo addslashes($dept['dept_name']); ?>')">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Reports Section -->
        <section id="reports" class="card">
            <h3>Enrollment Reports</h3>
            <form action="generate_report.php" method="POST">
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Department</label>
                        <select name="department" class="w-full p-2 border rounded">
                            <option value="">All Departments</option>
                            <?php foreach ($departments_report as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['dept_name']); ?>">
                                    <?php echo htmlspecialchars($dept['dept_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Semester</label>
                        <select name="semester" class="w-full p-2 border rounded">
                            <option value="">All Semesters</option>
                            <option value="Semester 1">Semester 1</option>
                            <option value="Semester 2">Semester 2</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn">Generate Report</button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Course Management Modal -->
        <div id="courseModal" class="modal">
            <div class="modal-content">
                <h3>Manage Courses</h3>
                <form action="manage_courses.php" method="POST">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Action</label>
                        <select name="action" onchange="toggleCourseFields(this.value)" class="w-full p-2 border rounded" required>
                            <option value="add">Add Course</option>
                            <option value="update">Update Course</option>
                        </select>
                    </div>
                    <div id="addCourseFields">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Course Name</label>
                            <input type="text" name="course_name" class="w-full p-2 border rounded" placeholder="e.g., Introduction to Programming" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Department</label>
                            <select name="department" class="w-full p-2 border rounded" required>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept['dept_name']); ?>"><?php echo htmlspecialchars($dept['dept_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="updateCourseFields" style="display:none;">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Select Course</label>
                            <select name="course_id" class="w-full p-2 border rounded">
                                <?php
                                $sql = "SELECT course_id, course_name FROM courses";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($course = $result->fetch_assoc()): ?>
                                    <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                <?php endwhile; $stmt->close(); ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">New Course Name</label>
                            <input type="text" name="new_course_name" class="w-full p-2 border rounded" placeholder="e.g., Advanced Programming">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="btn" style="background: #6b7280;" onclick="closeModal('courseModal')">Cancel</button>
                        <button type="submit" class="btn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grade Edit Modal -->
        <div id="gradeModal" class="modal">
            <div class="modal-content">
                <h3>Edit/Add Grade</h3>
                <form action="manage_grades.php" method="POST">
                    <input type="hidden" name="grade_id" id="grade_id">
                    <input type="hidden" name="username" id="grade_username">
                    <input type="hidden" name="course_name" id="grade_course_name">
                    <input type="hidden" name="semester" id="grade_semester">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Grade</label>
                        <select name="grade" id="grade_select" class="w-full p-2 border rounded" required>
                            <option value="">Select Grade</option>
                            <option value="A+">A+</option>
                            <option value="A">A</option>
                            <option value="B+">B+</option>
                            <option value="B">B</option>
                            <option value="C+">C+</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="F">F</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Credit Hours</label>
                        <input type="number" name="credit_hour" id="grade_credit_hour" class="w-full p-2 border rounded" placeholder="e.g., 3" min="1" max="6" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="btn" style="background: #6b7280;" onclick="closeModal('gradeModal')">Cancel</button>
                        <button type="submit" name="action" value="update" class="btn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Student Edit Modal -->
        <div id="studentModal" class="modal">
            <div class="modal-content">
                <h3>Edit Student</h3>
                <form action="manage_student.php" method="POST">
                    <input type="hidden" name="student_id" id="student_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Department</label>
                        <select name="department" id="student_department" class="w-full p-2 border rounded" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['dept_name']); ?>"><?php echo htmlspecialchars($dept['dept_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Semester</label>
                        <select name="semester" id="student_semester" class="w-full p-2 border rounded">
                            <option value="">Not Assigned</option>
                            <option value="Semester 1">Semester 1</option>
                            <option value="Semester 2">Semester 2</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="btn" style="background: #6b7280;" onclick="closeModal('studentModal')">Cancel</button>
                        <button type="submit" class="btn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Department Modal -->
        <div id="addDepartmentModal" class="modal">
            <div class="modal-content">
                <h3>Add Department</h3>
                <form action="manage_department.php" method="POST">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Department Name</label>
                        <input type="text" name="dept_name" class="w-full p-2 border rounded" placeholder="e.g., Computer Science" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Department Code</label>
                        <input type="text" name="dept_code" class="w-full p-2 border rounded" placeholder="e.g., CS">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="btn" style="background: #6b7280;" onclick="closeModal('addDepartmentModal')">Cancel</button>
                        <button type="submit" name="action" value="add" class="btn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Department Modal -->
        <div id="departmentModal" class="modal">
            <div class="modal-content">
                <h3>Edit Department</h3>
                <form action="manage_department.php" method="POST">
                    <input type="hidden" name="dept_id" id="dept_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Department Name</label>
                        <input type="text" name="dept_name" id="dept_name" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Department Code</label>
                        <input type="text" name="dept_code" id="dept_code" class="w-full p-2 border rounded">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="btn" style="background: #6b7280;" onclick="closeModal('departmentModal')">Cancel</button>
                        <button type="submit" name="action" value="update" class="btn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openModal(modalId) {
                document.getElementById(modalId).classList.add('modal-open');
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.remove('modal-open');
            }

            function openGradeModal(gradeId, currentGrade, username, courseName, semester, creditHour) {
                document.getElementById('grade_id').value = gradeId || '';
                document.getElementById('grade_select').value = currentGrade || '';
                document.getElementById('grade_username').value = username;
                document.getElementById('grade_course_name').value = courseName;
                document.getElementById('grade_semester').value = semester;
                document.getElementById('grade_credit_hour').value = creditHour || '';
                openModal('gradeModal');
            }

            function openStudentModal(studentId, department, semester) {
                document.getElementById('student_id').value = studentId;
                document.getElementById('student_department').value = department;
                document.getElementById('student_semester').value = semester || '';
                openModal('studentModal');
            }

            function openDepartmentModal(deptId, deptName) {
                document.getElementById('dept_id').value = deptId;
                document.getElementById('dept_name').value = deptName;
                openModal('departmentModal');
            }

            function toggleCourseFields(action) {
                document.getElementById('updateCourseFields').style.display = action === 'update' ? 'block' : 'none';
                document.getElementById('addCourseFields').style.display = action === 'add' ? 'block' : 'none';
            }
        </script>
    </body>
</html>