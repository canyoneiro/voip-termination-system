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
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
                min-height: 100vh;
            }

            .login-card {
                background: white;
                border-radius: 16px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            }

            .dark-input {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                color: #1e293b;
                border-radius: 8px;
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                width: 100%;
                transition: all 0.15s ease;
            }

            .dark-input:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
                outline: none;
                background: white;
            }

            .btn-primary {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                width: 100%;
                transition: all 0.15s ease;
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
                transform: translateY(-1px);
            }

            .alert-error {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #991b1b;
                padding: 0.75rem 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
                font-size: 0.875rem;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <!-- Logo -->
            <div class="mb-6">
                <div class="flex items-center justify-center">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <h1 class="text-white text-2xl font-bold text-center mt-2">Portal de Cliente</h1>
                <p class="text-blue-100 text-center text-sm mt-1">Accede a tu cuenta</p>
            </div>

            <!-- Login Card -->
            <div class="login-card w-full sm:max-w-md p-8">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <p class="text-blue-100 text-xs mt-6">
                &copy; {{ date('Y') }} {{ config('app.name', 'VoIP Panel') }}. Todos los derechos reservados.
            </p>
        </div>
    </body>
</html>
