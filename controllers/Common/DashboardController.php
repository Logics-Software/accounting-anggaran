<?php
namespace Controllers\Common;

use Controller;
use Auth;
use Session;

class DashboardController extends Controller {
    
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $role = $user['role'] ?? '';
        
        // Dashboard akan dikembangkan lebih lanjut
        // Data dan logika akan ditambahkan sesuai kebutuhan
        
        $data = [
            'user' => $user,
            'role' => $role,
        ];
        
        $this->view('Common/dashboard/index', $data);
    }
}
