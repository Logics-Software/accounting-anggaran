<?php
namespace Controllers\Master;

use Controller;
use Auth;
use Message;
use Models\Master\SettingJurnal;
use Validator;
use Sanitizer;

class SettingJurnalController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $settingJurnalModel = new SettingJurnal();
        $settingJurnal = $settingJurnalModel->get();
        $isEdit = $settingJurnal !== null;
        
        $data = [
            'settingJurnal' => $settingJurnal,
            'isEdit' => $isEdit
        ];
        
        $this->view('Master/setting-jurnal/index', $data);
    }
    
    public function store() {
        Auth::requireRole(['admin', 'manajemen']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/setting-jurnal');
        }
        
        $settingJurnalModel = new SettingJurnal();
        $isEdit = $settingJurnalModel->exists();
        
        // Sanitize input
        $postData = Sanitizer::post($_POST);
        
        // Get switch values - check if value is '1' (explicit check)
        // This ensures we get the final state of all switches
        $data = [
            'inisial_jurnal_umum' => Sanitizer::string($postData['inisial_jurnal_umum'] ?? ''),
            'inisial_jurnal_penyesuaian' => Sanitizer::string($postData['inisial_jurnal_penyesuaian'] ?? ''),
            'inisial_jurnal_neraca' => Sanitizer::string($postData['inisial_jurnal_neraca'] ?? ''),
            'bulan_jurnal_umum' => isset($postData['bulan_jurnal_umum']) && ($postData['bulan_jurnal_umum'] === '1' || $postData['bulan_jurnal_umum'] === 1),
            'bulan_jurnal_penyesuaian' => isset($postData['bulan_jurnal_penyesuaian']) && ($postData['bulan_jurnal_penyesuaian'] === '1' || $postData['bulan_jurnal_penyesuaian'] === 1),
            'bulan_jurnal_neraca' => isset($postData['bulan_jurnal_neraca']) && ($postData['bulan_jurnal_neraca'] === '1' || $postData['bulan_jurnal_neraca'] === 1),
            'tahun_jurnal_umum' => isset($postData['tahun_jurnal_umum']) && ($postData['tahun_jurnal_umum'] === '1' || $postData['tahun_jurnal_umum'] === 1),
            'tahun_jurnal_penyesuaian' => isset($postData['tahun_jurnal_penyesuaian']) && ($postData['tahun_jurnal_penyesuaian'] === '1' || $postData['tahun_jurnal_penyesuaian'] === 1),
            'tahun_jurnal_neraca' => isset($postData['tahun_jurnal_neraca']) && ($postData['tahun_jurnal_neraca'] === '1' || $postData['tahun_jurnal_neraca'] === 1),
            'jurnal_approval' => isset($postData['jurnal_approval']) && ($postData['jurnal_approval'] === '1' || $postData['jurnal_approval'] === 1)
        ];
        
        // Validate using Validator
        $validator = new Validator($data);
        $rules = [
            'inisial_jurnal_umum' => 'max:5',
            'inisial_jurnal_penyesuaian' => 'max:5',
            'inisial_jurnal_neraca' => 'max:5'
        ];
        
        if (!$validator->validate($rules)) {
            $firstError = array_values($validator->errors())[0][0] ?? 'Validasi gagal';
            Message::error($firstError);
            $this->redirect('/setting-jurnal');
        }
        
        try {
            // Save setting jurnal
            if ($isEdit) {
                $settingJurnalModel->update($data);
                $message = 'Setting jurnal berhasil diperbarui';
            } else {
                $settingJurnalModel->create($data);
                $message = 'Setting jurnal berhasil disimpan';
            }
            
            Message::success($message);
            $this->redirect('/setting-jurnal');
            
        } catch (\Exception $e) {
            Message::error('Gagal menyimpan setting jurnal: ' . $e->getMessage());
            $this->redirect('/setting-jurnal');
        }
    }
}

