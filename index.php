
<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Student Registration</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome for social media icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        .container {
            position: relative;
            width: 100%;
            height: 100vh;
            background: linear-gradient(45deg, #ff4d4d, #ff8c66, #ffd700, #87ceeb, #ff4d4d);
            background-size: 400%;
            animation: gradientPulse 15s ease infinite;
            overflow: hidden;
        }

        @keyframes gradientPulse {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .sun {
            position: absolute;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, #ffd700 30%, #ffeb3b 70%, transparent 100%);
            border-radius: 50%;
            box-shadow: 0 0 50px rgba(255, 215, 0, 0.8);
            animation: sunGlow 3s ease-in-out infinite;
        }

        @keyframes sunGlow {
            0%, 100% { transform: translateX(-50%) scale(1); box-shadow: 0 0 50px rgba(255, 215, 0, 0.8); }
            50% { transform: translateX(-50%) scale(1.1); box-shadow: 0 0 70px rgba(255, 215, 0, 1); }
        }

        .clouds {
            position: absolute;
            top: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.7" d="M0,160 C150,100 300,220 450,160 S750,100 900,160 1200,220 1350,160 1440,100 1440,160 V320 H0 Z"/></svg>');
            background-size: cover;
            animation: moveClouds 10s linear infinite;
        }

        .clouds::before {
            content: '';
            position: absolute;
            top: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.4" d="M0,120 C200,60 400,180 600,120 S1000,60 1200,120 1440,180 1440,120 V320 H0 Z"/></svg>');
            background-size: cover;
            animation: moveClouds 15s linear infinite reverse;
        }

        @keyframes moveClouds {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .skyline {
            position: absolute;
            bottom: 25%;
            width: 100%;
            height: 150px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100"><path fill="url(%23skylineGrad)" d="M0,100 L1440,100 L1440,0 L0,0 Z M0,100 L80,70 L120,40 L160,80 L200,50 L240,90 L300,60 L360,30 L400,70 L460,40 L500,80 L560,50 L600,90 L660,60 L720,30 L780,70 L840,40 L900,80 L960,50 L1020,90 L1080,60 L1140,30 L1200,70 L1260,40 L1320,80 L1380,50 L1440,90 L1440,100 Z"/><defs><linearGradient id="skylineGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:%23111111;stop-opacity:1"/><stop offset="100%" style="stop-color:%23444444;stop-opacity:1"/></linearGradient></defs></svg>');
            background-size: cover;
            filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.6));
        }

.road {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 25%;
            background: linear-gradient(to bottom, rgba(50, 50, 50, 0.9), rgba(80, 80, 80, 0.9));
            box-shadow: inset 0 10px 30px rgba(255, 255, 255, 0.3);
            animation: roadShimmer 5s ease infinite;
        }

        @keyframes roadShimmer {
            0%, 100% { box-shadow: inset 0 10px 30px rgba(255, 255, 255, 0.3); }
            50% { box-shadow: inset 0 10px 50px rgba(255, 255, 255, 0.5); }
        }

        .road::before {
            content: '';
            position: absolute;
            top: 50%;
            width: 100%;
            height: 5px;
            background: repeating-linear-gradient(to right, #ffd700 0, #ffd700 30px, transparent 30px, transparent 60px);
        }

        .car {
            position: absolute;
            bottom: 12%;
            width: 200px;
            height: 100px;
            animation: moveCar 4s linear forwards;
            filter: drop-shadow(5px 5px 10px rgba(0, 0, 0, 0.5));
            z-index: 5;
        }

        .car:nth-child(2) {
            animation-delay: 0.5s;
        }

        .car:nth-child(3) {
            animation-delay: 1s;
        }

        .car-light {
            position: absolute;
            width: 60px;
            height: 20px;
            background: linear-gradient(to right, rgba(255, 255, 0, 0.9), rgba(255, 255, 255, 0.3));
            border-radius: 5px;
            top: -20px;
            left: 70px;
            filter: blur(4px);
            opacity: 1;
            transition: opacity 0.5s;
            z-index: 4;
        }

        @keyframes moveCar {
            0% { left: -250px; transform: rotate(0deg); }
            80% { left: 50%; }
            100% { left: 50%; transform: rotate(2deg); }
        }

        .overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            opacity: 0;
            transition: opacity 0.5s, transform 0.5s;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3), 0 0 20px rgba(255, 215, 0, 0.5);
            border: 2px solid transparent;
            animation: neonBorder 2s ease-in-out infinite;
            z-index: 10;
        }

        @keyframes neonBorder {
            0%, 100% { border-color: rgba(255, 215, 0, 0.5); box-shadow: 0 0 20px rgba(255, 215, 0, 0.5); }
            50% { border-color: rgba(255, 215, 0, 1); box-shadow: 0 0 30px rgba(255, 215, 0, 1); }
        }

        .overlay h1 {
            color: #fff;
            font-size: 3rem;
            text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.5);
            margin-bottom: 15px;
        }

        .overlay p {
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 25px;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.4);
        }

        .overlay .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(to right, #ffd700, #ffeb3b, #ffd700);
            background-size: 200%;
            color: #333;
            font-weight: bold;
            font-size: 1.2rem;
            text-decoration: none;
            border-radius: 10px;
            transition: background-position 0.3s, transform 0.3s;
            animation: btnGlow 2s ease-in-out infinite;
        }

        .overlay .btn:hover {
            background-position: 100%;
            transform: scale(1.1);
        }

        @keyframes btnGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(255, 215, 0, 0.5); }
            50% { box-shadow: 0 0 25px rgba(255, 215, 0, 1); }
        }

footer {
            background-color: #111;
            color: white;
            padding: 40px 20px;
            text-align: center;
            position: relative;
            bottom: 0;
            width: 100%;
            margin-top: 150px;
            z-index: 2;
        }

        footer h3 {
            margin-bottom: 10px;
            color: #FFD700;
        }

        footer p {
            max-width: 600px;
            margin: 0 auto 20px;
            line-height: 1.6;
        }

        .social-icons a {
            color: white;
            margin: 0 15px;
            transition: color 0.3s, transform 0.3s;
        }

        .social-icons a:hover {
            color: #FFD700;
            transform: scale(1.2);
        }
    </style>
</head>
<body class="home">
    <div class="container">
        <div class="sun"></div>
        <div class="clouds"></div>
        <div class="skyline"></div>
        <div class="road"></div>
        <div class="car">
            <svg viewBox="0 0 300 150" fill="none" stroke="#000" stroke-width="2">
                <path d="M30,110 H270 M30,110 Q50,60 90,60 H210 Q250,60 270,110" fill="#ffd700"/>
                <rect x="90" y="40" width="120" height="40" fill="#ffd700"/>
                <rect x="100" y="50" width="100" height="30" fill="#87ceeb"/>
                <circle cx="75" cy="120" r="15" fill="#333"/>
                <circle cx="225" cy="120" r="15" fill="#333"/>
                <circle cx="75" cy="120" r="8" fill="#666"/>
                <circle cx="225" cy="120" r="8" fill="#666"/>
                <path d="M270,80 Q280,70 270,60" fill="#ff4d4d"/>
                <rect x="30" y="80" width="10" height="20" fill="#ff4d4d"/>
            </svg>
            <div class="car-light"></div>
        </div>
        <div class="car">
            <svg viewBox="0 0 300 150" fill="none" stroke="#000" stroke-width="2">
                <path d="M30,110 H270 M30,110 Q50,60 90,60 H210 Q250,60 270,110" fill="#ffd700"/>
                <rect x="90" y="40" width="120" height="40" fill="#ffd700"/>
                <rect x="100" y="50" width="100" height="30" fill="#87ceeb"/>
                <circle cx="75" cy="120" r="15" fill="#333"/>
                <circle cx="225" cy="120" r="15" fill="#333"/>
                <circle cx="75" cy="120" r="8" fill="#666"/>
                <circle cx="225" cy="120" r="8" fill="#666"/>
                <path d="M270,80 Q280,70 270,60" fill="#ff4d4d"/>
                <rect x="30" y="80" width="10" height="20" fill="#ff4d4d"/>
            </svg>
            <div class="car-light"></div>
        </div>
        <div class="car">
            <svg viewBox="0 0 300 150" fill="none" stroke="#000" stroke-width="2">
                <path d="M30,110 H270 M30,110 Q50,60 90,60 H210 Q250,60 270,110" fill="#ffd700"/>
                <rect x="90" y="40" width="120" height="40" fill="#ffd700"/>
                <rect x="100" y="50" width="100" height="30" fill="#87ceeb"/>
                <circle cx="75" cy="120" r="15" fill="#333"/>
                <circle cx="225" cy="120" r="15" fill="#333"/>
                <circle cx="75" cy="120" r="8" fill="#666"/>
                <circle cx="225" cy="120" r="8" fill="#666"/>
                <path d="M270,80 Q280,70 270,60" fill="#ff4d4d"/>
                <rect x="30" y="80" width="10" height="20" fill="#ff4d4d"/>
            </svg>
            <div class="car-light"></div>
        </div>
        <div class="overlay">
            <h1>Welcome to Online Student Registration System</h1>
            <p>Register and manage your courses online with ease.</p>
            <a href="login.php" class="btn">Login</a>
        </div>
    </div>

brukti, [2025-05-04 5:11 AM]
<footer>
        <div>
            <h3><a href="aboutus.php" style="color: #FFD700; text-decoration: none;">About Us</a></h3>
            <p><a href="aboutus.php" style="color: white; text-decoration: underline;">Click here to learn more about our system and purpose.</a></p>
        </div>
        <div class="social-icons" style="margin-top: 20px;">
            <h3>Contact Us</h3>
            <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fab fa-facebook fa-2x"></i></a>
            <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fab fa-instagram fa-2x"></i></a>
            <a href="https://twitter.com" target="_blank" title="Twitter"><i class="fab fa-twitter fa-2x"></i></a>
        </div>
    </footer>

    <script>
        console.log("Cars initialized:", document.querySelectorAll('.car').length);
        const cars = document.querySelectorAll('.car');
        cars.forEach(car => {
            car.addEventListener('animationstart', () => {
                console.log("Car animation started:", car);
            });
            car.addEventListener('animationend', () => {
                console.log("Car animation ended:", car);
                car.querySelector('.car-light').style.opacity = '0';
                setTimeout(() => {
                    const overlay = document.querySelector('.overlay');
                    overlay.style.opacity = '1';
                    overlay.style.transform = 'translate(-50%, -50%) scale(1.02)';
                }, 500);
            });
        });
    </script>
</body>
</html>