<x-app-layout>
    @push('head')
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
        <style>
            /* Custom overrides for DataTables to match Tailwind */
            .dataTables_wrapper .dataTables_length select {
                padding-right: 2.5rem;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
                background-position: right 0.5rem center;
                background-repeat: no-repeat;
                background-size: 1.5em 1.5em;
                border-radius: 0.5rem;
                border-color: #e2e8f0;
                font-size: 0.875rem;
                color: #475569;
            }

            .dataTables_wrapper .dataTables_length select:focus {
                border-color: #3b82f6;
                ring: 2px solid #3b82f6;
            }

            .dataTables_wrapper .dataTables_filter input {
                border-radius: 0.5rem;
                border: 1px solid #e2e8f0;
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
                color: #1e293b;
                transition: all 0.2s;
            }

            .dataTables_wrapper .dataTables_filter input:focus {
                border-color: #3b82f6;
                outline: none;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            table.dataTable.no-footer {
                border-bottom: 1px solid #e2e8f0;
            }

            table.dataTable thead th,
            table.dataTable thead td {
                border-bottom: 1px solid #e2e8f0;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #3b82f6 !important;
                color: white !important;
                border: 1px solid #3b82f6 !important;
                border-radius: 0.5rem;
                font-weight: 600;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: #f1f5f9 !important;
                color: #0f172a !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.5rem;
            }

            .dataTables_wrapper .dataTables_info {
                color: #64748b;
                font-size: 0.875rem;
            }
        </style>
    @endpush

    <div class="min-h-screen bg-slate-50 py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 px-4 sm:px-0">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">
                        Buoy Activity Logs
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Monitor all proximity warnings and buoy events.</p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-3">
                    <div class="text-xs text-slate-500 bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm">
                        <span class="font-semibold text-slate-700">Total Logs:</span> {{ $logs->total() }}
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div
                class="bg-white overflow-hidden shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] sm:rounded-2xl border border-slate-100">
                <div class="p-6">

                    @if (session('success'))
                        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-100 p-4 flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-emerald-800">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-hidden">
                        <table id="logsTable" class="w-full display responsive nowrap" style="width:100%">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider rounded-tl-lg">
                                        ID</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Buoy Name</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Device ID</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Ship Location</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Radius</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider rounded-tr-lg">
                                        Logged At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($logs as $log)
                                    <tr class="hover:bg-slate-50/80 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900">#{{ $log->id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-slate-700">
                                                {{ $log->buoy->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600 font-mono">
                                                {{ $log->buoy->device_id ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center text-sm text-slate-500">
                                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-slate-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ number_format($log->lat, 5) }}, {{ number_format($log->lng, 5) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-800">
                                                {{ $log->radius }}m
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize
                                                {{ $log->status === 'normal' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $log->status === 'normal' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                {{ $log->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600">
                                                {{ $log->logged_at->format('d M Y') }}
                                            </div>
                                            <div class="text-xs text-slate-400">
                                                {{ $log->logged_at->format('H:i:s') }} WIB
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- jQuery & DataTables JS -->
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#logsTable').DataTable({
                    responsive: true,
                    order: [
                        [0, 'desc']
                    ], // Sort by ID descending (newest first)
                    language: {
                        search: "",
                        searchPlaceholder: "Search logs...",
                        lengthMenu: "Show _MENU_",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        paginate: {
                            first: "«",
                            last: "»",
                            next: "Next",
                            previous: "Prev"
                        }
                    },
                    columnDefs: [{
                        orderable: true,
                        targets: '_all'
                    }],
                    dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 space-y-2 md:space-y-0"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 space-y-2 md:space-y-0"ip>'
                });
            });
        </script>
    @endpush
</x-app-layout>
