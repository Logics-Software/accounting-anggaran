<?php
$title = 'Tambah User';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/users">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/users">User</a></li>
                    <li class="breadcrumb-item active">Tambah User</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data User</h4>
                    </div>
                </div>
                <form method="POST" action="/users/create" enctype="multipart/form-data">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="namalengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namalengkap" name="namalengkap" required placeholder="Masukkan nama lengkap">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="contoh@email.com">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Minimal 6 karakter">
                                    <button type="button" class="password-toggle-btn" data-target="password" aria-label="Toggle password visibility">
                                        <?= icon('eye-slash', '', 18) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="manajemen">Manajemen</option>
                                    <option value="user" selected>User</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nomorhp" class="form-label">Nomor HP</label>
                                <input type="text" class="form-control" id="nomorhp" name="nomorhp" placeholder="Contoh: 081234567890">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="non aktif">Non Aktif</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="picture" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                        </div>
                        
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/users" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan User</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle
    const passwordToggle = document.querySelector('[data-target="password"]');
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
            }
        });
    }

    // Initialize form submit handler with spinner
    if (typeof initFormSubmitHandler === 'function') {
        initFormSubmitHandler('form[action="/users/create"]', {
            loadingText: 'Menyimpan...'
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

