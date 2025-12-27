<?php
namespace Controllers\Master;

use Controller;
use Auth;
use Message;
use Session;
use Models\Master\PeriodeAkuntansi;
use Models\Master\MasterAkun;
use Models\Master\SaldoAkun;
use Sanitizer;
use Validator;

class PeriodeAkuntansiController extends Controller {
    
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get per_page from GET or session, default to 10
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (isset($_GET['per_page'])) {
            $perPage = (int)$_GET['per_page'];
            if (in_array($perPage, $validPerPage)) {
                Session::set('per_page_periode_akuntansi', $perPage);
            } else {
                $perPage = 10;
            }
        } else {
            $perPage = (int)Session::get('per_page_periode_akuntansi', 10);
            if (!in_array($perPage, $validPerPage)) {
                $perPage = 10;
            }
        }
        
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'periode';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        $model = new PeriodeAkuntansi();
        $items = $model->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $model->count($search);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        // For now, return JSON or simple response since no view yet
        $this->json([
            'success' => true,
            'data' => $data,
            'message' => 'Periode Akuntansi module - View will be created later'
        ]);
    }
    
    public function bukaPeriode() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $periodeModel = new PeriodeAkuntansi();
        
        // Check if periode_akuntansi already has data
        $existing = $periodeModel->exists();
        if ($existing) {
            Message::error('Periode akuntansi sudah aktif!');
            $this->redirect('/dashboard');
        }
        
        // Get current year for default
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        $data = [
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth
        ];
        
        $this->view('Master/periode-akuntansi/buka-periode', $data);
    }
    
    public function prosesBukaPeriode() {
        Auth::requireRole(['admin', 'manajemen']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/periode-akuntansi/buka-periode');
        }
        
        $periodeModel = new PeriodeAkuntansi();
        $masterAkunModel = new MasterAkun();
        $saldoAkunModel = new SaldoAkun();
        
        // Check if periode_akuntansi already has data
        $existing = $periodeModel->exists();
        if ($existing) {
            Message::error('Periode akuntansi sudah aktif!');
            $this->redirect('/periode-akuntansi/buka-periode');
        }
        
        $postData = Sanitizer::post($_POST);
        
        $bulan = isset($postData['bulan']) ? (int)$postData['bulan'] : 0;
        $tahun = isset($postData['tahun']) ? (int)$postData['tahun'] : 0;
        
        // Validate bulan and tahun
        if ($bulan < 1 || $bulan > 12) {
            Message::error('Bulan tidak valid (harus 1-12)');
            $this->redirect('/periode-akuntansi/buka-periode');
        }
        
        if ($tahun < 2000 || $tahun > 9999) {
            Message::error('Tahun tidak valid');
            $this->redirect('/periode-akuntansi/buka-periode');
        }
        
        // Create periode (YYYYMM format)
        $periode = str_pad($tahun, 4, '0', STR_PAD_LEFT) . str_pad($bulan, 2, '0', STR_PAD_LEFT);
        
        // Check if periode already exists
        $existingPeriode = $periodeModel->findByPeriode($periode);
        if ($existingPeriode) {
            Message::error('Periode ' . $periode . ' sudah ada');
            $this->redirect('/periode-akuntansi/buka-periode');
        }
        
        try {
            // Create periode akuntansi
            $periodeData = [
                'periode' => $periode,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggalproses' => date('Y-m-d'),
                'aktif' => 1, // Set as active
                'id_user' => Auth::user() ? Auth::user()['id'] : null
            ];
            
            $periodeId = $periodeModel->create($periodeData);
            
            // Get all master akun level 5
            $akunLevel5 = $masterAkunModel->getByLevel(5);
            
            // Create saldo_akun for each master akun level 5
            foreach ($akunLevel5 as $akun) {
                $saldoData = [
                    'id_akun' => $akun['id'],
                    'periode' => $periode,
                    'saldoawal' => 0.00,
                    'debet' => 0.00,
                    'kredit' => 0.00,
                    'saldoakhir' => 0.00
                ];
                
                $saldoAkunModel->create($saldoData);
            }
            
            Message::success('Periode akuntansi berhasil dibuka dan saldo akun berhasil diinisialisasi');
            $this->redirect('/dashboard');
            
        } catch (\Exception $e) {
            Message::error('Gagal membuka periode akuntansi: ' . $e->getMessage());
            $this->redirect('/periode-akuntansi/buka-periode');
        }
    }
    
    public function create() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new PeriodeAkuntansi();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Validate periode format (YYYYMM)
            if (!preg_match('/^\d{6}$/', $postData['periode'] ?? '')) {
                Message::error('Format periode tidak valid. Gunakan format YYYYMM (contoh: 202401)');
                $this->json(['success' => false, 'message' => 'Format periode tidak valid']);
                return;
            }
            
            // Extract bulan and tahun from periode
            $periode = $postData['periode'];
            $tahun = (int)substr($periode, 0, 4);
            $bulan = (int)substr($periode, 4, 2);
            
            // Validate bulan and tahun
            if ($bulan < 1 || $bulan > 12) {
                Message::error('Bulan tidak valid (harus 1-12)');
                $this->json(['success' => false, 'message' => 'Bulan tidak valid']);
                return;
            }
            
            // Check if periode already exists
            $existing = $model->findByPeriode($periode);
            if ($existing) {
                Message::error('Periode sudah ada');
                $this->json(['success' => false, 'message' => 'Periode sudah ada']);
                return;
            }
            
            $data = [
                'periode' => $periode,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggalproses' => $postData['tanggalproses'] ?? date('Y-m-d'),
                'aktif' => !empty($postData['aktif']) ? 1 : 0,
                'id_user' => Auth::user() ? Auth::user()['id'] : null
            ];
            
            $validator = new Validator($data);
            $rules = [
                'periode' => 'required|min:6|max:6',
                'bulan' => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12',
                'tahun' => 'required|min:2000|max:9999',
                'tanggalproses' => 'required'
            ];
            
            if (!$validator->validate($rules)) {
                $errors = $validator->errors();
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        Message::error($error);
                    }
                }
                $this->json(['success' => false, 'message' => 'Validasi gagal']);
                return;
            }
            
            try {
                $id = $model->create($data);
                Message::success('Periode akuntansi berhasil ditambahkan');
                $this->json(['success' => true, 'id' => $id, 'message' => 'Periode akuntansi berhasil ditambahkan']);
            } catch (\Exception $e) {
                Message::error('Gagal menambahkan periode akuntansi: ' . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Gagal menambahkan periode akuntansi']);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Method not allowed']);
        }
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new PeriodeAkuntansi();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Periode akuntansi tidak ditemukan');
            $this->json(['success' => false, 'message' => 'Periode akuntansi tidak ditemukan']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Validate periode format if changed
            if (isset($postData['periode']) && $postData['periode'] !== $item['periode']) {
                if (!preg_match('/^\d{6}$/', $postData['periode'])) {
                    Message::error('Format periode tidak valid. Gunakan format YYYYMM');
                    $this->json(['success' => false, 'message' => 'Format periode tidak valid']);
                    return;
                }
                
                // Check if new periode already exists
                $existing = $model->findByPeriode($postData['periode']);
                if ($existing && $existing['id'] != $id) {
                    Message::error('Periode sudah digunakan');
                    $this->json(['success' => false, 'message' => 'Periode sudah digunakan']);
                    return;
                }
                
                // Extract bulan and tahun from periode
                $periode = $postData['periode'];
                $tahun = (int)substr($periode, 0, 4);
                $bulan = (int)substr($periode, 4, 2);
                
                $data = [
                    'periode' => $periode,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tanggalproses' => $postData['tanggalproses'] ?? $item['tanggalproses'],
                    'aktif' => !empty($postData['aktif']) ? 1 : 0,
                    'id_user' => Auth::user() ? Auth::user()['id'] : null
                ];
            } else {
                $data = [
                    'tanggalproses' => $postData['tanggalproses'] ?? $item['tanggalproses'],
                    'aktif' => !empty($postData['aktif']) ? 1 : 0,
                    'id_user' => Auth::user() ? Auth::user()['id'] : null
                ];
            }
            
            try {
                $model->update($id, $data);
                Message::success('Periode akuntansi berhasil diupdate');
                $this->json(['success' => true, 'message' => 'Periode akuntansi berhasil diupdate']);
            } catch (\Exception $e) {
                Message::error('Gagal mengupdate periode akuntansi: ' . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Gagal mengupdate periode akuntansi']);
            }
        } else {
            $this->json(['success' => true, 'data' => $item]);
        }
    }
    
    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new PeriodeAkuntansi();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Periode akuntansi tidak ditemukan');
            $this->json(['success' => false, 'message' => 'Periode akuntansi tidak ditemukan']);
            return;
        }
        
        try {
            $model->delete($id);
            Message::success('Periode akuntansi berhasil dihapus');
            $this->json(['success' => true, 'message' => 'Periode akuntansi berhasil dihapus']);
        } catch (\Exception $e) {
            Message::error('Gagal menghapus periode akuntansi: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Gagal menghapus periode akuntansi']);
        }
    }
    
    /**
     * Get active periode
     */
    public function getActive() {
        Auth::requireAuth();
        
        $model = new PeriodeAkuntansi();
        $active = $model->getActive();
        
        $this->json([
            'success' => true,
            'data' => $active
        ]);
    }
}
