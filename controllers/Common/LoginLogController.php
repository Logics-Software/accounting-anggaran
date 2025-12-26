<?php
namespace Controllers\Common;

use Controller;
use Auth;
use Session;
use Models\Common\LoginLog;

class LoginLogController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get per_page from GET or session, default to 20
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (isset($_GET['per_page'])) {
            $perPage = (int)$_GET['per_page'];
            if (in_array($perPage, $validPerPage)) {
                // Save to session for this module
                Session::set('per_page_login_logs', $perPage);
            } else {
                $perPage = 20;
            }
        } else {
            // Get from session, default to 20
            $perPage = (int)Session::get('per_page_login_logs', 20);
            if (!in_array($perPage, $validPerPage)) {
                $perPage = 20;
            }
        }
        
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'login_at';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        $loginLogModel = new LoginLog();
        $result = $loginLogModel->getAll($page, $perPage, $search, $status, $dateFrom, $dateTo, $sortBy, $sortOrder);
        
        $total = $result['total'];
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'logs' => $result['data'],
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('Common/loginlog/index', $data);
    }
}

