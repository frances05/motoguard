<?php
session_start();
$conn = new mysqli("localhost", "root", "", "motorguard");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = '';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['loggedin'] = true;
            $_SESSION['firstname'] = $user['firstname'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Email not registered!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotorGuard | Secure Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.6);
            --primary: #38bdf8;
            --danger: #ef4444;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-main);
            overflow: hidden;
        }

        .background {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
        }

        .shape {
            position: absolute;
            background: linear-gradient(45deg, var(--primary), transparent);
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.2;
            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from { transform: translate(0, 0); }
            to { transform: translate(100px, 100px); }
        }

        .login-box {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 40px 30px;
            border-radius: 28px;
            width: 100%;
            max-width: 350px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            z-index: 10;
        }

        .logo-container {
            margin-bottom: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2.logo-animation { 
            margin: 0; 
            font-size: 2.2rem;
            letter-spacing: -1px;
            font-weight: 800;
            background: linear-gradient(to right, #fff, var(--primary), #fff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 3s linear infinite, float 4s ease-in-out infinite;
            text-shadow: 0 0 20px rgba(56, 189, 248, 0.3);
        }

        @keyframes shine { to { background-position: 200% center; } }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group i.left-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.8);
            color: white;
            box-sizing: border-box;
            transition: all 0.3s ease;
            outline: none;
            font-size: 15px;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 1);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
        }

        /* --- FORGOT PASSWORD LINK START --- */
        .forgot-pass-container {
            text-align: right;
            margin-top: -8px;
            margin-bottom: 20px;
        }

        .btn-forgot {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-forgot:hover {
            color: var(--primary);
        }
        /* --- FORGOT PASSWORD LINK END --- */

        button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #0f172a;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
        }

        .error { 
            background: rgba(239, 68, 68, 0.15);
            color: #ffa3a3;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .footer-text {
            margin-top: 25px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .btn-secondary { color: var(--primary); text-decoration: none; font-weight: 700; }

        .shape:nth-child(1) { width: 200px; height: 200px; top: -50px; left: -50px; }
        .shape:nth-child(2) { width: 300px; height: 300px; bottom: -100px; right: -50px; animation-duration: 15s; }
    </style>
</head>
<body>

    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-box">
        <div class="logo-container">
            <h2 class="logo-animation">MotorGuard</h2>
        </div>
        <div class="subtitle">Enter your credentials to continue</div>

        <?php if($error) echo "<div class='error'><i class='fa-solid fa-circle-exclamation'></i> $error</div>"; ?>

        <form method="POST">
            <div class="input-group">
                <i class="fa-solid fa-envelope left-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock left-icon"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="fa-solid fa-eye toggle-password" id="eyeIcon" onclick="togglePass()"></i>
            </div>

            <div class="forgot-pass-container">
                <a href="forgotPass.php" class="btn-forgot">Forgot Password?</a>
            </div>

            <button name="login" type="submit">LOGIN TO SYSTEM</button>
        </form>

        <div class="footer-text">
            Don't have an account? 
            <a href="register.php" class="btn-secondary">Sign Up</a>
        </div>
    </div>

    <script>
        function togglePass() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>