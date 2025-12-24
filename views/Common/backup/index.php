<?php
$title = 'Backup Database';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Backup Database</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Backup Database</h4>
                    </div>
                </div>

                <form method="POST" action="/backup/process" id="backupForm">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="backup_folder" class="form-label">Folder Backup <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="backup_folder" name="backup_folder" 
                                   value="<?= htmlspecialchars($backupFolder ?? '') ?>" 
                                   required placeholder="Contoh: D:\PROJECTS\PHP\accounting\backups atau /var/backups">
                            <small class="text-muted">
                                Masukkan path lengkap folder tempat menyimpan backup. Folder akan dibuat otomatis jika belum ada.<br>
                                <strong>Contoh Windows:</strong> D:\PROJECTS\PHP\accounting\backups<br>
                                <strong>Contoh Linux:</strong> /var/backups/accounting
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input checkbox-square-green" type="checkbox" id="remember_folder" checked>
                                <label class="form-check-label mt-1" for="remember_folder">
                                    Simpan folder ini untuk proses backup selanjutnya
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Catatan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Pastikan folder yang dipilih memiliki permission write</li>
                                <?php 
                                $dbConfig = require __DIR__ . '/../../../config/database.php';
                                $dbName = htmlspecialchars($dbConfig['dbname'] ?? 'database');
                                ?>
                                <li>Nama file backup: <code>backup_<?= $dbName ?>_YYYY-MM-DD_HHMMSS.sql</code></li>
                                <li>Proses backup mungkin memakan waktu beberapa saat tergantung ukuran database</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('download', 'me-1 mb-1', 18) ?>Proses Backup
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Download Option -->
            <div class="card mt-3">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0">Download Langsung ke Folder "downloads"</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-center">
                    <a href="/backup/download" class="btn btn-warning">
                        <?= icon('download', 'me-1 mb-1', 18) ?>Download Backup Sekarang
                    </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form submit handler with spinner
    if (typeof initFormSubmitHandler === 'function') {
        initFormSubmitHandler('#backupForm', {
            loadingText: 'Memproses backup...'
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

