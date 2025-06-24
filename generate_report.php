<?php
session_start();
require_once 'db_connect.php';

$department = $_POST['department'] ?? '';
$semester = $_POST['semester'] ?? '';

try {
    $query = "SELECT u.firstname, u.lastname, c.course_name, r.semester 
              FROM registrations r 
              JOIN users u ON r.username = u.username 
              JOIN courses c ON r.course_id = c.course_id 
              WHERE r.status = 'Approved'";
    $params = [];
    
    if ($department) {
        $query .= " AND (c.department = ? OR u.department = ?)";
        $params[] = $department;
        $params[] = $department;
    }
    if ($semester) {
        $query .= " AND r.semester = ?";
        $params[] = $semester;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header("Location: registrar.php#reports");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-4">Enrollment Report</h2>
        <?php if (empty($enrollments)): ?>
            <p>No enrollments found for the selected criteria.</p>
        <?php else: ?>
            <table class="w-full text-left border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-3">Student</th>
                        <th class="p-3">Course</th>
                        <th class="p-3">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr class="border-b">
                            <td class="p-3"><?php echo htmlspecialchars($enrollment['firstname'] . ' ' . $enrollment['lastname']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($enrollment['semester']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="registrar.php#reports" class="mt-4 inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Back to Dashboard</a>
    </div>
</body>
</html>