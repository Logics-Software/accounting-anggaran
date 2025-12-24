<?php
$title = $isEdit ? 'Edit Konfigurasi' : 'Setting Konfigurasi';
$config = require __DIR__ . '/../../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Konfigurasi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0"><?= $isEdit ? 'Edit Konfigurasi' : 'Setting Konfigurasi' ?></h4>
                    </div>
                </div>

                <form method="POST" action="/konfigurasi/store" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="namaorganisasi" class="form-label">Nama Organisasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="namaorganisasi" name="namaorganisasi" 
                                   value="<?= htmlspecialchars($konfigurasi['namaorganisasi'] ?? '') ?>" 
                                   required placeholder="Masukkan nama organisasi" 
                                   minlength="2" maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="alamatorganisasi" class="form-label">Alamat Organisasi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamatorganisasi" name="alamatorganisasi" 
                                      rows="3" required placeholder="Masukkan alamat organisasi" 
                                      minlength="5" maxlength="500"><?= htmlspecialchars($konfigurasi['alamatorganisasi'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="notelporganisasi" class="form-label">No. Telpon Organisasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="notelporganisasi" name="notelporganisasi" 
                                   value="<?= htmlspecialchars($konfigurasi['notelporganisasi'] ?? '') ?>" 
                                   required placeholder="Contoh: 021-12345678 atau 081234567890" 
                                   minlength="8" maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label for="namaapp" class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="namaapp" name="namaapp" 
                                   value="<?= htmlspecialchars($konfigurasi['namaapp'] ?? '') ?>" 
                                   required placeholder="Masukkan nama aplikasi" 
                                   minlength="2" maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo</label>
                            <?php if ($isEdit && !empty($konfigurasi['logo']) && file_exists(__DIR__ . '/../../../uploads/' . $konfigurasi['logo'])): ?>
                            <div class="mb-3">
                                <img src="<?= htmlspecialchars($baseUrl) ?>/uploads/<?= htmlspecialchars($konfigurasi['logo']) ?>" 
                                     alt="Logo" class="img-thumbnail rounded" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <p class="mb-2 text-muted"><em>Belum ada logo</em></p>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB). <?= $isEdit ? 'Kosongkan jika tidak ingin mengubah logo.' : '' ?></small>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('save', 'me-1 mb-1', 18) ?><?= $isEdit ? 'Update Konfigurasi' : 'Simpan Konfigurasi' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form submit handler with spinner
    if (typeof initFormSubmitHandler === 'function') {
        initFormSubmitHandler('form[action="/konfigurasi/store"]', {
            loadingText: '<?= $isEdit ? 'Memperbarui...' : 'Menyimpan...' ?>'
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

