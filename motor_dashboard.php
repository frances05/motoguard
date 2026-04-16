<?php
session_start();

// Simple session check (optional)
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MotorGuard Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f2027;
            color: white;
        }
        .container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
        }
        .card {
            background: #203a43;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        h2, h3 {
            margin: 0 0 10px;
        }
        .status {
            font-weight: bold;
            color: limegreen;
        }
        .btn {
            width: 100%;
            padding: 12px;
            margin: 5px 0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-blue { background: #007bff; color: white; }
        .btn-red { background: red; color: white; }
        @media(max-width:480px){
            h2 { font-size: 1.2rem; }
            h3 { font-size: 1rem; }
            .btn { font-size: 0.9rem; padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🏍 MotorGuard</h2>

        <div class="card">
            <h3>Status:</h3>
            <p class="status">🟢 SAFE</p>
            <p>Motor inside safe zone</p>
        </div>

        <div class="card">
            <button class="btn btn-blue" onclick="window.location.href='live_tracking.php'">View Live Tracking</button>
            <button class="btn btn-blue">View Route History</button>
        </div>

        <div class="card">
            <h3>Alerts:</h3>
            <p>No alerts</p>
        </div>
    </div>
</body>
</html>