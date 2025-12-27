<?php
namespace Controllers\Transaction;

use Controller;
use Auth;
use Message;
use Session;
use Models\Transaction\HeaderJurnal;
use Models\Transaction\DetailJurnal;
use Models\Master\PeriodeAkuntansi;
use Models\Master\MasterAkun;
use Sanitizer;
use Validator;

class JurnalController extends Controller {
    
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get per_page from GET or session, default to 10
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (isset($_GET['per_page'])) {
            $perPage = (int)$_GET['per_page'];
            if (in_array($perPage, $validPerPage)) {
                Session::set('per_page_jurnal', $perPage);
            } else {
                $perPage = 10;
            }
        } else {
            $perPage = (int)Session::get('per_page_jurnal', 10);
            if (!in_array($perPage, $validPerPage)) {
                $perPage = 10;
            }
        }
        
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        $filterTipeJurnal = $_GET['filter_tipe_jurnal'] ?? '';
        $filterPeriode = $_GET['filter_periode'] ?? '';
        
        // Get active periode as default if filter_periode is empty
        $periodeModel = new PeriodeAkuntansi();
        $activePeriode = $periodeModel->getActive();
        if (empty($filterPeriode) && $activePeriode) {
            $filterPeriode = $activePeriode['periode'];
        }
        
        // Get all periode for dropdown
        $allPeriode = $periodeModel->getAllForDropdown();
        
        $model = new HeaderJurnal();
        $items = $model->getAll($page, $perPage, $search, $filterTipeJurnal, $filterPeriode, $sortBy, $sortOrder);
        $total = $model->count($search, $filterTipeJurnal, $filterPeriode);
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
            'filterTipeJurnal' => $filterTipeJurnal,
            'filterPeriode' => $filterPeriode,
            'allPeriode' => $allPeriode,
            'tipeJurnalOptions' => ['UMUM', 'PENYESUAIAN', 'NERACA AWAL']
        ];
        
        $this->view('Transaction/jurnal/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $periodeModel = new PeriodeAkuntansi();
        $activePeriode = $periodeModel->getActive();
        
        if (!$activePeriode) {
            Message::error('Tidak ada periode aktif. Silakan set periode aktif terlebih dahulu.');
            $this->redirect('/jurnal');
        }
        
        $settingJurnalModel = new \Models\Master\SettingJurnal();
        $settingJurnal = $settingJurnalModel->get();
        
        $akunModel = new MasterAkun();
        $allAkun = $akunModel->getAllForDropdown();
        
        $data = [
            'activePeriode' => $activePeriode,
            'settingJurnal' => $settingJurnal,
            'allAkun' => $allAkun,
            'tipeJurnalOptions' => ['UMUM', 'PENYESUAIAN', 'NERACA AWAL'],
            'postingOptions' => ['NON POSTING', 'JURNAL KAS', 'JURNAL ANGGARAN']
        ];
        
        $this->view('Transaction/jurnal/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $headerModel = new HeaderJurnal();
        $detailModel = new DetailJurnal();
        
        $header = $headerModel->findById($id);
        
        if (!$header) {
            Message::error('Jurnal tidak ditemukan');
            $this->redirect('/jurnal');
        }
        
        $settingJurnalModel = new \Models\Master\SettingJurnal();
        $settingJurnal = $settingJurnalModel->get();
        
        $details = $detailModel->getByJurnal($id);
        
        $akunModel = new MasterAkun();
        $allAkun = $akunModel->getAllForDropdown();
        
        $data = [
            'header' => $header,
            'settingJurnal' => $settingJurnal,
            'details' => $details,
            'allAkun' => $allAkun,
            'tipeJurnalOptions' => ['UMUM', 'PENYESUAIAN', 'NERACA AWAL'],
            'postingOptions' => ['NON POSTING', 'JURNAL KAS', 'JURNAL ANGGARAN']
        ];
        
        $this->view('Transaction/jurnal/edit', $data);
    }
    
    public function store() {
        Auth::requireRole(['admin', 'manajemen']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/jurnal');
        }
        
        $headerModel = new HeaderJurnal();
        $detailModel = new DetailJurnal();
        $periodeModel = new PeriodeAkuntansi();
        
        // Get active periode
        $activePeriode = $periodeModel->getActive();
        if (!$activePeriode) {
            Message::error('Tidak ada periode aktif');
            $this->redirect('/jurnal');
        }
        
        $postData = Sanitizer::post($_POST);
        
        // Validate tanggal jurnal sesuai periode aktif
        $tanggalJurnal = $postData['tanggaljurnal'] ?? date('Y-m-d');
        // Convert date to datetime format for database (set time to 00:00:00)
        $tanggalJurnalDatetime = $tanggalJurnal . ' 00:00:00';
        $year = (int)substr($activePeriode['periode'], 0, 4);
        $month = (int)substr($activePeriode['periode'], 4, 2);
        $dateJurnalParts = explode('-', $tanggalJurnal);
        $jurnalYear = (int)$dateJurnalParts[0];
        $jurnalMonth = (int)$dateJurnalParts[1];
        
        if ($jurnalYear != $year || $jurnalMonth != $month) {
            Message::error('Tanggal jurnal harus sesuai dengan periode aktif (' . $activePeriode['periode'] . ')');
            $this->redirect('/jurnal/create');
        }
        
        // Validate keterangan
        $keterangan = trim($postData['keterangan'] ?? '');
        if (empty($keterangan)) {
            Message::error('Keterangan jurnal wajib diisi');
            $this->redirect('/jurnal/create');
        }
        
        // Validate details
        $details = $postData['details'] ?? [];
        if (empty($details) || !is_array($details)) {
            Message::error('Detail jurnal tidak boleh kosong');
            $this->redirect('/jurnal/create');
        }
        
        // Validate uraian for each detail
        foreach ($details as $index => $detail) {
            $uraian = trim($detail['uraian'] ?? '');
            if (empty($uraian)) {
                Message::error('Uraian pada detail jurnal wajib diisi (baris ' . ($index + 1) . ')');
                $this->redirect('/jurnal/create');
            }
        }
        
        // Calculate total debet and kredit
        $totalDebet = 0;
        $totalKredit = 0;
        foreach ($details as $detail) {
            $debet = (float)($detail['debet'] ?? 0);
            $kredit = (float)($detail['kredit'] ?? 0);
            $totalDebet += $debet;
            $totalKredit += $kredit;
        }
        
        // Validate balance
        if (abs($totalDebet - $totalKredit) > 0.01) {
            Message::error('Total debet dan kredit harus seimbang');
            $this->redirect('/jurnal/create');
        }
        
        try {
            // Create header
            $headerData = [
                'periode' => $activePeriode['periode'],
                'tipejurnal' => $postData['tipejurnal'] ?? 'UMUM',
                'nojurnal' => $postData['nojurnal'],
                'tanggaljurnal' => $tanggalJurnalDatetime,
                'noreferensi' => $postData['noreferensi'] ?? null,
                'keterangan' => $postData['keterangan'] ?? null,
                'posting' => $postData['posting'] ?? 'NON POSTING',
                'nomor_posting' => ($postData['posting'] ?? 'NON POSTING') !== 'NON POSTING' ? ($postData['nomor_posting'] ?? null) : null,
                'totaldebet' => $totalDebet,
                'totalkredit' => $totalKredit,
                'approvement' => 'APPROVAL',
                'id_user' => Auth::user() ? Auth::user()['id'] : null
            ];
            
            // Check if nojurnal already exists in this periode
            $existing = $headerModel->findByNojurnalPeriode($headerData['nojurnal'], $headerData['periode']);
            if ($existing) {
                Message::error('Nomor jurnal sudah ada pada periode ini');
                $this->redirect('/jurnal/create');
            }
            
            $headerId = $headerModel->create($headerData);
            
            // Create details
            $nourut = 1;
            foreach ($details as $detail) {
                $detailData = [
                    'id_jurnal' => $headerId,
                    'periode' => $activePeriode['periode'],
                    'nojurnal' => $headerData['nojurnal'],
                    'id_perkiraan' => (int)$detail['id_perkiraan'],
                    'uraian' => $detail['uraian'] ?? null,
                    'debet' => (float)($detail['debet'] ?? 0),
                    'kredit' => (float)($detail['kredit'] ?? 0),
                    'nourut' => $nourut++
                ];
                $detailModel->create($detailData);
            }
            
            Message::success('Jurnal berhasil ditambahkan');
            $this->redirect('/jurnal');
            
        } catch (\Exception $e) {
            Message::error('Gagal menambahkan jurnal: ' . $e->getMessage());
            $this->redirect('/jurnal/create');
        }
    }
    
    public function update($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/jurnal');
        }
        
        $headerModel = new HeaderJurnal();
        $detailModel = new DetailJurnal();
        $periodeModel = new PeriodeAkuntansi();
        
        $header = $headerModel->findById($id);
        if (!$header) {
            Message::error('Jurnal tidak ditemukan');
            $this->redirect('/jurnal');
        }
        
        // Get active periode
        $activePeriode = $periodeModel->getActive();
        if (!$activePeriode) {
            Message::error('Tidak ada periode aktif');
            $this->redirect('/jurnal');
        }
        
        $postData = Sanitizer::post($_POST);
        
        // Validate tanggal jurnal sesuai periode aktif
        $tanggalJurnal = $postData['tanggaljurnal'] ?? date('Y-m-d', strtotime($header['tanggaljurnal']));
        // Convert date to datetime format for database (set time to 00:00:00)
        $tanggalJurnalDatetime = $tanggalJurnal . ' 00:00:00';
        $year = (int)substr($activePeriode['periode'], 0, 4);
        $month = (int)substr($activePeriode['periode'], 4, 2);
        $dateJurnalParts = explode('-', $tanggalJurnal);
        $jurnalYear = (int)$dateJurnalParts[0];
        $jurnalMonth = (int)$dateJurnalParts[1];
        
        if ($jurnalYear != $year || $jurnalMonth != $month) {
            Message::error('Tanggal jurnal harus sesuai dengan periode aktif (' . $activePeriode['periode'] . ')');
            $this->redirect('/jurnal/edit/' . $id);
        }
        
        // Validate keterangan
        $keterangan = trim($postData['keterangan'] ?? '');
        if (empty($keterangan)) {
            Message::error('Keterangan jurnal wajib diisi');
            $this->redirect('/jurnal/edit/' . $id);
        }
        
        // Validate details
        $details = $postData['details'] ?? [];
        if (empty($details) || !is_array($details)) {
            Message::error('Detail jurnal tidak boleh kosong');
            $this->redirect('/jurnal/edit/' . $id);
        }
        
        // Validate uraian for each detail
        foreach ($details as $index => $detail) {
            $uraian = trim($detail['uraian'] ?? '');
            if (empty($uraian)) {
                Message::error('Uraian pada detail jurnal wajib diisi (baris ' . ($index + 1) . ')');
                $this->redirect('/jurnal/edit/' . $id);
            }
        }
        
        // Calculate total debet and kredit
        $totalDebet = 0;
        $totalKredit = 0;
        foreach ($details as $detail) {
            $debet = (float)($detail['debet'] ?? 0);
            $kredit = (float)($detail['kredit'] ?? 0);
            $totalDebet += $debet;
            $totalKredit += $kredit;
        }
        
        // Validate balance
        if (abs($totalDebet - $totalKredit) > 0.01) {
            Message::error('Total debet dan kredit harus seimbang');
            $this->redirect('/jurnal/edit/' . $id);
        }
        
        try {
            // Update header
            $headerData = [
                'tipejurnal' => $postData['tipejurnal'] ?? $header['tipejurnal'],
                'tanggaljurnal' => $tanggalJurnalDatetime,
                'noreferensi' => $postData['noreferensi'] ?? $header['noreferensi'],
                'keterangan' => $postData['keterangan'] ?? $header['keterangan'],
                'posting' => $postData['posting'] ?? $header['posting'],
                'nomor_posting' => ($postData['posting'] ?? $header['posting']) !== 'NON POSTING' ? ($postData['nomor_posting'] ?? $header['nomor_posting']) : null,
                'totaldebet' => $totalDebet,
                'totalkredit' => $totalKredit
            ];
            
            // Check if nojurnal changed and already exists
            if (isset($postData['nojurnal']) && $postData['nojurnal'] !== $header['nojurnal']) {
                $existing = $headerModel->findByNojurnalPeriode($postData['nojurnal'], $header['periode']);
                if ($existing) {
                    Message::error('Nomor jurnal sudah ada pada periode ini');
                    $this->redirect('/jurnal/edit/' . $id);
                }
                $headerData['nojurnal'] = $postData['nojurnal'];
            }
            
            $headerModel->update($id, $headerData);
            
            // Delete old details
            $detailModel->deleteByJurnal($id);
            
            // Create new details
            $nourut = 1;
            foreach ($details as $detail) {
                $detailData = [
                    'id_jurnal' => $id,
                    'periode' => $header['periode'],
                    'nojurnal' => $headerData['nojurnal'] ?? $header['nojurnal'],
                    'id_perkiraan' => (int)$detail['id_perkiraan'],
                    'uraian' => $detail['uraian'] ?? null,
                    'debet' => (float)($detail['debet'] ?? 0),
                    'kredit' => (float)($detail['kredit'] ?? 0),
                    'nourut' => $nourut++
                ];
                $detailModel->create($detailData);
            }
            
            Message::success('Jurnal berhasil diupdate');
            $this->redirect('/jurnal');
            
        } catch (\Exception $e) {
            Message::error('Gagal mengupdate jurnal: ' . $e->getMessage());
            $this->redirect('/jurnal/edit/' . $id);
        }
    }
    
    public function generateNoJurnal() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $tipeJurnal = $_GET['tipe'] ?? 'UMUM';
        $periode = $_GET['periode'] ?? '';
        
        if (empty($periode)) {
            $periodeModel = new PeriodeAkuntansi();
            $activePeriode = $periodeModel->getActive();
            if (!$activePeriode) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Tidak ada periode aktif']);
                exit;
            }
            $periode = $activePeriode['periode'];
        }
        
        $settingJurnalModel = new \Models\Master\SettingJurnal();
        $settingJurnal = $settingJurnalModel->get();
        
        if (!$settingJurnal) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Setting jurnal tidak ditemukan']);
            exit;
        }
        
        // Determine inisial based on tipe jurnal
        $inisial = '';
        $showBulan = false;
        $showTahun = false;
        
        if ($tipeJurnal === 'UMUM') {
            $inisial = $settingJurnal['inisial_jurnal_umum'] ?? '';
            $showTahun = !empty($settingJurnal['tahun_jurnal_umum']);
            $showBulan = !empty($settingJurnal['bulan_jurnal_umum']);
        } elseif ($tipeJurnal === 'PENYESUAIAN') {
            $inisial = $settingJurnal['inisial_jurnal_penyesuaian'] ?? '';
            $showTahun = !empty($settingJurnal['tahun_jurnal_penyesuaian']);
            $showBulan = !empty($settingJurnal['bulan_jurnal_penyesuaian']);
        } elseif ($tipeJurnal === 'NERACA AWAL') {
            $inisial = $settingJurnal['inisial_jurnal_neraca'] ?? '';
            $showTahun = !empty($settingJurnal['tahun_jurnal_neraca']);
            $showBulan = !empty($settingJurnal['bulan_jurnal_neraca']);
        }
        
        if (empty($inisial)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Inisial jurnal tidak ditemukan']);
            exit;
        }
        
        // Build prefix
        $prefix = $inisial;
        
        // Extract bulan and tahun from periode (YYYYMM)
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 4, 2);
        $tahun2digit = substr($tahun, -2); // Last 2 digits
        
        if ($showTahun) {
            $prefix .= $tahun2digit;
        }

        if ($showBulan) {
            $prefix .= $bulan;
        }        
        
        // Get last counter
        $headerModel = new HeaderJurnal();
        $lastCounter = $headerModel->getLastCounter($prefix, $periode);
        
        // Generate next counter (5 digits)
        $nextCounter = $lastCounter + 1;
        $counterStr = str_pad($nextCounter, 5, '0', STR_PAD_LEFT);
        
        // Generate nomor jurnal
        $nojurnal = $prefix . $counterStr;
        
        header('Content-Type: application/json');
        echo json_encode(['nojurnal' => $nojurnal]);
        exit;
    }
    
    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $headerModel = new HeaderJurnal();
        $header = $headerModel->findById($id);
        
        if (!$header) {
            Message::error('Jurnal tidak ditemukan');
            $this->redirect('/jurnal');
        }
        
        try {
            $headerModel->delete($id); // Cascade delete details
            Message::success('Jurnal berhasil dihapus');
        } catch (\Exception $e) {
            Message::error('Gagal menghapus jurnal: ' . $e->getMessage());
        }
        
        $this->redirect('/jurnal');
    }
}

