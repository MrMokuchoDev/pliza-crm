<div class="w-full max-w-full overflow-x-hidden">
    <div class="mb-4 lg:mb-6">
        <h1 class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400">Resumen de tu actividad comercial</p>
    </div>

    <!-- Cards de Estadísticas - 2x2 en móvil -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6 lg:mb-8">
        <!-- Total Contactos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex-shrink-0">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 lg:ml-4 min-w-0">
                    <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Contactos</p>
                    <p class="text-lg lg:text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_leads']) }}</p>
                </div>
            </div>
            <div class="mt-2 lg:mt-4 text-xs lg:text-sm text-gray-500 dark:text-gray-400">
                <span class="text-green-600 dark:text-green-400 font-medium">+{{ $stats['leads_this_month'] }}</span> este mes
            </div>
        </div>

        <!-- Negocios Abiertos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 flex-shrink-0">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-3 lg:ml-4 min-w-0">
                    <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Abiertos</p>
                    <p class="text-lg lg:text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['open_deals']) }}</p>
                </div>
            </div>
            <div class="mt-2 lg:mt-4 text-xs lg:text-sm text-gray-500 dark:text-gray-400">
                <span class="font-medium">{{ $stats['total_deals'] }}</span> totales
            </div>
        </div>

        <!-- Negocios Ganados -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex-shrink-0">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 lg:ml-4 min-w-0">
                    <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Ganados</p>
                    <p class="text-lg lg:text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['won_deals']) }}</p>
                </div>
            </div>
            <div class="mt-2 lg:mt-4 text-xs lg:text-sm text-gray-500 dark:text-gray-400">
                Conv: <span class="text-green-600 dark:text-green-400 font-medium">{{ $stats['conversion_rate'] }}%</span>
            </div>
        </div>

        <!-- Valor Total -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex-shrink-0">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 lg:ml-4 min-w-0">
                    <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Valor</p>
                    <p class="text-lg lg:text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($stats['total_won_value'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-2 lg:mt-4 text-xs lg:text-sm text-gray-500 dark:text-gray-400">
                <span class="text-red-600 dark:text-red-400 font-medium">{{ $stats['lost_deals'] }}</span> perdidos
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <!-- Negocios por Fase -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
            <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white mb-3 lg:mb-4">Negocios por Fase</h3>
            <div class="space-y-3">
                @foreach($dealsByPhase as $phase)
                    <div>
                        <div class="flex justify-between text-xs lg:text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400 truncate mr-2">{{ $phase['name'] }}</span>
                            <span class="font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $phase['count'] }}
                                @if($phase['value'] > 0)
                                    <span class="text-gray-400 hidden sm:inline">({{ number_format($phase['value'], 0, ',', '.') }})</span>
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            @php
                                $maxCount = collect($dealsByPhase)->max('count') ?: 1;
                                $percentage = ($phase['count'] / $maxCount) * 100;
                            @endphp
                            <div class="h-2 rounded-full" style="width: {{ $percentage }}%; background-color: {{ $phase['color'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Leads por Fuente -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6" wire:ignore>
            <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white mb-3 lg:mb-4">Contactos por Fuente</h3>
            @if(count($leadsBySource) > 0)
                <div class="relative" style="height: 200px;">
                    <canvas id="leadsBySourceChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-40 text-gray-500 dark:text-gray-400 text-sm">
                    Sin datos disponibles
                </div>
            @endif
        </div>
    </div>

    <!-- Tendencia de Leads -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6 mb-6 lg:mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-3 lg:mb-4">
            <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white">Tendencia de Contactos</h3>
            <select wire:model.live="trendPeriod" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 w-full sm:w-auto">
                <option value="daily">Ultimos 30 dias</option>
                <option value="weekly">Ultimas 12 semanas</option>
                <option value="monthly">Ultimos 12 meses</option>
            </select>
        </div>
        <div class="relative" style="height: 250px;">
            <canvas id="leadsTrendChart"></canvas>
        </div>
    </div>

    <!-- Funnel de Conversión -->
    @if(count($conversionFunnel) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
        <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white mb-3 lg:mb-4">Funnel de Conversion</h3>
        <div class="flex flex-col items-center space-y-2">
            @foreach($conversionFunnel as $index => $stage)
                @php
                    $maxWidth = 100 - ($index * 10);
                    $maxWidth = max($maxWidth, 40);
                @endphp
                <div class="relative w-full flex justify-center">
                    <div
                        class="py-2 lg:py-3 px-3 lg:px-4 text-center text-white font-medium rounded transition-all duration-300"
                        style="width: {{ $maxWidth }}%; background-color: {{ $stage['color'] }};"
                    >
                        <span class="block text-sm lg:text-base">{{ $stage['name'] }}</span>
                        <span class="text-xs lg:text-sm opacity-90">{{ $stage['count'] }} ({{ $stage['percentage'] }}%)</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Scripts para gráficos -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detectar modo oscuro
            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
            const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

            // Colores para el gráfico de fuentes
            const sourceColors = {
                'WhatsApp': '#25D366',
                'Llamada': '#3B82F6',
                'Formulario': '#8B5CF6',
                'Manual': '#6B7280',
                'Desconocido': '#9CA3AF'
            };

            // Gráfico de Leads por Fuente (Doughnut)
            const leadsBySourceData = @json($leadsBySource);
            const sourceLabels = Object.keys(leadsBySourceData);
            const sourceValues = Object.values(leadsBySourceData);
            const sourceBackgrounds = sourceLabels.map(label => sourceColors[label] || '#6B7280');

            if (sourceLabels.length > 0) {
                const isMobile = window.innerWidth < 640;
                new Chart(document.getElementById('leadsBySourceChart'), {
                    type: 'doughnut',
                    data: {
                        labels: sourceLabels,
                        datasets: [{
                            data: sourceValues,
                            backgroundColor: sourceBackgrounds,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: isMobile ? 'bottom' : 'right',
                                labels: {
                                    boxWidth: isMobile ? 12 : 20,
                                    padding: isMobile ? 8 : 15,
                                    font: {
                                        size: isMobile ? 11 : 12
                                    },
                                    color: textColor
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de Tendencia de Leads (Line)
            let trendChart = null;
            function renderTrendChart(data) {
                const ctx = document.getElementById('leadsTrendChart');
                const isDark = document.documentElement.classList.contains('dark');
                const txtColor = isDark ? '#9CA3AF' : '#6B7280';
                const grdColor = isDark ? '#374151' : '#E5E7EB';

                if (trendChart) {
                    trendChart.destroy();
                }

                trendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(item => item.date),
                        datasets: [{
                            label: 'Contactos',
                            data: data.map(item => item.count),
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    color: txtColor
                                },
                                grid: {
                                    color: grdColor
                                }
                            },
                            x: {
                                ticks: {
                                    color: txtColor
                                },
                                grid: {
                                    color: grdColor
                                }
                            }
                        }
                    }
                });
            }

            // Renderizar gráfico inicial
            renderTrendChart(@json($leadsTrend));

            // Escuchar actualizaciones de Livewire
            Livewire.on('trendsUpdated', (event) => {
                renderTrendChart(event.trends);
            });
        });
    </script>
    @endpush
</div>
