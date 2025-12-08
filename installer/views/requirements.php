<?php
require_once $basePath . '/installer/helpers/RequirementsChecker.php';
$checker = new RequirementsChecker($basePath);
$requirements = $checker->getResults();
$summary = $checker->getSummary();
$allPassed = $checker->allPassed();
?>

<!-- Verificación de Requisitos -->
<div class="p-6 border-b border-gray-100">
    <h2 class="text-xl font-bold text-gray-900">Verificaci&oacute;n de Requisitos</h2>
    <p class="text-gray-600 mt-1">Comprobando que tu servidor cumple con los requisitos m&iacute;nimos.</p>
</div>

<div class="p-6">
    <!-- Resumen -->
    <div class="mb-6 p-4 rounded-xl <?= $allPassed ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' ?>">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php if ($allPassed): ?>
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-green-800">Todos los requisitos cumplidos</p>
                    <p class="text-sm text-green-600">Tu servidor est&aacute; listo para instalar MiniCRM</p>
                </div>
                <?php else: ?>
                <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-yellow-800">Algunos requisitos no se cumplen</p>
                    <p class="text-sm text-yellow-600">Corrige los problemas antes de continuar</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold <?= $allPassed ? 'text-green-600' : 'text-yellow-600' ?>">
                    <?= $summary['passed'] ?>/<?= $summary['total'] ?>
                </p>
                <p class="text-sm text-gray-500">requisitos</p>
            </div>
        </div>
    </div>

    <!-- PHP Version -->
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Versi&oacute;n de PHP</h3>
        <div class="bg-gray-50 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg <?= $requirements['php']['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                        <?php if ($requirements['php']['passed']): ?>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?php else: ?>
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($requirements['php']['name']) ?></p>
                        <p class="text-sm text-gray-500">Versi&oacute;n actual: <?= htmlspecialchars($requirements['php']['current']) ?></p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium <?= $requirements['php']['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= $requirements['php']['passed'] ? 'OK' : 'Requerido' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Extensiones -->
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Extensiones de PHP</h3>
        <div class="bg-gray-50 rounded-xl divide-y divide-gray-200">
            <?php foreach ($requirements['extensions'] as $ext => $info): ?>
            <div class="p-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded <?= $info['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                        <?php if ($info['passed']): ?>
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?php else: ?>
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <span class="text-gray-900"><?= htmlspecialchars($info['name']) ?></span>
                    <span class="text-gray-400 text-sm">(<?= htmlspecialchars($ext) ?>)</span>
                </div>
                <span class="text-sm <?= $info['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $info['passed'] ? 'Instalada' : 'No instalada' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Directorios -->
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Permisos de Directorios</h3>
        <div class="bg-gray-50 rounded-xl divide-y divide-gray-200">
            <?php foreach ($requirements['directories'] as $dir => $info): ?>
            <div class="p-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded <?= $info['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                        <?php if ($info['passed']): ?>
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?php else: ?>
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <span class="text-gray-900 font-mono text-sm"><?= htmlspecialchars($dir) ?></span>
                </div>
                <span class="text-sm <?= $info['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $info['passed'] ? 'Escribible' : 'Sin permisos' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Composer -->
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Dependencias</h3>
        <div class="bg-gray-50 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg <?= $requirements['composer']['passed'] ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                        <?php if ($requirements['composer']['passed']): ?>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?php else: ?>
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($requirements['composer']['name']) ?></p>
                        <p class="text-sm text-gray-500">Carpeta vendor/ con autoload.php</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium <?= $requirements['composer']['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= $requirements['composer']['passed'] ? 'Instalado' : 'No encontrado' ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between">
    <a href="?step=welcome"
       class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
        </svg>
        Atr&aacute;s
    </a>

    <?php if ($allPassed): ?>
    <form method="POST" action="?step=requirements">
        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-sm">
            Continuar
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>
    <?php else: ?>
    <button disabled
            class="inline-flex items-center gap-2 px-6 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed">
        Corrige los errores primero
    </button>
    <?php endif; ?>
</div>
