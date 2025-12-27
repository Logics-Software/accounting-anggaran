<?php
namespace Controllers\Master;

use Controller;
use Auth;
use Message;
use Session;
use Models\Master\SaldoAkun;
use Models\Master\MasterAkun;
use Sanitizer;
use Validator;

class SaldoAkunController extends Controller {
    
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get per_page from GET or session, default to 10
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (isset($_GET['per_page'])) {
            $perPage = (int)$_GET['per_page'];
            if (in_array($perPage, $validPerPage)) {
                Session::set('per_page_saldo_akun', $perPage);
            } else {
                $perPage = 10;
            }
        } else {
            $perPage = (int)Session::get('per_page_saldo_akun', 10);
            if (!in_array($perPage, $validPerPage)) {
                $perPage = 10;
            }
        }
        
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'periode';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        $filterPeriode = $_GET['filter_periode'] ?? '';
        $filterAkun = $_GET['filter_akun'] ?? '';
        
        $model = new SaldoAkun();
        $items = $model->getAll($page, $perPage, $search, $filterPeriode, $filterAkun, $sortBy, $sortOrder);
        $total = $model->count($search, $filterPeriode, $filterAkun);
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
            'filterPeriode' => $filterPeriode,
            'filterAkun' => $filterAkun
        ];
        
        // For now, return JSON or simple response since no view yet
        $this->json([
            'success' => true,
            'data' => $data,
            'message' => 'Saldo Akun module - View will be created later'
        ]);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new SaldoAkun();
        $akunModel = new MasterAkun();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Validate periode format (YYYYMM)
            if (!preg_match('/^\d{6}$/', $postData['periode'] ?? '')) {
                Message::error('Format periode tidak valid. Gunakan format YYYYMM');
                $this->json(['success' => false, 'message' => 'Format periode tidak valid']);
                return;
            }
            
            // Check if akun exists
            $akun = $akunModel->findById($postData['id_akun'] ?? 0);
            if (!$akun) {
                Message::error('Akun tidak ditemukan');
                $this->json(['success' => false, 'message' => 'Akun tidak ditemukan']);
                return;
            }
            
            // Check if saldo akun already exists for this akun and periode
            $existing = $model->findByAkunPeriode($postData['id_akun'], $postData['periode']);
            if ($existing) {
                Message::error('Saldo akun untuk periode ini sudah ada');
                $this->json(['success' => false, 'message' => 'Saldo akun untuk periode ini sudah ada']);
                return;
            }
            
            // Calculate saldo akhir
            $saldoAwal = (float)($postData['saldoawal'] ?? 0);
            $debet = (float)($postData['debet'] ?? 0);
            $kredit = (float)($postData['kredit'] ?? 0);
            $saldoAkhir = $model->calculateSaldoAkhir($saldoAwal, $debet, $kredit, $akun['saldo_normal']);
            
            $data = [
                'id_akun' => (int)$postData['id_akun'],
                'periode' => $postData['periode'],
                'saldoawal' => $saldoAwal,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldoakhir' => $saldoAkhir
            ];
            
            $validator = new Validator($data);
            $rules = [
                'id_akun' => 'required',
                'periode' => 'required|min:6|max:6',
                'saldoawal' => 'required',
                'debet' => 'required',
                'kredit' => 'required'
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
                Message::success('Saldo akun berhasil ditambahkan');
                $this->json(['success' => true, 'id' => $id, 'message' => 'Saldo akun berhasil ditambahkan']);
            } catch (\Exception $e) {
                Message::error('Gagal menambahkan saldo akun: ' . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Gagal menambahkan saldo akun']);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Method not allowed']);
        }
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new SaldoAkun();
        $akunModel = new MasterAkun();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Saldo akun tidak ditemukan');
            $this->json(['success' => false, 'message' => 'Saldo akun tidak ditemukan']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Get akun for saldo normal calculation
            $akun = $akunModel->findById($item['id_akun']);
            if (!$akun) {
                Message::error('Akun tidak ditemukan');
                $this->json(['success' => false, 'message' => 'Akun tidak ditemukan']);
                return;
            }
            
            // Calculate saldo akhir
            $saldoAwal = (float)($postData['saldoawal'] ?? $item['saldoawal']);
            $debet = (float)($postData['debet'] ?? $item['debet']);
            $kredit = (float)($postData['kredit'] ?? $item['kredit']);
            $saldoAkhir = $model->calculateSaldoAkhir($saldoAwal, $debet, $kredit, $akun['saldo_normal']);
            
            $data = [
                'saldoawal' => $saldoAwal,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldoakhir' => $saldoAkhir
            ];
            
            // Allow changing periode if provided
            if (isset($postData['periode']) && $postData['periode'] !== $item['periode']) {
                if (!preg_match('/^\d{6}$/', $postData['periode'])) {
                    Message::error('Format periode tidak valid');
                    $this->json(['success' => false, 'message' => 'Format periode tidak valid']);
                    return;
                }
                
                // Check if new periode already exists for this akun
                $existing = $model->findByAkunPeriode($item['id_akun'], $postData['periode']);
                if ($existing && $existing['id'] != $id) {
                    Message::error('Saldo akun untuk periode ini sudah ada');
                    $this->json(['success' => false, 'message' => 'Saldo akun untuk periode ini sudah ada']);
                    return;
                }
                
                $data['periode'] = $postData['periode'];
            }
            
            try {
                $model->update($id, $data);
                Message::success('Saldo akun berhasil diupdate');
                $this->json(['success' => true, 'message' => 'Saldo akun berhasil diupdate']);
            } catch (\Exception $e) {
                Message::error('Gagal mengupdate saldo akun: ' . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Gagal mengupdate saldo akun']);
            }
        } else {
            $this->json(['success' => true, 'data' => $item]);
        }
    }
    
    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new SaldoAkun();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Saldo akun tidak ditemukan');
            $this->json(['success' => false, 'message' => 'Saldo akun tidak ditemukan']);
            return;
        }
        
        try {
            $model->delete($id);
            Message::success('Saldo akun berhasil dihapus');
            $this->json(['success' => true, 'message' => 'Saldo akun berhasil dihapus']);
        } catch (\Exception $e) {
            Message::error('Gagal menghapus saldo akun: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Gagal menghapus saldo akun']);
        }
    }
    
    /**
     * Get saldo akun by periode
     */
    public function getByPeriode($periode) {
        Auth::requireAuth();
        
        $model = new SaldoAkun();
        $items = $model->getByPeriode($periode);
        
        $this->json([
            'success' => true,
            'data' => $items
        ]);
    }
    
    /**
     * Get saldo akun by id_akun
     */
    public function getByAkun($idAkun) {
        Auth::requireAuth();
        
        $model = new SaldoAkun();
        $items = $model->getByAkun($idAkun);
        
        $this->json([
            'success' => true,
            'data' => $items
        ]);
    }
    
    /**
     * Update or create saldo akun (upsert)
     */
    public function updateOrCreate() {
        Auth::requireRole(['admin', 'manajemen']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $postData = Sanitizer::post($_POST);
        $model = new SaldoAkun();
        $akunModel = new MasterAkun();
        
        // Validate required fields
        if (empty($postData['id_akun']) || empty($postData['periode'])) {
            $this->json(['success' => false, 'message' => 'id_akun dan periode harus diisi']);
            return;
        }
        
        // Get akun for saldo normal calculation
        $akun = $akunModel->findById($postData['id_akun']);
        if (!$akun) {
            $this->json(['success' => false, 'message' => 'Akun tidak ditemukan']);
            return;
        }
        
        // Calculate saldo akhir
        $saldoAwal = (float)($postData['saldoawal'] ?? 0);
        $debet = (float)($postData['debet'] ?? 0);
        $kredit = (float)($postData['kredit'] ?? 0);
        $saldoAkhir = $model->calculateSaldoAkhir($saldoAwal, $debet, $kredit, $akun['saldo_normal']);
        
        $data = [
            'saldoawal' => $saldoAwal,
            'debet' => $debet,
            'kredit' => $kredit,
            'saldoakhir' => $saldoAkhir
        ];
        
        try {
            $result = $model->updateOrCreate($postData['id_akun'], $postData['periode'], $data);
            $this->json([
                'success' => true,
                'message' => 'Saldo akun berhasil disimpan',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal menyimpan saldo akun: ' . $e->getMessage()
            ]);
        }
    }
}

