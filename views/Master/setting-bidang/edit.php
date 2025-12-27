<?php
$title = 'Edit Setting Bidang';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/setting-bidang">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/setting-bidang">Setting Bidang</a></li>
                    <li class="breadcrumb-item active">Edit Bidang</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Bidang</h4>
                    </div>
                </div>

                <form method="POST" action="/setting-bidang/edit/<?= $item['id'] ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="namabidang" class="form-label">Nama Bidang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namabidang" name="namabidang" value="<?= htmlspecialchars($item['namabidang'] ?? '') ?>" required placeholder="Masukkan nama bidang">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pengelola_akun" name="pengelola_akun" value="1" <?= !empty($item['pengelola_akun']) ? 'checked' : '' ?>>
                                    <label class="form-label ms-2 mb-0 mt-1" for="pengelola_akun">
                                        Pengelola Akun
                                    </label>
                                </div>
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
                        <a href="/setting-bidang" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle toggle switch for pengelola_akun - ensure value is always sent
    const toggleSwitch = document.getElementById('pengelola_akun');
    const form = document.querySelector('form[action*="/setting-bidang/edit/"]');
    
    if (form && toggleSwitch) {
        // Always ensure pengelola_akun value is sent
        form.addEventListener('submit', function(e) {
            // Remove any existing hidden input first
            const existingHidden = form.querySelector('input[type="hidden"][name="pengelola_akun"]');
            if (existingHidden) {
                existingHidden.remove();
            }
            
            // If checkbox is not checked, add hidden input with value 0
            if (!toggleSwitch.checked) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'pengelola_akun';
                hiddenInput.value = '0';
                form.appendChild(hiddenInput);
            }
        });
    }

    // Initialize form submit handler with spinner
    if (typeof initFormSubmitHandler === 'function') {
        initFormSubmitHandler('form[action*="/setting-bidang/edit/"]', {
            loadingText: 'Memperbarui...'
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

