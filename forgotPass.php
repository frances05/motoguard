<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Siguraduhin na ang PHPMailer folder ay nasa loob ng CAPSTONE1
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$conn = new mysqli("localhost", "root", "", "motorguard");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
$isError = false;

if(isset($_POST['send_reset'])){
    $email = $_POST['email'];
    
    // 1. Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        // 2. Generate Token and Expiry (1 hour)
        $token = bin2hex(random_bytes(16)); 
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // 3. Save to Database
        $update = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // 4. Send Email via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'melanieocampo200519@gmail.com'; // ILAGAY MO DITO ANG GMAIL MO
            $mail->Password   = 'rhmizuulajywknaj';   // ITONG APP PASSWORD MO
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@motorguard.com', 'MotorGuard Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'MotorGuard - Password Reset Request';
            
            // Link to resetPass.php
            $resetLink = "http://localhost/CAPSTONE1/resetPass.php?token=$token";
            
            $mail->Body = "
                <div style='font-family: sans-serif; max-width: 400px; padding: 20px; border: 1px solid #334155; background: #0f172a; color: white; border-radius: 15px;'>
                    <h2 style='color: #38bdf8;'>MotorGuard Security</h2>
                    <p>Nakatanggap kami ng request na i-reset ang iyong password.</p>
                    <p>I-click ang button sa ibaba para mag-set ng bagong password:</p>
                    <a href='$resetLink' style='display: inline-block; padding: 12px 20px; background: #38bdf8; color: #0f172a; text-decoration: none; border-radius: 8px; font-weight: bold;'>RESET PASSWORD</a>
                    <p style='font-size: 11px; color: #94a3b8; margin-top: 20px;'>Mag-eexpire ang link na ito sa loob ng isang oras.</p>
                </div>";

            $mail->send();
            $message = "Reset link sent! Please check your email inbox or spam.";
            $isError = false;
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $isError = true;
        }
    } else {
        $message = "That email address is not registered in MotorGuard.";
        $isError = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotorGuard | Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --primary: #38bdf8;
            --text-main: #f1f5f9;
        }
        body {
            margin: 0; font-family: 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            height: 100vh; display: flex; justify-content: center; align-items: center; color: var(--text-main);
        }
        .box {
            background: var(--card-bg); backdrop-filter: blur(15px);
            padding: 40px 30px; border-radius: 28px; width: 100%; max-width: 350px;
            text-align: center; border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        h2 { color: var(--primary); margin-bottom: 10px; }
        p { font-size: 0.9rem; color: #94a3b8; margin-bottom: 25px; }
        input {
            width: 100%; padding: 14px; margin-bottom: 15px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(15,23,42,0.8);
            color: white; outline: none; box-sizing: border-box;
        }
        button {
            width: 100%; padding: 14px; background: var(--primary);
            color: #0f172a; border: none; border-radius: 12px;
            font-weight: 800; cursor: pointer; transition: 0.3s;
        }
        button:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3); }
        .msg { padding: 12px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid; }
        .success { background: rgba(34,197,94,0.1); color: #4ade80; border-color: rgba(34,197,94,0.2); }
        .error { background: rgba(239,68,68,0.1); color: #f87171; border-color: rgba(239,68,68,0.2); }
        .back-link { display: block; margin-top: 20px; color: var(--primary); text-decoration: none; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="box">
        <h2><i class="fa-solid fa-key"></i> Reset Help</h2>
        <p>Enter your email address and we'll send you a link to reset your password.</p>

        <?php if($message): ?>
            <div class="msg <?php echo $isError ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <button name="send_reset" type="submit">SEND RESET LINK</button>
        </form>

        <a href="login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
    </div>
</body>
</html>