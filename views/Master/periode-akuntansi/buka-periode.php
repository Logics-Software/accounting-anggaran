<?php
$title = 'Buka Periode Akuntansi';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Buka Periode Akuntansi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Buka Periode Akuntansi</h4>
                    </div>
                </div>

                <form method="POST" action="/periode-akuntansi/proses-buka-periode" id="bukaPeriodeForm">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Catatan:</strong> Modul ini hanya bisa dijalankan jika tabel periode_akuntansi masih kosong. 
                            Setelah proses ini, akan dibuat saldo awal untuk semua akun level 5.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bulan" class="form-label">Bulan <span class="text-danger">*</span></label>
                                <select class="form-select" id="bulan" name="bulan" required>
                                    <option value="">Pilih Bulan</option>
                                    <?php
                                    $bulanNames = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                    foreach ($bulanNames as $num => $name):
                                    ?>
                                        <option value="<?= $num ?>" <?= $num == $currentMonth ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select class="form-select" id="tahun" name="tahun" required>
                                    <option value="">Pilih Tahun</option>
                                    <?php
                                    $startYear = $currentYear - 5;
                                    $endYear = $currentYear + 5;
                                    for ($y = $startYear; $y <= $endYear; $y++):
                                    ?>
                                        <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-warning">
                                <strong>Perhatian:</strong> Setelah proses ini dijalankan:
                                <ul class="mb-0 mt-2">
                                    <li>Periode yang dipilih akan menjadi periode aktif</li>
                                    <li>Semua akun level 5 akan memiliki saldo awal 0</li>
                                    <li>Data periode dan saldo awal tidak dapat diubah melalui modul ini</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('check', 'me-1 mb-1', 18) ?>Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('bukaPeriodeForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const bulan = document.getElementById('bulan').value;
            const tahun = document.getElementById('tahun').value;
            
            if (!bulan || !tahun) {
                alert('Harap pilih bulan dan tahun terlebih dahulu');
                return false;
            }
            
            const bulanNames = {
                1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
                5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
                9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
            };
            
            const bulanName = bulanNames[parseInt(bulan)];
            const confirmMsg = `Apakah Anda yakin ingin membuka periode akuntansi untuk <strong>${bulanName} ${tahun}</strong>?<br><br>Setelah proses ini, akan dibuat saldo awal untuk semua akun level 5.`;
            
            // Use custom confirm modal
            if (typeof showConfirmModal === 'function') {
                showConfirmModal({
                    title: 'Konfirmasi Buka Periode Akuntansi',
                    message: confirmMsg,
                    buttonText: 'Ya, Proses',
                    buttonClass: 'btn-primary',
                    onConfirm: function() {
                        form.submit();
                    }
                });
            } else {
                // Fallback to native confirm
                if (confirm(confirmMsg.replace(/<br><br>/g, '\n\n').replace(/<strong>|<\/strong>/g, ''))) {
                    form.submit();
                }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

