<?php
$title = 'Tambah Jurnal';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/jurnal">Jurnal</a></li>
                    <li class="breadcrumb-item active">Tambah Jurnal</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data Jurnal</h4>
                    </div>
                </div>
                <form method="POST" action="/jurnal/store" id="jurnalForm">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="periode" class="form-label">Periode <span class="text-danger">*</span></label>
                                <?php
                                $periodeLabel = '';
                                if (!empty($activePeriode)) {
                                    // Use bulan and tahun if available, otherwise extract from periode
                                    if (isset($activePeriode['bulan']) && isset($activePeriode['tahun'])) {
                                        $bulan = (int)$activePeriode['bulan'];
                                        $tahun = $activePeriode['tahun'];
                                    } else {
                                        $periodeValue = $activePeriode['periode'] ?? '';
                                        if (!empty($periodeValue) && strlen($periodeValue) === 6) {
                                            $tahun = substr($periodeValue, 0, 4);
                                            $bulan = (int)substr($periodeValue, 4, 2);
                                        } else {
                                            $bulan = 0;
                                            $tahun = '';
                                        }
                                    }
                                    
                                    if ($bulan > 0 && !empty($tahun)) {
                                        $bulanNames = [
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ];
                                        $bulanName = $bulanNames[$bulan] ?? '';
                                        $periodeLabel = $bulanName . ' ' . $tahun;
                                    } else {
                                        $periodeLabel = $activePeriode['periode'] ?? '';
                                    }
                                }
                                ?>
                                <input type="text" class="form-control" id="periode" value="<?= htmlspecialchars($periodeLabel) ?>" disabled>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="tipejurnal" class="form-label">Tipe Jurnal <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipejurnal" name="tipejurnal" required>
                                    <?php foreach ($tipeJurnalOptions as $tipe): ?>
                                        <option value="<?= htmlspecialchars($tipe) ?>" <?= $tipe == 'UMUM' ? 'selected' : '' ?>><?= htmlspecialchars($tipe) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="nojurnal" class="form-label">No. Jurnal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nojurnal" name="nojurnal" required maxlength="20" readonly>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="tanggaljurnal" class="form-label">Tanggal Jurnal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggaljurnal" name="tanggaljurnal" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="noreferensi" class="form-label">No. Referensi</label>
                                <input type="text" class="form-control" id="noreferensi" name="noreferensi" maxlength="50" placeholder="Masukkan nomor referensi">
                            </div>
                        </div>
                        
                        <input type="hidden" name="posting" value="NON POSTING">
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="keterangan" class="form-label">Uraian <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2" maxlength="254" required placeholder="Masukkan Uraian"></textarea>
                            </div>
                        </div>

                        <h6 class="mb-2">Detail Jurnal</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailTable">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 30%;">Akun</th>
                                        <th style="width: 25%;">Uraian</th>
                                        <th style="width: 15%;" class="text-end">Debet</th>
                                        <th style="width: 15%;" class="text-end">Kredit</th>
                                        <th style="width: 10%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="detailBody">
                                    <tr class="detail-row">
                                        <td>1</td>
                                        <td>
                                            <select class="form-select akun-select" name="details[0][id_perkiraan]" required>
                                                <option value="">Pilih Akun</option>
                                                <?php foreach ($allAkun as $akun): ?>
                                                    <option value="<?= $akun['id'] ?>" data-nomor="<?= htmlspecialchars($akun['nomor_akun']) ?>" data-nama="<?= htmlspecialchars($akun['nama_akun']) ?>">
                                                        <?= htmlspecialchars($akun['nomor_akun'] . ' - ' . $akun['nama_akun']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="details[0][uraian]" maxlength="254" required placeholder="Uraian">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-end debet-input" name="details[0][debet]" value="" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-end kredit-input" name="details[0][kredit]" value="" placeholder="0">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row" title="Hapus baris">
                                                <?= icon('trash-can', '', 16) ?>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                                        <td class="text-end fw-bold" id="totalDebet">0</td>
                                        <td class="text-end fw-bold" id="totalKredit">0</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" id="addRow" title="Tambah baris">
                                                <?= icon('plus', '', 16) ?>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">SELISIH:</td>
                                        <td colspan="2" class="text-end fw-bold" id="selisih">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/jurnal" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('save', 'me-1 mb-1', 18) ?>Simpan Jurnal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Generate nomor jurnal function
    function generateNoJurnal(tipeJurnal) {
        const nojurnalInput = document.getElementById('nojurnal');
        if (!nojurnalInput) return;
        
        const periode = '<?= htmlspecialchars($activePeriode['periode'] ?? '') ?>';
        
        if (!periode || !tipeJurnal) {
            nojurnalInput.value = '';
            return;
        }
        
        // Show loading state
        nojurnalInput.value = 'Generating...';
        nojurnalInput.disabled = true;
        
        // Call API to generate nomor jurnal
        fetch(`/jurnal/generate-no?tipe=${encodeURIComponent(tipeJurnal)}&periode=${encodeURIComponent(periode)}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    nojurnalInput.value = '';
                } else {
                    nojurnalInput.value = data.nojurnal || '';
                }
                nojurnalInput.disabled = false;
            })
            .catch(error => {
                console.error('Error generating nomor jurnal:', error);
                nojurnalInput.value = '';
                nojurnalInput.disabled = false;
            });
    }

document.addEventListener('DOMContentLoaded', function() {
    let rowCount = 1;
    const allAkun = <?= json_encode($allAkun) ?>;
    
    
    // Add new row
    document.getElementById('addRow').addEventListener('click', function() {
        rowCount++;
        const tbody = document.getElementById('detailBody');
        const newRow = document.createElement('tr');
        newRow.className = 'detail-row';
        newRow.innerHTML = `
            <td>${rowCount}</td>
            <td>
                <select class="form-select akun-select" name="details[${rowCount - 1}][id_perkiraan]" required>
                    <option value="">Pilih Akun</option>
                    ${allAkun.map(akun => 
                        `<option value="${akun.id}" data-nomor="${akun.nomor_akun}" data-nama="${akun.nama_akun}">${akun.nomor_akun} - ${akun.nama_akun}</option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <input type="text" class="form-control" name="details[${rowCount - 1}][uraian]" maxlength="254" placeholder="Uraian">
            </td>
            <td>
                <input type="text" class="form-control text-end debet-input" name="details[${rowCount - 1}][debet]" value="" placeholder="0">
            </td>
            <td>
                <input type="text" class="form-control text-end kredit-input" name="details[${rowCount - 1}][kredit]" value="" placeholder="0">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row" title="Hapus baris">
                    <?= icon('trash-can', '', 16) ?>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        updateRowNumbers();
        attachRowEvents(newRow);
    });
    
    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('.detail-row');
            if (document.querySelectorAll('.detail-row').length > 1) {
                row.remove();
                updateRowNumbers();
                calculateTotals();
            } else {
                alert('Minimal harus ada 1 baris detail');
            }
        }
    });
    
    // Format number with thousand separator (no decimal)
    function formatNumber(num) {
        if (!num && num !== 0) return '0';
        const numValue = Math.round(parseFloat(num));
        if (isNaN(numValue)) return '0';
        // Format with dot as thousand separator, no decimal
        return numValue.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
    
    // Remove formatting and get numeric value
    function unformatNumber(str) {
        if (!str) return 0;
        // Remove dot (thousand separator)
        let numStr = str.toString().replace(/\./g, '');
        const numValue = parseInt(numStr, 10);
        return isNaN(numValue) ? 0 : numValue;
    }
    
    // Handle input formatting for debet/kredit
    function setupNumberInput(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value;
            // Remove all non-numeric characters
            value = value.replace(/[^\d]/g, '');
            
            if (value === '') {
                e.target.value = '';
                return;
            }
            
            // Format with thousand separator (dot) while typing
            const numValue = parseInt(value, 10);
            if (!isNaN(numValue)) {
                e.target.value = numValue.toLocaleString('id-ID');
            }
        });
        
        input.addEventListener('blur', function(e) {
            const numValue = unformatNumber(e.target.value);
            if (numValue > 0) {
                e.target.value = formatNumber(numValue);
            } else {
                e.target.value = '';
            }
        });
    }
    
    // Calculate totals
    function calculateTotals() {
        let totalDebet = 0;
        let totalKredit = 0;
        
        document.querySelectorAll('.debet-input').forEach(input => {
            totalDebet += unformatNumber(input.value);
        });
        
        document.querySelectorAll('.kredit-input').forEach(input => {
            totalKredit += unformatNumber(input.value);
        });
        
        document.getElementById('totalDebet').textContent = formatNumber(totalDebet);
        document.getElementById('totalKredit').textContent = formatNumber(totalKredit);
        
        const selisih = Math.abs(totalDebet - totalKredit);
        const selisihElement = document.getElementById('selisih');
        selisihElement.textContent = formatNumber(selisih);
        
        if (selisih > 0) {
            selisihElement.classList.add('text-danger');
        } else {
            selisihElement.classList.remove('text-danger');
        }
    }
    
    // Update row numbers
    function updateRowNumbers() {
        document.querySelectorAll('.detail-row').forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }
    
    // Attach events to row
    function attachRowEvents(row) {
        const debetInput = row.querySelector('.debet-input');
        const kreditInput = row.querySelector('.kredit-input');
        const uraianInput = row.querySelector('input[name*="[uraian]"]');
        
        // Setup number formatting
        if (debetInput) setupNumberInput(debetInput);
        if (kreditInput) setupNumberInput(kreditInput);
        
        debetInput.addEventListener('input', calculateTotals);
        kreditInput.addEventListener('input', calculateTotals);
        
        // Ensure only one of debet or kredit is filled
        debetInput.addEventListener('input', function() {
            const numValue = unformatNumber(this.value);
            if (numValue > 0) {
                kreditInput.value = '';
            }
        });
        
        kreditInput.addEventListener('input', function() {
            const numValue = unformatNumber(this.value);
            if (numValue > 0) {
                debetInput.value = '';
            }
        });
        
        // Auto-fill uraian on blur if empty
        if (uraianInput) {
            uraianInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    // Get all detail rows
                    const allRows = Array.from(document.querySelectorAll('.detail-row'));
                    const currentIndex = allRows.indexOf(row);
                    
                    if (currentIndex === 0) {
                        // Baris pertama: ambil dari keterangan header
                        const keteranganInput = document.getElementById('keterangan');
                        if (keteranganInput && keteranganInput.value.trim()) {
                            this.value = keteranganInput.value.trim();
                        }
                    } else if (currentIndex > 0) {
                        // Baris selanjutnya: ambil dari uraian baris sebelumnya
                        const previousRow = allRows[currentIndex - 1];
                        const previousUraianInput = previousRow.querySelector('input[name*="[uraian]"]');
                        if (previousUraianInput && previousUraianInput.value.trim()) {
                            this.value = previousUraianInput.value.trim();
                        }
                    }
                }
            });
        }
    }
    
    // Attach events to existing rows
    document.querySelectorAll('.detail-row').forEach(row => {
        attachRowEvents(row);
    });
    
    // Generate nomor jurnal on tipe jurnal change
    const tipejurnalSelect = document.getElementById('tipejurnal');
    if (tipejurnalSelect) {
        tipejurnalSelect.addEventListener('change', function() {
            generateNoJurnal(this.value);
        });
        
        // Generate nomor jurnal on initial load
        if (tipejurnalSelect.value) {
            generateNoJurnal(tipejurnalSelect.value);
        }
    }
    
    // Before form submit, convert formatted values back to numeric format
    document.getElementById('jurnalForm').addEventListener('submit', function(e) {
        // Convert all debet/kredit inputs from formatted to numeric before submit
        document.querySelectorAll('.debet-input, .kredit-input').forEach(input => {
            const numValue = unformatNumber(input.value);
            input.value = numValue.toString();
        });
        
        const totalDebet = unformatNumber(document.getElementById('totalDebet').textContent);
        const totalKredit = unformatNumber(document.getElementById('totalKredit').textContent);
        const selisih = Math.abs(totalDebet - totalKredit);
        
        if (selisih > 0.01) {
            e.preventDefault();
            alert('Total debet dan kredit harus seimbang. Selisih: ' + formatNumber(selisih));
            return false;
        }
    });
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

