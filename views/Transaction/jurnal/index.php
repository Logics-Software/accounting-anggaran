<?php
$title = 'Jurnal';
$config = require __DIR__ . '/../../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $filterTipeJurnal = '', $filterPeriode = '') {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if (!empty($filterTipeJurnal)) $params['filter_tipe_jurnal'] = $filterTipeJurnal;
        if (!empty($filterPeriode)) $params['filter_periode'] = $filterPeriode;
        return '/jurnal?' . http_build_query($params);
    }
}

// Helper function to build link
if (!function_exists('buildLink')) {
    function buildLink($p, $perPage, $search, $sortBy, $sortOrder, $filterTipeJurnal = '', $filterPeriode = '') {
        $params = [
            'page' => $p,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];
        if (!empty($search)) $params['search'] = $search;
        if (!empty($filterTipeJurnal)) $params['filter_tipe_jurnal'] = $filterTipeJurnal;
        if (!empty($filterPeriode)) $params['filter_periode'] = $filterPeriode;
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
                    <li class="breadcrumb-item active">Jurnal</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
				<div class="card-header">
					<div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Jurnal</h4>
						<div class="ms-auto">
							<a href="/jurnal/create" class="btn btn-primary btn-sm">Tambah Jurnal</a>
						</div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/jurnal" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nomor jurnal, no referensi, atau uraian..." value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                                <div class="col-6 col-md-2">
                                    <select name="filter_tipe_jurnal" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Tipe</option>
                                        <?php foreach ($tipeJurnalOptions as $tipe): ?>
                                            <option value="<?= htmlspecialchars($tipe) ?>" <?= ($filterTipeJurnal == $tipe) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipe) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <select name="filter_periode" class="form-select" onchange="this.form.submit()">
                                        <?php 
                                        $bulanNames = [
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ];
                                        foreach ($allPeriode as $periode): 
                                            $bulanName = $bulanNames[$periode['bulan']] ?? '';
                                            $label = $bulanName . ' ' . $periode['tahun'];
                                        ?>
                                            <option value="<?= htmlspecialchars($periode['periode']) ?>" <?= ($filterPeriode == $periode['periode']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
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
                                    <a href="/jurnal?page=1&per_page=10&sort_by=<?= htmlspecialchars($sortBy ?? 'id') ?>&sort_order=<?= htmlspecialchars($sortOrder ?? 'DESC') ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy ?? 'id') ?>">
                            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder ?? 'DESC') ?>">
                        </form>
                    </div>

                    <?php if (!empty($items)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><a href="<?= getSortUrl('id', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">ID</a></th>
                                    <th><a href="<?= getSortUrl('periode', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">Periode</a></th>
                                    <th><a href="<?= getSortUrl('tipejurnal', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">Tipe Jurnal</a></th>
                                    <th><a href="<?= getSortUrl('nojurnal', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">No. Jurnal</a></th>
                                    <th><a href="<?= getSortUrl('tanggaljurnal', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">Tanggal</a></th>
                                    <th>Keterangan</th>
                                    <th><a href="<?= getSortUrl('totaldebet', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">Total Debet</a></th>
                                    <th><a href="<?= getSortUrl('totalkredit', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">Total Kredit</a></th>
                                    <th><a href="<?= getSortUrl('approvement', $sortBy, $sortOrder, $search, $perPage, $filterTipeJurnal, $filterPeriode) ?>" class="text-decoration-none text-dark">Approvement</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td><?= htmlspecialchars($item['periode']) ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($item['tipejurnal']) ?></span></td>
                                    <td><?= htmlspecialchars($item['nojurnal']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($item['tanggaljurnal'])) ?></td>
                                    <td><?= htmlspecialchars($item['keterangan'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format($item['totaldebet'], 2, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($item['totalkredit'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $approvementClass = 'bg-secondary';
                                        if ($item['approvement'] == 'APPROVED') $approvementClass = 'bg-success';
                                        elseif ($item['approvement'] == 'DECLINED') $approvementClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $approvementClass ?>"><?= htmlspecialchars($item['approvement']) ?></span>
                                    </td>
                                    <td>
                                        <a href="/jurnal/edit/<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <?= icon('edit', '', 16) ?>
                                        </a>
                                        <a href="/jurnal/delete/<?= $item['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Apakah Anda yakin ingin menghapus jurnal ini?')">
                                            <?= icon('trash', '', 16) ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildLink($page - 1, $perPage, $search, $sortBy, $sortOrder, $filterTipeJurnal, $filterPeriode) ?>">Previous</a>
                            </li>
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            for ($p = $startPage; $p <= $endPage; $p++):
                            ?>
                                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= buildLink($p, $perPage, $search, $sortBy, $sortOrder, $filterTipeJurnal, $filterPeriode) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildLink($page + 1, $perPage, $search, $sortBy, $sortOrder, $filterTipeJurnal, $filterPeriode) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="text-center text-muted">
                        Menampilkan <?= ($page - 1) * $perPage + 1 ?> - <?= min($page * $perPage, $total) ?> dari <?= $total ?> jurnal
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="alert alert-info">Tidak ada data jurnal.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

