<?php
namespace Controllers\Common;

use Controller;
use Auth;
use Session;
use Message;
use Models\Common\User;
use FileUpload;
use Validator;
use Sanitizer;

class UserController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        $userModel = new User();
        $users = $userModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $userModel->count($search);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'users' => $users,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('Common/users/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'manajemen']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $postData = Sanitizer::post($_POST);
            
            $data = [
                'username' => Sanitizer::string($postData['username'] ?? ''),
                'namalengkap' => Sanitizer::string($postData['namalengkap'] ?? ''),
                'email' => Sanitizer::email($postData['email'] ?? ''),
                'password' => $postData['password'] ?? '',
                'role' => Sanitizer::string($postData['role'] ?? 'user'),
                'nomorhp' => !empty($postData['nomorhp']) ? Sanitizer::string($postData['nomorhp']) : null,
                'status' => Sanitizer::string($postData['status'] ?? 'aktif')
            ];
            
            // Validate using Validator
            $validator = new Validator($data);
            $rules = [
                'username' => 'required|min:3|max:50',
                'namalengkap' => 'required|min:2|max:100',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'role' => 'required|in:admin,manajemen,user',
                'status' => 'required|in:aktif,nonaktif'
            ];
            
            if (!$validator->validate($rules)) {
                $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
                Message::error($firstError);
                $this->redirect('/users/create');
            }
            
            $userModel = new User();
            
            // Check if username exists
            if ($userModel->findByUsername($data['username'])) {
                Message::error('Username sudah digunakan');
                $this->redirect('/users/create');
            }
            
            // Check if email exists
            if ($userModel->findByEmail($data['email'])) {
                Message::error('Email sudah digunakan');
                $this->redirect('/users/create');
            }
            
            // Use transaction for file upload + database insert
            $pictureFilename = null;
            try {
                $config = require __DIR__ . '/../../config/app.php';
                $uploadPath = $config['upload_path'];
                
                $this->db->transaction(function($db) use ($data, &$pictureFilename, $uploadPath, $config) {
                    // Handle picture upload INSIDE transaction using FileUpload helper
                    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                        $pictureFilename = FileUpload::uploadImage(
                            $_FILES['picture'],
                            $uploadPath,
                            $config['allowed_image_types'],
                            $config['max_file_size'],
                            true // compress image
                        );
                        $data['picture'] = $pictureFilename;
                    }
                    
                    // Create user in database
                    $userModel = new User();
                    $userModel->create($data);
                });
                
                Message::success('User berhasil ditambahkan');
                $this->redirect('/users');
                
            } catch (Exception $e) {
                // If transaction fails, uploaded file will be cleaned up
                if ($pictureFilename) {
                    $config = require __DIR__ . '/../../config/app.php';
                    FileUpload::delete($pictureFilename, $config['upload_path']);
                }
                
                Message::error('Gagal menambahkan user: ' . $e->getMessage());
                $this->redirect('/users/create');
            }
        }
        
        $data = [];
        $this->view('Common/users/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $userModel = new User();
        $user = $userModel->findById($id);
        
        if (!$user) {
            Message::error('User tidak ditemukan');
            $this->redirect('/users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $postData = Sanitizer::post($_POST);
            
            $data = [
                'username' => Sanitizer::string($postData['username'] ?? ''),
                'namalengkap' => Sanitizer::string($postData['namalengkap'] ?? ''),
                'email' => Sanitizer::email($postData['email'] ?? ''),
                'role' => Sanitizer::string($postData['role'] ?? 'user'),
                'nomorhp' => !empty($postData['nomorhp']) ? Sanitizer::string($postData['nomorhp']) : null,
                'status' => Sanitizer::string($postData['status'] ?? 'aktif')
            ];
            
            // Validate using Validator
            $validator = new Validator($data);
            $rules = [
                'username' => 'required|min:3|max:50',
                'namalengkap' => 'required|min:2|max:100',
                'email' => 'required|email',
                'role' => 'required|in:admin,manajemen,user',
                'status' => 'required|in:aktif,nonaktif'
            ];
            
            if (!$validator->validate($rules)) {
                $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
                Message::error($firstError);
                $this->redirect("/users/edit/{$id}");
            }
            
            // Check username uniqueness (except current user)
            $existingUser = $userModel->findByUsername($data['username']);
            if ($existingUser && $existingUser['id'] != $id) {
                Message::error('Username sudah digunakan');
                $this->redirect("/users/edit/{$id}");
            }
            
            // Check email uniqueness (except current user)
            $existingEmail = $userModel->findByEmail($data['email']);
            if ($existingEmail && $existingEmail['id'] != $id) {
                Message::error('Email sudah digunakan');
                $this->redirect("/users/edit/{$id}");
            }
            
            // Use transaction for file upload + database update
            $oldPicture = $user['picture'];
            $newPictureFilename = null;
            
            try {
                $config = require __DIR__ . '/../../config/app.php';
                $uploadPath = $config['upload_path'];
                
                $this->db->transaction(function($db) use ($id, &$data, &$newPictureFilename, $oldPicture, $uploadPath, $config) {
                    // Handle picture upload INSIDE transaction using FileUpload helper
                    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                        $newPictureFilename = FileUpload::uploadImage(
                            $_FILES['picture'],
                            $uploadPath,
                            $config['allowed_image_types'],
                            $config['max_file_size'],
                            true // compress image
                        );
                        $data['picture'] = $newPictureFilename;
                    }
                    
                    // Update user in database
                    $userModel = new User();
                    $userModel->update($id, $data);
                    
                    // Delete old picture AFTER successful database update
                    if ($newPictureFilename && $oldPicture) {
                        FileUpload::delete($oldPicture, $uploadPath);
                    }
                });
                
                Message::success('User berhasil diupdate');
                $this->redirect('/users');
                
            } catch (Exception $e) {
                // If transaction fails, cleanup new uploaded file
                if ($newPictureFilename) {
                    $config = require __DIR__ . '/../../config/app.php';
                    FileUpload::delete($newPictureFilename, $config['upload_path']);
                }
                
                Message::error('Gagal mengupdate user: ' . $e->getMessage());
                $this->redirect("/users/edit/{$id}");
            }
        }
        
        $data = ['user' => $user];
        $this->view('Common/users/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen']);
        
        $userModel = new User();
        $user = $userModel->findById($id);
        
        if (!$user) {
            Message::error('User tidak ditemukan');
            $this->redirect('/users');
        }
        
        // Don't allow deleting yourself
        if ($user['id'] == Auth::user()['id']) {
            Message::error('Tidak dapat menghapus akun sendiri');
            $this->redirect('/users');
        }
        
        // Use transaction for file delete + database delete
        $config = require __DIR__ . '/../../config/app.php';
        $uploadPath = $config['upload_path'];
        
        try {
            $this->db->transaction(function($db) use ($id, $user, $uploadPath) {
                // Delete from database FIRST
                $userModel = new User();
                $userModel->delete($id);
                
                // Delete picture AFTER successful database delete using FileUpload helper
                if ($user['picture']) {
                    FileUpload::delete($user['picture'], $uploadPath);
                }
            });
            
            Message::success('User berhasil dihapus');
            $this->redirect('/users');
            
        } catch (Exception $e) {
            Message::error('Gagal menghapus user: ' . $e->getMessage());
            $this->redirect('/users');
        }
    }
}

