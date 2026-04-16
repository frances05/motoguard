<?php
// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "motorguard");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$isError = false;

// 2. Registration Logic
if(isset($_POST['register'])){
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure Hashing

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $message = "Email already registered!";
        $isError = true;
    } else {
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $firstname, $lastname, $email, $password);
        if($stmt->execute()){
            $message = "Registered successfully! You can now login.";
            $isError = false;
        } else {
            $message = "Registration failed!";
            $isError = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotorGuard | Create Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --primary: #38bdf8;
            --success: #22c55e;
            --danger: #ef4444;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-main);
            padding: 20px;
            overflow-x: hidden;
        }

        .box {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 40px 30px;
            border-radius: 28px;
            width: 100%;
            max-width: 380px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 { 
            margin: 0 0 10px 0; 
            font-size: 2rem;
            letter-spacing: -1px;
            background: linear-gradient(to right, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .input-row {
            display: flex;
            gap: 10px;
        }

        .field-container {
            position: relative;
            width: 100%;
            text-align: left;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            margin: 8px 0;
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
        }

        /* Eye Icon Position */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 25px;
            cursor: pointer;
            color: var(--text-muted);
            transition: 0.3s;
            z-index: 10;
        }

        .toggle-password:hover { color: var(--primary); }

        /* Strength Message */
        #pass-msg {
            font-size: 0.75rem;
            margin-bottom: 10px;
            min-height: 20px;
            transition: 0.3s;
        }

        button[name="register"] {
            width: 100%;
            padding: 15px;
            margin-top: 10px;
            background: var(--primary);
            color: #0f172a;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        button[name="register"]:disabled {
            background: #334155;
            cursor: not-allowed;
            opacity: 0.5;
            transform: none;
        }

        button[name="register"]:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
        }

        .msg { 
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid;
        }
        .msg-success { background: rgba(34, 197, 94, 0.15); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
        .msg-error { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }

        .footer-text {
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .btn-back {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>MotorGuard</h2>
    <div class="subtitle">Create an account to secure your ride</div>

    <?php if($message): ?>
        <div class="msg <?php echo $isError ? 'msg-error' : 'msg-success'; ?>">
            <i class="fa-solid <?php echo $isError ? 'fa-circle-xmark' : 'fa-circle-check'; ?>"></i> 
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="regForm">
        <div class="input-row">
            <input type="text" name="firstname" placeholder="First Name" required>
            <input type="text" name="lastname" placeholder="Last Name" required>
        </div>
        
        <input type="email" name="email" placeholder="Email Address" required>
        
        <div class="field-container">
            <input type="password" name="password" id="password" placeholder="Create Strong Password" onkeyup="checkStrength()" required>
            <i class="fa-solid fa-eye toggle-password" id="eyeIcon" onclick="togglePass()"></i>
        </div>
        
        <div id="pass-msg"></div>

        <button name="register" type="submit" id="regBtn" disabled>REGISTER SYSTEM</button>
    </form>

    <div class="footer-text">
        Already a member? 
        <a href="login.php" class="btn-back">Login here</a>
    </div>
</div>

<script>
// 1. Toggle Show/Hide Password
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

// 2. Strong Password Validation Logic
function checkStrength() {
    const password = document.getElementById('password').value;
    const msg = document.getElementById('pass-msg');
    const btn = document.getElementById('regBtn');
    
    // Requirement: At least 8 chars, 1 Uppercase, 1 Number
    const strongRegex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})");

    if (password.length === 0) {
        msg.innerHTML = "";
        btn.disabled = true;
        return;
    }

    if (strongRegex.test(password)) {
        msg.innerHTML = "<span style='color: #4ade80;'><i class='fa-solid fa-shield-check'></i> Strong Password</span>";
        btn.disabled = false;
    } else {
        msg.innerHTML = "<span style='color: #f87171;'><i class='fa-solid fa-triangle-exclamation'></i> Weak (Need 8+ chars, Capital, & Number)</span>";
        btn.disabled = true;
    }
}
</script>

</body>
</html>