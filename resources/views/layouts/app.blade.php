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
            :root {
                --sidebar-width: 260px;
                --sidebar-collapsed-width: 70px;
            }

            * {
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: #f1f5f9;
                min-height: 100vh;
                color: #334155;
                margin: 0;
            }

            /* ===== LAYOUT ===== */
            .app-layout {
                display: flex;
                min-height: 100vh;
            }

            .sidebar {
                width: var(--sidebar-width);
                background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 50;
                display: flex;
                flex-direction: column;
                transition: transform 0.3s ease, width 0.3s ease;
            }

            .main-content {
                flex: 1;
                margin-left: var(--sidebar-width);
                min-height: 100vh;
                transition: margin-left 0.3s ease;
            }

            @media (max-width: 1024px) {
                .sidebar {
                    transform: translateX(-100%);
                }
                .sidebar.open {
                    transform: translateX(0);
                }
                .main-content {
                    margin-left: 0;
                }
                .sidebar-overlay {
                    display: none;
                    position: fixed;
                    inset: 0;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 40;
                }
                .sidebar-overlay.open {
                    display: block;
                }
            }

            /* ===== CARDS ===== */
            .dark-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            }

            /* ===== STAT CARDS ===== */
            .stat-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1.25rem;
                position: relative;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                overflow: hidden;
            }
            .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
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
                padding: 0.75rem 1rem;
                text-align: left;
                border-bottom: 1px solid #e2e8f0;
            }
            .dark-table td {
                padding: 0.75rem 1rem;
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
                color: #334155;
                border-radius: 8px;
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                transition: all 0.15s ease;
                width: 100%;
            }
            .dark-input:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                outline: none;
            }
            .dark-input::placeholder {
                color: #94a3b8;
            }

            .dark-select {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                color: #334155;
                border-radius: 8px;
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                cursor: pointer;
            }
            .dark-select:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                outline: none;
            }
            .dark-select option {
                background: #ffffff;
                color: #334155;
            }

            /* ===== BUTTONS ===== */
            .btn-primary {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .btn-primary:hover {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: #ffffff;
                color: #475569;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                border: 1px solid #cbd5e1;
                cursor: pointer;
                transition: all 0.15s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .btn-secondary:hover {
                background: #f8fafc;
                border-color: #94a3b8;
            }

            .btn-danger {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .btn-danger:hover {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
            }

            .btn-success {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .btn-success:hover {
                background: linear-gradient(135deg, #059669 0%, #047857 100%);
                box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            }

            /* ===== BADGES ===== */
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 0.2rem 0.5rem;
                border-radius: 5px;
                font-size: 0.7rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
            .badge-green { background: #dcfce7; color: #166534; }
            .badge-red { background: #fee2e2; color: #991b1b; }
            .badge-yellow { background: #fef3c7; color: #92400e; }
            .badge-blue { background: #dbeafe; color: #1e40af; }
            .badge-purple { background: #ede9fe; color: #5b21b6; }
            .badge-gray { background: #f1f5f9; color: #475569; }
            .badge-orange { background: #ffedd5; color: #9a3412; }

            /* ===== INFO BOXES ===== */
            .info-box {
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                border-radius: 8px;
                padding: 1rem;
                color: #1e40af;
            }
            .warning-box {
                background: #fffbeb;
                border: 1px solid #fcd34d;
                border-radius: 8px;
                padding: 1rem;
                color: #92400e;
            }
            .danger-box {
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 8px;
                padding: 1rem;
                color: #991b1b;
            }
            .success-box {
                background: #f0fdf4;
                border: 1px solid #86efac;
                border-radius: 8px;
                padding: 1rem;
                color: #166534;
            }

            /* ===== SCROLLBAR ===== */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: #f1f5f9;
            }
            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            /* ===== UTILITY ===== */
            .mono {
                font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
            }

            .animate-pulse {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.6; }
            }

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

            /* Progress bars */
            .bg-green-500 { background: #10b981 !important; }
            .bg-yellow-500 { background: #f59e0b !important; }
            .bg-red-500 { background: #ef4444 !important; }
            .bg-blue-500 { background: #3b82f6 !important; }
            .bg-purple-500 { background: #8b5cf6 !important; }

            /* Light theme text color overrides */
            .text-white { color: #1e293b !important; }
            .text-gray-100 { color: #334155 !important; }
            .text-gray-200 { color: #475569 !important; }
            .text-gray-300 { color: #64748b !important; }
            .text-gray-400 { color: #94a3b8 !important; }
            .text-gray-500 { color: #64748b !important; }
            .text-gray-600 { color: #475569 !important; }

            /* Light theme backgrounds */
            .bg-gray-700 { background: #e2e8f0 !important; }
            .bg-gray-800 { background: #f1f5f9 !important; }
            .bg-gray-800\/50 { background: rgba(241, 245, 249, 0.7) !important; }
            .bg-gray-900 { background: #e2e8f0 !important; }

            /* Border colors */
            .border-gray-700 { border-color: #e2e8f0 !important; }
            .border-gray-700\/50 { border-color: rgba(226, 232, 240, 0.5) !important; }
            .border-gray-600 { border-color: #cbd5e1 !important; }

            /* Hover states */
            .hover\:bg-gray-700:hover { background: #e2e8f0 !important; }
            .hover\:bg-gray-800:hover { background: #f1f5f9 !important; }
            .hover\:bg-gray-800\/50:hover { background: rgba(241, 245, 249, 0.7) !important; }
            .hover\:text-white:hover { color: #1e293b !important; }
            .hover\:text-blue-300:hover { color: #2563eb !important; }
            .hover\:text-blue-400:hover { color: #3b82f6 !important; }

            /* Links */
            a.text-blue-400 { color: #2563eb; }
            a.text-blue-400:hover { color: #1d4ed8; }

            /* Form labels */
            label { color: #475569; }

            /* Pre/code blocks keep dark for contrast */
            pre {
                background: #1e293b !important;
                color: #e2e8f0 !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="app-layout" x-data="{ sidebarOpen: false }">
            <!-- Sidebar Overlay (mobile) -->
            <div class="sidebar-overlay" :class="{ 'open': sidebarOpen }" @click="sidebarOpen = false"></div>

            <!-- Sidebar -->
            @include('layouts.navigation')

            <!-- Main Content -->
            <div class="main-content">
                <!-- Top Bar -->
                <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
                    <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                        <!-- Mobile Menu Button -->
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Page Title -->
                        @isset($header)
                            <div class="flex-1 lg:flex-none">
                                {{ $header }}
                            </div>
                        @else
                            <div class="flex-1"></div>
                        @endisset

                        <!-- Right Side -->
                        <div class="flex items-center gap-4">
                            <!-- Live Calls Indicator -->
                            @php $activeCallsCount = \App\Models\ActiveCall::count(); @endphp
                            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg">
                                <span class="relative flex h-2 w-2">
                                    @if($activeCallsCount > 0)
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    @else
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-slate-400"></span>
                                    @endif
                                </span>
                                <span class="text-sm font-semibold text-slate-700">{{ $activeCallsCount }}</span>
                                <span class="text-xs text-slate-500">llamadas</span>
                            </div>

                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <span class="hidden md:block text-sm font-medium text-slate-700 max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition
                                     class="absolute right-0 mt-2 w-56 rounded-xl bg-white border border-slate-200 shadow-lg py-1 z-50">
                                    <div class="px-4 py-3 border-b border-slate-100">
                                        <p class="text-sm font-medium text-slate-800">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Mi Perfil
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Cerrar Sesion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4 lg:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>
    </body>
</html>
