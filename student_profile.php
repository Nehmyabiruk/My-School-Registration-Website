<?php
// student_profile.php
session_start();
require_once 'db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['username']) || $_SESSION['usertype'] !== 'student') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT username, firstname, email, department, dob, phone, profile_picture FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Student not found.";
    exit();
}

// Fetch course progress
$stmt = $conn->prepare("SELECT COUNT(*) as completed, (SELECT COUNT(*) FROM registrations WHERE username = :username) as total FROM grades WHERE username = :username AND grade != 'F'");
$stmt->execute(['username' => $username]);
$progress = $stmt->fetch(PDO::FETCH_ASSOC);
$progress_percent = $progress['total'] > 0 ? ($progress['completed'] / $progress['total']) * 100 : 0;

// Random motivational quote
$quotes = [
    "Keep pushing forward! 🚀 Your dreams are within reach.",
    "Every step you take is progress. Keep shining! 🌟",
    "Believe in yourself—you've got this! 💪",
    "Your hard work will pay off. Stay focused! 🎯"
];
$random_quote = $quotes[array_rand($quotes)];

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);

    try {
        $stmt = $conn->prepare("SELECT username FROM users WHERE email = :email AND username != :username");
        $stmt->execute(['email' => $email, 'username' => $username]);
        if ($stmt->fetch()) {
            $_SESSION['message'] = 'Email is already in use by another account.';
            $_SESSION['message_type'] = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message'] = 'Invalid email format.';
            $_SESSION['message_type'] = 'error';
        } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            $_SESSION['message'] = 'Phone number must be 10-15 digits.';
            $_SESSION['message_type'] = 'error';
        } else {
            $stmt = $conn->prepare("UPDATE users SET email = :email, phone = :phone WHERE username = :username");
            $stmt->execute(['email' => $email, 'phone' => $phone, 'username' => $username]);
            $_SESSION['message'] = 'Profile updated successfully!';
            $_SESSION['message_type'] = 'success';
            $stmt = $conn->prepare("SELECT username, firstname, email, department, dob, phone, profile_picture FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Database error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        error_log('Profile update error: ' . $e->getMessage());
    }
    header("Location: student_profile.php");
    exit();
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $target_dir = __DIR__ . "/Uploads/";
        $relative_dir = "Uploads/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $_SESSION['message'] = 'Failed to create upload directory.';
                $_SESSION['message_type'] = 'error';
                error_log('Failed to create directory: ' . $target_dir);
                header("Location: student_profile.php");
                exit();
            }
        }
        $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $target_file = $relative_dir . uniqid('img_', true) . '.' . $file_extension;
        $absolute_target_file = $target_dir . basename($target_file);
        $imageFileType = $file_extension;

        // Validate that the file is an image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        try {
            if ($check === false) {
                $_SESSION['message'] = 'File is not a valid image.';
                $_SESSION['message_type'] = 'error';
                error_log('Invalid image file: ' . $_FILES["profile_picture"]["name"]);
            } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $_SESSION['message'] = 'Invalid file type. Use JPG, PNG, or GIF.';
                $_SESSION['message_type'] = 'error';
                error_log('Invalid file type: ' . $imageFileType);
            } elseif ($_FILES["profile_picture"]["size"] > 5000000) {
                $_SESSION['message'] = 'File size exceeds 5MB limit.';
                $_SESSION['message_type'] = 'error';
                error_log('File size too large: ' . $_FILES["profile_picture"]["size"]);
            } elseif (!is_writable($target_dir)) {
                $_SESSION['message'] = 'Upload directory is not writable.';
                $_SESSION['message_type'] = 'error';
                error_log('Upload directory not writable: ' . $target_dir);
            } else {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $absolute_target_file)) {
                    chmod($absolute_target_file, 0644);
                    $stmt = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE username = :username");
                    $stmt->execute(['profile_picture' => $target_file, 'username' => $username]);
                    $_SESSION['message'] = 'Profile picture updated successfully!';
                    $_SESSION['message_type'] = 'success';
                    $_SESSION['new_image'] = $target_file;
                    $stmt = $conn->prepare("SELECT username, firstname, email, department, dob, phone, profile_picture FROM users WHERE username = :username");
                    $stmt->execute(['username' => $username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    error_log('Profile picture uploaded: ' . $target_file);
                } else {
                    $_SESSION['message'] = 'Failed to move uploaded file. Check server permissions.';
                    $_SESSION['message_type'] = 'error';
                    error_log('File upload failed: ' . $_FILES["profile_picture"]["error"]);
                }
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Database error: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
            error_log('Profile picture update error: ' . $e->getMessage());
        }
    } else {
        $error_msg = $_FILES['profile_picture']['error'] ?? 'No file selected';
        $_SESSION['message'] = 'No file selected or upload error.';
        $_SESSION['message_type'] = 'error';
        error_log('File upload error: ' . $error_msg);
    }
    header("Location: student_profile.php");
    exit();
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
$new_image = isset($_SESSION['new_image']) ? $_SESSION['new_image'] : '';
unset($_SESSION['message'], $_SESSION['message_type'], $_SESSION['new_image']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-confetti@latest/dist/js-confetti.browser.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a, #8b5cf6);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            position: relative;
        }
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .profile-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            perspective: 1000px;
            transition: box-shadow 0.3s;
        }
        .profile-card.flipped {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.8);
        }
        .card-inner {
            position: relative;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }
        .card-front, .card-back {
            backface-visibility: hidden;
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
        }
        .card-back {
            transform: rotateY(180deg);
        }
        .flipped .card-inner {
            transform: rotateY(180deg);
        }
        .profile-img {
            border: 5px solid #FFD700;
            transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s, opacity 0.3s;
            object-fit: cover;
            width: 200px;
            height: 200px;
            max-width: 100%;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
        }
        .profile-img.loading {
            opacity: 0.5;
            filter: blur(2px);
        }
        .profile-img:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 25px rgba(255, 215, 0, 0.8);
        }
        .profile-img.new-image {
            border-color: #10b981;
            animation: borderPulse 2s infinite;
        }
        .progress-bar {
            background: #4b5563;
            border-radius: 9999px;
            height: 10px;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, #10b981, #34d399);
            height: 100%;
            transition: width 1s ease-in-out;
        }
        .btn-neon {
            background: linear-gradient(45deg, #8b5cf6, #ec4899);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            transition: all 0.3s;
            box-shadow: 0 0 10px rgba(139, 92, 246, 0.5);
            position: relative;
        }
        .btn-neon:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.8);
        }
        .btn-neon.valid {
            animation: pulse 1.5s infinite;
        }
        .btn-neon:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .typewriter {
            overflow: hidden;
            white-space: nowrap;
            animation: typing 2s steps(40, end);
        }
        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 10px rgba(139, 92, 246, 0.5); }
            50% { box-shadow: 0 0 20px rgba(139, 92, 246, 1); }
            100% { box-shadow: 0 0 10px rgba(139, 92, 246, 0.5); }
        }
        @keyframes borderPulse {
            0% { border-color: #10b981; }
            50% { border-color: #34d399; }
            100% { border-color: #10b981; }
        }
        .success-check {
            display: none;
            font-size: 2rem;
            color: #10b981;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="flex justify-center items-center min-h-screen px-4">
        <div class="profile-card" id="profileCard">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <div class="card-inner" id="cardInner">
                <!-- Front Side -->
                <div class="card-front text-center text-white">
                    <img src="<?php echo htmlspecialchars($user['profile_picture'] ? $user['profile_picture'] : 'images/placeholder.jpg'); ?>" alt="Profile Picture" class="profile-img rounded-full mx-auto mb-4 <?php echo $new_image ? 'new-image' : ''; ?>" id="profileImg" onerror="this.src='images/placeholder.jpg'; console.error('Failed to load profile image:', this.src);">
                    <h2 class="text-3xl font-bold mb-2 text-yellow-400 typewriter">Welcome, <?php echo htmlspecialchars($user['username']); ?>! 👋</h2>
                    <p class="text-lg mb-4 italic"><?php echo htmlspecialchars($random_quote); ?></p>
                    <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($user['firstname']); ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="mb-2"><strong>Department:</strong> <?php echo htmlspecialchars($user['department']); ?></p>
                    <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p class="mb-4"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['dob']); ?></p>
                    <div class="mb-4">
                        <p class="mb-2"><strong>Course Progress:</strong></p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
                        </div>
                        <p class="text-sm mt-1"><?php echo round($progress_percent, 1); ?>% Complete</p>
                    </div>
                    <div class="flex justify-center gap-4 flex-wrap">
                        <button class="btn-neon" onclick="flipCard()">Edit Profile</button>
                        <button class="btn-neon" onclick="celebrate()">Celebrate 🎉</button>
                        <a href="student.php" class="btn-neon">Dashboard</a>
                        <a href="logout.php" class="btn-neon">Logout</a>
                    </div>
                </div>
                <!-- Back Side -->
                <div class="card-back text-center text-white">
                    <h3 class="text-2xl font-bold mb-4 text-yellow-400">Update Profile</h3>
                    <form id="pictureForm" action="student_profile.php" method="POST" enctype="multipart/form-data" class="mb-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Profile Picture</label>
                            <input type="file" name="profile_picture" accept="image/*" class="w-full p-2 border rounded bg-gray-800 text-white" onchange="previewImage(event)">
                        </div>
                        <div class="flex justify-center">
                            <button type="submit" name="upload_picture" class="btn-neon" id="uploadBtn">Upload Picture</button>
                        </div>
                    </form>
                    <form id="profileForm" action="student_profile.php" method="POST">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 border rounded bg-gray-800 text-white" required>
                            <p class="text-sm text-red-400 hidden" id="emailError">Invalid email format</p>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="w-full p-2 border rounded bg-gray-800 text-white" pattern="[0-9]{10,15}" title="Phone number should be 10-15 digits" required>
                            <p class="text-sm text-red-400 hidden" id="phoneError">Phone number must be 10-15 digits</p>
                        </div>
                        <div class="flex justify-center gap-4">
                            <button type="submit" name="update_profile" class="btn-neon" id="saveBtn">Save Changes</button>
                            <button type="button" class="btn-neon" onclick="flipCard()">Back</button>
                        </div>
                        <div class="success-check" id="successCheck">✔</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // GSAP Animations
        gsap.from(".profile-card", {
            opacity: 0,
            y: 100,
            duration: 1,
            ease: "power3.out"
        });
        gsap.from(".profile-img", {
            scale: 0,
            duration: 0.8,
            delay: 0.5,
            ease: "elastic.out(1, 0.3)"
        });

        <?php if ($new_image): ?>
        gsap.fromTo(".profile-img", {
            opacity: 0,
            scale: 0.8
        }, {
            opacity: 1,
            scale: 1,
            duration: 0.5,
            ease: "power2.out",
            onComplete: function() {
                console.log('New image animation completed');
            }
        });
        <?php endif; ?>

        // Particles.js
        particlesJS("particles-js", {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: "#FFD700" },
                shape: { type: "circle" },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: false },
                move: { enable: true, speed: 2, direction: "none", random: true }
            },
            interactivity: {
                detect_on: "canvas",
                events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" } },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
            }
        });

        // Confetti Celebration
        const jsConfetti = new JSConfetti();
        function celebrate() {
            jsConfetti.addConfetti({
                emojis: ['🎉', '🌟', '🚀'],
                confettiNumber: 100,
                confettiRadius: 6
            });
        }

        // Card Flip
        function flipCard() {
            try {
                const cardInner = document.getElementById('cardInner');
                const profileCard = document.getElementById('profileCard');
                cardInner.classList.toggle('flipped');
                profileCard.classList.toggle('flipped');
                console.log('Card flipped:', cardInner.classList.contains('flipped'));
            } catch (error) {
                console.error('Card flip error:', error);
            }
        }

        // Image Preview
        function previewImage(event) {
            const reader = new FileReader();
            const profileImg = document.getElementById('profileImg');
            profileImg.classList.add('loading');
            reader.onload = function() {
                profileImg.src = reader.result;
                profileImg.classList.remove('loading');
                console.log('Image preview set:', reader.result);
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Form Submission Handling
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            setTimeout(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';
            }, 2000);
        });

        document.getElementById('pictureForm').眨addEventListener('submit', function(e) {
            const uploadBtn = document.getElementById('uploadBtn');
            const profileImg = document.getElementById('profileImg');
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            profileImg.classList.add('loading');
            setTimeout(() => {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload Picture';
                profileImg.classList.remove('loading');
            }, 2000);
        });

        // Client-side Validation
        const profileForm = document.getElementById('profileForm');
        const emailInput = profileForm.querySelector('input[name="email"]');
        const phoneInput = profileForm.querySelector('input[name="phone"]');
        const saveBtn = document.getElementById('saveBtn');
        const emailError = document.getElementById('emailError');
        const phoneError = document.getElementById('phoneError');

        profileForm.addEventListener('input', function() {
            let isValid = true;

            if (!emailInput.validity.valid) {
                emailError.classList.remove('hidden');
                isValid = false;
            } else {
                emailError.classList.add('hidden');
            }

            if (!phoneInput.validity.valid) {
                phoneError.classList.remove('hidden');
                isValid = false;
            } else {
                phoneError.classList.add('hidden');
            }

            if (isValid) {
                saveBtn.classList.add('valid');
            } else {
                saveBtn.classList.remove('valid');
            }
        });

        // Easter Egg
        let clickCount = 0;
        document.getElementById('profileImg').addEventListener('click', () => {
            clickCount++;
            if (clickCount === 3) {
                gsap.to(".profile-img", {
                    rotation: 360,
                    scale: 1.3,
                    duration: 1,
                    ease: "bounce.out",
                    onComplete: () => {
                        gsap.to(".profile-img", { scale: 1, rotation: 0, duration: 0.5 });
                        alert("You found the Easter egg! 🥚 Keep shining!");
                    }
                });
                clickCount = 0;
            }
        });
    </script>
</body>
</html>