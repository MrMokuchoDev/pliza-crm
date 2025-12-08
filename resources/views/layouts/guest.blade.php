<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MiniCRM') }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Livewire Styles (includes Alpine.js) -->
    @livewireStyles

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 50%, #1e293b 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen gradient-bg flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500 rounded-full opacity-10 blur-3xl"></div>
        </div>

        <!-- Logo -->
        <div class="relative z-10 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ config('app.name', 'MiniCRM') }}</h1>
                    <p class="text-blue-200 text-xs">Sistema de Gestión de Leads</p>
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="relative z-10 w-full sm:max-w-md glass-card shadow-2xl rounded-2xl overflow-hidden">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <p class="relative z-10 mt-8 text-center text-blue-200 text-sm">
            &copy; {{ date('Y') }} {{ config('app.name', 'MiniCRM') }}. Todos los derechos reservados.
        </p>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
