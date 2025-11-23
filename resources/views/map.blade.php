<x-app-layout>
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

        <style>
            :root {
                --bg: #eef2f7;
                --topbar: #0f172a;
                --card: rgba(255, 255, 255, 0.95);
                --accent: #2563eb;
                --danger: #dc2626;
                --muted: #6b7280;
            }

            body {
                margin: 0;
                background: var(--bg);
            }

            .topbar {
                width: 100%;
                padding: 14px 22px;
                background: var(--topbar);
                color: #fff;
                font-size: 20px;
                font-weight: 700;
                box-shadow: 0 4px 18px rgba(2, 6, 23, 0.35);
                z-index: 2000;
            }

            .map-wrap {
                display: grid;
                grid-template-columns: 1fr 330px;
                gap: 14px;
                padding: 12px;
                height: calc(100vh - 70px);
                box-sizing: border-box;
            }

            #map {
                width: 100%;
                height: 100%;
                border-radius: 10px;
                overflow: hidden;
            }

            .panel {
                background: var(--card);
                padding: 16px;
                border-radius: 14px;
                box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
                overflow-y: auto;
            }

            .panel h3 {
                margin-bottom: 10px;
                font-size: 17px;
                font-weight: 700;
                color: #0f172a;
            }

            .buoy-item {
                padding: 10px;
                border-radius: 12px;
                background: #fff;
                border: 1px solid rgba(0, 0, 0, 0.05);
                margin-bottom: 8px;
            }

            .warning {
                position: absolute;
                top: 80px;
                left: 50%;
                transform: translateX(-50%);
                background: var(--danger);
                color: white;
                padding: 12px 18px;
                border-radius: 12px;
                font-weight: 700;
                display: none;
                z-index: 3000;
            }

            .ship-pill {
                position: absolute;
                bottom: 26px;
                left: 22px;
                background: rgba(255, 255, 255, 0.95);
                padding: 14px 18px;
                border-radius: 14px;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
                z-index: 2500;
            }
        </style>
    @endpush


    <!-- TOPBAR -->
    <div class="topbar">Digital Buoy Monitoring</div>

    <!-- WARNING -->
    <div class="warning" id="warningBox">Kamu memasuki area bahaya</div>

    <div class="map-wrap">

        <!-- MAP -->
        <div id="map"></div>

        <!-- RIGHT PANEL -->
        <div class="panel" id="statusPanel">
            <h3>Status Buoy</h3>
            <ul id="buoyList"></ul>

            <hr class="my-3">

            <h3>Peta Layers</h3>
            <ul class="controls layer-list">
                <li>
                    <button class="btn-primary" onclick="setLayer('default')">Default Map</button>
                </li>
                <li>
                    <button class="btn-ghost" onclick="setLayer('night')">Night Mode</button>
                </li>
                <li>
                    <button class="btn-ghost" onclick="setLayer('satellite')">Satellite</button>
                </li>
                <li>
                    <button class="btn-ghost" onclick="setLayer('marine')">Marine Map</button>
                </li>
            </ul>
        </div>


    </div>

    <!-- SHIP PILL -->
    <div class="ship-pill">
        <div style="font-weight:700;">Posisi Kapal</div>
        <div id="shipCoords">Lat: -, Lng: -</div>
    </div>


    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

        <script>
            /* =============================
                           BASE MAP + LAYERS
                        ============================= */

            // base / night / satellite gunakan provider yang umum
            let defaultLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            });

            // ganti night + satellite ke provider yang andal dan tambahkan fallback on error
            let nightLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png', {
                maxZoom: 20,
                attribution: '&copy; CartoDB'
            });

            // Esri World Imagery (ArcGIS REST) — pastikan {z}/{y}/{x}
            let satelliteLayer = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Esri'
                });

            // helper: jika terjadi banyak tileerror, fallback ke default supaya tidak putih
            function enableTileErrorFallback(layer, name) {
                let errors = 0;
                layer.on('tileerror', (e) => {
                    errors++;
                    console.error(`[tileerror] ${name}`, e.tile && e.tile.src, errors);
                    // jika lebih dari 6 tile error dalam 2 detik anggap provider bermasalah
                    setTimeout(() => {
                        errors = Math.max(0, errors - 6);
                    }, 2000);
                    if (errors > 6) {
                        console.warn(`${name} gagal banyak, fallback ke default`);
                        setLayer('default');
                        errors = 0;
                    }
                });
            }

            const map = L.map('map', {
                layers: [defaultLayer]
            }).setView([-6.2, 106.8], 13);

            // buat pane khusus untuk seamark supaya selalu di atas base tiles
            map.createPane('seamarkPane');
            map.getPane('seamarkPane').style.zIndex = 650;

            let marineLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
                pane: 'seamarkPane',
                opacity: 0.95,
                attribution: 'OpenSeaMap'
            });

            function setLayer(type) {
                console.log('setLayer called ->', type);

                // hapus hanya base/overlay tiles, biarkan marker/circle
                [defaultLayer, nightLayer, satelliteLayer].forEach(l => {
                    if (map.hasLayer(l)) map.removeLayer(l);
                });
                if (map.hasLayer(marineLayer)) map.removeLayer(marineLayer);

                if (type === 'default') {
                    map.addLayer(defaultLayer);
                } else if (type === 'night') {
                    map.addLayer(nightLayer);
                } else if (type === 'satellite') {
                    map.addLayer(satelliteLayer);
                } else if (type === 'marine') {
                    // tambahkan base + marine overlay
                    map.addLayer(defaultLayer);
                    map.addLayer(marineLayer);
                }

                // paksa redraw jika perlu
                setTimeout(() => map.invalidateSize(), 200);
            }

            /* =============================
               BUOY + SHIP LOGIC
            ============================= */

            let buoyCircles = {};
            let buoyMarkers = {};
            let userMarker = null;

            const shipIcon = L.icon({
                iconUrl: "/assets/icons/transport.png",
                iconSize: [50, 50],
                iconAnchor: [25, 25]
            });

            async function loadBuoys() {
                const res = await fetch("/api/buoys-dummy");
                return await res.json();
            }

            async function renderBuoys() {
                const buoys = await loadBuoys();
                let panelHTML = "";

                buoys.forEach(b => {

                    /* ==== ANIMASI / SMOOTH POSITION ==== */
                    let oldPos = buoyMarkers[b.device_id]?.getLatLng();
                    let newPos = L.latLng(b.lat, b.lng);

                    if (oldPos) {
                        let step = 0;
                        let interval = setInterval(() => {
                            step += 0.1;
                            let lat = oldPos.lat + (newPos.lat - oldPos.lat) * step;
                            let lng = oldPos.lng + (newPos.lng - oldPos.lng) * step;

                            buoyMarkers[b.device_id].setLatLng([lat, lng]);
                            buoyCircles[b.device_id].setLatLng([lat, lng]);

                            if (step >= 1) clearInterval(interval);
                        }, 50);
                    }

                    if (!buoyCircles[b.device_id]) {
                        buoyCircles[b.device_id] = L.circle(newPos, {
                            radius: b.radius,
                            color: "#dc2626",
                            fillColor: "#ef4444",
                            fillOpacity: 0.28,
                            weight: 2
                        }).addTo(map);
                    } else {
                        buoyCircles[b.device_id].setRadius(b.radius);
                    }

                    if (!buoyMarkers[b.device_id]) {
                        buoyMarkers[b.device_id] = L.marker(newPos).addTo(map);
                    }

                    /* PANEL LIST */
                    panelHTML += `
            <div class="buoy-item">
                <div class="meta">
                    <span class="buoy-name">${b.name}</span>
                    <span class="buoy-status small">Lat: ${b.lat} | Lng: ${b.lng}</span>
                </div>
            </div>
        `;
                });

                document.getElementById("buoyList").innerHTML = panelHTML;
            }

            async function checkDanger(lat, lng) {
                const buoys = await loadBuoys();
                let danger = false;

                buoys.forEach(b => {
                    const d = map.distance([lat, lng], [b.lat, b.lng]);
                    if (d < b.radius) danger = true;
                });

                document.getElementById("warningBox").style.display =
                    danger ? "block" : "none";
            }

            function updateShip(lat, lng) {
                if (!userMarker) {
                    userMarker = L.marker([lat, lng], {
                        icon: shipIcon
                    }).addTo(map);
                } else {
                    /* smooth animation kapal */
                    let old = userMarker.getLatLng();
                    let step = 0;
                    let target = L.latLng(lat, lng);

                    let anim = setInterval(() => {
                        step += 0.12;
                        let nl = old.lat + (target.lat - old.lat) * step;
                        let ng = old.lng + (target.lng - old.lng) * step;
                        userMarker.setLatLng([nl, ng]);
                        if (step >= 1) clearInterval(anim);
                    }, 40);
                }

                document.getElementById("shipCoords").innerHTML =
                    `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`;

                checkDanger(lat, lng);
            }

            /* GEOLOCATION */
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (pos) => updateShip(pos.coords.latitude, pos.coords.longitude),
                    (err) => console.log("GPS error:", err.message), {
                        enableHighAccuracy: true
                    }
                );
            }

            /* AUTO REFRESH BUOYS */
            // panggil render sekali saat load supaya tidak harus menunggu interval pertama
            renderBuoys();
            setInterval(renderBuoys, 1000);
        </script>
    @endpush

</x-app-layout>
