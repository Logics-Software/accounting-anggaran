<?php
namespace Controllers\Master;

use Controller;
use Auth;
use Message;
use Session;
use Models\Master\MasterAkun;
use Sanitizer;
use Validator;

class MasterAkunController extends Controller {
    
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get per_page from GET or session, default to 10
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (isset($_GET['per_page'])) {
            $perPage = (int)$_GET['per_page'];
            if (in_array($perPage, $validPerPage)) {
                // Save to session for this module
                Session::set('per_page_master_akun', $perPage);
            } else {
                $perPage = 100;
            }
        } else {
            // Get from session, default to 10
            $perPage = (int)Session::get('per_page_master_akun', 10);
            if (!in_array($perPage, $validPerPage)) {
                $perPage = 10;
            }
        }
        
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'nomor_akun';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $filterKelompok = $_GET['filter_kelompok'] ?? '';
        $filterLevel = $_GET['filter_level'] ?? '';
        
        $model = new MasterAkun();
        $items = $model->getAll($page, $perPage, $search, $sortBy, $sortOrder, $filterKelompok, $filterLevel);
        $total = $model->count($search, $filterKelompok, $filterLevel);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'filterKelompok' => $filterKelompok,
            'filterLevel' => $filterLevel,
            'kelompokOptions' => ['AKTIVA', 'PASIVA', 'PENDAPATAN', 'BEBAN'],
            'levelOptions' => [1, 2, 3, 4, 5]
        ];
        
        $this->view('Master/master-akun/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new MasterAkun();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Calculate saldo_normal
            $saldoNormal = $model->calculateSaldoNormal($postData['kelompok_akun'] ?? '');
            
            $data = [
                'nomor_akun' => Sanitizer::string($postData['nomor_akun'] ?? ''),
                'nama_akun' => Sanitizer::string($postData['nama_akun'] ?? ''),
                'kelompok_akun' => Sanitizer::string($postData['kelompok_akun'] ?? ''),
                'detail_akun' => !empty($postData['detail_akun']) ? Sanitizer::string($postData['detail_akun']) : null,
                'level_akun' => isset($postData['level_akun']) ? (int)$postData['level_akun'] : 1,
                'saldo_normal' => $saldoNormal,
                'level1' => !empty($postData['level1']) ? (int)$postData['level1'] : null,
                'level2' => !empty($postData['level2']) ? (int)$postData['level2'] : null,
                'level3' => !empty($postData['level3']) ? (int)$postData['level3'] : null,
                'level4' => !empty($postData['level4']) ? (int)$postData['level4'] : null,
                'namalevel1' => !empty($postData['namalevel1']) ? Sanitizer::string($postData['namalevel1']) : null,
                'namalevel2' => !empty($postData['namalevel2']) ? Sanitizer::string($postData['namalevel2']) : null,
                'namalevel3' => !empty($postData['namalevel3']) ? Sanitizer::string($postData['namalevel3']) : null,
                'namalevel4' => !empty($postData['namalevel4']) ? Sanitizer::string($postData['namalevel4']) : null,
                'id_induk' => !empty($postData['id_induk']) ? (int)$postData['id_induk'] : null,
                'id_user' => Auth::user() ? Auth::user()['id'] : null
            ];
            
            $validator = new Validator($data);
            $rules = [
                'nomor_akun' => 'required|min:1|max:20',
                'nama_akun' => 'required|min:2|max:200',
                'kelompok_akun' => 'required|in:AKTIVA,PASIVA,PENDAPATAN,BEBAN',
                'level_akun' => 'required|in:1,2,3,4,5'
            ];
            
            if (!$validator->validate($rules)) {
                $errors = $validator->errors();
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        Message::error($error);
                    }
                }
                $this->redirect('/master-akun/create');
                return;
            }
            
            // If level is 1, set id_induk to null
            if ($data['level_akun'] == 1) {
                $data['id_induk'] = null;
            } else {
                // For level > 1, id_induk is required
                if (empty($data['id_induk'])) {
                    Message::error('Akun induk wajib diisi untuk level 2 ke atas');
                    $this->redirect('/master-akun/create');
                    return;
                }
            }
            
            // Check if nomor_akun already exists
            $existing = $model->findByNomorAkun($data['nomor_akun']);
            if ($existing) {
                Message::error('Nomor akun sudah digunakan');
                $this->redirect('/master-akun/create');
                return;
            }
            
            try {
                $id = $model->create($data);
                Message::success('Master akun berhasil ditambahkan');
                $this->redirect('/master-akun');
            } catch (\Exception $e) {
                Message::error('Gagal menambahkan master akun: ' . $e->getMessage());
                $this->redirect('/master-akun/create');
            }
            return;
        }
        
        // Get all akun for parent dropdown
        $allAkun = $model->getAllForDropdown();
        
        $data = [
            'allAkun' => $allAkun,
            'kelompokOptions' => ['AKTIVA', 'PASIVA', 'PENDAPATAN', 'BEBAN'],
            'detailOptions' => ['KAS', 'PIUTANG', 'PERSEDIAAN', 'BIAYA_DIMUKA', 'ASET_TETAP', 'ASET_LAIN', 'KEWAJIBAN', 'ASET_BERSIH', 'LABARUGI', 'PENDAPATAN', 'PENDAPATAN_LAIN', 'BEBAN', 'BEBAN_LAIN'],
            'levelOptions' => [1, 2, 3, 4, 5]
        ];
        
        $this->view('Master/master-akun/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new MasterAkun();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Master akun tidak ditemukan');
            $this->redirect('/master-akun');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Calculate saldo_normal if kelompok_akun changed
            $saldoNormal = $model->calculateSaldoNormal($postData['kelompok_akun'] ?? $item['kelompok_akun']);
            
            // Set detail_akun to null if level is not 5
            $levelAkun = isset($postData['level_akun']) ? (int)$postData['level_akun'] : $item['level_akun'];
            $detailAkun = null;
            if ($levelAkun === 5 && !empty($postData['detail_akun'])) {
                $detailAkun = Sanitizer::string($postData['detail_akun']);
            }
            
            $data = [
                'nomor_akun' => Sanitizer::string($postData['nomor_akun'] ?? ''),
                'nama_akun' => Sanitizer::string($postData['nama_akun'] ?? ''),
                'kelompok_akun' => Sanitizer::string($postData['kelompok_akun'] ?? ''),
                'detail_akun' => $detailAkun,
                'level_akun' => $levelAkun,
                'saldo_normal' => $saldoNormal,
                'level1' => !empty($postData['level1']) ? (int)$postData['level1'] : null,
                'level2' => !empty($postData['level2']) ? (int)$postData['level2'] : null,
                'level3' => !empty($postData['level3']) ? (int)$postData['level3'] : null,
                'level4' => !empty($postData['level4']) ? (int)$postData['level4'] : null,
                'namalevel1' => !empty($postData['namalevel1']) ? Sanitizer::string($postData['namalevel1']) : null,
                'namalevel2' => !empty($postData['namalevel2']) ? Sanitizer::string($postData['namalevel2']) : null,
                'namalevel3' => !empty($postData['namalevel3']) ? Sanitizer::string($postData['namalevel3']) : null,
                'namalevel4' => !empty($postData['namalevel4']) ? Sanitizer::string($postData['namalevel4']) : null,
                'id_induk' => !empty($postData['id_induk']) ? (int)$postData['id_induk'] : null,
                'id_user' => Auth::user() ? Auth::user()['id'] : null
            ];
            
            $validator = new Validator($data);
            $rules = [
                'nomor_akun' => 'required|min:1|max:20',
                'nama_akun' => 'required|min:2|max:200',
                'kelompok_akun' => 'required|in:AKTIVA,PASIVA,PENDAPATAN,BEBAN',
                'level_akun' => 'required|in:1,2,3,4,5'
            ];
            
            if (!$validator->validate($rules)) {
                $errors = $validator->errors();
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        Message::error($error);
                    }
                }
                $this->redirect("/master-akun/edit/{$id}");
                return;
            }
            
            // If level is 1, set id_induk to null
            if ($data['level_akun'] == 1) {
                $data['id_induk'] = null;
            } else {
                // For level > 1, id_induk is required
                if (empty($data['id_induk'])) {
                    Message::error('Akun induk wajib diisi untuk level 2 ke atas');
                    $this->redirect("/master-akun/edit/{$id}");
                    return;
                }
            }
            
            // Check if nomor_akun already exists (excluding current record)
            $existing = $model->findByNomorAkun($data['nomor_akun']);
            if ($existing && $existing['id'] != $id) {
                Message::error('Nomor akun sudah digunakan');
                $this->redirect("/master-akun/edit/{$id}");
                return;
            }
            
            try {
                $model->update($id, $data);
                Message::success('Master akun berhasil diperbarui');
                $this->redirect('/master-akun');
            } catch (\Exception $e) {
                Message::error('Gagal memperbarui master akun: ' . $e->getMessage());
                $this->redirect("/master-akun/edit/{$id}");
            }
            return;
        }
        
        // Get all akun for parent dropdown (excluding current item)
        $allAkun = $model->getAllForDropdown($id);
        
        $data = [
            'item' => $item,
            'allAkun' => $allAkun,
            'kelompokOptions' => ['AKTIVA', 'PASIVA', 'PENDAPATAN', 'BEBAN'],
            'detailOptions' => ['KAS', 'PIUTANG', 'PERSEDIAAN', 'BIAYA_DIMUKA', 'ASET_TETAP', 'ASET_LAIN', 'KEWAJIBAN', 'ASET_BERSIH', 'LABARUGI', 'PENDAPATAN', 'PENDAPATAN_LAIN', 'BEBAN', 'BEBAN_LAIN'],
            'levelOptions' => [1, 2, 3, 4, 5]
        ];
        
        $this->view('Master/master-akun/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new MasterAkun();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Master akun tidak ditemukan');
            $this->redirect('/master-akun');
            return;
        }
        
        try {
            $model->delete($id);
            Message::success('Master akun berhasil dihapus');
        } catch (\Exception $e) {
            Message::error('Gagal menghapus master akun: ' . $e->getMessage());
        }
        
        $this->redirect('/master-akun');
    }
    
    /**
     * Export to Excel
     */
    public function exportExcel() {
        Auth::requireRole(['admin', 'manajemen']);
        
        try {
            $search = $_GET['search'] ?? '';
            $sortBy = $_GET['sort_by'] ?? 'nomor_akun';
            $sortOrder = $_GET['sort_order'] ?? 'ASC';
            $filterKelompok = $_GET['filter_kelompok'] ?? '';
            $filterLevel = $_GET['filter_level'] ?? '';
            
            $model = new MasterAkun();
            $items = $model->getAllForExport($search, $filterKelompok, $filterLevel, $sortBy, $sortOrder);
            
            // Try to use PhpSpreadsheet Xlsx writer
            try {
                // Create new Spreadsheet
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Master Akun');
                
                // Set headers
                $headers = ['No', 'Nomor Akun', 'Nama Akun', 'Kelompok', 'Level', 'Detail Akun', 'Saldo Normal'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . '1', $header);
                    $col++;
                }
                
                // Style header
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ];
                $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
                
                // Add data
                $row = 2;
                $no = 1;
                foreach ($items as $item) {
                    $sheet->setCellValue('A' . $row, $no++);
                    $sheet->setCellValue('B' . $row, $item['nomor_akun']);
                    
                    // Add indentation for nama_akun based on level
                    $indent = str_repeat('   ', ($item['level_akun'] - 1));
                    $sheet->setCellValue('C' . $row, $indent . $item['nama_akun']);
                    
                    $sheet->setCellValue('D' . $row, $item['kelompok_akun']);
                    $sheet->setCellValue('E' . $row, $item['level_akun']);
                    $sheet->setCellValue('F' . $row, $item['detail_akun'] ?? '-');
                    $sheet->setCellValue('G' . $row, $item['saldo_normal']);
                    $row++;
                }
                
                // Auto size columns
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Set filename
                $filename = 'Master_Akun_' . date('Y-m-d_His') . '.xlsx';
                
                // Set headers for download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');
                
                // Write file
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
                exit;
                
            } catch (\Exception $e) {
                // Fallback to CSV if Xlsx writer fails (e.g., ZipStream not installed)
                // Export as CSV with UTF-8 BOM for Excel compatibility
                // Use CSV as fallback (can be opened in Excel)
                $filename = 'Master_Akun_' . date('Y-m-d_His') . '.csv';
                
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');
                
                // Output UTF-8 BOM for Excel
                echo "\xEF\xBB\xBF";
                
                // Open output stream
                $output = fopen('php://output', 'w');
                
                // Write headers
                fputcsv($output, ['No', 'Nomor Akun', 'Nama Akun', 'Kelompok', 'Level', 'Detail Akun', 'Saldo Normal']);
                
                // Write data
                $no = 1;
                foreach ($items as $item) {
                    $indent = str_repeat('   ', ($item['level_akun'] - 1));
                    fputcsv($output, [
                        $no++,
                        $item['nomor_akun'],
                        $indent . $item['nama_akun'],
                        $item['kelompok_akun'],
                        $item['level_akun'],
                        $item['detail_akun'] ?? '-',
                        $item['saldo_normal']
                    ]);
                }
                
                fclose($output);
                exit;
            }
            
        } catch (\Exception $e) {
            Message::error('Gagal mengekspor data: ' . $e->getMessage());
            $this->redirect('/master-akun');
        }
    }
    
    /**
     * Export to PDF
     */
    public function exportPdf() {
        Auth::requireRole(['admin', 'manajemen']);
        
        try {
            $search = $_GET['search'] ?? '';
            $sortBy = $_GET['sort_by'] ?? 'nomor_akun';
            $sortOrder = $_GET['sort_order'] ?? 'ASC';
            $filterKelompok = $_GET['filter_kelompok'] ?? '';
            $filterLevel = $_GET['filter_level'] ?? '';
            
            $model = new MasterAkun();
            $items = $model->getAllForExport($search, $filterKelompok, $filterLevel, $sortBy, $sortOrder);
            
            // Get badge colors
            $kelompokColors = [
                'AKTIVA' => 'primary',
                'PASIVA' => 'warning',
                'PENDAPATAN' => 'success',
                'BEBAN' => 'danger'
            ];
            
            // Generate HTML content
            ob_start();
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Master Akun</title>
                <style>
                    @page {
                        size: A4 landscape;
                        margin: 0.5cm;
                        margin-top: 1cm;
                        margin-bottom: 1cm;
                    }
                    @media print {
                        @page {
                            margin: 0;
                            size: A4 landscape;
                        }
                        body {
                            margin: 1cm;
                        }
                    }
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 10pt;
                        margin: 20px;
                        padding: 0;
                    }
                    h1 {
                        text-align: center;
                        margin-bottom: 20px;
                        color: #333;
                        margin-top: 0;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }
                    th {
                        background-color: #4472C4;
                        color: white;
                        padding: 8px;
                        text-align: left;
                        border: 1px solid #ddd;
                        font-weight: bold;
                    }
                    td {
                        padding: 6px;
                        border: 1px solid #ddd;
                    }
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .badge {
                        padding: 3px 8px;
                        border-radius: 3px;
                        font-size: 9pt;
                        font-weight: bold;
                    }
                    .badge-primary { background-color: #0d6efd; color: white; }
                    .badge-warning { background-color: #ffc107; color: #000; }
                    .badge-success { background-color: #198754; color: white; }
                    .badge-danger { background-color: #dc3545; color: white; }
                    @media print {
                        body { 
                            margin: 0;
                            padding: 10px;
                        }
                        table { page-break-inside: auto; }
                        tr { page-break-inside: avoid; page-break-after: auto; }
                        thead { display: table-header-group; }
                        tfoot { display: table-footer-group; }
                    }
                </style>
            </head>
            <body>
                <h1>Daftar Master Akun</h1>
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th style="width: 100px;">Nomor Akun</th>
                            <th>Nama Akun</th>
                            <th style="width: 100px;">Kelompok</th>
                            <th class="text-center" style="width: 60px;">Level</th>
                            <th style="width: 120px;">Detail Akun</th>
                            <th style="width: 100px;">Saldo Normal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($items as $item): 
                            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', ($item['level_akun'] - 1));
                            $kelompokColor = $kelompokColors[$item['kelompok_akun']] ?? 'secondary';
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($item['nomor_akun']) ?></td>
                            <td><?= $indent . htmlspecialchars($item['nama_akun']) ?></td>
                            <td>
                                <span class="badge badge-<?= $kelompokColor ?>">
                                    <?= htmlspecialchars($item['kelompok_akun']) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $item['level_akun'] ?></td>
                            <td><?= htmlspecialchars($item['detail_akun'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['saldo_normal']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div style="margin-top: 20px; font-size: 9pt; color: #666;">
                    <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
                </div>
                <div style="margin-top: 10px; padding: 10px; background-color: #f0f0f0; border-radius: 5px; font-size: 9pt; color: #666; display: none;" id="print-instruction">
                    <strong>Tip:</strong> Untuk menyembunyikan header dan footer browser:
                    <ul style="margin: 5px 0 0 20px; padding: 0;">
                        <li><strong>Chrome/Edge:</strong> Di dialog Print, hapus centang "Headers and footers"</li>
                        <li><strong>Firefox:</strong> Di dialog Print, klik "More Settings" dan hapus centang "Print headers and footers"</li>
                    </ul>
                </div>
                <script>
                    window.onload = function() {
                        // Show instruction before printing (only on screen, will be hidden when printing)
                        var instruction = document.getElementById('print-instruction');
                        if (instruction) {
                            instruction.style.display = 'block';
                            setTimeout(function() {
                                window.print();
                            }, 500);
                        } else {
                            window.print();
                        }
                    };
                </script>
                <style>
                    @media print {
                        #print-instruction {
                            display: none !important;
                        }
                    }
                </style>
            </body>
            </html>
            <?php
            $html = ob_get_clean();
            
            // Set filename
            $filename = 'Master_Akun_' . date('Y-m-d_His') . '.pdf';
            
            // Output HTML with print-friendly styling
            // User can use browser's "Print to PDF" feature
            // Note: To hide browser headers/footers, user should disable them in browser print settings
            // Chrome/Edge: Uncheck "Headers and footers" in print dialog
            // Firefox: Uncheck "Print headers and footers" in print settings
            header('Content-Type: text/html; charset=UTF-8');
            echo $html;
            exit;
            
        } catch (\Exception $e) {
            Message::error('Gagal mengekspor data: ' . $e->getMessage());
            $this->redirect('/master-akun');
        }
    }
}

