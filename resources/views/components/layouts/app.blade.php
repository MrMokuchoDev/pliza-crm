<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'MiniCRM') }}</title>

    <!-- Prevent FOUC (Flash of Unstyled Content) for dark mode -->
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- Livewire Styles (includes Alpine.js) -->
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-gradient {
            background: linear-gradient(180deg, #1e3a5f 0%, #0f172a 100%);
        }
        .nav-item-active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-left: 3px solid #3b82f6;
        }
        .nav-item:hover:not(.nav-item-active) {
            background: rgba(255, 255, 255, 0.05);
        }
        /* Sortable.js styles */
        .sortable-ghost {
            opacity: 0.4;
            background: #dbeafe;
        }
        .dark .sortable-ghost {
            background: #1e3a8a;
        }
        .sortable-chosen {
            opacity: 0.5;
        }
        .sortable-drag {
            opacity: 0;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-40 w-64 sidebar-gradient transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:z-auto shadow-xl"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white">{{ config('app.name', 'MiniCRM') }}</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- User Info Mini -->
            <div class="px-4 py-3 mx-3 mb-4 bg-white/5 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-semibold shadow-md">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="px-3 flex-1">
                <p class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Principal</p>
                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('dashboard') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                        <div class="w-8 h-8 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        Dashboard
                    </a>

                    <!-- Contactos -->
                    @if(auth()->user()?->canAccessLeads())
                    <a href="{{ route('leads.index') }}"
                       class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('leads.*') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                        <div class="w-8 h-8 rounded-lg {{ request()->routeIs('leads.*') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('leads.*') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        Contactos
                    </a>
                    @endif

                    @if(auth()->user()?->canAccessDeals())
                    <!-- Negocios Lista -->
                    @php $isDealsActive = request()->routeIs('deals.index') || request()->routeIs('deals.show'); @endphp
                    <a href="{{ route('deals.index') }}"
                       class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ $isDealsActive ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                        <div class="w-8 h-8 rounded-lg {{ $isDealsActive ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 {{ $isDealsActive ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        Negocios
                    </a>

                    <!-- Pipeline/Kanban -->
                    <a href="{{ route('deals.kanban') }}"
                       class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('deals.kanban') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                        <div class="w-8 h-8 rounded-lg {{ request()->routeIs('deals.kanban') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('deals.kanban') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                        </div>
                        Pipeline
                    </a>
                    @endif
                </div>

                @if(auth()->user()?->hasAnyConfigPermission())
                <!-- Sección Configuración - Basada en permisos -->
                @php
                    $configRoutes = ['sale-phases.*', 'sites.*', 'users.*', 'roles.*', 'custom-fields.*', 'maintenance', 'updates'];
                    $isConfigActive = collect($configRoutes)->contains(fn($route) => request()->routeIs($route));
                @endphp
                <div x-data="{ configOpen: {{ $isConfigActive ? 'true' : 'false' }} }" class="mt-6">
                    <!-- Accordion Header -->
                    <button @click="configOpen = !configOpen"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-400 transition">
                        <span>Configuraci&oacute;n</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': configOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="configOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="space-y-1 mt-2">

                        @if(auth()->user()?->canManagePhases())
                        <!-- Fases de Venta -->
                        <a href="{{ route('sale-phases.index') }}"
                           class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('sale-phases.*') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                            <div class="w-8 h-8 rounded-lg {{ request()->routeIs('sale-phases.*') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('sale-phases.*') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            Fases de Venta
                        </a>
                        @endif

                        @if(auth()->user()?->canManageSites())
                        <!-- Sitios Web -->
                        <a href="{{ route('sites.index') }}"
                           class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('sites.*') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                            <div class="w-8 h-8 rounded-lg {{ request()->routeIs('sites.*') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('sites.*') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </div>
                            Sitios Web
                        </a>
                        @endif

                        @if(auth()->user()?->canManageUsers())
                        <!-- Usuarios -->
                        <a href="{{ route('users.index') }}"
                           class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('users.*') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                            <div class="w-8 h-8 rounded-lg {{ request()->routeIs('users.*') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('users.*') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            Usuarios
                        </a>
                        @endif

                        @if(auth()->user()?->canManageRoles())
                        <!-- Roles y Permisos -->
                        <a href="{{ route('roles.index') }}"
                           class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('roles.*') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                            <div class="w-8 h-8 rounded-lg {{ request()->routeIs('roles.*') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('roles.*') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            Roles y Permisos
                        </a>
                        @endif

                        @if(auth()->user()?->canManageCustomFields())
                        <!-- Campos Personalizados -->
                        <a href="{{ route('custom-fields.index') }}"
                           class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('custom-fields.*') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                            <div class="w-8 h-8 rounded-lg {{ request()->routeIs('custom-fields.*') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('custom-fields.*') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            Campos Personalizados
                        </a>
                        @endif

                        @if(auth()->user()?->canAccessMaintenance())
                        <!-- Mantenimiento -->
                        <a href="{{ route('maintenance') }}"
                           class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('maintenance') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                            <div class="w-8 h-8 rounded-lg {{ request()->routeIs('maintenance') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('maintenance') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            Mantenimiento
                        </a>
                        @endif

                        @if(auth()->user()?->canManageUpdates())
                        <!-- Actualizaciones -->
                        <a href="{{ route('updates') }}"
                           class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 {{ request()->routeIs('updates') ? 'nav-item-active text-blue-400' : 'text-gray-300' }}">
                            <div class="w-8 h-8 rounded-lg {{ request()->routeIs('updates') ? 'bg-blue-500/20' : 'bg-white/5' }} flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('updates') ? 'text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            Actualizaciones
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 mt-auto">
                <div class="p-3 bg-gradient-to-r from-blue-600/20 to-indigo-600/20 rounded-xl border border-blue-500/20">
                    <p class="text-xs text-gray-400 mb-2">Versi&oacute;n {{ config('version.current', '1.0.0') }}</p>
                    <p class="text-xs text-gray-500">Sistema de gesti&oacute;n de leads</p>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black bg-opacity-50 lg:hidden"
             x-cloak>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-screen lg:ml-0 overflow-x-hidden">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-16 flex items-center justify-between px-4 lg:px-6 transition-colors duration-200">
                <!-- Mobile: Menu button + Title -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <!-- Page Title -->
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-2" x-data="{ open: false }">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode; document.documentElement.classList.toggle('dark', darkMode)"
                            class="relative p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                            :title="darkMode ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'">
                        <!-- Sun icon (shown in dark mode) -->
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg x-show="!darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    <!-- Notifications (placeholder) -->
                    <button class="relative p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>

                    <!-- User dropdown -->
                    <div class="relative">
                        <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition focus:outline-none">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white text-sm font-semibold shadow-sm">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden sm:block">{{ auth()->user()->name ?? 'Usuario' }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-2 z-50"
                             x-cloak>
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name ?? 'Usuario' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Mi Perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Cerrar Sesi&oacute;n
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-4 lg:px-6 py-4 lg:py-6 min-w-0">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Page-specific Scripts -->
    @stack('scripts')

    <!-- Global Notifications & Helpers -->
    <script>
        window.confirmAction = function(options) {
            const isDark = document.documentElement.classList.contains('dark');
            return Swal.fire({
                title: options.title || '¿Estás seguro?',
                text: options.text || '',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmColor || '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: options.confirmText || 'Sí, continuar',
                cancelButtonText: options.cancelText || 'Cancelar',
                background: isDark ? '#1f2937' : '#ffffff',
                color: isDark ? '#f3f4f6' : '#1f2937'
            });
        }

        // Listen for Livewire notifications
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (data) => {
                const isDark = document.documentElement.classList.contains('dark');
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    width: '380px',
                    padding: '12px 16px',
                    background: isDark ? '#1f2937' : '#ffffff',
                    color: isDark ? '#f3f4f6' : '#1f2937',
                    customClass: {
                        popup: 'text-sm',
                        title: 'text-sm font-medium'
                    }
                });
                Toast.fire({
                    icon: data.type || 'success',
                    title: data.message
                });
            });
        });
    </script>
</body>
</html>
