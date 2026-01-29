<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VoIP Panel') }} - Panel de Control</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: #f1f5f9;
                min-height: 100vh;
                color: #1e293b;
                margin: 0;
            }

            /* ===== CARDS ===== */
            .dark-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            }

            /* ===== STAT CARDS ===== */
            .stat-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1.25rem;
                position: relative;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            }
            .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                border-radius: 12px 12px 0 0;
            }
            .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
            .stat-card.green::before { background: linear-gradient(90deg, #10b981, #34d399); }
            .stat-card.yellow::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
            .stat-card.purple::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
            .stat-card.red::before { background: linear-gradient(90deg, #ef4444, #f87171); }

            /* ===== TABLES ===== */
            .dark-table {
                width: 100%;
                border-collapse: collapse;
            }
            .dark-table th {
                background: #f8fafc;
                color: #64748b;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.7rem;
                letter-spacing: 0.05em;
                padding: 0.875rem 1rem;
                text-align: left;
                border-bottom: 2px solid #e2e8f0;
            }
            .dark-table td {
                padding: 0.875rem 1rem;
                color: #334155;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.875rem;
            }
            .dark-table tbody tr:hover {
                background: #f8fafc;
            }
            .dark-table tbody tr:last-child td {
                border-bottom: none;
            }

            /* ===== INPUTS ===== */
            .dark-input {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                color: #1e293b;
                border-radius: 8px;
                padding: 0.625rem 0.875rem;
                font-size: 0.875rem;
                transition: all 0.15s ease;
            }
            .dark-input:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
                outline: none;
            }
            .dark-input::placeholder {
                color: #94a3b8;
            }

            .dark-select {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                color: #1e293b;
                border-radius: 8px;
                padding: 0.625rem 0.875rem;
                font-size: 0.875rem;
                cursor: pointer;
            }
            .dark-select:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
                outline: none;
            }

            /* ===== BUTTONS ===== */
            .btn-primary {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: white;
                padding: 0.625rem 1.25rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
                box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
            }
            .btn-primary:hover {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: #ffffff;
                color: #475569;
                padding: 0.625rem 1.25rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                border: 1px solid #cbd5e1;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .btn-secondary:hover {
                background: #f8fafc;
                border-color: #94a3b8;
            }

            .btn-danger {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                padding: 0.625rem 1.25rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .btn-danger:hover {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
            }

            .btn-success {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 0.625rem 1.25rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .btn-success:hover {
                background: linear-gradient(135deg, #059669 0%, #047857 100%);
                box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
            }

            /* ===== BADGES ===== */
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.625rem;
                border-radius: 6px;
                font-size: 0.75rem;
                font-weight: 600;
            }
            .badge-green {
                background: #dcfce7;
                color: #166534;
            }
            .badge-red {
                background: #fee2e2;
                color: #991b1b;
            }
            .badge-yellow {
                background: #fef3c7;
                color: #92400e;
            }
            .badge-blue {
                background: #dbeafe;
                color: #1e40af;
            }
            .badge-purple {
                background: #ede9fe;
                color: #5b21b6;
            }
            .badge-gray {
                background: #f1f5f9;
                color: #475569;
            }

            /* ===== INFO BOXES ===== */
            .info-box {
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                border-radius: 8px;
                padding: 1rem;
            }
            .info-box h4 {
                color: #1d4ed8;
                font-weight: 600;
                margin: 0 0 0.5rem 0;
            }

            .warning-box {
                background: #fffbeb;
                border: 1px solid #fcd34d;
                border-radius: 8px;
                padding: 1rem;
            }
            .warning-box h4 {
                color: #b45309;
                font-weight: 600;
                margin: 0 0 0.5rem 0;
            }

            .danger-box {
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 8px;
                padding: 1rem;
            }
            .danger-box h4 {
                color: #b91c1c;
                font-weight: 600;
                margin: 0 0 0.5rem 0;
            }

            .success-box {
                background: #f0fdf4;
                border: 1px solid #86efac;
                border-radius: 8px;
                padding: 1rem;
            }
            .success-box h4 {
                color: #166534;
                font-weight: 600;
                margin: 0 0 0.5rem 0;
            }

            /* ===== FORM SECTIONS ===== */
            .form-section {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 1rem;
            }

            /* ===== SCROLLBAR ===== */
            .scrollbar-dark::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            .scrollbar-dark::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 3px;
            }
            .scrollbar-dark::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }
            .scrollbar-dark::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            /* ===== UTILITY CLASSES ===== */
            .mono {
                font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
            }

            .text-white { color: #1e293b !important; }
            .text-gray-200 { color: #334155 !important; }
            .text-gray-300 { color: #475569 !important; }
            .text-gray-400 { color: #64748b !important; }
            .text-gray-500 { color: #94a3b8 !important; }
            .text-gray-600 { color: #475569 !important; }

            .text-blue-400 { color: #2563eb !important; }
            .text-blue-400:hover { color: #1d4ed8 !important; }
            .text-green-400 { color: #059669 !important; }
            .text-green-400:hover { color: #047857 !important; }
            .text-yellow-400 { color: #d97706 !important; }
            .text-yellow-400:hover { color: #b45309 !important; }
            .text-red-400 { color: #dc2626 !important; }
            .text-red-400:hover { color: #b91c1c !important; }
            .text-purple-400 { color: #7c3aed !important; }

            .bg-gray-700 { background: #f1f5f9 !important; }
            .bg-gray-700\/50 { background: rgba(241, 245, 249, 0.5) !important; }
            .bg-gray-800 { background: #e2e8f0 !important; }
            .bg-gray-800\/50 { background: rgba(226, 232, 240, 0.5) !important; }
            .bg-gray-900 { background: #f8fafc !important; }

            .border-gray-600 { border-color: #e2e8f0 !important; }
            .border-gray-700 { border-color: #e2e8f0 !important; }
            .border-gray-700\/50 { border-color: rgba(226, 232, 240, 0.5) !important; }

            .hover\:bg-gray-700:hover { background: #f1f5f9 !important; }
            .hover\:bg-gray-600\/50:hover { background: rgba(241, 245, 249, 0.8) !important; }

            /* Progress bars */
            .bg-green-500 { background: #10b981 !important; }
            .bg-yellow-500 { background: #f59e0b !important; }
            .bg-red-500 { background: #ef4444 !important; }
            .bg-blue-500 { background: #3b82f6 !important; }
            .bg-purple-500 { background: #8b5cf6 !important; }

            /* Alerts */
            .bg-green-500\/10 { background: rgba(16, 185, 129, 0.1) !important; }
            .bg-green-500\/20 { background: rgba(16, 185, 129, 0.15) !important; }
            .bg-red-500\/10 { background: rgba(239, 68, 68, 0.1) !important; }
            .bg-red-500\/20 { background: rgba(239, 68, 68, 0.15) !important; }
            .bg-yellow-500\/10 { background: rgba(245, 158, 11, 0.1) !important; }
            .bg-yellow-500\/20 { background: rgba(245, 158, 11, 0.15) !important; }
            .bg-blue-500\/10 { background: rgba(59, 130, 246, 0.1) !important; }
            .bg-blue-500\/20 { background: rgba(59, 130, 246, 0.15) !important; }
            .bg-purple-500\/20 { background: rgba(139, 92, 246, 0.15) !important; }

            .border-green-500\/20 { border-color: rgba(16, 185, 129, 0.3) !important; }
            .border-green-500\/30 { border-color: rgba(16, 185, 129, 0.4) !important; }
            .border-red-500\/20 { border-color: rgba(239, 68, 68, 0.3) !important; }
            .border-red-500\/30 { border-color: rgba(239, 68, 68, 0.4) !important; }
            .border-yellow-500\/20 { border-color: rgba(245, 158, 11, 0.3) !important; }
            .border-blue-500\/20 { border-color: rgba(59, 130, 246, 0.3) !important; }
            .border-blue-500\/30 { border-color: rgba(59, 130, 246, 0.4) !important; }

            /* Animation */
            .animate-pulse {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.6; }
            }

            /* Code blocks */
            pre {
                background: #1e293b !important;
                color: #a5f3fc !important;
                padding: 1rem;
                border-radius: 8px;
                overflow-x: auto;
                font-size: 0.8125rem;
            }
            code {
                font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-slate-200 shadow-sm">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pb-8">
                {{ $slot }}
            </main>
        </div>

        <!-- Toast Notifications -->
        <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>
    </body>
</html>
