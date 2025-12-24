<?php
$title = 'Settings';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/profile">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/profile">Profil</a></li>
                    <li class="breadcrumb-item active">Setting</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Setting</h4>
                    </div>
                </div>

                <div class="card-body">
                    <p class="text-muted">Pengaturan akan tersedia di sini.</p>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="/dashboard" class="btn btn-secondary"><?= icon('back', 'me-1 mb-1', 18) ?>Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

