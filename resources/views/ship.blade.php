<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Ship Tracking Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/ship.css') }}"> --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --primary: #00f2ff;
            --primary-dim: rgba(0, 242, 255, 0.1);
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --danger: #ef4444;
            --success: #22c55e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Outfit", sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: var(--card-bg);
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3),
                0 10px 10px -5px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .status-box {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .status-label {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-value {
            font-size: 18px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .status-active .status-value {
            color: var(--success);
        }

        .status-active .status-indicator {
            background-color: var(--success);
            box-shadow: 0 0 10px var(--success);
            animation: pulse 2s infinite;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 30px;
        }

        .data-item {
            background: rgba(255, 255, 255, 0.03);
            padding: 15px;
            border-radius: 12px;
        }

        .data-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .data-value {
            font-family: "Courier New", monospace;
            font-size: 16px;
            color: var(--primary);
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            font-family: inherit;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-start {
            background: var(--primary);
            color: #0f172a;
            box-shadow: 0 4px 12px var(--primary-dim);
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px var(--primary-dim);
        }

        .btn-stop {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-stop:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        .info-text {
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #334155;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            white-space: nowrap;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>📡 Ship Transmitter</h2>

        <div id="statusBox" class="status-box">
            <div class="status-label">Status Koneksi</div>
            <div class="status-value">
                <div class="status-indicator"></div>
                <span id="statusText">OFFLINE</span>
            </div>
        </div>

        <div class="data-grid">
            <div class="data-item">
                <div class="data-label">LATITUDE</div>
                <div class="data-value" id="lat">--.----</div>
            </div>
            <div class="data-item">
                <div class="data-label">LONGITUDE</div>
                <div class="data-value" id="lng">--.----</div>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <div class="data-label">LAST UPDATE</div>
            <div class="data-value" id="time" style="font-size: 14px; color: var(--text-muted);">Belum ada data
            </div>
        </div>

        <button id="startBtn" class="btn btn-start">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            Mulai Tracking
        </button>

        <button id="stopBtn" class="btn btn-stop" style="display:none;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Hentikan Tracking
        </button>

        <p class="info-text">
            ⚠️ Pastikan layar tetap menyala. Browser mungkin menghentikan pengiriman lokasi jika layar mati atau tab
            diminimize.
        </p>
    </div>

    <div id="toast" class="toast">Pesan notifikasi</div>

    <script>
        let watchId = null;
        let lastSentTime = 0;
        const SEND_INTERVAL = 1000;

        const statusBox = document.getElementById('statusBox');
        const statusText = document.getElementById('statusText');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const latEl = document.getElementById('lat');
        const lngEl = document.getElementById('lng');
        const timeEl = document.getElementById('time');

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Check for Secure Context
        if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !==
            '127.0.0.1') {
            alert(
                "PERINGATAN: Anda mengakses halaman ini melalui HTTP (Tidak Aman). Browser modern memblokir akses GPS pada koneksi tidak aman. Mohon gunakan HTTPS atau akses via localhost jika memungkinkan."
            );
        }

        startBtn.onclick = function() {
            if (!navigator.geolocation) {
                alert("Browser anda tidak mendukung Geolocation.");
                return;
            }

            statusBox.classList.add('status-active');
            statusText.textContent = "ONLINE";
            startBtn.style.display = "none";
            stopBtn.style.display = "flex";

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    const now = Date.now();
                    const lat = pos.coords.latitude.toFixed(6);
                    const lng = pos.coords.longitude.toFixed(6);
                    const timestamp = new Date().toLocaleTimeString();

                    // Update UI selalu (agar terlihat responsif di HP)
                    latEl.textContent = lat;
                    lngEl.textContent = lng;
                    timeEl.textContent = timestamp;

                    // THROTTLE: Hanya kirim ke server jika sudah lewat 3 detik
                    if (now - lastSentTime < SEND_INTERVAL) {
                        return;
                    }
                    lastSentTime = now;

                    // Kirim data ke server
                    fetch("/api/ship/update-location", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude,
                            device_id: "{{ Auth::id() }}",
                            label: "{{ Auth::user()->name ?? 'Unknown Ship' }}"
                        })
                    }).then(res => {
                        if (res.status === 419) {
                            console.error("CSRF Token Mismatch. Reloading page...");
                            location.reload();
                        }
                        if (!res.ok) console.error("Gagal mengirim data", res.status);
                    }).catch(err => {
                        console.error("Network Error:", err);
                    });
                },
                (err) => {
                    console.warn(`ERROR(${err.code}): ${err.message}`);
                    let msg = "Gagal mendapatkan lokasi.";
                    if (err.code === 1) msg =
                        "Izin lokasi ditolak. Mohon izinkan akses lokasi di pengaturan browser.";
                    else if (err.code === 2) msg = "Posisi tidak tersedia. Pastikan GPS aktif.";
                    else if (err.code === 3) msg = "Waktu permintaan habis.";

                    alert(msg + "\nError: " + err.message);
                    stopTracking();
                },
                options
            );
        };

        function stopTracking() {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            statusBox.classList.remove('status-active');
            statusText.textContent = "STANDBY";
            stopBtn.style.display = "none";
            startBtn.style.display = "flex";
        }

        stopBtn.onclick = stopTracking;
    </script>

</body>

</html>
