<div class="w-full space-y-6">
    @php $stats = $this->statistics; @endphp

    @if(!$stats['site'])
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Sitio no encontrado</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">El sitio solicitado no existe o ha sido eliminado.</p>
            <a href="{{ route('sites.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                Volver a sitios
            </a>
        </div>
    @else
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('sites.index') }}" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['site']->name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $stats['site']->domain }}</p>
                </div>
            </div>

            <!-- Date Range Selector -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                    @foreach(['7d' => '7 dias', '30d' => '30 dias', '90d' => '90 dias', '12m' => '12 meses', 'all' => 'Todo'] as $key => $label)
                        <button wire:click="setDateRange('{{ $key }}')"
                                class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $periodPreset === $key ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="dateFrom"
                           class="px-2 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <span class="text-gray-400">-</span>
                    <input type="date" wire:model.live="dateTo"
                           class="px-2 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Total Leads -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['totals']['leads']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Leads</p>
                    </div>
                </div>
            </div>

            <!-- Total Deals -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['totals']['deals']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Negocios</p>
                    </div>
                </div>
            </div>

            <!-- Won -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['totals']['won']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ganados</p>
                    </div>
                </div>
            </div>

            <!-- Lost -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['totals']['lost']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Perdidos</p>
                    </div>
                </div>
            </div>

            <!-- Value -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($stats['totals']['value'], 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Rate & Source Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Conversion Rate -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Tasa de Conversion</h3>
                <div class="flex items-center justify-center">
                    <div class="relative w-40 h-40">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="#e5e7eb" class="dark:stroke-gray-700" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="#3b82f6" stroke-width="3"
                                    stroke-dasharray="{{ $stats['conversion_rate'] }}, 100"
                                    stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['conversion_rate'] }}%</span>
                        </div>
                    </div>
                </div>
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                    Leads que avanzaron en el pipeline o fueron ganados
                </p>
            </div>

            <!-- Source Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 lg:col-span-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Distribucion por Fuente</h3>
                @if(count($stats['leads_by_source']) > 0)
                    <div class="space-y-4">
                        @php
                            $totalBySource = array_sum($stats['leads_by_source']);
                        @endphp
                        @foreach($stats['leads_by_source'] as $source => $count)
                            @php
                                $percentage = $totalBySource > 0 ? round(($count / $totalBySource) * 100, 1) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $this->getSourceTypeLabel($source) }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $count }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                    <div class="{{ $this->getSourceTypeColor($source) }} h-2.5 rounded-full transition-all duration-500"
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p>No hay datos de fuentes en este periodo</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Leads Timeline Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Leads en el Tiempo</h3>
            @if(count($stats['leads_by_period']) > 0)
                <div class="h-64" wire:ignore>
                    <canvas id="leadsChart"></canvas>
                </div>
            @else
                <div class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">
                    <p>No hay datos en este periodo</p>
                </div>
            @endif
        </div>

        <!-- Deals by Phase & Recent Leads -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Deals by Phase -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Negocios por Fase</h3>
                @if(count($stats['deals_by_phase']) > 0)
                    <div class="space-y-3">
                        @foreach($stats['deals_by_phase'] as $phase)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $phase['color'] ?? '#6b7280' }}"></div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $phase['name'] }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $phase['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p>No hay negocios en este periodo</p>
                    </div>
                @endif
            </div>

            <!-- Recent Leads -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Leads Recientes</h3>
                @if($stats['recent_leads']->count() > 0)
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @foreach($stats['recent_leads'] as $lead)
                            <a href="{{ route('leads.show', $lead->id) }}"
                               target="_blank"
                               class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-700 border border-transparent transition group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/40 transition">
                                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                            {{ strtoupper(substr($lead->name ?? 'L', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-blue-700 dark:group-hover:text-blue-400 transition">{{ $lead->name ?? 'Sin nombre' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->phone ?? $lead->email ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 flex items-center gap-2">
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $lead->source_type->value === 'whatsapp_button' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' :
                                               ($lead->source_type->value === 'phone_button' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' :
                                               ($lead->source_type->value === 'contact_form' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400')) }}">
                                            {{ $this->getSourceTypeLabel($lead->source_type->value) }}
                                        </span>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $lead->created_at->diffForHumans() }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p>No hay leads en este periodo</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($stats['site'] && count($stats['leads_by_period']) > 0)
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('livewire:navigated', initChart);
            document.addEventListener('DOMContentLoaded', initChart);

            function initChart() {
                const ctx = document.getElementById('leadsChart');
                if (!ctx) return;

                // Destruir chart existente si hay
                if (window.leadsChartInstance) {
                    window.leadsChartInstance.destroy();
                }

                const data = @json($stats['leads_by_period']);
                const isDark = document.documentElement.classList.contains('dark');

                window.leadsChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Leads',
                            data: data.map(d => d.count),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6',
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
                                    color: isDark ? '#9ca3af' : '#6b7280'
                                },
                                grid: {
                                    color: isDark ? '#374151' : '#e5e7eb'
                                }
                            },
                            x: {
                                ticks: {
                                    color: isDark ? '#9ca3af' : '#6b7280'
                                },
                                grid: {
                                    color: isDark ? '#374151' : '#e5e7eb'
                                }
                            }
                        }
                    }
                });
            }

            // Reinicializar cuando Livewire actualiza
            Livewire.hook('morph.updated', () => {
                setTimeout(initChart, 100);
            });
        </script>
        @endpush
    @endif
</div>
