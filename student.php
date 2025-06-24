<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$username = $_SESSION['username'];

// Fetch registration status
$regQuery = "SELECT COUNT(*) as count FROM registrations WHERE username = ?";
$stmt = $conn->prepare($regQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$regCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background: url('assets/bg.jpg') no-repeat center/cover;
            height: 300px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .overlay {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            margin: 50px auto;
            width: 80%;
            max-width: 1000px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: slideUp 1s ease-in;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .panel-button {
            position: relative;
            width: 200px;
            height: 150px;
            border: none;
            border-radius: 12px;
            background-size: cover;
            background-position: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .panel-button:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .badge-notification {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
        }
        .progress-widget {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    </header>

    <div class="overlay">
        <h2 class="text-center mb-4">Student Dashboard</h2>
        <p class="text-center text-muted">Manage your courses and grades</p>

        <!-- Progress Widget -->
        <div class="progress-widget">
            <h5><i class="fas fa-chart-line"></i> Course Registration Status</h5>
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($regCount / 10 * 100); ?>%;" aria-valuenow="<?php echo $regCount; ?>" aria-valuemin="0" aria-valuemax="10"></div>
            </div>
            <small class="text-muted">You have registered for <?php echo $regCount; ?> out of 10 possible courses.</small>
        </div>

        <div class="button-container">
            <button class="panel-button" style="background-image: url('assets/profile.jpg');" onclick="location.href='student_profile.php'">
                Profile
            </button>
            <button class="panel-button" style="background-image: url('assets/reg.jpg');" onclick="location.href='course_registration.php'">
                Course Registration
                <?php if ($regCount < 5): ?>
                    <span class="badge-notification"><?php echo 5 - $regCount; ?> left</span>
                <?php endif; ?>
            </button>
            <button class="panel-button" style="background-image: url('assets/view.jpg');" onclick="location.href='view_registration_status.php'">
                View Registration
            </button>
            <button class="panel-button" style="background-image: url('assets/book.jpg');" onclick="location.href='view_courses.php'">
                View Courses
            </button>
            <button class="panel-button" style="background-image: url('assets/grade.jpg');" onclick="location.href='view_grades.php'">
                View Grades
            </button>
            <button class="panel-button" style="background-image: url('assets/bg.jpg');" onclick="location.href='logout.php'">
                Logout
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate buttons on load
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.panel-button');
            buttons.forEach((button, index) => {
                button.style.animation = `fadeIn 0.5s ease-in ${index * 0.1}s forwards`;
                button.style.opacity = 0;
            });
        });
    </script>
</body>
</html>