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

class ProfileController extends Controller {
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $data = ['user' => $user];
        $this->view('Common/profile/index', $data);
    }
    
    public function update() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $userModel = new User();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $postData = Sanitizer::post($_POST);
            
            $data = [];
            
            // Only update fields that are provided
            if (isset($postData['username']) && !empty($postData['username'])) {
                $data['username'] = Sanitizer::string($postData['username']);
                
                // Validate username
                if (!Validator::minLength($data['username'], 3) || !Validator::maxLength($data['username'], 50)) {
                    Message::error('Username harus antara 3-50 karakter');
                    $this->redirect('/profile');
                }
                
                // Check username uniqueness (except current user)
                $existingUser = $userModel->findByUsername($data['username']);
                if ($existingUser && $existingUser['id'] != $user['id']) {
                    Message::error('Username sudah digunakan');
                    $this->redirect('/profile');
                }
            }
            
            if (isset($postData['namalengkap']) && !empty($postData['namalengkap'])) {
                $data['namalengkap'] = Sanitizer::string($postData['namalengkap']);
            }
            
            if (isset($postData['email']) && !empty($postData['email'])) {
                $data['email'] = Sanitizer::email($postData['email']);
                
                if (empty($data['email'])) {
                    Message::error('Email tidak valid');
                    $this->redirect('/profile');
                }
                
                // Check email uniqueness (except current user)
                $existingEmail = $userModel->findByEmail($data['email']);
                if ($existingEmail && $existingEmail['id'] != $user['id']) {
                    Message::error('Email sudah digunakan');
                    $this->redirect('/profile');
                }
            }
            
            if (isset($postData['nomorhp']) && !empty($postData['nomorhp'])) {
                $data['nomorhp'] = Sanitizer::string($postData['nomorhp']);
            }
            
            // Use transaction for file upload + database update
            $oldPicture = $user['picture'];
            $newPictureFilename = null;
            
            try {
                $config = require __DIR__ . '/../../config/app.php';
                $uploadPath = $config['upload_path'];
                
                $this->db->transaction(function($db) use ($user, &$data, &$newPictureFilename, $oldPicture, $uploadPath, $config) {
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
                    
                    // Update database
                    if (!empty($data)) {
                        $userModel = new User();
                        $userModel->update($user['id'], $data);
                    }
                    
                    // Delete old picture AFTER successful database update
                    if ($newPictureFilename && $oldPicture) {
                        FileUpload::delete($oldPicture, $uploadPath);
                    }
                });
                
                Message::success('Profile berhasil diupdate');
                $this->redirect('/profile');
                
            } catch (Exception $e) {
                // Cleanup new uploaded file if transaction fails
                if ($newPictureFilename) {
                    $config = require __DIR__ . '/../../config/app.php';
                    FileUpload::delete($newPictureFilename, $config['upload_path']);
                }
                
                Message::error($e->getMessage());
                $this->redirect('/profile');
            }
        }
        
        $data = ['user' => $user];
        $this->view('Common/profile/index', $data);
    }
    
    public function changePassword() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $userModel = new User();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input (password tidak perlu sanitize, hanya trim)
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            
            // Validate using Validator
            $validator = new Validator([
                'current_password' => $currentPassword,
                'new_password' => $newPassword,
                'confirm_password' => $confirmPassword
            ]);
            
            $rules = [
                'current_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password'
            ];
            
            if (!$validator->validate($rules)) {
                $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
                Message::error($firstError);
                $this->redirect('/profile/change-password');
            }
            
            if (!$userModel->verifyPassword($currentPassword, $user['password'])) {
                Message::error('Password lama salah');
                $this->redirect('/profile/change-password');
            }
            
            $userModel->update($user['id'], ['password' => $newPassword]);
            Message::success('Password berhasil diubah');
            $this->redirect('/profile/change-password');
        }
        
        $data = ['user' => $user];
        $this->view('Common/profile/change-password', $data);
    }
    
    public function settings() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $data = ['user' => $user];
        $this->view('Common/profile/setting', $data);
    }
}

