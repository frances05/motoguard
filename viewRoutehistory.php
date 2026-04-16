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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MotorGuard | Route Analytics</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>
    :root {
        --bg: #0f172a;
        --card-bg: #1e293b;
        --accent: #38bdf8;
        --text-main: #f8fafc;
        --text-dim: #94a3b8;
        --danger: #ef4444;
    }

    body { 
        margin:0; 
        background: var(--bg); 
        color: var(--text-main); 
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
        overflow-x: hidden;
    }

    #map { 
        height: 45vh; 
        width: 100%;
        border-bottom: 2px solid var(--accent);
    }

    .container {
        padding: 20px;
        max-width: 600px;
        margin: 0 auto;
    }

    .header-nav {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }

    /* Stats Overview */
    .stats-overview {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: var(--card-bg);
        padding: 15px;
        border-radius: 16px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .stat-card i { color: var(--accent); font-size: 20px; margin-bottom: 5px; }
    .stat-card div { font-size: 18px; font-weight: 700; }
    .stat-card span { font-size: 11px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }

    /* Date Grouping Header */
    .date-group-header {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-dim);
        margin: 20px 0 10px 5px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .trip-card {
        background: var(--card-bg);
        padding: 16px;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.05);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .trip-card:hover {
        transform: translateY(-3px);
        background: #27354a;
        border-color: var(--accent);
    }

    .trip-card.active {
        border-left: 5px solid var(--accent);
        background: #2d3e5a;
    }

    .trip-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .trip-date { font-size: 14px; font-weight: 600; }
    .trip-info {
        display: flex;
        gap: 15px;
        font-size: 12px;
        color: var(--text-dim);
    }

    /* Playback Panel & Exit Button */
    .controls-overlay {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 400px;
        background: rgba(30, 41, 59, 0.98);
        backdrop-filter: blur(10px);
        padding: 24px 20px 20px 20px;
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
        z-index: 1000;
        display: none;
        border: 1px solid rgba(56, 189, 248, 0.3);
    }

    .close-panel {
        position: absolute;
        top: 10px;
        right: 15px;
        color: var(--text-dim);
        font-size: 24px;
        cursor: pointer;
        transition: 0.2s;
    }

    .close-panel:hover { color: var(--danger); }

    .play-btns { display: flex; gap: 10px; }

    button {
        flex: 1;
        padding: 14px;
        border: none;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        transition: 0.2s;
    }

    .btn-play { background: var(--accent); color: var(--bg); }
    .btn-stop { background: var(--danger); color: white; }
    .btn-del { background: #334155; color: white; margin-top: 10px; width: 100%; }
</style>
</head>

<body>

<div id="map"></div>

<div class="container">
    <a href="dashboard.php" class="header-nav">
        <i class='bx bx-chevron-left' style="font-size: 24px;"></i> Back to Dashboard
    </a>

    <div class="stats-overview">
        <div class="stat-card">
            <i class='bx bx-map-alt'></i>
            <div id="totalTrips">0</div>
            <span>Total Trips</span>
        </div>
        <div class="stat-card">
            <i class='bx bx-trending-up'></i>
            <div id="totalDist">0.0 km</div>
            <span>Distance Total</span>
        </div>
    </div>

    <div class="section-title">
        <i class='bx bx-history'></i> Route Activity
    </div>

    <div id="list"></div>
    <div style="height: 120px;"></div> 
</div>

<div id="controls" class="controls-overlay">
    <i class='bx bx-x close-panel' onclick="closeControls()"></i>
    <div id="tripDetails" style="font-size:12px; margin-bottom:12px; text-align: center; color: var(--text-dim);"></div>
    <div class="play-btns">
        <button class="btn-play" onclick="playRoute()"><i class='bx bx-play-circle'></i> PLAY</button>
        <button class="btn-stop" onclick="stopReplay()"><i class='bx bx-stop-circle'></i> STOP</button>
    </div>
    <button class="btn-del" onclick="deleteTrip()"><i class='bx bx-trash'></i> Delete Record</button>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map = L.map('map', { zoomControl: false }).setView([14.5995, 120.9842], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.control.zoom({ position: 'topright' }).addTo(map);

    let marker = L.marker([0,0], {
        icon: L.divIcon({
            html: "<div id='car-marker' style='color: #38bdf8; font-size: 28px; filter: drop-shadow(0 0 5px rgba(56,189,248,0.8));'>➤</div>",
            className: ""
        })
    }).addTo(map);

    let line = L.polyline([], { color: "#38bdf8", weight: 5, opacity: 0.8, lineJoin: 'round' }).addTo(map);

    let routes = JSON.parse(localStorage.getItem("routes") || "[]");
    let selectedIndex = null;
    let replayTimer = null;

    function init() {
        const listDiv = document.getElementById("list");
        let totalKm = 0;
        if (!routes.length) { listDiv.innerHTML = "<div class='stat-card'>No route data found</div>"; return; }

        const grouped = routes.reduce((acc, route, index) => {
            const dateStr = route.date.split(',')[0].trim();
            if (!acc[dateStr]) acc[dateStr] = [];
            acc[dateStr].push({ ...route, originalIndex: index });
            return acc;
        }, {});

        listDiv.innerHTML = "";
        Object.keys(grouped).reverse().forEach(date => {
            const heading = document.createElement("div");
            heading.className = "date-group-header";
            heading.innerText = date;
            listDiv.appendChild(heading);

            grouped[date].reverse().forEach(route => {
                totalKm += (route.distance / 1000);
                let card = document.createElement("div");
                card.className = "trip-card";
                card.innerHTML = `
                    <div class="trip-header">
                        <div class="trip-date">Trip #${route.originalIndex + 1}</div>
                        <div style="font-size:12px; color:var(--accent); font-weight:700;">${route.date.split(',')[1] || ''}</div>
                    </div>
                    <div class="trip-info">
                        <span><i class='bx bx-move-horizontal'></i> ${(route.distance/1000).toFixed(2)}km</span>
                    </div>
                `;
                card.onclick = () => selectTrip(route.originalIndex, card);
                listDiv.appendChild(card);
            });
        });
        document.getElementById("totalTrips").innerText = routes.length;
        document.getElementById("totalDist").innerText = totalKm.toFixed(1) + " km";
    }

    function selectTrip(index, element) {
        stopReplay();
        selectedIndex = index;
        let route = routes[index];
        document.querySelectorAll('.trip-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        document.getElementById("controls").style.display = "block";
        document.getElementById("tripDetails").innerText = `Trip #${index + 1} Selected`;
        line.setLatLngs(route.path);
        marker.setLatLng(route.path[0]);
        map.fitBounds(line.getBounds(), { padding: [40, 40] });
    }

    function closeControls() {
        stopReplay();
        selectedIndex = null;
        document.getElementById("controls").style.display = "none";
        document.querySelectorAll('.trip-card').forEach(el => el.classList.remove('active'));
        line.setLatLngs([]);
        marker.setLatLng([0,0]);
    }

    function playRoute() {
        if (selectedIndex === null) return;
        stopReplay();
        let pathData = routes[selectedIndex].path;
        line.setLatLngs([]); 
        let step = 0;
        replayTimer = setInterval(() => {
            if (step >= pathData.length) { clearInterval(replayTimer); return; }
            let pos = pathData[step];
            marker.setLatLng(pos);
            line.addLatLng(pos);
            map.panTo(pos);
            step++;
        }, 150);
    }

    function stopReplay() { if (replayTimer) clearInterval(replayTimer); }

    function deleteTrip() {
        if (confirm("Permanently remove this trip?")) {
            routes.splice(selectedIndex, 1);
            localStorage.setItem("routes", JSON.stringify(routes));
            location.reload();
        }
    }

    init();
</script>

</body>
</html>