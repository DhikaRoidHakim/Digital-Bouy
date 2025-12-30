<x-app-layout>
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
        <style>
            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }

            ::-webkit-scrollbar-track {
                background: transparent;
            }

            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            /* Animations */
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-entry {
                animation: slideUp 0.5s ease-out forwards;
                opacity: 0;
            }

            .delay-100 {
                animation-delay: 0.1s;
            }

            .delay-200 {
                animation-delay: 0.2s;
            }

            .delay-300 {
                animation-delay: 0.3s;
            }

            .glass-header {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            }
        </style>
    @endpush

    <div class="min-h-screen bg-slate-50/50 pb-12">

        <!-- Dashboard Header -->
        <div class="sticky top-0 z-40 glass-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                            Command Center
                        </h1>
                        <p class="text-xs text-slate-500 font-medium">Real-time maritime monitoring system</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div
                            class="hidden md:flex items-center space-x-2 text-sm text-slate-600 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="font-medium">System Online</span>
                        </div>
                        <div id="live-clock"
                            class="text-sm font-mono text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg">
                            {{ now()->format('H:i:s') }} WIB
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-entry">
                <!-- Total Buoys -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <svg class="w-24 h-24 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Total
                                Bouy</span>
                        </div>
                        <div class="flex items-baseline space-x-1">
                            <span class="text-4xl font-bold text-slate-800 tracking-tight">{{ $totalBuoys }}</span>
                            <span class="text-sm text-slate-500">deployed</span>
                        </div>
                    </div>
                </div>

                <!-- Active Buoys -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <svg class="w-24 h-24 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span
                                class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Bouy
                                Normal</span>
                        </div>
                        <div class="flex items-baseline space-x-1">
                            <span class="text-4xl font-bold text-slate-800 tracking-tight">{{ $activeBuoys }}</span>
                            <span class="text-sm text-slate-500">online</span>
                        </div>
                    </div>
                </div>

                <!-- Warning Buoys -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <svg class="w-24 h-24 text-rose-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="p-3 {{ $warningBuoys > 0 ? 'bg-rose-50 text-rose-600 animate-pulse' : 'bg-slate-50 text-slate-400' }} rounded-xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <span
                                class="text-xs font-semibold {{ $warningBuoys > 0 ? 'text-rose-600 bg-rose-50' : 'text-slate-500 bg-slate-100' }} px-2 py-1 rounded-full">Bouy
                                Warning</span>
                        </div>
                        <div class="flex items-baseline space-x-1">
                            <span class="text-4xl font-bold text-slate-800 tracking-tight">{{ $warningBuoys }}</span>
                            <span class="text-sm text-slate-500">critical</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-entry delay-100">

                <!-- Map Section -->
                <div
                    class="lg:col-span-2 bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 flex flex-col h-[500px] overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white z-10">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>
                            Live Geospatial View
                        </h3>
                        <a href="{{ route('map') }}"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-colors flex items-center">
                            Expand Map
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                        </a>
                    </div>
                    <div class="flex-1 relative bg-slate-100">
                        <div id="mini-map" class="absolute inset-0 z-0"></div>
                    </div>
                </div>

                <!-- Recent Logs Section -->
                <div
                    class="bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 flex flex-col h-[500px]">
                    <div class="p-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-2"></span>
                            Activity Feed
                        </h3>
                    </div>

                    <div class="flex-1 overflow-y-auto p-2 space-y-1">
                        @forelse($recentLogs as $log)
                            <div
                                class="group flex items-start space-x-3 p-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                                <div class="mt-1.5 flex-shrink-0">
                                    @if ($log->status === 'normal')
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 ring-4 ring-emerald-50"></div>
                                    @else
                                        <div
                                            class="w-2 h-2 rounded-full bg-rose-500 ring-4 ring-rose-50 animate-pulse">
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $log->buoy_name }}
                                        </p>
                                        <span
                                            class="text-xs text-slate-400 whitespace-nowrap ml-2">{{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $log->device_id }}</p>
                                    <div class="mt-2">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $log->status === 'normal' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center h-full text-slate-400">
                                <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-medium">No recent activity</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="p-3 border-t border-slate-100 text-center bg-slate-50 rounded-b-2xl">
                        <a href="#"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">View Full
                            History &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Mini Map
                const map = L.map('mini-map', {
                    zoomControl: false,
                    attributionControl: false,
                    dragging: true,
                    scrollWheelZoom: false
                }).setView([-6.2, 106.8], 10);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(map);

                // Custom Zoom Control
                L.control.zoom({
                    position: 'bottomright'
                }).addTo(map);

                // Fetch Buoys for Mini Map
                fetch('/api/buoys')
                    .then(res => res.json())
                    .then(data => {
                        if (data.length > 0) {
                            const bounds = L.latLngBounds();
                            data.forEach(b => {
                                const lat = parseFloat(b.lat);
                                const lng = parseFloat(b.lng);
                                const isNormal = b.status === 'normal';

                                const marker = L.circleMarker([lat, lng], {
                                    radius: 6,
                                    fillColor: isNormal ? '#10b981' : '#f43f5e',
                                    color: '#fff',
                                    weight: 2,
                                    opacity: 1,
                                    fillOpacity: 1
                                }).addTo(map);

                                if (!isNormal) {
                                    L.circleMarker([lat, lng], {
                                        radius: 12,
                                        fillColor: '#f43f5e',
                                        color: 'transparent',
                                        fillOpacity: 0.2
                                    }).addTo(map);
                                }

                                marker.bindPopup(`
                                    <div class="p-2 text-slate-800 font-sans">
                                        <h3 class="font-bold text-sm">${b.name}</h3>
                                        <p class="text-xs text-slate-500 font-mono mb-1">${b.device_id}</p>
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold ${isNormal ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">${b.status.toUpperCase()}</span>
                                    </div>
                                `);
                                bounds.extend([lat, lng]);
                            });
                            map.fitBounds(bounds, {
                                padding: [50, 50]
                            });
                        }
                    })
                    .catch(err => console.error('Error loading buoys:', err));
                // Live Clock
                setInterval(() => {
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-GB', {
                        timeZone: 'Asia/Jakarta',
                        hour12: false
                    });
                    const clockEl = document.getElementById('live-clock');
                    if (clockEl) {
                        clockEl.innerText = timeString + ' WIB';
                    }
                }, 1000);

            });
        </script>
    @endpush
</x-app-layout>
