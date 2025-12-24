<?php
$title = 'Login Log';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Log Login</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
				<div class="card-header">
					<div class="d-flex align-items-center">
						<h4 class="mb-0">Login Log</h4>
					</div>
                </div>

                <div class="card-body">
                    <form method="GET" action="/login-logs" class="mb-3" id="searchForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Cari username, nama, IP..." value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="success" <?= ($status ?? '') === 'success' ? 'selected' : '' ?>>Success</option>
                                    <option value="failed" <?= ($status ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="logout" <?= ($status ?? '') === 'logout' ? 'selected' : '' ?>>Logout</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>">
                            </div>
                            <div class="col-4 col-md-1">
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
                                    <option value="<?= $pp ?>" <?= ($perPage ?? 10) == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-md-1">
                                <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                            </div>
                            <div class="col-4 col-md-1">
                                <a href="/login-logs?page=1&per_page=<?= $perPage ?? 20 ?>&sort_by=<?= htmlspecialchars($sortBy ?? 'login_at') ?>&sort_order=<?= htmlspecialchars($sortOrder ?? 'DESC') ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy ?? 'login_at') ?>">
                        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder ?? 'DESC') ?>">
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Login At</th>
                                    <th>Logout At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No data found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['username'] ?? 'N/A') ?> (<?= htmlspecialchars($log['namalengkap'] ?? 'N/A') ?>)</td>
                                    <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($log['login_at'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($log['logout_at'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'secondary';
                                        if ($log['status'] === 'success') $statusClass = 'success';
                                        elseif ($log['status'] === 'failed') $statusClass = 'danger';
                                        elseif ($log['status'] === 'logout') $statusClass = 'info';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars($log['status'] ?? 'N/A') ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <?php
                    // Get current page from URL (always use $_GET to ensure it's current)
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
                    // Ensure currentPage is at least 1
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    }
                    // Use currentPage for all calculations
                    $page = $currentPage;
                    $totalPages = (int)$totalPages;
                    $perPage = (int)($perPage ?? 10);
                    $search = $search ?? '';
                    $status = $status ?? '';
                    $dateFrom = $dateFrom ?? '';
                    $dateTo = $dateTo ?? '';
                    $sortBy = $sortBy ?? 'login_at';
                    $sortOrder = $sortOrder ?? 'DESC';
                    
                    // Build link function for pagination
                    $buildLink = function ($p) use ($perPage, $search, $status, $dateFrom, $dateTo, $sortBy, $sortOrder) {
                        $params = [
                            'page' => $p,
                            'per_page' => $perPage,
                            'sort_by' => $sortBy,
                            'sort_order' => $sortOrder
                        ];
                        if (!empty($search)) $params['search'] = $search;
                        if (!empty($status)) $params['status'] = $status;
                        if (!empty($dateFrom)) $params['date_from'] = $dateFrom;
                        if (!empty($dateTo)) $params['date_to'] = $dateTo;
                        return '?' . http_build_query($params);
                    };
                    $maxLinks = 3;
                    $half = (int)floor($maxLinks / 2);
                    $start = max(1, $page - $half);
                    $end = min($totalPages, $start + $maxLinks - 1);
                    if ($end - $start + 1 < $maxLinks) {
                        $start = max(1, $end - $maxLinks + 1);
                    }
                    ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <?php
                                // Calculate previous page, ensuring it's an integer
                                $prevPage = (int)max(1, (int)$page - 1);
                                // Ensure prevPage is at least 1
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="/login-logs<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/login-logs' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/login-logs' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/login-logs' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php
                                // Calculate next page: current page + 1 (increment)
                                // $page is already cast to int and validated above
                                // Simply increment current page
                                $nextPage = $page + 1;
                                
                                // Only cap at totalPages if it exceeds (for disabled state)
                                if ($nextPage > $totalPages) {
                                    $nextPage = $totalPages;
                                }
                                
                                // Ensure it's an integer
                                $nextPage = (int)$nextPage;
                                ?>
                                <a class="page-link" href="/login-logs<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    
                    <!-- Pagination Info -->
                    <div class="text-center text-muted mt-2">
                        Menampilkan <?= (((int)$page - 1) * (int)$perPage) + 1 ?> sampai 
                        <?= min((int)$page * (int)$perPage, (int)$total) ?> 
                        dari <?= $total ?> log
                    </div>
                    <?php elseif (isset($total) && $total > 0): ?>
                    <div class="text-center text-muted mt-3">
                        Total: <?= $total ?> log
                    </div>
                    <?php endif; ?>
                </div>
            </div>             
        </div>    
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

