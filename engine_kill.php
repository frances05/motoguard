<?php
session_start();
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit();
}

// ACTION HANDLER: Dito naseset ang session kapag pinindot ang button
if(isset($_GET['engine'])){
    $_SESSION['engine_status'] = ($_GET['engine'] == "disable") ? "DISABLED" : "ENABLED";
    header("Location: engine_kill.php");
    exit();
}

// FIX: Check kung existing ang key, kung wala, default ay "ENABLED"
$status = isset($_SESSION['engine_status']) ? $_SESSION['engine_status'] : "ENABLED";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotorGuard | Kill Switch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --primary: #38bdf8;
            --danger: #ef4444;
            --success: #22c55e;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
        }

        /* Universal box-sizing para pantay ang buttons */
        * {
            box-sizing: border-box;
        }

        body { 
            margin: 0; 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            color: var(--text-main);
            display: flex; 
            justify-content: center; 
            align-items: center;
            min-height: 100vh;
            padding: 20px; 
        }

        .container { 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }

        .control-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 35px 25px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            width: 100%;
        }

        .status-icon-wrapper {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            transition: all 0.5s ease;
        }

        .status-enabled { 
            background: rgba(34, 197, 94, 0.1); 
            color: var(--success); 
            border: 2px solid var(--success);
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
        }

        .status-disabled { 
            background: rgba(239, 68, 68, 0.1); 
            color: var(--danger); 
            border: 2px solid var(--danger);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
        }

        h2 { margin: 0; font-size: 1.5rem; letter-spacing: -0.5px; }
        
        .status-label { 
            font-size: 0.85rem; 
            color: var(--text-muted); 
            margin-bottom: 30px; 
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 16px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 12px;
            text-decoration: none;
        }

        .btn-kill {
            background: linear-gradient(135deg, #b91c1c, #ef4444);
            color: white;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }
        .btn-kill:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .btn-start {
            background: linear-gradient(135deg, #15803d, #22c55e);
            color: white;
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
        }
        .btn-start:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .btn-back {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 15px;
        }
        .btn-back:hover { color: var(--text-main); background: rgba(255,255,255,0.05); }

        .blink { animation: blinker 1.5s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.5; } }
    </style>
</head>
<body>

<div class="container">
    <div class="control-card">
        <div class="status-icon-wrapper <?php echo $status == 'DISABLED' ? 'status-disabled blink' : 'status-enabled'; ?>">
            <i class="fa-solid <?php echo $status == 'DISABLED' ? 'fa-circle-xmark' : 'fa-bolt-lightning'; ?>"></i>
        </div>

        <h2>Engine <?php echo $status == "DISABLED" ? "Terminated" : "Running"; ?></h2>
        <div class="status-label">Current Status: <?php echo $status; ?></div>

        <?php if($status == "ENABLED"): ?>
            <a href="engine_kill.php?engine=disable" class="action-btn btn-kill">
                <i class="fa-solid fa-power-off"></i> ACTIVATE KILL SWITCH
            </a>
        <?php else: ?>
            <a href="engine_kill.php?engine=enable" class="action-btn btn-start">
                <i class="fa-solid fa-key"></i> RESTORE ENGINE POWER
            </a>
        <?php endif; ?>

        <a href="dashboard.php" class="action-btn btn-back">
            <i class="fa-solid fa-house"></i> Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>