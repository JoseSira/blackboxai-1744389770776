<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/BusinessController.php';

$auth = new AuthController();
$auth->requireAuth();

// Get current user and business data
$currentUser = $auth->getCurrentUser();
$currentBusiness = $auth->getCurrentBusiness();

// Get business stats
$businessController = new BusinessController();
$stats = $businessController->getBusinessStats($currentBusiness['id']);

// Start output buffering
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Welcome Section -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            Bienvenido, <?= htmlspecialchars($currentUser['username']) ?>
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            <?= htmlspecialchars($currentBusiness['name']) ?>
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Sales Today -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-cash-register text-2xl text-indigo-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Ventas Hoy
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    $<?= number_format($stats['today_sales'], 2) ?>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products in Stock -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-box text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Productos en Stock
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    <?= number_format($stats['total_products']) ?>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Alertas de Stock
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    <?= number_format($stats['low_stock_count']) ?>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Clientes
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    <?= number_format($stats['total_customers']) ?>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php if ($auth->hasPermission('manage_sales')): ?>
            <a href="/pos.php" class="flex flex-col items-center p-4 border rounded-lg hover:bg-gray-50">
                <i class="fas fa-cash-register text-2xl text-indigo-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Nueva Venta</span>
            </a>
            <?php endif; ?>

            <?php if ($auth->hasPermission('manage_products')): ?>
            <a href="/products.php" class="flex flex-col items-center p-4 border rounded-lg hover:bg-gray-50">
                <i class="fas fa-box text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Gestionar Productos</span>
            </a>
            <?php endif; ?>

            <?php if ($auth->hasPermission('manage_customers')): ?>
            <a href="/customers.php" class="flex flex-col items-center p-4 border rounded-lg hover:bg-gray-50">
                <i class="fas fa-users text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Gestionar Clientes</span>
            </a>
            <?php endif; ?>

            <?php if ($auth->hasPermission('view_reports')): ?>
            <a href="/sales.php" class="flex flex-col items-center p-4 border rounded-lg hover:bg-gray-50">
                <i class="fas fa-chart-bar text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Ver Ventas</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/includes/layout.php';
?>
