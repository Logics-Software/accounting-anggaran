<?php
namespace Controllers\Common;

use Controller;
use Auth;
use Models\Common\BackupLog;

class BackupLogController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $search = $_GET['search'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'tanggal_backup';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 20;
        }
        
        $backupLogModel = new BackupLog();
        $logs = $backupLogModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $dateFrom, $dateTo);
        $total = $backupLogModel->count($search, $dateFrom, $dateTo);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'logs' => $logs,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('Common/backuplog/index', $data);
    }
}

