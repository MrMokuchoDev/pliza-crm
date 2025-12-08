<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalar MiniCRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .step-active { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .step-completed { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .step-pending { background: #e5e7eb; }
        .installer-gradient { background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <div class="installer-gradient text-white py-6 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">MiniCRM</h1>
                        <p class="text-blue-200 text-sm">Instalador v<?= htmlspecialchars($version) ?></p>
                    </div>
                </div>
                <?php if ($step !== 'welcome' && $step !== 'complete' && $step !== 'already-installed'): ?>
                <div class="text-right">
                    <p class="text-sm text-blue-200">Paso <?= $installer->getStepNumber() ?> de <?= $installer->getTotalSteps() ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Progress Steps -->
    <?php if ($step !== 'welcome' && $step !== 'complete' && $step !== 'already-installed'): ?>
    <div class="bg-white border-b shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <?php
                $steps = $installer->getSteps();
                $stepKeys = array_keys($steps);
                $currentIndex = array_search($step, $stepKeys);

                foreach ($steps as $key => $label):
                    $index = array_search($key, $stepKeys);
                    $isCompleted = $installer->isStepComplete($key);
                    $isCurrent = $key === $step;
                    $isPending = $index > $currentIndex;

                    if ($key === 'welcome') continue;
                ?>
                <div class="flex items-center <?= $key !== end($stepKeys) ? 'flex-1' : '' ?>">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                            <?php if ($isCompleted): ?>
                                step-completed text-white
                            <?php elseif ($isCurrent): ?>
                                step-active text-white
                            <?php else: ?>
                                step-pending text-gray-500
                            <?php endif; ?>
                        ">
                            <?php if ($isCompleted): ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            <?php else: ?>
                                <?= $index ?>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs mt-1 text-gray-500 hidden sm:block"><?= htmlspecialchars($label) ?></span>
                    </div>
                    <?php if ($key !== end($stepKeys) && $key !== 'welcome'): ?>
                    <div class="flex-1 h-1 mx-2 rounded <?= $isCompleted ? 'bg-green-500' : 'bg-gray-200' ?>"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
        <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Se encontraron errores</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <?php include $viewFile; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center py-6 text-gray-500 text-sm">
        <p>MiniCRM &copy; <?= date('Y') ?> - Sistema de Gesti&oacute;n de Leads</p>
    </div>

    <script>
        // Helper para confirmaciones
        function confirmAction(message) {
            return confirm(message);
        }

        // Validación de formularios
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...';
                }
            });
        });
    </script>
</body>
</html>
