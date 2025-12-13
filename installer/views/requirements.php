<?php
require_once $basePath . '/installer/helpers/RequirementsChecker.php';
$checker = new RequirementsChecker($basePath);
$requirements = $checker->getResults();
$summary = $checker->getSummary();
$allPassed = $checker->allPassed();
?>

<!-- Verificación de Requisitos -->
<div class="p-4 border-b border-gray-100">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Verificaci&oacute;n de Requisitos</h2>
            <p class="text-gray-600 text-sm">Comprobando que tu servidor cumple con los requisitos m&iacute;nimos.</p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold <?= $allPassed ? 'text-green-600' : 'text-yellow-600' ?>">
                <?= $summary['passed'] ?>/<?= $summary['total'] ?>
            </p>
            <p class="text-xs text-gray-500">requisitos</p>
        </div>
    </div>
</div>

<div class="p-4">
    <!-- Resumen compacto -->
    <div class="mb-4 p-3 rounded-lg <?= $allPassed ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' ?>">
        <div class="flex items-center gap-3">
            <?php if ($allPassed): ?>
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm text-green-800">Tu servidor est&aacute; listo para instalar Pliza CRM</p>
            <?php else: ?>
            <svg class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm text-yellow-800">Algunos requisitos no se cumplen. Corrige los problemas antes de continuar.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Layout de dos columnas -->
    <div class="grid lg:grid-cols-2 gap-4">
        <!-- Columna izquierda -->
        <div class="space-y-4">
            <!-- PHP Version -->
            <div>
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">PHP</h3>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded <?= $requirements['php']['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                                <?php if ($requirements['php']['passed']): ?>
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <?php else: ?>
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($requirements['php']['name']) ?></p>
                                <p class="text-xs text-gray-500">Actual: <?= htmlspecialchars($requirements['php']['current']) ?></p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?= $requirements['php']['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= $requirements['php']['passed'] ? 'OK' : 'Requerido' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Extensiones -->
            <div>
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Extensiones PHP</h3>
                <div class="bg-gray-50 rounded-lg divide-y divide-gray-200">
                    <?php foreach ($requirements['extensions'] as $ext => $info): ?>
                    <div class="px-3 py-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded <?= $info['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                                <?php if ($info['passed']): ?>
                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <?php else: ?>
                                <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <span class="text-sm text-gray-900"><?= htmlspecialchars($info['name']) ?></span>
                        </div>
                        <span class="text-xs <?= $info['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $info['passed'] ? 'OK' : 'Falta' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="space-y-4">
            <!-- Directorios -->
            <div>
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Permisos de Directorios</h3>
                <div class="bg-gray-50 rounded-lg divide-y divide-gray-200">
                    <?php foreach ($requirements['directories'] as $dir => $info): ?>
                    <div class="px-3 py-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded <?= $info['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                                <?php if ($info['passed']): ?>
                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <?php else: ?>
                                <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <span class="text-sm text-gray-700 font-mono"><?= htmlspecialchars($dir) ?></span>
                        </div>
                        <span class="text-xs <?= $info['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $info['passed'] ? 'OK' : 'Sin permisos' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Composer -->
            <div>
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Dependencias</h3>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded <?= $requirements['composer']['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                                <?php if ($requirements['composer']['passed']): ?>
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <?php else: ?>
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($requirements['composer']['name']) ?></p>
                                <p class="text-xs text-gray-500">vendor/autoload.php</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?= $requirements['composer']['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= $requirements['composer']['passed'] ? 'OK' : 'No encontrado' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex justify-between">
    <a href="?step=welcome"
       class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-100 transition text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
        </svg>
        Atr&aacute;s
    </a>

    <?php if ($allPassed): ?>
    <form method="POST" action="?step=requirements">
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-sm text-sm">
            Continuar
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>
    <?php else: ?>
    <button disabled
            class="inline-flex items-center gap-2 px-5 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed text-sm">
        Corrige los errores primero
    </button>
    <?php endif; ?>
</div>
