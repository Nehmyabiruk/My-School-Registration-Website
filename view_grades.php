<?php
// view_grades.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

try {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $grades = [];
    $message = isset($_SESSION['grade_message']) ? $_SESSION['grade_message'] : '';
    unset($_SESSION['grade_message']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['semester'])) {
        $semester = $_POST['semester'];
        $stmt = $conn->prepare("SELECT course_name, grade, credit_hour 
                                FROM grades 
                                WHERE username = ? AND semester = ? AND status = 'Published'");
        $stmt->execute([$username, $semester]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grades</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #1e3a8a, #3b82f6);
            min-height: 100vh;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="flex flex-col font-sans text-gray-800 p-4">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-white mb-6">View Grades</h1>
        <div class="card p-6 rounded-lg shadow-lg fade-in">
            <?php if ($message): ?>
                <div class="message-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="message-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($studentInfo['username']); ?>" disabled class="w-full p-2 border rounded bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Student ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($studentInfo['id']); ?>" disabled class="w-full p-2 border rounded bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Semester</label>
                        <select name="semester" class="w-full p-2 border rounded" required>
                            <option value="">-- Select Semester --</option>
                            <option value="Semester 1">Semester 1</option>
                            <option value="Semester 2">Semester 2</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">View Grades</button>
            </form>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <?php if (empty($grades)): ?>
                    <p class="text-gray-600">No published grades found for the selected semester.</p>
                <?php else: ?>
                    <table class="w-full text-left border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3">Course</th>
                                <th class="p-3">Grade</th>
                                <th class="p-3">Credit Hour</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                                <tr class="border-b fade-in">
                                    <td class="p-3"><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($grade['grade']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($grade['credit_hour']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <form action="download_grades_pdf.php" method="POST" class="mt-4">
                        <input type="hidden" name="semester" value="<?php echo htmlspecialchars($semester); ?>">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($studentInfo['username']); ?>">
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($studentInfo['id']); ?>">
                        <?php foreach ($grades as $index => $grade): ?>
                            <input type="hidden" name="grades[<?php echo $index; ?>][course_name]" value="<?php echo htmlspecialchars($grade['course_name']); ?>">
                            <input type="hidden" name="grades[<?php echo $index; ?>][grade]" value="<?php echo htmlspecialchars($grade['grade']); ?>">
                            <input type="hidden" name="grades[<?php echo $index; ?>][credit_hour]" value="<?php echo htmlspecialchars($grade['credit_hour']); ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Download PDF</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <a href="student.php" class="mt-4 inline-block bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>