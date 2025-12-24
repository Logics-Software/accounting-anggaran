<?php
namespace Controllers\Common;

use Controller;
use Auth;
use Message;
use Models\Common\Konfigurasi;
use FileUpload;
use Validator;
use Sanitizer;

class KonfigurasiController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $konfigurasiModel = new Konfigurasi();
        $konfigurasi = $konfigurasiModel->get();
        $isEdit = $konfigurasi !== null;
        
        $data = [
            'konfigurasi' => $konfigurasi,
            'isEdit' => $isEdit
        ];
        
        $this->view('Common/konfigurasi/index', $data);
    }
    
    public function store() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/konfigurasi');
        }
        
        $konfigurasiModel = new Konfigurasi();
        $isEdit = $konfigurasiModel->exists();
        
        // Sanitize input
        $postData = Sanitizer::post($_POST);
        
        $data = [
            'namaorganisasi' => Sanitizer::string($postData['namaorganisasi'] ?? ''),
            'alamatorganisasi' => Sanitizer::string($postData['alamatorganisasi'] ?? ''),
            'notelporganisasi' => Sanitizer::string($postData['notelporganisasi'] ?? ''),
            'namaapp' => Sanitizer::string($postData['namaapp'] ?? '')
        ];
        
        // Validate using Validator
        $validator = new Validator($data);
        $rules = [
            'namaorganisasi' => 'required|min:2|max:255',
            'alamatorganisasi' => 'required|min:5|max:500',
            'notelporganisasi' => 'required|min:8|max:20',
            'namaapp' => 'required|min:2|max:255'
        ];
        
        if (!$validator->validate($rules)) {
            $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
            Message::error($firstError);
            $this->redirect('/konfigurasi');
        }
        
        $oldLogo = null;
        if ($isEdit) {
            $existing = $konfigurasiModel->get();
            $oldLogo = $existing['logo'] ?? null;
        }
        
        try {
            $config = require __DIR__ . '/../../config/app.php';
            $uploadPath = $config['upload_path'];
            $newLogoFilename = null;
            
            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $newLogoFilename = FileUpload::uploadImage(
                    $_FILES['logo'],
                    $uploadPath,
                    $config['allowed_image_types'],
                    $config['max_file_size'],
                    true // compress image
                );
                $data['logo'] = $newLogoFilename;
            } elseif ($isEdit) {
                // Keep existing logo if not uploading new one
                $data['logo'] = $oldLogo;
            }
            
            // Save configuration
            if ($isEdit) {
                $konfigurasiModel->update($data);
                $message = 'Konfigurasi berhasil diperbarui';
            } else {
                $konfigurasiModel->create($data);
                $message = 'Konfigurasi berhasil disimpan';
            }
            
            // Delete old logo AFTER successful database update
            if ($newLogoFilename && $oldLogo) {
                FileUpload::delete($oldLogo, $uploadPath);
            }
            
            Message::success($message);
            $this->redirect('/konfigurasi');
            
        } catch (\Exception $e) {
            // Cleanup new uploaded file if transaction fails
            if (isset($newLogoFilename) && $newLogoFilename) {
                $config = require __DIR__ . '/../../config/app.php';
                FileUpload::delete($newLogoFilename, $config['upload_path']);
            }
            
            Message::error($e->getMessage());
            $this->redirect('/konfigurasi');
        }
    }
}

