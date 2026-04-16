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
<title>Route Replay</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
body{margin:0;background:#0f172a;color:white;font-family:Segoe UI;}
#map{height:70vh;}
.panel{padding:10px;}

.card{background:#1e293b;padding:10px;border-radius:10px;margin-bottom:10px;}

button{
width:100%;
padding:10px;
margin:5px 0;
border:none;
border-radius:8px;
font-weight:bold;
cursor:pointer;
}

.blue{background:#38bdf8;color:#0f172a;}
.red{background:#ef4444;color:white;}
</style>
</head>

<body>

<div id="map"></div>

<div class="panel">

<div class="card">
<b>Route History</b>
<div id="list"></div>
</div>

<div class="card">
<button class="blue" onclick="play()">PLAY ROUTE</button>
<button class="red" onclick="stop()">STOP</button>
</div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

let map=L.map('map').setView([14.5995,120.9842],16);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

let marker=L.marker([0,0],{
icon:L.divIcon({
html:"<div style='color:#38bdf8;font-size:26px;'>➤</div>",
className:""
})
}).addTo(map);

let line=L.polyline([],{
color:"#38bdf8",
weight:5
}).addTo(map);

let routes=JSON.parse(localStorage.getItem("routes")||"[]");
let selected=null;

function load(){

let list=document.getElementById("list");
list.innerHTML="";

routes.forEach(r=>{

let b=document.createElement("button");
b.className="blue";
b.innerText=r.date+" ("+Math.round(r.distance)+" m)";

b.onclick=()=>selected=r;

list.appendChild(b);

});

}

load();

/* PLAY */
let i=0,t=null;

function play(){

if(!selected) return;

let p=selected.path;

map.fitBounds(p);

line.setLatLngs([]);
i=0;

t=setInterval(()=>{

if(i>=p.length){
clearInterval(t);
return;
}

marker.setLatLng(p[i]);
line.addLatLng(p[i]);
map.panTo(p[i]);

i++;

},200);

}

/* STOP */
function stop(){
if(t) clearInterval(t);
}

</script>
</body>
</html>