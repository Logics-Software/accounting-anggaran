<?php
$title = $isEdit ? 'Edit Setting Jurnal' : 'Setting Jurnal';
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
                    <li class="breadcrumb-item active">Setting Jurnal</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0"><?= $isEdit ? 'Edit Setting Jurnal' : 'Setting Jurnal' ?></h4>
                    </div>
                </div>

                <form method="POST" action="/setting-jurnal/store">
                    <div class="card-body">
                        <h5 class="mb-3">Setting Nomor Jurnal Umum</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <input type="text" class="form-control" id="inisial_jurnal_umum" name="inisial_jurnal_umum" 
                                    value="<?= htmlspecialchars($settingJurnal['inisial_jurnal_umum'] ?? '') ?>" 
                                    placeholder="Contoh: JU" 
                                    maxlength="5">
                            </div>
                            <div class="col-md-3 mb-3 mt-2">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" role="switch" id="bulan_jurnal_umum" name="bulan_jurnal_umum" value="1" <?= !empty($settingJurnal['bulan_jurnal_umum']) ? 'checked' : '' ?>>
                                    <label class="form-label ms-2 mb-0 mt-1" for="bulan_jurnal_umum">
                                        Tampilkan Bulan
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mt-2">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" role="switch" id="tahun_jurnal_umum" name="tahun_jurnal_umum" value="1" <?= !empty($settingJurnal['tahun_jurnal_umum']) ? 'checked' : '' ?>>
                                    <label class="form-label ms-2 mb-0 mt-1" for="tahun_jurnal_umum">
                                        Tampilkan Tahun
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        <h5 class="mb-3">Setting Nomor Jurnal Penyesuaian</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3 mt-2">
                                <input type="text" class="form-control" id="inisial_jurnal_penyesuaian" name="inisial_jurnal_penyesuaian" 
                                    value="<?= htmlspecialchars($settingJurnal['inisial_jurnal_penyesuaian'] ?? '') ?>" 
                                    placeholder="Contoh: JP" 
                                    maxlength="5">
                            </div>
                            <div class="col-md-3 mb-3 mt-2">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" role="switch" id="bulan_jurnal_penyesuaian" name="bulan_jurnal_penyesuaian" value="1" <?= !empty($settingJurnal['bulan_jurnal_penyesuaian']) ? 'checked' : '' ?>>
                                    <label class="form-label ms-2 mb-0 mt-1" for="bulan_jurnal_penyesuaian">
                                        Tampilkan Bulan
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mt-2">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" role="switch" id="tahun_jurnal_penyesuaian" name="tahun_jurnal_penyesuaian" value="1" <?= !empty($settingJurnal['tahun_jurnal_penyesuaian']) ? 'checked' : '' ?>>
                                    <label class="form-label ms-2 mb-0 mt-1" for="tahun_jurnal_penyesuaian">
                                        Tampilkan Tahun
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        <h5 class="mb-3">Setting Nomor Jurnal Neraca Awal</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3 mt-2">
                                <input type="text" class="form-control" id="inisial_jurnal_neraca" name="inisial_jurnal_neraca" 
                                    value="<?= htmlspecialchars($settingJurnal['inisial_jurnal_neraca'] ?? '') ?>" 
                                    placeholder="Contoh: JN" 
                                    maxlength="5">
                            </div>
                            <div class="col-md-3 mb-3 mt-2">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" role="switch" id="bulan_jurnal_neraca" name="bulan_jurnal_neraca" value="1" <?= !empty($settingJurnal['bulan_jurnal_neraca']) ? 'checked' : '' ?>>
                                    <label class="form-label ms-2 mb-0 mt-1" for="bulan_jurnal_neraca">
                                        Tampilkan Bulan
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mt-2">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input" type="checkbox" role="switch" id="tahun_jurnal_neraca" name="tahun_jurnal_neraca" value="1" <?= !empty($settingJurnal['tahun_jurnal_neraca']) ? 'checked' : '' ?>>
                                    <label class="form-label ms-2 mb-0 mt-1" for="tahun_jurnal_neraca">
                                        Tampilkan Tahun
                                    </label>
                                </div>
                            </div>
                        </div>


                        <hr class="my-2">

                        <h5 class="mb-3">Approval</h5>
                        <div class="mb-3">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" role="switch" id="jurnal_approval" name="jurnal_approval" value="1" <?= !empty($settingJurnal['jurnal_approval']) ? 'checked' : '' ?>>
                                <label class="form-label ms-2 mb-0 mt-1" for="jurnal_approval">
                                    Jurnal Approval
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('save', 'me-1 mb-1', 18) ?><?= $isEdit ? 'Update Setting Jurnal' : 'Simpan Setting Jurnal' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle toggle switches - ensure value is always sent
    const form = document.querySelector('form[action="/setting-jurnal/store"]');
    const switches = [
        'bulan_jurnal_umum',
        'bulan_jurnal_penyesuaian',
        'bulan_jurnal_neraca',
        'tahun_jurnal_umum',
        'tahun_jurnal_penyesuaian',
        'tahun_jurnal_neraca',
        'jurnal_approval'
    ];
    
    if (form) {
        // Remove checkbox name attributes to prevent duplicate submission
        // We'll use hidden inputs only
        switches.forEach(function(switchId) {
            const toggleSwitch = document.getElementById(switchId);
            if (toggleSwitch) {
                // Remove name from checkbox to prevent duplicate
                toggleSwitch.removeAttribute('name');
                
                // Add hidden input to ensure value is sent
                // Initialize with current checked state
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = switchId;
                hiddenInput.value = toggleSwitch.checked ? '1' : '0';
                form.appendChild(hiddenInput);
                
                // Update hidden input immediately when switch changes
                toggleSwitch.addEventListener('change', function() {
                    hiddenInput.value = this.checked ? '1' : '0';
                });
            }
        });
        
        // On form submit, ensure all switches send their current state
        // This is a final check to capture the latest switch positions
        form.addEventListener('submit', function(e) {
            // Update all hidden inputs with current switch state before submit
            switches.forEach(function(switchId) {
                const toggleSwitch = document.getElementById(switchId);
                const hiddenInput = form.querySelector('input[type="hidden"][name="' + switchId + '"]');
                if (toggleSwitch && hiddenInput) {
                    // Always update with current switch state (final check)
                    hiddenInput.value = toggleSwitch.checked ? '1' : '0';
                }
            });
        });
    }
    
    // Initialize form submit handler with spinner
    if (typeof initFormSubmitHandler === 'function') {
        initFormSubmitHandler('form[action="/setting-jurnal/store"]', {
            loadingText: '<?= $isEdit ? 'Memperbarui...' : 'Menyimpan...' ?>'
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

