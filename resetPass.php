<?php
$conn = new mysqli("localhost", "root", "", "motorguard");

$message = '';
$isError = false;

// 1. I-check kung may token sa URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 2. Hanapin ang user na may ganitong token at i-check kung hindi pa expire
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $message = "Invalid or expired reset link.";
        $isError = true;
    }
} else {
    header("Location: login.php");
    exit();
}

// 3. Pag-update ng Password
if (isset($_POST['reset_password'])) {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match!";
        $isError = true;
    } else {
        // I-hash ang bagong password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // I-update ang DB at burahin ang token para hindi na maulit
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $hashedPassword, $token);
        
        if ($update->execute()) {
            echo "<script>alert('Password updated successfully!'); window.location.href='login.php';</script>";
            exit();
        } else {
            $message = "Something went wrong. Please try again.";
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
    <title>MotorGuard | Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-color: #0f172a; --card-bg: rgba(30, 41, 59, 0.6); --primary: #38bdf8; --text-main: #f1f5f9; --text-muted: #94a3b8; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #0f172a; height: 100vh; display: flex; justify-content: center; align-items: center; color: var(--text-main); }
        .login-box { background: var(--card-bg); backdrop-filter: blur(20px); padding: 40px 30px; border-radius: 28px; width: 100%; max-width: 350px; text-align: center; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
        h2 { margin: 0; font-size: 1.8rem; font-weight: 800; background: linear-gradient(to right, #fff, var(--primary), #fff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px; }
        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        input { width: 100%; padding: 14px 15px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(15, 23, 42, 0.8); color: white; box-sizing: border-box; outline: none; }
        button { width: 100%; padding: 14px; background: var(--primary); color: #0f172a; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3); }
        .error-msg { background: rgba(239, 68, 68, 0.15); color: #ffa3a3; padding: 10px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>New Password</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px;">Enter your new secure password below.</p>

        <?php if($message): ?>
            <div class="error-msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if(!$isError): ?>
        <form method="POST">
            <div class="input-group">
                <input type="password" name="password" placeholder="New Password" required>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" name="reset_password">UPDATE PASSWORD</button>
        </form>
        <?php else: ?>
            <a href="forgotPass.php" style="color: var(--primary); text-decoration: none;">Request new link</a>
        <?php endif; ?>
    </div>
</body>
</html>