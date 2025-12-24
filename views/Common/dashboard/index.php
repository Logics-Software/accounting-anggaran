<?php
$title = 'Dashboard';
$config = require __DIR__ . '/../../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$user = $user ?? Auth::user();
$role = $role ?? ($user['role'] ?? '');

require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="mb-0">Dashboard</h1>
        </div>
    </div>
        
    <div class="row">
        <div class="col-12">
            <div class="card">
                    <div class="card-body">
                    <h5 class="card-title">Selamat Datang</h5>
                    <p class="card-text">
                        Dashboard akan dikembangkan lebih lanjut.
                    </p>
                    <?php if ($user): ?>
                        <p class="text-muted mb-0">
                            Logged in as: <strong><?= htmlspecialchars($user['name'] ?? $user['username'] ?? 'User') ?></strong>
                            (<?= htmlspecialchars($role) ?>)
                        </p>
            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
