







<?php
session_start();
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit();
}

if(!isset($_SESSION['geofence'])){
    $_SESSION['geofence'] = [
        'lat' => 14.5995,
        'lng' => 120.9842,
        'radius' => 150,
        'active' => false
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>MotorGuard Live Tracking</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
}

#map{height:60vh;}

.leaflet-div-icon{
    background:transparent !important;
    border:none !important;
}

.panel{
    position:fixed;
    bottom:0;
    width:100%;
    background:rgba(30,41,59,0.95);
    padding:10px;
    box-sizing:border-box;
}

#historyList{
    max-height:110px;
    overflow-y:auto;
    font-size:12px;
    color:#94a3b8;
    margin-bottom:8px;
}

.range-box{display:none;margin-bottom:8px;}

.label{
    display:flex;
    justify-content:space-between;
    font-size:12px;
    color:#94a3b8;
}

input[type=range]{
    width:100%;
}

.btn-row{
    display:flex;
    gap:6px;
    margin-top:6px;
}

button{
    flex:1;
    padding:10px;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.blue{background:#38bdf8;color:#0f172a;}
.red{background:#ef4444;color:white;}
.gray{background:#334155;color:white;}
</style>
</head>

<body>

<div id="map"></div>

<div class="panel">

<div style="font-size:12px;">
Distance: <b id="geoDist">0 m</b> |
Travel: <b id="travelDist">0 m</b>
</div>

<div id="historyList"></div>

<!-- RANGE -->
<div id="rangeBox" class="range-box">
<div class="label">
<span>Boundary Radius</span>
<span><b id="rVal"><?= $_SESSION['geofence']['radius'] ?></b> m</span>
</div>

<input type="range" id="radius" min="10" max="500"
value="<?= $_SESSION['geofence']['radius'] ?>">
</div>

<!-- START / STOP -->
<div class="btn-row">
<button class="blue" onclick="startTracking()">START TRACKING</button>
<button class="red" onclick="stopTracking()">STOP TRACKING</button>
</div>

<!-- GEO BUTTONS -->
<div class="btn-row">
<button class="blue" onclick="setGeofence()">SET GEOFENCE</button>
<button id="killBtn" class="gray" onclick="restoreEngine()">
DISABLED KILL SWITCH / RESTORE ENGINE
</button>
<button class="red" onclick="removeGeofence()">REMOVE</button>
</div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

let geo = <?= json_encode($_SESSION['geofence']) ?>;

let map = L.map('map').setView([geo.lat, geo.lng], 16);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

/* VEHICLE */
let vehicle = L.marker([geo.lat, geo.lng], {
    icon: L.divIcon({
        html:"<div style='color:#38bdf8;font-size:22px;'>➤</div>",
        className:""
    })
}).addTo(map);

/* ROUTE */
let path = [];
let line = L.polyline([], {color:"#38bdf8",weight:4}).addTo(map);

/* STATE */
let tracking = false;
let circle = null;
let engineKilled = false;

/* POSITION MEMORY */
let lastLat = null;
let lastLng = null;
let totalDistance = 0;

/* START TRACK */
function startTracking(){

    tracking = true;
    alert("📍 Tracking Started");

    navigator.geolocation.getCurrentPosition(pos=>{

        let lat = pos.coords.latitude;
        let lng = pos.coords.longitude;

        map.flyTo([lat,lng], 17, {duration:1.5});

        vehicle.setLatLng([lat,lng]);

        path = [[lat,lng]];
        line.setLatLngs(path);

        lastLat = lat;
        lastLng = lng;

    }, ()=>alert("GPS error"));
}

/* STOP TRACK */
function stopTracking(){

    tracking = false;
    alert("🛑 Tracking Stopped");

    map.flyTo(vehicle.getLatLng(), 14, {duration:1.5});
}

/* GPS LIVE */
navigator.geolocation.watchPosition(pos=>{

    if(!tracking) return;

    let lat = pos.coords.latitude;
    let lng = pos.coords.longitude;

    vehicle.setLatLng([lat,lng]);

    if(lastLat !== null){

        let d = map.distance([lastLat,lastLng],[lat,lng]);

        if(d > 2){

            totalDistance += d;

            path.push([lat,lng]);
            line.setLatLngs(path);

            document.getElementById("travelDist").innerText =
            totalDistance < 1000 ?
            Math.round(totalDistance)+" m" :
            (totalDistance/1000).toFixed(2)+" km";

            lastLat = lat;
            lastLng = lng;
        }
    }

    if(circle){
        let d = map.distance([lat,lng],[geo.lat,geo.lng]);

        if(d > geo.radius){
            triggerKill();
        }

        document.getElementById("geoDist").innerText =
        d < 1000 ? Math.round(d)+" m" : (d/1000).toFixed(2)+" km";
    }

},{enableHighAccuracy:true});

/* GEOFENCE */
function setGeofence(){

    geo.active = true;

    let p = vehicle.getLatLng();
    geo.lat = p.lat;
    geo.lng = p.lng;

    drawCircle();
}

function removeGeofence(){

    geo.active = false;

    if(circle) map.removeLayer(circle);

    circle = null;

    document.getElementById("rangeBox").style.display="none";
}

function drawCircle(){

    if(circle) map.removeLayer(circle);

    if(geo.active){
        circle = L.circle([geo.lat,geo.lng],{
            radius: geo.radius,
            color:"#38bdf8",
            fillOpacity:0.15
        }).addTo(map);

        document.getElementById("rangeBox").style.display="block";
    }
}

/* RANGE FIX */
let range = document.getElementById("radius");

range.addEventListener("input", function(){

    geo.radius = parseInt(this.value);

    document.getElementById("rVal").innerText = geo.radius;

    if(circle){
        circle.setRadius(geo.radius);
    }
});

/* KILL SWITCH */
function triggerKill(){

    if(engineKilled) return;

    engineKilled = true;

    let btn = document.getElementById("killBtn");

    btn.classList.remove("gray");
    btn.classList.add("red");

    btn.innerText = "RESTORE ENGINE";
}

function restoreEngine(){

    if(!engineKilled) return;

    engineKilled = false;

    let btn = document.getElementById("killBtn");

    btn.classList.remove("red");
    btn.classList.add("gray");

    btn.innerText = "DISABLED KILL SWITCH / RESTORE ENGINE";
}

</script>

</body>
</html>