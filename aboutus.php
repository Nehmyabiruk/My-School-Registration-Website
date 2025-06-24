<!-- studereg/aboutus.php -->
<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>About Us</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,rgb(217, 210, 210), rgb(217, 210, 210));
            color: #333;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: 1px solid #ffd700;
        }

        h1, h2 {
            color: #333;
        }

        p {
            line-height: 1.8;
            font-size: 16px;
        }

        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
        }

        .feature {
            width: 280px;
            margin: 15px;
            background:rgb(198, 189, 189);
            border: 1px solid #ffd700;
            border-radius: 8px;
            padding: 20px;
            text-align: left;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .feature:hover {
            background: #f0fff0;
            transform: translateY(-5px);
        }

        .feature h3 {
            color: #333;
        }

        .images {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
            gap: 20px;
        }

        .images img {
            width: 300px;
            border-radius: 10px;
            border: 2px solid #ffd700;
            transition: transform 0.3s ease;
        }

        .images img:hover {
            transform: scale(1.05);
        }

        a.back-btn {
            display: inline-block;
            margin-top: 30px;
            color: white;
            background-color: #ffd700;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s ease, color 0.3s ease;
        }

        a.back-btn:hover {
            background-color: #333;
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>About Us</h1>
        <p>
            Welcome to the Online Student Registration System. Our platform provides an intuitive, fast, and secure way for students to manage their academic registration while giving university staff powerful tools to oversee the process.
        </p>

        <h2>Our Mission</h2>
        <p>
            To make education administration simpler, smarter, and more accessible. We believe students should spend less time in lines and more time learning.
        </p>

        <h2>Key Features</h2>
        <div class="features">
            <div class="feature">
                <h3>📚 Course Registration</h3>
                <p>Register for courses anytime, anywhere. View current and upcoming course options easily.</p>
            </div>
            <div class="feature">
                <h3>🧾 Grade Viewing</h3>
                <p>Students can track academic performance and download transcripts as needed.</p>
            </div>
            <div class="feature">
                <h3>📊 Reporting Tools</h3>
                <p>Admins can generate reports for departments, students, and performance trends.</p>
            </div>
            <div class="feature">
                <h3>🛡️ Secure Login</h3>
                <p>All accounts are protected with role-based access to ensure data privacy.</p>
            </div>
            <div class="feature">
                <h3>👥 Multi-User Roles</h3>
                <p>Admin, student, and registrar panels are tailored for each role’s responsibilities.</p>
            </div>
            <div class="feature">
                <h3>📄 Document Verification</h3>
                <p>Registrars can verify student credentials and manage document approval status.</p>
            </div>
        </div>

        <h2>Gallery</h2>
        <div class="images">
            <img src="assets/students.jpg" alt="Students working">
            <img src="assets/bb.png" alt="Admin Panel Example">
            <img src="assets/online-form.jpg" alt="Online Registration Form">
        </div>

        <a href="index.php" class="back-btn">← Back to Home</a>
    </div>
</body>
</html>
