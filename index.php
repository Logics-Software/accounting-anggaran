<?php
// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Override PHP upload settings if possible (only works if not disabled by server)
// This helps when php.ini cannot be modified
@ini_set('upload_max_filesize', '6M'); // 6M for buffer (app limit is 5MB)
@ini_set('post_max_size', '6M'); // 6M for buffer (app limit is 5MB)
@ini_set('max_execution_time', '300'); // 5 minutes for large uploads
@ini_set('max_input_time', '300');

// OPTIMIZATION: Pre-compute base directory (avoid multiple __DIR__ calls)
$baseDir = __DIR__;

// Autoload classes with namespace support
spl_autoload_register(function ($class) use ($baseDir) {
    // OPTIMIZATION: Check if class already loaded (prevent duplicate loads)
    if (class_exists($class, false)) {
        return;
    }
    
    // Special handling for Message class to avoid conflict
    // Always load core Message first, never load models/Message.php
    if ($class === 'Message') {
        $corePath = $baseDir . '/core/' . $class . '.php';
        if (file_exists($corePath)) {
            require_once $corePath; // OPTIMIZATION: Use require_once
        }
        // Never load models/Message.php to avoid conflict
        // Use MessageModel for database operations instead
        return;
    }
    
    // Convert namespace to directory path
    $classPath = str_replace('\\', '/', $class);
    
    // OPTIMIZATION: Build search paths with optimized order (most common first)
    $paths = [];
    
    // OPTIMIZATION: Check namespace-based paths first (most common)
    // Controllers with namespace (Controllers\Common\DashboardController) - Most common
    if (strpos($class, 'Controllers\\') === 0) {
        $relativePath = str_replace('Controllers\\', '', $class);
        $paths[] = $baseDir . '/controllers/' . str_replace('\\', '/', $relativePath) . '.php';
    }
    
    // Models with namespace (Models\Common\User) - Most common
    if (strpos($class, 'Models\\') === 0) {
        $relativePath = str_replace('Models\\', '', $class);
        $paths[] = $baseDir . '/models/' . str_replace('\\', '/', $relativePath) . '.php';
    }
    
    // Core classes (no namespace) - Frequently used
    $paths[] = $baseDir . '/core/' . $class . '.php';
    
    // Helper classes (no namespace) - Frequently used
    $paths[] = $baseDir . '/core/Helpers/' . $class . '.php';
    
    // OPTIMIZATION: Backward compatibility paths (less common, checked later)
    // Controllers without namespace (backward compatibility)
    $paths[] = $baseDir . '/controllers/Common/' . $class . '.php';
    $paths[] = $baseDir . '/controllers/' . $classPath . '.php';
    
    // Models without namespace (backward compatibility)
    $paths[] = $baseDir . '/models/Common/' . $class . '.php';
    $paths[] = $baseDir . '/models/Master/' . $class . '.php';
    $paths[] = $baseDir . '/models/Transaction/' . $class . '.php';
    $paths[] = $baseDir . '/models/Accounting/' . $class . '.php';
    $paths[] = $baseDir . '/models/' . $classPath . '.php';
    
    // OPTIMIZATION: Early exit on first match, use require_once
    foreach ($paths as $path) {
        // Skip models/Message.php to prevent conflict
        if (strpos($path, '/models/Message.php') !== false) {
            continue;
        }
        if (file_exists($path)) {
            require_once $path; // OPTIMIZATION: Use require_once to prevent duplicate loads
            return; // OPTIMIZATION: Early exit on match
        }
    }
    
    // Autoload vendor libraries (PhpSpreadsheet and dependencies)
    // Handle PSR-4 namespaces
    $vendorDir = $baseDir . '/vendor';
    
    // PhpOffice\PhpSpreadsheet namespace
    if (strpos($class, 'PhpOffice\\PhpSpreadsheet\\') === 0) {
        $relativeClass = substr($class, strlen('PhpOffice\\PhpSpreadsheet\\'));
        $file = $vendorDir . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // PSR\SimpleCache namespace
    if (strpos($class, 'Psr\\SimpleCache\\') === 0) {
        $relativeClass = substr($class, strlen('Psr\\SimpleCache\\'));
        $file = $vendorDir . '/psr/simple-cache/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Matrix namespace
    if (strpos($class, 'Matrix\\') === 0) {
        $relativeClass = substr($class, strlen('Matrix\\'));
        $file = $vendorDir . '/markbaker/matrix/classes/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Complex namespace
    if (strpos($class, 'Complex\\') === 0) {
        $relativeClass = substr($class, strlen('Complex\\'));
        $file = $vendorDir . '/markbaker/complex/classes/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Composer\Pcre namespace
    if (strpos($class, 'Composer\\Pcre\\') === 0) {
        $relativeClass = substr($class, strlen('Composer\\Pcre\\'));
        $file = $vendorDir . '/composer/pcre/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // ZipStream namespace (for PhpSpreadsheet Excel export)
    if (strpos($class, 'ZipStream\\') === 0) {
        $relativeClass = substr($class, strlen('ZipStream\\'));
        // maennchen/zipstream-php path
        $file = $vendorDir . '/maennchen/zipstream-php/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Start session
Session::start();

// IDLE TIMEOUT: Check if session has expired (before routing)
// This ensures expired sessions are caught early
if (Session::isActive() && Session::isExpired()) {
    // Session expired, destroy it
    Session::destroy();
    // Redirect to login will be handled by Auth::requireAuth() if needed
}

// Initialize router
$router = new Router();

// Root route - handled in Router dispatch

// Auth routes
$router->get('/login', 'Controllers\Common\AuthController', 'login');
$router->post('/login', 'Controllers\Common\AuthController', 'login');
$router->get('/logout', 'Controllers\Common\AuthController', 'logout');

// Download routes with error handling
$router->get('/download/file', 'Controllers\Common\DownloadController', 'file');
$router->get('/download/check', 'Controllers\Common\DownloadController', 'check');

// Dashboard routes
$router->get('/dashboard', 'Controllers\Common\DashboardController', 'index');

// Login Log routes (admin/manajemen only)
$router->get('/login-logs', 'Controllers\Common\LoginLogController', 'index');

// Backup Log routes (admin/manajemen only)
$router->get('/backup-log', 'Controllers\Common\BackupLogController', 'index');

// User management routes (admin/manajemen only)
$router->get('/users', 'Controllers\Common\UserController', 'index');
$router->get('/users/create', 'Controllers\Common\UserController', 'create');
$router->post('/users/create', 'Controllers\Common\UserController', 'create');
$router->get('/users/edit/{id}', 'Controllers\Common\UserController', 'edit');
$router->post('/users/edit/{id}', 'Controllers\Common\UserController', 'edit');
$router->get('/users/delete/{id}', 'Controllers\Common\UserController', 'delete');

// Profile routes
$router->get('/profile', 'Controllers\Common\ProfileController', 'index');
$router->post('/profile', 'Controllers\Common\ProfileController', 'update');
$router->get('/profile/change-password', 'Controllers\Common\ProfileController', 'changePassword');
$router->post('/profile/change-password', 'Controllers\Common\ProfileController', 'changePassword');
$router->get('/settings', 'Controllers\Common\ProfileController', 'settings');

// Konfigurasi routes (admin only)
$router->get('/konfigurasi', 'Controllers\Common\KonfigurasiController', 'index');
$router->post('/konfigurasi/store', 'Controllers\Common\KonfigurasiController', 'store');

// Backup database routes (admin only)
$router->get('/backup', 'Controllers\Common\BackupController', 'index');
$router->post('/backup/process', 'Controllers\Common\BackupController', 'process');
$router->get('/backup/download', 'Controllers\Common\BackupController', 'download');

// Message routes - specific routes first, then generic ones
$router->get('/messages/show/{id}', 'Controllers\Common\MessageController', 'show');
$router->get('/messages/delete/{id}', 'Controllers\Common\MessageController', 'delete');
$router->get('/messages/sent', 'Controllers\Common\MessageController', 'sent');
$router->get('/messages/create', 'Controllers\Common\MessageController', 'create');
$router->get('/messages/search', 'Controllers\Common\MessageController', 'search');
$router->get('/messages/searchUsers', 'Controllers\Common\MessageController', 'searchUsers');
$router->get('/messages/getUnreadCount', 'Controllers\Common\MessageController', 'getUnreadCount');
$router->get('/messages/markAllAsRead', 'Controllers\Common\MessageController', 'markAllAsRead');
$router->get('/messages/markAsRead', 'Controllers\Common\MessageController', 'markAsRead');
$router->post('/messages/store', 'Controllers\Common\MessageController', 'store');
$router->get('/messages', 'Controllers\Common\MessageController', 'index');

// Setting Bidang routes (admin/manajemen only)
$router->get('/setting-bidang', 'Controllers\Master\SettingBidangController', 'index');
$router->get('/setting-bidang/create', 'Controllers\Master\SettingBidangController', 'create');
$router->post('/setting-bidang/create', 'Controllers\Master\SettingBidangController', 'create');
$router->get('/setting-bidang/edit/{id}', 'Controllers\Master\SettingBidangController', 'edit');
$router->post('/setting-bidang/edit/{id}', 'Controllers\Master\SettingBidangController', 'edit');
$router->get('/setting-bidang/delete/{id}', 'Controllers\Master\SettingBidangController', 'delete');

// Setting Bagian routes (admin/manajemen only)
$router->get('/setting-bagian', 'Controllers\Master\SettingBagianController', 'index');
$router->get('/setting-bagian/create', 'Controllers\Master\SettingBagianController', 'create');
$router->post('/setting-bagian/create', 'Controllers\Master\SettingBagianController', 'create');
$router->get('/setting-bagian/edit/{id}', 'Controllers\Master\SettingBagianController', 'edit');
$router->post('/setting-bagian/edit/{id}', 'Controllers\Master\SettingBagianController', 'edit');
$router->get('/setting-bagian/delete/{id}', 'Controllers\Master\SettingBagianController', 'delete');

// Setting Unit routes (admin/manajemen only)
$router->get('/setting-unit', 'Controllers\Master\SettingUnitController', 'index');
$router->get('/setting-unit/create', 'Controllers\Master\SettingUnitController', 'create');
$router->post('/setting-unit/create', 'Controllers\Master\SettingUnitController', 'create');
$router->get('/setting-unit/edit/{id}', 'Controllers\Master\SettingUnitController', 'edit');
$router->post('/setting-unit/edit/{id}', 'Controllers\Master\SettingUnitController', 'edit');
$router->get('/setting-unit/delete/{id}', 'Controllers\Master\SettingUnitController', 'delete');

// Master Akun routes (admin/manajemen only)
$router->get('/master-akun', 'Controllers\Master\MasterAkunController', 'index');
$router->get('/master-akun/create', 'Controllers\Master\MasterAkunController', 'create');
$router->post('/master-akun/create', 'Controllers\Master\MasterAkunController', 'create');
$router->get('/master-akun/edit/{id}', 'Controllers\Master\MasterAkunController', 'edit');
$router->post('/master-akun/edit/{id}', 'Controllers\Master\MasterAkunController', 'edit');
$router->get('/master-akun/delete/{id}', 'Controllers\Master\MasterAkunController', 'delete');
$router->get('/master-akun/export/excel', 'Controllers\Master\MasterAkunController', 'exportExcel');
$router->get('/master-akun/export/pdf', 'Controllers\Master\MasterAkunController', 'exportPdf');

// Setting Jurnal routes (admin/manajemen only)
$router->get('/setting-jurnal', 'Controllers\Master\SettingJurnalController', 'index');
$router->post('/setting-jurnal/store', 'Controllers\Master\SettingJurnalController', 'store');

// Periode Akuntansi routes (admin/manajemen only)
$router->get('/periode-akuntansi', 'Controllers\Master\PeriodeAkuntansiController', 'index');
$router->get('/periode-akuntansi/buka-periode', 'Controllers\Master\PeriodeAkuntansiController', 'bukaPeriode');
$router->post('/periode-akuntansi/proses-buka-periode', 'Controllers\Master\PeriodeAkuntansiController', 'prosesBukaPeriode');
$router->get('/periode-akuntansi/create', 'Controllers\Master\PeriodeAkuntansiController', 'create');
$router->post('/periode-akuntansi/create', 'Controllers\Master\PeriodeAkuntansiController', 'create');
$router->get('/periode-akuntansi/edit/{id}', 'Controllers\Master\PeriodeAkuntansiController', 'edit');
$router->post('/periode-akuntansi/edit/{id}', 'Controllers\Master\PeriodeAkuntansiController', 'edit');
$router->get('/periode-akuntansi/delete/{id}', 'Controllers\Master\PeriodeAkuntansiController', 'delete');
$router->get('/periode-akuntansi/active', 'Controllers\Master\PeriodeAkuntansiController', 'getActive');

// Saldo Akun routes (admin/manajemen only)
$router->get('/saldo-akun', 'Controllers\Master\SaldoAkunController', 'index');
$router->get('/saldo-akun/create', 'Controllers\Master\SaldoAkunController', 'create');
$router->post('/saldo-akun/create', 'Controllers\Master\SaldoAkunController', 'create');
$router->get('/saldo-akun/edit/{id}', 'Controllers\Master\SaldoAkunController', 'edit');
$router->post('/saldo-akun/edit/{id}', 'Controllers\Master\SaldoAkunController', 'edit');
$router->get('/saldo-akun/delete/{id}', 'Controllers\Master\SaldoAkunController', 'delete');
$router->get('/saldo-akun/periode/{periode}', 'Controllers\Master\SaldoAkunController', 'getByPeriode');
$router->get('/saldo-akun/akun/{idAkun}', 'Controllers\Master\SaldoAkunController', 'getByAkun');
$router->post('/saldo-akun/update-or-create', 'Controllers\Master\SaldoAkunController', 'updateOrCreate');

// Jurnal routes (admin/manajemen only)
$router->get('/jurnal', 'Controllers\Transaction\JurnalController', 'index');
$router->get('/jurnal/create', 'Controllers\Transaction\JurnalController', 'create');
$router->post('/jurnal/store', 'Controllers\Transaction\JurnalController', 'store');
$router->get('/jurnal/edit/{id}', 'Controllers\Transaction\JurnalController', 'edit');
$router->post('/jurnal/edit/{id}', 'Controllers\Transaction\JurnalController', 'update');
$router->get('/jurnal/generate-no', 'Controllers\Transaction\JurnalController', 'generateNoJurnal');
$router->get('/jurnal/delete/{id}', 'Controllers\Transaction\JurnalController', 'delete');

// Dispatch
$router->dispatch();

