<?php
$title = 'Setting Unit';
$config = require __DIR__ . '/../../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $filterBagian = '') {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = http_build_query([
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'filter_bagian' => $filterBagian,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ]);
        return '/setting-unit?' . $params;
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
                    <li class="breadcrumb-item active">Setting Unit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
				<div class="card-header">
					<div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Unit/Prodi</h4>
						<a href="/setting-unit/create" class="btn btn-primary btn-sm ms-auto">Tambah Unit</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/setting-unit" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nama unit, bagian, jabatan, atau pimpinan..." value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="filter_bagian" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Bagian/Fakultas</option>
                                        <?php if (!empty($bagians)): ?>
                                        <?php foreach ($bagians as $bagian): ?>
                                        <option value="<?= $bagian['id'] ?>" <?= ($filterBagian ?? '') == $bagian['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($bagian['namabagian']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
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
                                    <a href="/setting-unit?page=1&per_page=10&sort_by=<?= htmlspecialchars($sortBy ?? 'id') ?>&sort_order=<?= htmlspecialchars($sortOrder ?? 'ASC') ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy ?? 'id') ?>">
                            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder ?? 'ASC') ?>">
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('namaunit', $sortBy, $sortOrder, $search, $perPage, $filterBagian ?? '') ?>">Nama Unit</a></th>
                                    <th>Bagian</th>
                                    <th>Jabatan Pimpinan</th>
                                    <th>Pimpinan</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('status', $sortBy, $sortOrder, $search, $perPage, $filterBagian ?? '') ?>">Status</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                // Ensure page and perPage are integers and valid
                                $currentPage = isset($page) ? max(1, (int)$page) : 1;
                                $currentPerPage = isset($perPage) ? max(1, (int)$perPage) : 10;
                                $no = ($currentPage - 1) * $currentPerPage + 1;
                                foreach ($items as $item): 
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($item['namaunit'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($item['namabagian'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['jabatan_pimpinan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['nama_pimpinan'] ?? '-') ?></td>
                                    <td align="center">
                                        <span class="badge bg-<?= $item['status'] == 'aktif' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/setting-unit/edit/<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <?= icon('pen-to-square', 'me-0 mb-1', 16) ?>
                                            </a>
                                            <a href="/setting-unit/delete/<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus unit <?= htmlspecialchars($item['namaunit'] ?? '') ?>?', this.href, this); return false;" title="Hapus">
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
                    
                    <?php if ($totalPages > 1): ?>
                    <?php
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
                    if ($currentPage < 1) $currentPage = 1;
                    $page = $currentPage;
                    $totalPages = (int)$totalPages;
                    $perPage = (int)$perPage;
                    
                    $buildLink = function ($p) use ($perPage, $search, $filterBagian, $sortBy, $sortOrder) {
                        return '?page=' . $p . '&per_page=' . $perPage . '&search=' . urlencode($search) . '&filter_bagian=' . urlencode($filterBagian ?? '') . '&sort_by=' . $sortBy . '&sort_order=' . $sortOrder;
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
                                <?php $prevPage = (int)max(1, (int)$page - 1); if ($prevPage < 1) $prevPage = 1; ?>
                                <a class="page-link" href="/setting-unit<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/setting-unit' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/setting-unit' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                echo '<li class="page-item"><a class="page-link" href="/setting-unit' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php $nextPage = $page + 1; if ($nextPage > $totalPages) $nextPage = $totalPages; $nextPage = (int)$nextPage; ?>
                                <a class="page-link" href="/setting-unit<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="text-center text-muted mt-2">
                        Menampilkan <?= (((int)$page - 1) * (int)$perPage) + 1 ?> sampai 
                        <?= min((int)$page * (int)$perPage, (int)$total) ?> 
                        dari <?= $total ?> data
                    </div>
                    <?php elseif (isset($total) && $total > 0): ?>
                    <div class="text-center text-muted mt-3">Total: <?= $total ?> data</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

