<?php
// studereg/course_registration.php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$successMessage = '';
$errorMessage = '';

// Fetch student details with fallback
$studentQuery = "SELECT department, semester FROM users WHERE username = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Ensure department and semester are set
$department = $student['department'] ?? '';
$currentSemester = $student['semester'] ?? 'Semester 1'; // Fallback to Semester 1 if not set
if (empty($department)) {
    $errorMessage = "Error: Your department is not set. Please contact the administrator.";
}

$selectedSemester = $_POST['semester'] ?? $currentSemester;
$availableCourses = [];
$registeredCourses = [];

// Fetch available courses for the selected semester and department
if ($selectedSemester && $department) {
    $courseQuery = "SELECT course_id, course_name FROM courses WHERE department = ? AND semester = ?";
    $stmt = $conn->prepare($courseQuery);
    $stmt->bind_param("ss", $department, $selectedSemester);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $availableCourses[] = $row;
    }
    $stmt->close();
    // Debug: Check if courses are fetched
    if (empty($availableCourses)) {
        $errorMessage = "No courses available for your department ($department) in $selectedSemester.";
    }
} else {
    $errorMessage = "Please select a semester to view available courses.";
}

// Fetch registered courses for the student in the selected semester
$regQuery = "SELECT course_id FROM registrations WHERE username = ? AND semester = ?";
$stmt = $conn->prepare($regQuery);
$stmt->bind_param("ss", $username, $selectedSemester);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $registeredCourses[] = $row['course_id'];
}
$stmt->close();

// Handle course registration
if (isset($_POST['register']) && !empty($_POST['courses'])) {
    $selectedCourses = $_POST['courses'];
    $stmt = $conn->prepare("INSERT INTO registrations (username, course_id, semester) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $username, $courseId, $selectedSemester);
    
    $registeredCount = 0;
    foreach ($selectedCourses as $courseId) {
        // Validate course_id exists in courses table
        $validateQuery = "SELECT course_id FROM courses WHERE course_id = ?";
        $validateStmt = $conn->prepare($validateQuery);
        $validateStmt->bind_param("i", $courseId);
        $validateStmt->execute();
        if ($validateStmt->get_result()->num_rows == 0) {
            $errorMessage = "Invalid course selected.";
            $validateStmt->close();
            continue;
        }
        $validateStmt->close();

        // Check for duplicate registration
        $checkQuery = "SELECT id FROM registrations WHERE username = ? AND course_id = ? AND semester = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("sis", $username, $courseId, $selectedSemester);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $errorMessage = "You are already registered for course ID $courseId.";
            $checkStmt->close();
            continue;
        }
        $checkStmt->close();

        // Register the course
        if ($stmt->execute()) {
            $registeredCount++;
        } else {
            $errorMessage = "Error registering course ID $courseId: " . $stmt->error;
        }
    }
    $stmt->close();
    
    if ($registeredCount > 0) {
        // Update user's semester
        $updateStmt = $conn->prepare("UPDATE users SET semester = ? WHERE username = ?");
        $updateStmt->bind_param("ss", $selectedSemester, $username);
        $updateStmt->execute();
        $updateStmt->close();
        $successMessage = "Successfully registered for $registeredCount course(s)!";
    } else if (empty($errorMessage)) {
        $errorMessage = "No new courses registered (possible duplicates or no valid courses selected).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        .registration-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-custom {
            background: linear-gradient(45deg, #28a745, #218838);
            color: white;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .course-card {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            transition: transform 0.3s, background 0.3s;
            cursor: pointer;
        }
        .course-card:hover {
            transform: scale(1.05);
            background: #e0f7fa;
        }
        .course-card input:checked + label {
            color: #28a745;
            font-weight: bold;
        }
        .progress-container {
            margin: 20px 0;
        }
        .alert {
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        .course-preview {
            display: none;
            position: absolute;
            background: #fff;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .course-card:hover .course-preview {
            display: block;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2 class="text-center mb-4"><i class="fas fa-book-open"></i> Course Registration</h2>
</head>
<body>
    <div class="stars"></div>
    <div class="moon"></div>
    <div class="registration-container">
        <h2 class="text-center mb-4"><i class="fas fa-book-open"></i> Course Registration</h2>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Progress Bar -->
        <div class="progress-container">
            <label>Registration Progress</label>
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo (count($registeredCourses) / (count($availableCourses) + count($registeredCourses) ?: 1) * 100); ?>%;" aria-valuenow="<?php echo count($registeredCourses); ?>" aria-valuemin="0" aria-valuemax="<?php echo count($availableCourses) + count($registeredCourses) ?: 1; ?>"></div>
            </div>
            <small class="text-muted"><?php echo count($registeredCourses); ?> of <?php echo count($availableCourses) + count($registeredCourses); ?> courses registered</small>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label for="semester" class="form-label"><i class="fas fa-calendar-alt"></i> Select Semester</label>
                <select name="semester" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- Choose Semester --</option>
                    <option value="Semester 1" <?php if ($selectedSemester == 'Semester 1') echo 'selected'; ?>>Semester 1</option>
                    <option value="Semester 2" <?php if ($selectedSemester == 'Semester 2') echo 'selected'; ?>>Semester 2</option>
                </select>
            </div>

            <?php if (!empty($availableCourses)): ?>
                <div class="mb-3">
                    <h5><i class="fas fa-list"></i> Available Courses</h5>
                    <?php foreach ($availableCourses as $course): ?>
                        <div class="course-card">
                            <input class="form-check-input" type="checkbox" name="courses[]" value="<?php echo $course['course_id']; ?>" id="course<?php echo $course['course_id']; ?>" <?php if (in_array($course['course_id'], $registeredCourses)) echo 'disabled checked'; ?>>
                            <label class="form-check-label" for="course<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                                <?php if (in_array($course['course_id'], $registeredCourses)): ?>
                                    <span class="badge bg-success ms-2">Registered</span>
                                <?php endif; ?>
                            </label>
                            <div class="course-preview">
                                <strong><?php echo htmlspecialchars($course['course_name']); ?></strong><br>
                                <small>Department: <?php echo htmlspecialchars($department); ?><br>Semester: <?php echo htmlspecialchars($selectedSemester); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="register" class="btn btn-custom w-100"><i class="fas fa-check-circle"></i> Register Courses</button>
            <?php else: ?>
                <p class="text-muted">No courses available for the selected semester.</p>
            <?php endif; ?>
        </form>
        <a href="student.php" class="btn btn-secondary w-100 mt-3"><i class="fas fa-arrow-left"></i> Return to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-confetti@0.11.0/dist/js-confetti.browser.js"></script>
    <script>
        // Confetti animation on successful registration
        <?php if ($successMessage): ?>
            const jsConfetti = new JSConfetti();
            jsConfetti.addConfetti({
                emojis: ['🎉', '📚', '⭐'],
                confettiNumber: 100,
                confettiRadius: 6,
            });
        <?php endif; ?>

        // Animate course cards on scroll
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.course-card');
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeIn 0.5s ease-in';
                    }
                });
            }, { threshold: 0.1 });
            cards.forEach(card => observer.observe(card));
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>