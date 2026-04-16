<?php
session_start();
if(!isset($_SESSION['loggedin'])){ header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MotorGuard Dashboard</title>
    <style>
        body{ margin:0; font-family:Arial; background:#0f172a; color:white; text-align: center;}
        .container{ max-width:500px; margin:auto; padding:20px; }
        .card{ background:#1e293b; padding:20px; border-radius:12px; margin-bottom:15px; border:1px solid #334155; }
        button{ width:100%; padding:14px; margin:8px 0; border:none; border-radius:10px; cursor:pointer; font-weight:bold; font-size:16px; transition: 0.2s; }
        .blue{background:#38bdf8;color:#0f172a;}
        h2{ color: #38bdf8; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🏍 MotorGuard</h2>
        <div class="card">
            <p>Vehicle Status: <b style="color:lime">ACTIVE</b></p>
        </div>
        <div class="card">
            <button class="blue" onclick="window.location.href='live_tracking.php'">📡 Live Tracking</button>
            <button class="blue" onclick="window.location.href='viewRoutehistory.php'">🛣 Route History</button>
        </div>
    </div>
</body>
</html>