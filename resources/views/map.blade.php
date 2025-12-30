<x-app-layout>
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            /* Reset Layout for Full Screen Map */
            .map-wrapper {
                position: relative;
                height: calc(100vh - 65px);
                /* Adjust for nav height */
                width: 100%;
                overflow: hidden;
                font-family: 'Inter', sans-serif;
            }

            #map {
                height: 100%;
                width: 100%;
                z-index: 0;
            }

            /* Floating Glass Panels */
            .glass-panel {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.5);
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                border-radius: 16px;
            }

            /* Floating Header */
            .map-header {
                position: absolute;
                top: 20px;
                left: 20px;
                z-index: 1000;
                padding: 12px 20px;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            /* Floating Sidebar */
            .map-sidebar {
                position: absolute;
                top: 20px;
                right: 20px;
                width: 320px;
                max-height: calc(100% - 40px);
                z-index: 1000;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .sidebar-header {
                padding: 16px 20px;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .sidebar-content {
                overflow-y: auto;
                padding: 10px;
                flex: 1;
            }

            /* Buoy List Items */
            .buoy-item {
                background: white;
                border-radius: 12px;
                padding: 12px;
                margin-bottom: 8px;
                cursor: pointer;
                transition: all 0.2s ease;
                border: 1px solid transparent;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            }

            .buoy-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                border-color: #3b82f6;
            }

            .buoy-status-dot {
                height: 8px;
                width: 8px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 6px;
            }

            /* Layer Controls */
            .layer-controls {
                position: absolute;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1000;
                padding: 6px;
                display: flex;
                gap: 6px;
            }

            .layer-btn {
                padding: 8px 16px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 600;
                color: #64748b;
                background: transparent;
                border: none;
                cursor: pointer;
                transition: all 0.2s;
            }

            .layer-btn:hover {
                background: rgba(0, 0, 0, 0.05);
                color: #0f172a;
            }

            .layer-btn.active {
                background: #fff;
                color: #3b82f6;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            /* Warning Box */
            .warning-overlay {
                position: absolute;
                top: 100px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1000;
                background: #fee2e2;
                color: #991b1b;
                padding: 12px 24px;
                border-radius: 50px;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
                display: none;
                animation: pulse-red 2s infinite;
                border: 1px solid #fecaca;
            }

            @keyframes pulse-red {
                0% {
                    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
                }

                70% {
                    box-shadow: 0 0 0 10px rgba(220, 38, 38, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
                }
            }

            /* Custom Scrollbar */
            .sidebar-content::-webkit-scrollbar {
                width: 4px;
            }

            .sidebar-content::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 2px;
            }

            /* Ship Info Overlay */
            .ship-info-card {
                position: absolute;
                bottom: 30px;
                left: 30px;
                z-index: 1000;
                padding: 16px;
                min-width: 200px;
            }
        </style>
    @endpush

    <div class="map-wrapper">

        <!-- Floating Header -->
        <div class="glass-panel map-header">
            <div class="p-2 bg-blue-600 rounded-lg text-white shadow-lg shadow-blue-600/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-800 leading-tight">Live Monitor</h1>
                <div class="flex items-center gap-2 mt-0.5" id="connectionStatus">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                            id="statusPing"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500" id="statusDot"></span>
                    </span>
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider"
                        id="statusText">System Online</span>
                </div>
            </div>
        </div>

        <!-- Warning Overlay -->
        <div id="warningBox" class="warning-overlay">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Peringatan Bahaya: Kapal berada di zona terlarang</span>
            </div>
        </div>

        <!-- Ship Info Card -->
        <div class="glass-panel ship-info-card">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-1.5 bg-indigo-100 text-indigo-600 rounded-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-500 uppercase">Tracking Kapal</span>
            </div>
            <div id="shipCoords">
                <p class="text-sm font-medium text-slate-800">Sinyal Kapal Tidak Aktif</p>
                <p class="text-xs text-slate-400 mt-1">Menunggu Data...</p>
            </div>
        </div>

        <!-- Map Container -->
        <div id="map"></div>

        <!-- Layer Controls -->
        <div class="glass-panel layer-controls">
            <button class="layer-btn active" onclick="setLayer('default', this)">Standard</button>
            <button class="layer-btn" onclick="setLayer('satellite', this)">Satellite</button>
            <button class="layer-btn" onclick="setLayer('marine', this)">Marine</button>
            <button class="layer-btn" onclick="setLayer('dark', this)">Dark</button>
        </div>

        <!-- Floating Sidebar -->
        <aside class="glass-panel map-sidebar">
            <div class="sidebar-header">
                <h3 class="font-bold text-slate-800">Active Buoys</h3>
                <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-full"
                    id="buoyCount">0</span>
            </div>
            <div class="sidebar-content">
                <ul id="buoyList" class="space-y-2">
                    <!-- Buoy items injected here -->
                    <div class="text-center py-8 text-slate-400 text-sm">Loading Bouy</div>
                </ul>
            </div>
        </aside>

    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
        <script type="module">
            /* =============================
                                                                                                                                                       MAP CONFIGURATION
                                                                                                                                                    ============================= */
            const map = L.map('map', {
                zoomControl: false,
                attributionControl: false
            }).setView([-6.2, 106.8], 13);

            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);

            // Layers
            const layers = {
                default: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }),
                dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png', {
                    maxZoom: 20
                }),
                satellite: L.tileLayer(
                    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19
                    }),
                marine: L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
                    opacity: 1
                })
            };

            // Initialize default
            layers.default.addTo(map);

            // Layer Switcher
            window.setLayer = function(type, btn) {
                document.querySelectorAll('.layer-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Remove all layers
                Object.values(layers).forEach(l => map.removeLayer(l));

                // Add selected
                if (type === 'marine') {
                    layers.default.addTo(map); // Marine needs a base
                    layers.marine.addTo(map);
                } else {
                    layers[type].addTo(map);
                }
            };

            /* =============================
               BUOY LOGIC
            ============================= */
            let buoyMarkers = {};
            let buoyCircles = {};
            let userMarker = null;

            // Custom Icons
            const shipIcon = L.icon({
                iconUrl: '/assets/icons/transport.png', // Better ship icon
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -16]
            });

            async function loadBuoys() {
                try {
                    const res = await fetch("/api/buoys");
                    const data = await res.json();
                    renderBuoys(data);
                    return data;
                } catch (e) {
                    console.error("Failed to load buoys", e);
                    return [];
                }
            }

            function renderBuoys(buoys) {
                const list = document.getElementById("buoyList");
                document.getElementById("buoyCount").innerText = buoys.length;

                if (buoys.length === 0) {
                    list.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm">No buoys found</div>';
                    return;
                }

                let html = "";

                buoys.forEach(b => {
                    const lat = parseFloat(b.lat);
                    const lng = parseFloat(b.lng);
                    const isNormal = b.status === 'normal';

                    // Update Map Markers
                    if (!buoyMarkers[b.device_id]) {
                        // Create Marker
                        const marker = L.circleMarker([lat, lng], {
                            radius: 8,
                            fillColor: isNormal ? '#10b981' : '#f43f5e',
                            color: '#fff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 1
                        }).addTo(map);

                        // Create Radius Circle
                        const circle = L.circle([lat, lng], {
                            radius: b.radius,
                            color: isNormal ? '#10b981' : '#f43f5e',
                            fillColor: isNormal ? '#10b981' : '#f43f5e',
                            fillOpacity: 0.1,
                            weight: 1
                        }).addTo(map);

                        marker.bindPopup(`
                            <div class="font-sans p-1">
                                <h3 class="font-bold text-slate-800">${b.name}</h3>
                                <p class="text-xs text-slate-500">${b.device_id}</p>
                                <p class="text-xs text-slate-500">${b.status}</p>
                                <p class="text-xs text-slate-500">${b.lat}</p>
                                <p class="text-xs text-slate-500">${b.lng}</p>
                            </div>
                        `);

                        buoyMarkers[b.device_id] = marker;
                        buoyCircles[b.device_id] = circle;
                    } else {
                        // Update existing
                        buoyMarkers[b.device_id].setLatLng([lat, lng]);
                        buoyCircles[b.device_id].setLatLng([lat, lng]);
                        buoyCircles[b.device_id].setRadius(b.radius);

                        // Update color if status changed
                        const color = isNormal ? '#10b981' : '#f43f5e';
                        buoyMarkers[b.device_id].setStyle({
                            fillColor: color
                        });
                        buoyCircles[b.device_id].setStyle({
                            color: color,
                            fillColor: color
                        });
                    }

                    // Build List Item
                    html += `
                        <li class="buoy-item group" onclick="window.flyToBuoy(${lat}, ${lng})">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-slate-700 text-sm group-hover:text-blue-600 transition-colors">${b.name}</h4>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">${b.device_id}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold ${isNormal ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'}">
                                    <span class="w-1.5 h-1.5 rounded-full ${isNormal ? 'bg-emerald-500' : 'bg-rose-500'} mr-1.5"></span>
                                    ${b.status.toUpperCase()}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center text-[10px] text-slate-400 space-x-3">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    ${lat.toFixed(4)}, ${lng.toFixed(4)}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                    ${b.radius}m
                                </span>
                            </div>
                        </li>
                    `;
                });

                list.innerHTML = html;
            }

            window.flyToBuoy = function(lat, lng) {
                map.flyTo([lat, lng], 16, {
                    animate: true,
                    duration: 3
                });
            };

            /* =============================
               SHIP TRACKING
            ============================= */
            function updateShip(lat, lng, label = "Unknown Ship") {
                if (!userMarker) {
                    userMarker = L.marker([lat, lng], {
                        icon: shipIcon
                    }).addTo(map);
                    userMarker.bindPopup(`<b class="text-sm font-sans">${label}</b>`);
                    map.flyTo([lat, lng], 14);
                } else {
                    userMarker.setLatLng([lat, lng]);
                    userMarker.setPopupContent(`<b class="text-sm font-sans">${label}</b>`);
                }

                // Update Info Card
                const infoEl = document.getElementById("shipCoords");
                infoEl.innerHTML = `
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-slate-800 text-sm">${label}</span>
                        <span class="text-[10px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold">LIVE</span>
                    </div>
                    <div class="text-xs text-slate-500 font-mono">
                        LAT: ${lat.toFixed(5)} <br>
                        LNG: ${lng.toFixed(5)}
                    </div>
                `;

                checkDanger(lat, lng);
            }

            async function checkDanger(lat, lng) {
                const buoys = await loadBuoys();
                let danger = false;

                buoys.forEach(b => {
                    const dist = map.distance([lat, lng], [b.lat, b.lng]);
                    if (dist < b.radius && b.status === 'warning') danger = true;
                });

                const box = document.getElementById("warningBox");
                box.style.display = danger ? "flex" : "none";
            }

            // Initial Load
            loadBuoys();
            setInterval(loadBuoys, 10000);

            // Realtime Subscription
            const subscribeToShipUpdates = () => {
                if (typeof window.Echo === 'undefined') {
                    console.warn("Echo not ready, retrying in 500ms...");
                    setTimeout(subscribeToShipUpdates, 500);
                    return;
                }

                console.log("Initializing Reverb connection...");

                // Debug Connection
                if (window.Echo.connector && window.Echo.connector.pusher) {
                    window.Echo.connector.pusher.connection.bind('connected', () => {
                        console.log('✅ Reverb Connected!');
                        document.getElementById('statusText').innerText = 'System Online';
                        document.getElementById('statusDot').className =
                            'relative inline-flex rounded-full h-2 w-2 bg-emerald-500';
                        document.getElementById('statusPing').className =
                            'animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75';
                    });

                    window.Echo.connector.pusher.connection.bind('failed', () => {
                        console.error('❌ Reverb Connection Failed.');
                        document.getElementById('statusText').innerText = 'Connection Failed';
                        document.getElementById('statusDot').className =
                            'relative inline-flex rounded-full h-2 w-2 bg-red-500';
                        document.getElementById('statusPing').className = 'hidden';
                    });

                    window.Echo.connector.pusher.connection.bind('disconnected', () => {
                        console.warn('⚠️ Reverb Disconnected');
                        document.getElementById('statusText').innerText = 'Reconnecting...';
                        document.getElementById('statusDot').className =
                            'relative inline-flex rounded-full h-2 w-2 bg-amber-500';
                        document.getElementById('statusPing').className = 'hidden';
                    });
                }

                window.Echo.leave("ship-location");
                window.Echo.channel("ship-location")
                    .listen(".ship.updated", (e) => {
                        console.log("🚢 Event Received:", e);
                        if (e.data && e.data.lat && e.data.lng) {
                            updateShip(parseFloat(e.data.lat), parseFloat(e.data.lng), e.data.label);
                        } else if (e.lat && e.lng) {
                            // Handle case where data might be flattened
                            updateShip(parseFloat(e.lat), parseFloat(e.lng), e.label);
                        }
                    });
            };
            subscribeToShipUpdates();
        </script>
    @endpush
</x-app-layout>
