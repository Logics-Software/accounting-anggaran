<?php
namespace Controllers\Master;

use Controller;
use Auth;
use Message;
use Session;
use Models\Master\SettingBidang;
use Validator;
use Sanitizer;

class SettingBidangController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get per_page from GET or session, default to 10
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (isset($_GET['per_page'])) {
            $perPage = (int)$_GET['per_page'];
            if (in_array($perPage, $validPerPage)) {
                // Save to session for this module
                Session::set('per_page_setting_bidang', $perPage);
            } else {
                $perPage = 10;
            }
        } else {
            // Get from session, default to 10
            $perPage = (int)Session::get('per_page_setting_bidang', 10);
            if (!in_array($perPage, $validPerPage)) {
                $perPage = 10;
            }
        }
        
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $model = new SettingBidang();
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
        
        $this->view('Master/setting-bidang/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'manajemen']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Handle pengelola_akun: checkbox sends "1" if checked, or we add "0" via JS
            // Convert to boolean (true/false) for BOOLEAN field
            $pengelolaAkun = false;
            if (isset($postData['pengelola_akun']) && $postData['pengelola_akun'] == '1') {
                $pengelolaAkun = true;
            }
            
            $data = [
                'namabidang' => Sanitizer::string($postData['namabidang'] ?? ''),
                'pengelola_akun' => $pengelolaAkun,
                'status' => Sanitizer::string($postData['status'] ?? 'aktif')
            ];
            
            $validator = new Validator($data);
            $rules = [
                'namabidang' => 'required|min:2|max:255',
                'pengelola_akun' => 'in:true,false,1,0',
                'status' => 'required|in:aktif,nonaktif'
            ];
            
            if (!$validator->validate($rules)) {
                $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
                Message::error($firstError);
                $this->redirect('/setting-bidang/create');
            }
            
            $model = new SettingBidang();
            $model->create($data);
            
            Message::success('Setting Bidang berhasil ditambahkan');
            $this->redirect('/setting-bidang');
        }
        
        $data = [];
        $this->view('Master/setting-bidang/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new SettingBidang();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Setting Bidang tidak ditemukan');
            $this->redirect('/setting-bidang');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            // Handle pengelola_akun: checkbox sends "1" if checked, or we add "0" via JS
            // Convert to boolean (true/false) for BOOLEAN field
            $pengelolaAkun = false;
            if (isset($postData['pengelola_akun']) && $postData['pengelola_akun'] == '1') {
                $pengelolaAkun = true;
            }
            
            $data = [
                'namabidang' => Sanitizer::string($postData['namabidang'] ?? ''),
                'pengelola_akun' => $pengelolaAkun,
                'status' => Sanitizer::string($postData['status'] ?? 'aktif')
            ];
            
            $validator = new Validator($data);
            $rules = [
                'namabidang' => 'required|min:2|max:255',
                'pengelola_akun' => 'in:true,false,1,0',
                'status' => 'required|in:aktif,nonaktif'
            ];
            
            if (!$validator->validate($rules)) {
                $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
                Message::error($firstError);
                $this->redirect("/setting-bidang/edit/{$id}");
            }
            
            $model->update($id, $data);
            
            Message::success('Setting Bidang berhasil diupdate');
            $this->redirect('/setting-bidang');
        }
        
        $data = ['item' => $item];
        $this->view('Master/setting-bidang/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new SettingBidang();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Setting Bidang tidak ditemukan');
            $this->redirect('/setting-bidang');
        }
        
        $model->delete($id);
        
        Message::success('Setting Bidang berhasil dihapus');
        $this->redirect('/setting-bidang');
    }
}

