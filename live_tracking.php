<?php
session_start();
if(!isset($_SESSION['loggedin'])){ header("Location: login.php"); exit(); }
if(!isset($_SESSION['geofence'])){
    $_SESSION['geofence'] = ['lat' => 14.5995, 'lng' => 120.9842, 'radius' => 150, 'active' => false];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MotorGuard Live Tracking</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body{ margin:0; font-family:Segoe UI; background:#0f172a; color:white; overflow-x: hidden; }
        
        .header-bar {
            padding: 15px;
            background: #0f172a;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #334155;
        }
        .btn-back {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #38bdf8;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        #map{ height:55vh; width: 100%; transition: all 0.5s ease; }
        .leaflet-div-icon{ background:transparent !important; border:none !important; }
        
        .panel{ position:fixed; bottom:0; width:100%; background:rgba(30,41,59,0.95); padding:15px; box-sizing:border-box; border-top: 1px solid #334155; z-index: 1000; }
        #historyList{ max-height:80px; overflow-y:auto; font-size:12px; color:#94a3b8; margin-bottom:8px; border-top:1px solid rgba(255,255,255,0.1); padding-top:5px; }
        .range-box{ display:none; margin-bottom:8px; }
        .label{ display:flex; justify-content:space-between; font-size:12px; color:#94a3b8; }
        
        input[type=range]{ width:100%; -webkit-appearance:none; background:transparent; }
        input[type=range]::-webkit-slider-runnable-track{ height:6px; background:linear-gradient(to right, #38bdf8 var(--val,50%), rgba(255,255,255,0.1) var(--val,50%)); border-radius:10px; }
        input[type=range]::-webkit-slider-thumb{ -webkit-appearance:none; height:18px; width:18px; border-radius:50%; background:#38bdf8; margin-top:-6px; }

        .btn-row{ display:flex; gap:6px; margin-top:5px; }
        button{ flex:1; padding:12px; border:none; border-radius:10px; font-weight:bold; cursor:pointer; transition: 0.2s; }
        .blue{background:#38bdf8;color:#0f172a;} .red{background:#ef4444;color:white;} .gray{background:#334155;color:white;}
        
        #toast{ position:fixed; top:70px; left:50%; transform:translateX(-50%); background:#1e293b; border:1px solid #38bdf8; padding:10px 20px; border-radius:50px; z-index:9999; display:none; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from{opacity:0; top:50px;} to{opacity:1; top:70px;} }
    </style>
</head>
<body>
    <div id="toast"></div>

    <div class="header-bar">
        <a href="dashboard.php" class="btn-back">
            <i class='bx bx-chevron-left' style="font-size: 20px;"></i> BACK TO DASHBOARD
        </a>
    </div>

    <div id="map"></div>
    
    <div class="panel">
        <div style="font-size:12px; margin-bottom:5px;">
            Dist: <b id="geoDist">0 m</b> | Travel: <b id="travelDist">0 m</b>
        </div>
        <div id="historyList"></div>
        
        <div id="rangeBox" class="range-box">
            <div class="label"><span>Boundary Radius</span> <span><b id="rVal"><?= $_SESSION['geofence']['radius'] ?></b> m</span></div>
            <input type="range" id="radius" min="10" max="500" value="<?= $_SESSION['geofence']['radius'] ?>">
        </div>

        <div class="btn-row">
            <button class="blue" onclick="startTracking()">START TRACKING</button>
            <button class="red" onclick="stopTracking()">STOP TRACKING</button>
        </div>
        <div class="btn-row">
            <button class="blue" onclick="setGeofence()">SET GEOFENCE</button>
            <button id="killBtn" class="gray" onclick="restoreEngine()">KILL SWITCH / RESTORE</button>
            <button class="red" style="flex:0.3" onclick="removeGeofence()">REMOVE</button>
        </div>
    </div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let geo = <?= json_encode($_SESSION['geofence']) ?>;
    let map = L.map('map', { zoomControl: false }).setView([geo.lat, geo.lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let vehicle = L.marker([geo.lat, geo.lng], { 
        icon: L.divIcon({ html:"<div style='color:#38bdf8;font-size:24px;transform:rotate(-45deg);'>➤</div>", className:"" }) 
    }).addTo(map);

    let path = [], line = L.polyline([], {color:"#38bdf8", weight:5, opacity:0.8}).addTo(map);
    
    let tracking = false, circle = null, engineKilled = false;
    let lastLat = null, lastLng = null, totalDistance = 0, sessionStartTime = null;

    function showAlert(msg){ 
        let t=document.getElementById('toast'); 
        t.innerText=msg; 
        t.style.display='block'; 
        setTimeout(()=>t.style.display='none', 2500); 
    }

    function moveMap(lat, lng) {
        if (!tracking) return; // Don't auto-move if we aren't tracking
        if (!lastLat) {
            map.flyTo([lat, lng], 17, { duration: 1.2 });
        } else {
            if (map.distance([lastLat, lastLng], [lat, lng]) > 3) {
                map.panTo([lat, lng]);
            }
        }
    }

    function startTracking(){
        tracking = true; 
        path = []; 
        totalDistance = 0; 
        line.setLatLngs([]); // Clear previous line
        sessionStartTime = new Date().toLocaleString();
        showAlert("📍 Tracking Started");
        // Zoom in to focus on tracking
        if(lastLat) map.flyTo([lastLat, lastLng], 17, { duration: 1.5 });
    }

    function stopTracking(){
        if(tracking){
            if(path.length > 1){
                // Save trip to history
                let routes = JSON.parse(localStorage.getItem("routes") || "[]");
                routes.push({ date: sessionStartTime, distance: totalDistance, path: path });
                localStorage.setItem("routes", JSON.stringify(routes));
                
                // ANIMATION: Zoom out to show the entire traveled path
                let bounds = L.polyline(path).getBounds();
                map.flyToBounds(bounds, { 
                    padding: [50, 50], 
                    duration: 2.0 
                });
                
                showAlert("✅ Trip Saved & Overview Generated");
            } else {
                // Just zoom out slightly if no path was made
                map.flyTo([lastLat, lastLng], 14, { duration: 1.5 });
                showAlert("🛑 Tracking Stopped");
            }
        }
        tracking = false;
    }

    function updatePosition(lat, lng){
        vehicle.setLatLng([lat, lng]);
        
        if(tracking){
            moveMap(lat, lng);
            if(lastLat !== null){
                let d = map.distance([lastLat, lastLng], [lat, lng]);
                if(d > 5){
                    totalDistance += d;
                    document.getElementById("travelDist").innerText = totalDistance < 1000 ? Math.round(totalDistance)+" m" : (totalDistance/1000).toFixed(2)+" km";
                    path.push([lat, lng]);
                    line.setLatLngs(path);

                    let div = document.createElement("div"); 
                    div.innerHTML = `📍 ${lat.toFixed(5)}, ${lng.toFixed(5)} <span style="float:right">${new Date().toLocaleTimeString()}</span>`;
                    document.getElementById("historyList").prepend(div);
                }
            } else {
                path.push([lat, lng]);
            }
        }

        if(circle && geo.active){
            let dist = map.distance([lat, lng], [geo.lat, geo.lng]);
            document.getElementById("geoDist").innerText = dist < 1000 ? Math.round(dist)+" m" : (dist/1000).toFixed(2)+" km";
            if(dist > geo.radius) triggerKill();
        }

        lastLat = lat; lastLng = lng;
    }

    navigator.geolocation.watchPosition(pos => {
        updatePosition(pos.coords.latitude, pos.coords.longitude);
    }, null, {enableHighAccuracy:true});

    let range = document.getElementById("radius");
    function updateRangeUI(){ 
        let val = ((range.value - range.min)/(range.max - range.min)*100);
        range.style.setProperty('--val', val+'%'); 
    }
    
    range.oninput = function(){ 
        geo.radius = parseInt(this.value); 
        document.getElementById("rVal").innerText = geo.radius; 
        updateRangeUI(); 
        if(circle) circle.setRadius(geo.radius); 
    };

    function setGeofence(){ 
        geo.active = true; geo.lat = lastLat; geo.lng = lastLng; 
        drawCircle(); updateRangeUI(); showAlert("🛡️ Geofence Active"); 
    }
    
    function removeGeofence(){ 
        geo.active = false; if(circle) map.removeLayer(circle); 
        circle = null; document.getElementById("rangeBox").style.display="none"; 
        showAlert("🗑️ Geofence Removed"); 
    }

    function drawCircle(){ 
        if(circle) map.removeLayer(circle); 
        if(geo.active){ 
            circle = L.circle([geo.lat, geo.lng], {
                radius: geo.radius, 
                color: "#38bdf8", 
                fillColor: "#38bdf8",
                fillOpacity: 0.15 
            }).addTo(map); 
            document.getElementById("rangeBox").style.display = "block"; 
        } 
    }

    function triggerKill(){ 
        if(engineKilled) return; 
        engineKilled = true; 
        let b = document.getElementById("killBtn"); 
        b.classList.replace("gray","red"); 
        b.innerText = "RESTORE ENGINE"; 
        showAlert("⚠️ GEOFENCE BREACH!"); 
    }

    function restoreEngine(){ 
        engineKilled = false; 
        let b = document.getElementById("killBtn"); 
        b.classList.replace("red","gray"); 
        b.innerText = "KILL SWITCH / RESTORE"; 
        showAlert("✅ System Secure"); 
    }

    updateRangeUI(); if(geo.active) drawCircle();
</script>
</body>
</html>
