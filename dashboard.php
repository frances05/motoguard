<?php
session_start();
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>MotorGuard Dashboard</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#0f172a;
    color:white;
}

.container{
    max-width:500px;
    margin:auto;
    padding:20px;
}

.card{
    background:#1e293b;
    padding:15px;
    border-radius:12px;
    margin-bottom:15px;
}

button{
    width:100%;
    padding:12px;
    margin:6px 0;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

.blue{background:#38bdf8;color:#0f172a;}
.red{background:#ef4444;color:white;}
</style>
</head>

<body>

<div class="container">

<h2>🏍 MotorGuard</h2>

<div class="card">
<p>Status: <b style="color:lime">SAFE</b></p>
</div>

<div class="card">

<!-- FIXED LINK -->
<button class="blue"
onclick="window.location.href='live_tracking.php'">
View Live Tracking
</button>

<!-- FIXED LINK (NO MORE set_geofence) -->
<button class="blue"
onclick="window.location.href='viewRouteHistory.php'">
🛣 View Route History
</button>

</div>

<div class="card">
<button class="red">Activate Kill Switch</button>
</div>

</div>

</body>
</html>