<?php
$title = 'Master Akun';
$config = require __DIR__ . '/../../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $filterKelompok = '', $filterLevel = '') {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if (!empty($filterKelompok)) $params['filter_kelompok'] = $filterKelompok;
        if (!empty($filterLevel)) $params['filter_level'] = $filterLevel;
        return '/master-akun?' . http_build_query($params);
    }
}

// Helper function to build link
if (!function_exists('buildLink')) {
    function buildLink($p, $perPage, $search, $sortBy, $sortOrder, $filterKelompok = '', $filterLevel = '') {
        $params = [
            'page' => $p,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];
        if (!empty($search)) $params['search'] = $search;
        if (!empty($filterKelompok)) $params['filter_kelompok'] = $filterKelompok;
        if (!empty($filterLevel)) $params['filter_level'] = $filterLevel;
        return '?' . http_build_query($params);
    }
}

require __DIR__ . '/../../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Master Akun</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
				<div class="card-header">
					<div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Master Akun</h4>
						<div class="ms-auto d-flex gap-2">
							<?php
							// Build export URL with current filters
							$exportParams = [];
							if (!empty($search)) $exportParams['search'] = $search;
							if (!empty($filterKelompok)) $exportParams['filter_kelompok'] = $filterKelompok;
							if (!empty($filterLevel)) $exportParams['filter_level'] = $filterLevel;
							if (!empty($sortBy)) $exportParams['sort_by'] = $sortBy;
							if (!empty($sortOrder)) $exportParams['sort_order'] = $sortOrder;
							$exportQuery = !empty($exportParams) ? '?' . http_build_query($exportParams) : '';
							?>
							<a href="/master-akun/export/excel<?= $exportQuery ?>" class="btn btn-success btn-sm" title="Download Excel">
								<?= icon('file-excel', 'me-1 mb-1', 16) ?>Excel
							</a>
							<a href="/master-akun/export/pdf<?= $exportQuery ?>" class="btn btn-danger btn-sm" title="Download PDF" target="_blank">
								<?= icon('file-pdf', 'me-1 mb-1', 16) ?>PDF
							</a>
							<a href="/master-akun/create" class="btn btn-primary btn-sm">Tambah Akun</a>
						</div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/master-akun" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nomor/nama akun..." value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                                <div class="col-6 col-md-2">
                                    <select name="filter_kelompok" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Kelompok</option>
                                        <?php foreach ($kelompokOptions as $kelompok): ?>
                                            <option value="<?= htmlspecialchars($kelompok) ?>" <?= ($filterKelompok == $kelompok) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kelompok) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <select name="filter_level" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Level</option>
                                        <?php foreach ($levelOptions as $level): ?>
                                            <option value="<?= $level ?>" <?= ($filterLevel == $level) ? 'selected' : '' ?>>
                                                Level <?= $level ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-1">
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-2">
                                    <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                </div>
                                <div class="col-4 col-md-2">
                                    <a href="/master-akun?page=1&per_page=10&sort_by=<?= htmlspecialchars($sortBy ?? 'nomor_akun') ?>&sort_order=<?= htmlspecialchars($sortOrder ?? 'ASC') ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy ?? 'nomor_akun') ?>">
                            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder ?? 'ASC') ?>">
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('nomor_akun', $sortBy, $sortOrder, $search, $perPage, $filterKelompok, $filterLevel) ?>">Akun</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('nama_akun', $sortBy, $sortOrder, $search, $perPage, $filterKelompok, $filterLevel) ?>">Nama Akun</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('kelompok_akun', $sortBy, $sortOrder, $search, $perPage, $filterKelompok, $filterLevel) ?>">Kelompok</a></th>
                                    <th>Detail</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('level_akun', $sortBy, $sortOrder, $search, $perPage, $filterKelompok, $filterLevel) ?>">Level</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $currentPage = isset($page) ? max(1, (int)$page) : 1;
                                $currentPerPage = isset($perPage) ? max(1, (int)$perPage) : 10;
                                $no = ($currentPage - 1) * $currentPerPage + 1;
                                foreach ($items as $item): 
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($item['nomor_akun'] ?? '') ?></td>
                                    <td>
                                        <?php 
                                        $level = isset($item['level_akun']) ? (int)$item['level_akun'] : 1;
                                        $spaces = ($level - 1) * 3;
                                        $indent = str_repeat('&nbsp;', $spaces);
                                        echo $indent . htmlspecialchars($item['nama_akun'] ?? '');
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $kelompok = $item['kelompok_akun'] ?? '';
                                        $badgeClass = 'bg-info'; // default
                                        switch ($kelompok) {
                                            case 'AKTIVA':
                                                $badgeClass = 'bg-primary';
                                                break;
                                            case 'PASIVA':
                                                $badgeClass = 'bg-warning';
                                                break;
                                            case 'PENDAPATAN':
                                                $badgeClass = 'bg-success';
                                                break;
                                            case 'BEBAN':
                                                $badgeClass = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($kelompok) ?></span>
                                    </td>
                                    <td><?= !empty($item['detail_akun']) ? htmlspecialchars($item['detail_akun']) : '-' ?></td>
                                    <td class="text-center fw-bold"><?= htmlspecialchars($item['level_akun'] ?? '1') ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/master-akun/edit/<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <?= icon('pen-to-square', 'me-0 mb-1', 16) ?>
                                            </a>
                                            <a href="/master-akun/delete/<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus akun <?= htmlspecialchars($item['nomor_akun'] ?? '') ?> - <?= htmlspecialchars($item['nama_akun'] ?? '') ?>?', this.href, this); return false;" title="Hapus">
                                                <?= icon('trash-can', 'me-0 mb-1', 16) ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <?php
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    }
                    $page = $currentPage;
                    $totalPages = (int)$totalPages;
                    $perPage = (int)($perPage ?? 10);
                    $search = $search ?? '';
                    $sortBy = $sortBy ?? 'nomor_akun';
                    $sortOrder = $sortOrder ?? 'ASC';
                    $filterKelompok = $filterKelompok ?? '';
                    $filterLevel = $filterLevel ?? '';
                    
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
                                $prevPage = (int)max(1, (int)$page - 1);
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="/master-akun<?php echo buildLink($prevPage, $perPage, $search, $sortBy, $sortOrder, $filterKelompok, $filterLevel); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/master-akun' . buildLink(1, $perPage, $search, $sortBy, $sortOrder, $filterKelompok, $filterLevel) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/master-akun' . buildLink($i, $perPage, $search, $sortBy, $sortOrder, $filterKelompok, $filterLevel) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/master-akun' . buildLink($totalPages, $perPage, $search, $sortBy, $sortOrder, $filterKelompok, $filterLevel) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php
                                $nextPage = $page + 1;
                                if ($nextPage > $totalPages) {
                                    $nextPage = $totalPages;
                                }
                                $nextPage = (int)$nextPage;
                                ?>
                                <a class="page-link" href="/master-akun<?php echo buildLink($nextPage, $perPage, $search, $sortBy, $sortOrder, $filterKelompok, $filterLevel); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    
                    <div class="text-center text-muted mt-2">
                        Menampilkan <?= (((int)$page - 1) * (int)$perPage) + 1 ?> sampai 
                        <?= min((int)$page * (int)$perPage, (int)$total) ?> 
                        dari <?= $total ?> akun
                    </div>
                    <?php elseif (isset($total) && $total > 0): ?>
                    <div class="text-center text-muted mt-3">
                        Total: <?= $total ?> akun
                    </div>
                    <?php endif; ?>
                </div>
            </div>             
        </div>    
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

