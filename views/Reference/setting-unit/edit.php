<?php
$title = 'Edit Setting Unit';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/setting-unit">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/setting-unit">Setting Unit</a></li>
                    <li class="breadcrumb-item active">Edit Unit/Prodi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Unit/Prodi</h4>
                    </div>
                </div>

                <form method="POST" action="/setting-unit/edit/<?= $item['id'] ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="namaunit" class="form-label">Nama Unit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namaunit" name="namaunit" value="<?= htmlspecialchars($item['namaunit'] ?? '') ?>" required placeholder="Masukkan nama unit">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_bagian" class="form-label">Bagian/Fakultas</label>
                                <select class="form-select" id="id_bagian" name="id_bagian">
                                    <option value="">Pilih Bagian</option>
                                    <?php foreach ($bagians as $bagian): ?>
                                    <option value="<?= $bagian['id'] ?>" <?= ($item['id_bagian'] ?? 0) == $bagian['id'] ? 'selected' : '' ?>><?= htmlspecialchars($bagian['namabagian'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="jabatan_pimpinan" class="form-label">Jabatan Pimpinan</label>
                                <input type="text" class="form-control" id="jabatan_pimpinan" name="jabatan_pimpinan" value="<?= htmlspecialchars($item['jabatan_pimpinan'] ?? '') ?>" placeholder="Contoh: Kepala Unit">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="id_pimpinan" class="form-label">Pimpinan</label>
                                <select class="form-select" id="id_pimpinan" name="id_pimpinan">
                                    <option value="">Pilih Pimpinan</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= ($item['id_pimpinan'] ?? 0) == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['namalengkap'] ?? $user['username'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" <?= ($item['status'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= ($item['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Non Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="/setting-unit" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initFormSubmitHandler === 'function') {
        initFormSubmitHandler('form[action*="/setting-unit/edit/"]', {
            loadingText: 'Memperbarui...'
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

