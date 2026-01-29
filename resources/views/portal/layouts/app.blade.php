<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VoIP Panel') }} - Portal de Cliente</title>

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

            /* Navigation */
            .portal-nav {
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .portal-nav-link {
                color: rgba(255, 255, 255, 0.8);
                padding: 0.5rem 1rem;
                border-radius: 6px;
                font-weight: 500;
                font-size: 0.875rem;
                transition: all 0.15s ease;
            }

            .portal-nav-link:hover {
                color: white;
                background: rgba(255, 255, 255, 0.1);
            }

            .portal-nav-link.active {
                color: white;
                background: rgba(255, 255, 255, 0.15);
            }

            /* Cards */
            .dark-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            }

            /* Stat Cards */
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

            /* Tables */
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

            /* Inputs */
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

            .dark-select {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                color: #1e293b;
                border-radius: 8px;
                padding: 0.625rem 0.875rem;
                font-size: 0.875rem;
            }

            /* Buttons */
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
            }
            .btn-primary:hover {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
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
            }

            /* Badges */
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.625rem;
                border-radius: 6px;
                font-size: 0.75rem;
                font-weight: 600;
            }
            .badge-green { background: #dcfce7; color: #166534; }
            .badge-red { background: #fee2e2; color: #991b1b; }
            .badge-yellow { background: #fef3c7; color: #92400e; }
            .badge-blue { background: #dbeafe; color: #1e40af; }
            .badge-gray { background: #f1f5f9; color: #475569; }

            /* Alerts */
            .alert-success {
                background: #f0fdf4;
                border: 1px solid #86efac;
                color: #166534;
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
            }

            .alert-error {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #991b1b;
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
            }

            /* Pagination */
            .pagination {
                display: flex;
                gap: 0.25rem;
            }
            .pagination a, .pagination span {
                padding: 0.5rem 0.75rem;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .pagination a {
                color: #475569;
                background: white;
                border: 1px solid #e2e8f0;
            }
            .pagination a:hover {
                background: #f8fafc;
            }
            .pagination .active span {
                background: #3b82f6;
                color: white;
            }

            .mono {
                font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="portal-nav">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Logo -->
                        <div class="flex items-center">
                            <a href="{{ route('portal.dashboard') }}" class="flex items-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="ml-2 text-white font-semibold text-lg">Portal de Cliente</span>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden md:flex items-center space-x-2">
                            <a href="{{ route('portal.dashboard') }}" class="portal-nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('portal.cdrs.index') }}" class="portal-nav-link {{ request()->routeIs('portal.cdrs.*') ? 'active' : '' }}">
                                CDRs
                            </a>
                            <a href="{{ route('portal.ips.index') }}" class="portal-nav-link {{ request()->routeIs('portal.ips.*') ? 'active' : '' }}">
                                IPs
                            </a>
                            <a href="{{ route('portal.profile.show') }}" class="portal-nav-link {{ request()->routeIs('portal.profile.*') ? 'active' : '' }}">
                                Perfil
                            </a>
                        </div>

                        <!-- User Menu -->
                        <div class="flex items-center">
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-white hover:text-white/80 transition">
                                    <span class="text-sm font-medium mr-2">{{ auth('customer')->user()->name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                                    <a href="{{ route('portal.profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Mi Perfil
                                    </a>
                                    <a href="{{ route('portal.profile.password') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Cambiar Password
                                    </a>
                                    <hr class="my-1">
                                    <form method="POST" action="{{ route('portal.logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            Cerrar Sesion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-slate-200 shadow-sm">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                @if (session('success'))
                    <div class="alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert-error">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="pb-8">
                {{ $slot }}
            </main>
        </div>

        <!-- Alpine.js for dropdown -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </body>
</html>
