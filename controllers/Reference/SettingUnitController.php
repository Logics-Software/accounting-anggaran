<?php
namespace Controllers\Reference;

use Controller;
use Auth;
use Message;
use Models\Reference\SettingUnit;
use Models\Reference\SettingBagian;
use Models\Common\User;
use Validator;
use Sanitizer;

class SettingUnitController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $filterBagian = $_GET['filter_bagian'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        // Get all active bagian for dropdown
        $bagianModel = new SettingBagian();
        $bagians = $bagianModel->getAllActive();
        
        $model = new SettingUnit();
        $items = $model->getAll($page, $perPage, $search, $sortBy, $sortOrder, $filterBagian);
        $total = $model->count($search, $filterBagian);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'filterBagian' => $filterBagian,
            'bagians' => $bagians,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('Reference/setting-unit/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $bagianModel = new SettingBagian();
        $bagians = $bagianModel->getAll(1, 1000, '', 'namabagian', 'ASC');
        
        $userModel = new User();
        $users = $userModel->getAll(1, 1000, '', 'namalengkap', 'ASC');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            $data = [
                'namaunit' => Sanitizer::string($postData['namaunit'] ?? ''),
                'id_bagian' => !empty($postData['id_bagian']) ? (int)$postData['id_bagian'] : null,
                'jabatan_pimpinan' => !empty($postData['jabatan_pimpinan']) ? Sanitizer::string($postData['jabatan_pimpinan']) : null,
                'id_pimpinan' => !empty($postData['id_pimpinan']) ? (int)$postData['id_pimpinan'] : null,
                'status' => Sanitizer::string($postData['status'] ?? 'aktif')
            ];
            
            $validator = new Validator($data);
            $rules = [
                'namaunit' => 'required|min:2|max:255',
                'status' => 'required|in:aktif,nonaktif'
            ];
            
            if (!$validator->validate($rules)) {
                $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
                Message::error($firstError);
                $this->redirect('/setting-unit/create');
            }
            
            $model = new SettingUnit();
            $model->create($data);
            
            Message::success('Setting Unit berhasil ditambahkan');
            $this->redirect('/setting-unit');
        }
        
        $data = ['bagians' => $bagians, 'users' => $users];
        $this->view('Reference/setting-unit/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new SettingUnit();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Setting Unit tidak ditemukan');
            $this->redirect('/setting-unit');
        }
        
        $bagianModel = new SettingBagian();
        $bagians = $bagianModel->getAll(1, 1000, '', 'namabagian', 'ASC');
        
        $userModel = new User();
        $users = $userModel->getAll(1, 1000, '', 'namalengkap', 'ASC');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            
            $data = [
                'namaunit' => Sanitizer::string($postData['namaunit'] ?? ''),
                'id_bagian' => !empty($postData['id_bagian']) ? (int)$postData['id_bagian'] : null,
                'jabatan_pimpinan' => !empty($postData['jabatan_pimpinan']) ? Sanitizer::string($postData['jabatan_pimpinan']) : null,
                'id_pimpinan' => !empty($postData['id_pimpinan']) ? (int)$postData['id_pimpinan'] : null,
                'status' => Sanitizer::string($postData['status'] ?? 'aktif')
            ];
            
            $validator = new Validator($data);
            $rules = [
                'namaunit' => 'required|min:2|max:255',
                'status' => 'required|in:aktif,nonaktif'
            ];
            
            if (!$validator->validate($rules)) {
                $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
                Message::error($firstError);
                $this->redirect("/setting-unit/edit/{$id}");
            }
            
            $model->update($id, $data);
            
            Message::success('Setting Unit berhasil diupdate');
            $this->redirect('/setting-unit');
        }
        
        $data = ['item' => $item, 'bagians' => $bagians, 'users' => $users];
        $this->view('Reference/setting-unit/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $model = new SettingUnit();
        $item = $model->findById($id);
        
        if (!$item) {
            Message::error('Setting Unit tidak ditemukan');
            $this->redirect('/setting-unit');
        }
        
        $model->delete($id);
        
        Message::success('Setting Unit berhasil dihapus');
        $this->redirect('/setting-unit');
    }
}

