<?php
$title = 'Edit Master Akun';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/master-akun">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/master-akun">Master Akun</a></li>
                    <li class="breadcrumb-item active">Edit Akun</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Master Akun</h4>
                    </div>
                </div>
                <form method="POST" action="/master-akun/edit/<?= $item['id'] ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nomor_akun" class="form-label">Nomor Akun <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nomor_akun" name="nomor_akun" required maxlength="20" value="<?= htmlspecialchars($item['nomor_akun'] ?? '') ?>" placeholder="Masukkan nomor akun">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_akun" class="form-label">Nama Akun <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_akun" name="nama_akun" required maxlength="200" value="<?= htmlspecialchars($item['nama_akun'] ?? '') ?>" placeholder="Masukkan nama akun">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kelompok_akun" class="form-label">Kelompok Akun <span class="text-danger">*</span></label>
                                <select class="form-select" id="kelompok_akun" name="kelompok_akun" required>
                                    <option value="">Pilih Kelompok</option>
                                    <?php foreach ($kelompokOptions as $kelompok): ?>
                                        <option value="<?= htmlspecialchars($kelompok) ?>" <?= ($item['kelompok_akun'] ?? '') === $kelompok ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kelompok) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="level_akun" class="form-label">Level Akun <span class="text-danger">*</span></label>
                                <select class="form-select" id="level_akun" name="level_akun" required>
                                    <option value="">Pilih Level</option>
                                    <?php foreach ($levelOptions as $level): ?>
                                        <option value="<?= $level ?>" <?= ($item['level_akun'] ?? 1) == $level ? 'selected' : '' ?>>
                                            Level <?= $level ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3" id="detail-akun-container" style="display: none;">
                                <label for="detail_akun" class="form-label">Detail Akun</label>
                                <select class="form-select" id="detail_akun" name="detail_akun">
                                    <option value="">Pilih Detail (Opsional)</option>
                                    <?php foreach ($detailOptions as $detail): ?>
                                        <option value="<?= htmlspecialchars($detail) ?>" <?= ($item['detail_akun'] ?? '') === $detail ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($detail) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="parent-akun-container" style="display: none;">
                                <label for="id_induk" class="form-label">Akun Induk <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_induk" name="id_induk">
                                    <option value="">Pilih Akun Induk</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="level1" class="form-label">Level 1</label>
                                <input class="form-control" id="level1" name="level1" value="<?= !empty($item['level1']) ? (int)$item['level1'] : '' ?>" placeholder="ID Level 1" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="namalevel1" class="form-label">Nama Akun</label>
                                <input type="text" class="form-control" id="namalevel1" name="namalevel1" maxlength="200" value="<?= htmlspecialchars($item['namalevel1'] ?? '') ?>" placeholder="Nama Level 1" disabled>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="level2" class="form-label">Level 2 </label>
                                <input class="form-control" id="level2" name="level2" value="<?= !empty($item['level2']) ? (int)$item['level2'] : '' ?>" placeholder="ID Level 2" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="namalevel2" class="form-label">Nama Akun</label>
                                <input type="text" class="form-control" id="namalevel2" name="namalevel2" maxlength="200" value="<?= htmlspecialchars($item['namalevel2'] ?? '') ?>" placeholder="Nama Level 2" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="level3" class="form-label">Level 3</label>
                                <input class="form-control" id="level3" name="level3" value="<?= !empty($item['level3']) ? (int)$item['level3'] : '' ?>" placeholder="ID Level 3" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="namalevel3" class="form-label">Nama Akun</label>
                                <input type="text" class="form-control" id="namalevel3" name="namalevel3" maxlength="200" value="<?= htmlspecialchars($item['namalevel3'] ?? '') ?>" placeholder="Nama Level 3" disabled>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="level4" class="form-label">Level 4</label>
                                <input class="form-control" id="level4" name="level4" value="<?= !empty($item['level4']) ? (int)$item['level4'] : '' ?>" placeholder="ID Level 4" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="namalevel4" class="form-label">Nama Akun</label>
                                <input type="text" class="form-control" id="namalevel4" name="namalevel4" maxlength="200" value="<?= htmlspecialchars($item['namalevel4'] ?? '') ?>" placeholder="Nama Level 4" disabled>
                            </div>
                        </div>    
                    </div>
                    
                    <div class="card-footer d-flex justify-content-between">
                        <a href="/master-akun" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate saldo_normal based on kelompok_akun
    const kelompokSelect = document.getElementById('kelompok_akun');
    if (kelompokSelect) {
        kelompokSelect.addEventListener('change', function() {
            const kelompok = this.value;
            const infoAlert = document.querySelector('.alert-info');
            if (infoAlert) {
                if (kelompok === 'ASET' || kelompok === 'BEBAN') {
                    infoAlert.innerHTML = '<strong>Info:</strong> Saldo Normal akan otomatis menjadi <strong>DEBET</strong> untuk kelompok ' + kelompok;
                } else if (kelompok) {
                    infoAlert.innerHTML = '<strong>Info:</strong> Saldo Normal akan otomatis menjadi <strong>KREDIT</strong> untuk kelompok ' + kelompok;
                } else {
                    infoAlert.innerHTML = '<strong>Info:</strong> Saldo Normal akan otomatis dihitung berdasarkan Kelompok Akun.';
                }
            }
        });
    }

    const levelSelect = document.getElementById('level_akun');
    const parentContainer = document.getElementById('parent-akun-container');
    const parentSelect = document.getElementById('id_induk');
    const detailContainer = document.getElementById('detail-akun-container');
    const detailSelect = document.getElementById('detail_akun');
    const allAkun = <?= json_encode($allAkun) ?>;
    const currentIdInduk = <?= !empty($item['id_induk']) ? (int)$item['id_induk'] : 'null' ?>;
    const currentDetailAkun = '<?= htmlspecialchars($item['detail_akun'] ?? '', ENT_QUOTES) ?>';
    
    // Function to filter and populate parent dropdown and show/hide detail
    function updateParentDropdown(selectedLevel) {
        const level = parseInt(selectedLevel);
        
        if (!parentContainer || !parentSelect) {
            return;
        }
        
        // Clear existing options except the first one
        parentSelect.innerHTML = '<option value="">Pilih Akun Induk</option>';
        
        // Handle parent dropdown
        if (level === 1) {
            // Hide parent container for level 1
            parentContainer.style.display = 'none';
            parentSelect.removeAttribute('required');
            // Clear value for level 1
            parentSelect.value = '';
        } else if (level > 1) {
            // Show parent container for level > 1
            parentContainer.style.display = 'block';
            parentSelect.setAttribute('required', 'required');
            
            // Filter akun with level = (selected level - 1)
            // Level 2 -> show level 1, Level 3 -> show level 2, etc.
            const parentLevel = level - 1;
            const filteredAkun = allAkun.filter(function(akun) {
                return parseInt(akun.level_akun) === parentLevel;
            });
            
            // Populate dropdown
            filteredAkun.forEach(function(akun) {
                const option = document.createElement('option');
                option.value = akun.id;
                option.textContent = akun.nomor_akun + ' - ' + akun.nama_akun + ' (Level ' + akun.level_akun + ')';
                // Select current id_induk if it exists and is in filtered list
                if (currentIdInduk && parseInt(akun.id) === currentIdInduk) {
                    option.selected = true;
                }
                parentSelect.appendChild(option);
            });
        }
        
        // Handle detail dropdown - only show for level 5
        if (detailContainer && detailSelect) {
            if (level === 5) {
                detailContainer.style.display = 'block';
                // Restore previous value if exists
                if (currentDetailAkun) {
                    detailSelect.value = currentDetailAkun;
                }
            } else {
                detailContainer.style.display = 'none';
                detailSelect.value = ''; // Clear value if not level 5
            }
        }
    }
    
    // Function to find akun by id
    function findAkunById(akunId) {
        return allAkun.find(function(akun) {
            return parseInt(akun.id) === parseInt(akunId);
        });
    }
    
    // Function to auto-fill level fields based on selected parent using recursive hierarchy
    function fillLevelFields(selectedParentId) {
        // Clear all level fields first
        document.getElementById('level1').value = '';
        document.getElementById('level2').value = '';
        document.getElementById('level3').value = '';
        document.getElementById('level4').value = '';
        document.getElementById('namalevel1').value = '';
        document.getElementById('namalevel2').value = '';
        document.getElementById('namalevel3').value = '';
        document.getElementById('namalevel4').value = '';
        
        if (!selectedParentId) {
            return;
        }
        
        // Start with the selected parent akun
        let currentAkunId = selectedParentId;
        let visited = new Set(); // To prevent infinite loops
        
        // Follow id_induk recursively to fill level fields
        while (currentAkunId) {
            const akun = findAkunById(currentAkunId);
            if (!akun) {
                break;
            }
            
            // Prevent infinite loops
            if (visited.has(parseInt(currentAkunId))) {
                break;
            }
            visited.add(parseInt(currentAkunId));
            
            const akunLevel = parseInt(akun.level_akun);
            
            // Fill the level field corresponding to this akun's level
            if (akunLevel >= 1 && akunLevel <= 4) {
                const levelField = document.getElementById('level' + akunLevel);
                const namaLevelField = document.getElementById('namalevel' + akunLevel);
                
                if (levelField && namaLevelField) {
                    levelField.value = akun.id || '';
                    namaLevelField.value = akun.nama_akun || '';
                }
            }
            
            // Stop if we've reached level 1
            if (akunLevel === 1) {
                break;
            }
            
            // Move to parent (id_induk)
            currentAkunId = akun.id_induk ? parseInt(akun.id_induk) : null;
            
            // Safety check
            if (visited.size > 5) {
                break;
            }
        }
    }
    
    // Handle level change
    if (levelSelect) {
        // Initialize on page load
        updateParentDropdown(levelSelect.value);
        
        levelSelect.addEventListener('change', function() {
            updateParentDropdown(this.value);
        });
    }
    
    // Handle parent selection change
    if (parentSelect) {
        parentSelect.addEventListener('change', function() {
            fillLevelFields(this.value);
        });
        
        // Auto-fill on page load if currentIdInduk exists
        if (currentIdInduk) {
            setTimeout(function() {
                fillLevelFields(currentIdInduk);
            }, 100);
        }
    }

    // Enable disabled fields before form submit
    const form = document.querySelector('form[action*="/master-akun/edit"]');
    if (form) {
        form.addEventListener('submit', function() {
            // Enable all disabled level fields so they are submitted
            const disabledFields = form.querySelectorAll('input[disabled]');
            disabledFields.forEach(function(field) {
                field.disabled = false;
            });
        });
    }

    // Initialize form submit handler with spinner
    if (typeof initFormSubmitHandler === 'function') {
        initFormSubmitHandler('form[action*="/master-akun/edit"]', {
            loadingText: 'Menyimpan...'
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

