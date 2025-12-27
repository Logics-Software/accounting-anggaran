<?php
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
// Fallback to relative path if base_url is not set correctly
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
// Define BASE_URL constant for compatibility
define('BASE_URL', $baseUrl);

// Helper function to display icon
if (!function_exists('icon')) {
    function icon($name, $class = '', $size = 16) {
        $config = require __DIR__ . '/../../config/app.php';
        // Normalize baseUrl - AssetHelper will handle empty/invalid baseUrl
        $baseUrl = rtrim($config['base_url'], '/');
        if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
            $baseUrl = ''; // Let AssetHelper handle root path
        }
        
        // VIEW OPTIMIZATION: Use AssetHelper for icon versioning
        require_once __DIR__ . '/../../core/Helpers/AssetHelper.php';
        $iconPath = \Core\Helpers\AssetHelper::icon($name . '.svg', $baseUrl);
        
        $classes = trim('icon-inline ' . $class);
        $classAttr = ' class="' . htmlspecialchars($classes) . '"';
        return '<img src="' . htmlspecialchars($iconPath) . '" alt="' . htmlspecialchars($name) . '" width="' . $size . '" height="' . $size . '"' . $classAttr . '>';
    }
}

// Get konfigurasi data (logo and app name) if available
$konfigurasiData = null;
$logoPath = $baseUrl . '/assets/images/logo.png'; // Default logo
$appName = $config['app_name'] ?? 'Accounting System';
try {
    $konfigurasiModel = new \Models\Common\Konfigurasi();
    $konfigurasiData = $konfigurasiModel->get();
    if ($konfigurasiData) {
        // Get logo from konfigurasi if available
        if (!empty($konfigurasiData['logo'])) {
            $logoFile = $konfigurasiData['logo'];
            $logoFullPath = __DIR__ . '/../../uploads/' . $logoFile;
            if (file_exists($logoFullPath)) {
                $logoPath = $baseUrl . '/uploads/' . htmlspecialchars($logoFile);
            }
        }
        // Get app name from konfigurasi if available
        if (!empty($konfigurasiData['namaapp'])) {
            $appName = $konfigurasiData['namaapp'];
        }
    }
} catch (Exception $e) {
    // Silently fail if konfigurasi not available, use defaults
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $title ?? $appName ?> - <?= $appName ?></title>
    <?php
    // VIEW OPTIMIZATION: Use AssetHelper for versioning (load once at top)
    require_once __DIR__ . '/../../core/Helpers/AssetHelper.php';
    ?>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($logoPath) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($logoPath) ?>">
    <link rel="icon" type="image/png" sizes="64x64" href="<?= htmlspecialchars($logoPath) ?>">
    <link rel="apple-touch-icon" sizes="128x128" href="<?= htmlspecialchars($logoPath) ?>">
    
    <?php
    // AssetHelper already loaded above
    ?>
    <link href="<?= htmlspecialchars(\Core\Helpers\AssetHelper::css('bootstrap.min.css', $baseUrl)) ?>" rel="stylesheet" type="text/css">
    <link href="<?= htmlspecialchars(\Core\Helpers\AssetHelper::css('style.css', $baseUrl)) ?>" rel="stylesheet" type="text/css">
    
    <?php
    // Load download alerts CSS on pages with file downloads
    $currentPath = $_SERVER['REQUEST_URI'] ?? '';
    $downloadPages = ['/messages/'];
    $needsDownloadCSS = false;
    
    foreach ($downloadPages as $page) {
        if (strpos($currentPath, $page) !== false) {
            $needsDownloadCSS = true;
            break;
        }
    }
    
    if ($needsDownloadCSS) {
        // VIEW OPTIMIZATION: Use AssetHelper for versioning
        echo '<link href="' . htmlspecialchars(\Core\Helpers\AssetHelper::css('download-alerts.css', $baseUrl)) . '" rel="stylesheet" type="text/css">';
    }
    ?>
    <?php if (!empty($additionalStyles)):
        $styles = is_array($additionalStyles) ? $additionalStyles : [$additionalStyles];
        foreach ($styles as $styleHref):
            if (!empty($styleHref)):
    ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($styleHref) ?>">
    <?php
            endif;
        endforeach;
    endif;
    ?>
    <?php if (!empty($additionalInlineStyles)):
        $inlineStyles = is_array($additionalInlineStyles) ? $additionalInlineStyles : [$additionalInlineStyles];
        foreach ($inlineStyles as $inlineStyle):
            if (!empty($inlineStyle)):
    ?>
    <style><?= $inlineStyle ?></style>
    <?php
            endif;
        endforeach;
    endif;
    ?>
</head>
<body class="<?= Auth::check() ? 'has-header' : '' ?>"><?php
// Get user data if logged in
$currentUser = Auth::check() ? Auth::user() : null;
$appConfig = require __DIR__ . '/../../config/app.php';
// Update app name from konfigurasi (already loaded above)
$appConfig['app_name'] = $appName;
$logoAlt = 'Logo';

if (Auth::check() && $currentUser): ?><header class="app-header">
        <nav class="navbar">
            <div class="container-fluid">
                <div class="header-content">
                    <!-- Logo Section -->
                    <div class="header-logo-section" id="headerLogoSection">
                        <a href="/dashboard" class="d-flex align-items-center text-decoration-none">
                            <img src="<?= htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($logoAlt) ?>" class="header-logo">
                        </a>
                        <h1 class="header-app-name"><?= htmlspecialchars($appConfig['app_name']) ?></h1>
                    </div>

                    <!-- Sidebar Toggle Button (Desktop Only) -->
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button" aria-label="Toggle sidebar" title="Collapse/Expand Sidebar">
                        <?= icon('hamburger-menu', '', 24) ?>
                    </button>

                    <!-- Hamburger Menu Button (Mobile Only) -->
                    <button class="hamburger-menu-toggle" type="button" id="hamburgerMenuToggle" aria-label="Toggle sidebar" aria-expanded="false">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>

                    <!-- User Profile Section -->
                    <div class="header-user-profile">
                        <?php
                        $unreadCount = 0;
                        if (Auth::check()) {
                            try {
                                $messageModel = new \Models\Common\MessageModel();
                                $unreadCount = $messageModel->getUnreadCount($currentUser['id']);
                            } catch (Exception $e) {
                                // Silently fail if MessageModel not available
                            }
                        }
                        ?>
                        <!-- Messages Bell Icon with Dropdown -->
                        <div class="header-messages-dropdown" id="headerMessagesDropdown">
                            <button class="header-messages-icon" type="button" id="headerMessagesToggle" title="Pesan Masuk" aria-expanded="false">
                                <?= icon('envelope-light', '', 20) ?>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="badge bg-danger messages-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="messages-dropdown-menu" id="messagesDropdownMenu">
                                <div class="messages-dropdown-header">
                                    <h6 class="mb-0">Pesan Masuk</h6>
                                    <a href="/messages" class="text-decoration-none small">Lihat Semua</a>
                                </div>
                                <div class="messages-dropdown-body">
                                    <?php
                                    $unreadMessages = [];
                                    if ($unreadCount > 0) {
                                        try {
                                            $messageModel = new \Models\Common\MessageModel();
                                            $unreadMessages = $messageModel->getUnreadMessages($currentUser['id'], 10);
                                        } catch (Exception $e) {
                                            // jika error
                                        }
                                    }
                                    
                                    if (empty($unreadMessages)):
                                    ?>
                                        <div class="messages-empty text-center py-3 text-muted">
                                            <small>Tidak ada pesan baru</small>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($unreadMessages as $msg): 
                                            $timeAgo = '';
                                            $createdAt = strtotime($msg['created_at']);
                                            $now = time();
                                            $diff = $now - $createdAt;
                                            
                                            if ($diff < 60) {
                                                $timeAgo = 'Baru saja';
                                            } elseif ($diff < 3600) {
                                                $timeAgo = floor($diff / 60) . ' menit lalu';
                                            } elseif ($diff < 86400) {
                                                $timeAgo = floor($diff / 3600) . ' jam lalu';
                                            } elseif ($diff < 604800) {
                                                $timeAgo = floor($diff / 86400) . ' hari lalu';
                                            } else {
                                                $timeAgo = date('d M Y', $createdAt);
                                            }
                                            
                                            $subject = htmlspecialchars($msg['subject'] ?? 'Tidak ada subjek');
                                            $senderName = htmlspecialchars($msg['sender_name'] ?? 'Unknown');
                                            $contentPreview = strip_tags($msg['content'] ?? '');
                                            $contentPreview = mb_substr($contentPreview, 0, 50);
                                            if (mb_strlen($msg['content'] ?? '') > 50) {
                                                $contentPreview .= '...';
                                            }
                                        ?>
                                            <a href="/messages/show/<?= $msg['id'] ?>" class="message-item">
                                                <div class="message-item-header">
                                                    <span class="message-sender"><?= $senderName ?></span>
                                                    <span class="message-time"><?= $timeAgo ?></span>
                                                </div>
                                                <div class="message-subject"><?= $subject ?></div>
                                                <div class="message-preview text-muted small"><?= htmlspecialchars($contentPreview) ?></div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="user-profile-dropdown" id="userProfileDropdown">
                            <button class="user-profile-toggle" type="button" id="userProfileToggle" aria-expanded="false">
                                <div class="user-avatar">
                                    <?php if (!empty($currentUser['picture'])): ?>
                                        <?php 
                                        $config = require __DIR__ . '/../../config/app.php';
                                        $pictureUrl = $baseUrl . $config['upload_url'] . htmlspecialchars($currentUser['picture']);
                                        $fallbackText = strtoupper(substr($currentUser['username'] ?? 'U', 0, 1));
                                        ?>
                                        <img src="<?= $pictureUrl ?>" alt="<?= htmlspecialchars($currentUser['namalengkap'] ?? $currentUser['username'] ?? 'User') ?>" class="user-avatar-img" data-fallback="<?= htmlspecialchars($fallbackText) ?>" onerror="this.style.display='none'; if(!this.parentElement.querySelector('.avatar-fallback')) { var span = document.createElement('span'); span.className='avatar-fallback'; span.textContent=this.getAttribute('data-fallback'); this.parentElement.appendChild(span); }">
                                    <?php else: ?>
                                        <span class="avatar-fallback"><?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="user-name"><?= htmlspecialchars($currentUser['namalengkap'] ?? $currentUser['username'] ?? 'User') ?></span>
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="ms-1">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="user-dropdown-menu">
                                <div class="dropdown-header mb-2">
                                    <p class="dropdown-user-name"><?= htmlspecialchars($currentUser['namalengkap'] ?? $currentUser['username'] ?? 'User') ?></p>
                                    <?php if (!empty($currentUser['email'])): ?>
                                        <p class="dropdown-user-email"><?= htmlspecialchars($currentUser['email']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="/messages" class="dropdown-item">
                                    <?= icon('envelope', 'me-2', 16) ?> Pesan
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="badge bg-danger ms-auto"><?= $unreadCount ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="/profile" class="dropdown-item"><?= icon('user-gear', 'me-2', 16) ?> Edit Profil</a>
                                <a href="/profile/change-password" class="dropdown-item"><?= icon('key', 'me-2', 16) ?> Ubah Password</a>
                                <a href="/settings" class="dropdown-item"><?= icon('gear', 'me-2', 16) ?> Setting</a>
                                <div class="dropdown-divider"></div>
                                <a href="/logout" class="dropdown-item danger">
                                    <?= icon('logout', 'me-2', 16) ?> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Sidebar -->
    <script>
    (function() {
        if (typeof window !== 'undefined' && window.innerWidth >= 992) {
            try {
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true') {
                    // Create style element immediately for sidebar and header logo section
                    // Use more aggressive selectors to prevent flash
                    const style = document.createElement('style');
                    style.id = 'sidebar-collapsed-inline';
                    style.textContent = '.app-sidebar{width:80px!important}.app-sidebar .sidebar-nav-item span,.app-sidebar .sidebar-nav-group-title{opacity:0!important;width:0!important;max-width:0!important;overflow:hidden!important;margin:0!important;padding:0!important}.app-sidebar .sidebar-nav-item{justify-content:center!important;padding:0.75rem!important}.app-sidebar .sidebar-nav-item img{margin:0!important}.app-sidebar .sidebar-toggle-btn{transform:rotate(180deg)!important}.app-sidebar .sidebar-nav-group{margin-top:0.5rem!important}.app-sidebar .sidebar-header{padding:1rem!important;justify-content:center!important}.app-sidebar~.main-content-wrapper{margin-left:80px!important}#headerLogoSection,.header-logo-section{width:80px!important;padding:0 1rem!important;justify-content:center!important;gap:0!important;transition:none!important}#headerLogoSection .header-app-name,.header-logo-section .header-app-name{opacity:0!important;width:0!important;max-width:0!important;overflow:hidden!important;margin:0!important;padding:0!important;display:none!important;visibility:hidden!important}#headerLogoSection>a,.header-logo-section>a{margin:0 auto!important;display:flex!important;justify-content:center!important;align-items:center!important;width:100%!important}#headerLogoSection .header-logo,.header-logo-section .header-logo{margin:0 auto!important;display:block!important}.header-content{margin-left:80px!important}';
                    // Insert at the very beginning of head to ensure it loads first
                    const firstStyle = document.head.querySelector('style');
                    if (firstStyle) {
                        document.head.insertBefore(style, firstStyle);
                    } else {
                        document.head.appendChild(style);
                    }
                    
                    // Also add collapsed class immediately to prevent re-render
                    // Use MutationObserver to watch for header logo section creation
                    const observer = new MutationObserver(function(mutations) {
                        const headerLogoSection = document.getElementById('headerLogoSection');
                        if (headerLogoSection && !headerLogoSection.classList.contains('collapsed')) {
                            headerLogoSection.classList.add('collapsed');
                            observer.disconnect();
                        }
                    });
                    observer.observe(document.body, { childList: true, subtree: true });
                    
                    // Fallback: try to apply immediately if element already exists
                    setTimeout(function() {
                        const headerLogoSection = document.getElementById('headerLogoSection');
                        if (headerLogoSection && !headerLogoSection.classList.contains('collapsed')) {
                            headerLogoSection.classList.add('collapsed');
                        }
                    }, 0);
                }
            } catch(e) {
                console.warn('Failed to load sidebar state:', e);
            }
        }
    })();
    </script>
    <aside class="app-sidebar" id="appSidebar">
        <nav class="sidebar-nav" id="sidebarNav">
            <a href="/dashboard" class="sidebar-nav-item <?= ($_SERVER['REQUEST_URI'] ?? '') === '/dashboard' ? 'active' : '' ?>">
                <?= icon('house', 'me-2', 20) ?>
                <span>Dashboard</span>
            </a>
            
            <div class="dropdown-divider"></div>
            
            <!-- Menu Setting -->
            <?php if (Auth::isAdmin()): ?>
            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Setting</div>

                <!-- Menu Setting Konfigurasi -->
                <a href="/konfigurasi" class="sidebar-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/konfigurasi') !== false ? 'active' : '' ?>">
                    <?= icon('gear', 'me-2', 20) ?>
                    <span>Konfigurasi</span>
                </a>
                                
                <a href="/users" class="sidebar-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/users') !== false ? 'active' : '' ?>">
                    <?= icon('users', 'me-2', 20) ?>
                    <span>Manajemen User</span>
                </a>
                
                <!-- Menu Master Log Dropdown -->
                <?php
                $currentPath = $_SERVER['REQUEST_URI'] ?? '';
                $isLoginLog = strpos($currentPath, '/login-logs') !== false;
                $isBackupLog = strpos($currentPath, '/backup-log') !== false;
                $isAnyLogActive = $isLoginLog || $isBackupLog;
                ?>
                <div class="sidebar-nav-dropdown <?= $isAnyLogActive ? 'open' : '' ?>" data-dropdown="daftar-log">
                    <a href="#" class="sidebar-nav-item sidebar-nav-dropdown-toggle <?= $isAnyLogActive ? 'active' : '' ?>" id="daftarLogDropdownToggle">
                        <?= icon('clock-rotate-left', 'me-2', 20) ?>
                        <span>Daftar Log</span>
                        <?= icon('caret-right', 'ms-auto dropdown-arrow', 16) ?>
                    </a>
                    <div class="sidebar-nav-dropdown-menu" id="daftarLogDropdownMenu">
                        <?php
                        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
                        $isLoginLog = strpos($currentPath, '/login-logs') !== false;
                        $isBackupLog = strpos($currentPath, '/backup-log') !== false;
                        ?>
                        <a href="/login-logs" class="sidebar-nav-dropdown-item <?= $isLoginLog ? 'active' : '' ?>">
                            <span>Login Log</span>
                        </a>
                        <a href="/backup-log" class="sidebar-nav-dropdown-item <?= $isBackupLog ? 'active' : '' ?>">
                            <span>Backup Log</span>
                        </a>
                    </div>
                    <div class="sidebar-nav-dropdown-popup" id="daftarLogDropdownPopup">
                        <a href="/login-logs" class="sidebar-nav-dropdown-popup-item <?= $isLoginLog ? 'active' : '' ?>">
                            <span>Login Log</span>
                        </a>
                        <a href="/backup-log" class="sidebar-nav-dropdown-popup-item <?= $isBackupLog ? 'active' : '' ?>">
                            <span>Backup Log</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Menu System -->
            <?php if (Auth::isAdmin()): ?>
            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">System</div>

                <!-- Menu Backup Database -->
                <a href="/backup" class="sidebar-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/backup') !== false ? 'active' : '' ?>">
                    <?= icon('database', 'me-2', 20) ?>
                    <span>Backup Database</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Menu Master -->
            <?php if (Auth::isAdmin() || Auth::isManajemen()): ?>
            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Master</div>
                <!-- Menu Setting Jurnal -->
                <a href="/setting-jurnal" class="sidebar-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/setting-jurnal') !== false ? 'active' : '' ?>">
                    <?= icon('file-invoice', 'me-2', 20) ?>
                    <span>Setting Jurnal</span>
                </a>
                <!-- Menu Setting Pemakai Anggaran Dropdown -->
                <?php
                $currentPath = $_SERVER['REQUEST_URI'] ?? '';
                $isSettingBidang = strpos($currentPath, '/setting-bidang') !== false;
                $isSettingBagian = strpos($currentPath, '/setting-bagian') !== false;
                $isSettingUnit = strpos($currentPath, '/setting-unit') !== false;
                $isAnySettingActive = $isSettingBidang || $isSettingBagian || $isSettingUnit;
                ?>
                <div class="sidebar-nav-dropdown <?= $isAnySettingActive ? 'open' : '' ?>" data-dropdown="setting-anggaran">
                    <a href="#" class="sidebar-nav-item sidebar-nav-dropdown-toggle <?= $isAnySettingActive ? 'active' : '' ?>" id="settingAnggaranDropdownToggle">
                        <?= icon('layer-group', 'me-2', 20) ?>
                        <span>Pemakai Anggaran</span>
                        <?= icon('caret-right', 'ms-auto dropdown-arrow', 16) ?>
                    </a>
                    <div class="sidebar-nav-dropdown-menu" id="settingAnggaranDropdownMenu">
                        <?php
                        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
                        $isSettingBidang = strpos($currentPath, '/setting-bidang') !== false;
                        $isSettingBagian = strpos($currentPath, '/setting-bagian') !== false;
                        $isSettingUnit = strpos($currentPath, '/setting-unit') !== false;
                        ?>
                        <a href="/setting-bidang" class="sidebar-nav-dropdown-item <?= $isSettingBidang ? 'active' : '' ?>">
                            <span>Bidang</span>
                        </a>
                        <a href="/setting-bagian" class="sidebar-nav-dropdown-item <?= $isSettingBagian ? 'active' : '' ?>">
                            <span>Bagian/Fakultas</span>
                        </a>
                        <a href="/setting-unit" class="sidebar-nav-dropdown-item <?= $isSettingUnit ? 'active' : '' ?>">
                            <span>Unit/Prodi</span>
                        </a>
                    </div>
                    <div class="sidebar-nav-dropdown-popup" id="settingAnggaranDropdownPopup">
                        <a href="/setting-bidang" class="sidebar-nav-dropdown-popup-item <?= $isSettingBidang ? 'active' : '' ?>">
                            <span>Bidang</span>
                        </a>
                        <a href="/setting-bagian" class="sidebar-nav-dropdown-popup-item <?= $isSettingBagian ? 'active' : '' ?>">
                            <span>Bagian/Fakultas</span>
                        </a>
                        <a href="/setting-unit" class="sidebar-nav-dropdown-popup-item <?= $isSettingUnit ? 'active' : '' ?>">
                            <span>Unit/Prodi</span>
                        </a>
                    </div>
                </div>
                <!-- Menu Master Akun -->
                <a href="/master-akun" class="sidebar-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/master-akun') !== false ? 'active' : '' ?>">
                    <?= icon('at', 'me-2', 20) ?>
                    <span>Master Akun</span>
                </a>
                
                <!-- Menu Buka Periode Akuntansi -->
                <a href="/periode-akuntansi/buka-periode" class="sidebar-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/periode-akuntansi/buka-periode') !== false ? 'active' : '' ?>">
                    <?= icon('calendar-check', 'me-2', 20) ?>
                    <span>Buka Periode Akuntansi</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Menu Transaksi -->
            <?php if (Auth::isAdmin() || Auth::isManajemen()): ?>
            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Transaksi</div>

                <!-- Menu Jurnal -->
                <a href="/jurnal" class="sidebar-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/jurnal') !== false ? 'active' : '' ?>">
                    <?= icon('file-invoice', 'me-2', 20) ?>
                    <span>Jurnal</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Menu Laporan -->
            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Laporan</div>

                <!-- Menu Laporan Dropdown -->
                <?php
                $currentPath = $_SERVER['REQUEST_URI'] ?? '';
                $isLaporanBukuBesar = strpos($currentPath, '/laporan/buku-besar') !== false;
                $isLaporanLabaRugi = strpos($currentPath, '/laporan/laba-rugi') !== false;
                $isLaporanNeraca = strpos($currentPath, '/laporan/neraca') !== false;
                $isAnyLaporanActive = $isLaporanBukuBesar || $isLaporanLabaRugi || $isLaporanNeraca;
                ?>
                <div class="sidebar-nav-dropdown <?= $isAnyLaporanActive ? 'open' : '' ?>" data-dropdown="laporan">
                    <a href="#" class="sidebar-nav-item sidebar-nav-dropdown-toggle <?= $isAnyLaporanActive ? 'active' : '' ?>" id="laporanDropdownToggle">
                        <?= icon('print-menu', 'me-2', 20) ?>
                        <span>Laporan</span>
                        <?= icon('caret-right', 'ms-auto dropdown-arrow', 16) ?>
                    </a>
                    <div class="sidebar-nav-dropdown-menu" id="laporanDropdownMenu">
                        <?php
                        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
                        $isLaporanBukuBesar = strpos($currentPath, '/laporan/buku-besar') !== false;
                        $isLaporanLabaRugi = strpos($currentPath, '/laporan/laba-rugi') !== false;
                        $isLaporanNeraca = strpos($currentPath, '/laporan/neraca') !== false;
                        $isAnyLaporanActive = $isLaporanBukuBesar || $isLaporanLabaRugi || $isLaporanNeraca;
                        ?>
                        <a href="#" class="sidebar-nav-dropdown-item <?= $isLaporanBukuBesar ? 'active' : '' ?>">
                            <span>Laporan Buku Besar</span>
                        </a>
                        <a href="#" class="sidebar-nav-dropdown-item <?= $isLaporanLabaRugi ? 'active' : '' ?>">
                            <span>Laporan Laba/Rugi</span>
                        </a>
                        <a href="#" class="sidebar-nav-dropdown-item <?= $isLaporanNeraca ? 'active' : '' ?>">
                            <span>Laporan Neraca Keuangan</span>
                        </a>
                    </div>
                    <!-- Popup submenu for collapsed sidebar -->
                    <div class="sidebar-nav-dropdown-popup" id="laporanDropdownPopup">
                        <a href="#" class="sidebar-nav-dropdown-popup-item <?= $isLaporanBukuBesar ? 'active' : '' ?>">
                            <span>Laporan Buku Besar</span>
                        </a>
                        <a href="#" class="sidebar-nav-dropdown-popup-item <?= $isLaporanLabaRugi ? 'active' : '' ?>">
                            <span>Laporan Laba/Rugi</span>
                        </a>
                        <a href="#" class="sidebar-nav-dropdown-popup-item <?= $isLaporanNeraca ? 'active' : '' ?>">
                            <span>Laporan Neraca Keuangan</span>
                        </a>
                    </div>
                </div>
            </div>

        </nav>
    </aside>
    
    <!-- Sidebar Overlay (Mobile Only) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Main Content Wrapper -->
    <div class="main-content-wrapper" id="mainContentWrapper">
<?php endif; ?>

<?php if (Auth::check() && $currentUser): ?>
    <script>
    // Toggle messages dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const messagesDropdown = document.getElementById('headerMessagesDropdown');
        const messagesToggle = document.getElementById('headerMessagesToggle');
        
        if (messagesToggle) {
            messagesToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                messagesDropdown.classList.toggle('show');
                // Close profile dropdown if open
                const profileDropdown = document.getElementById('userProfileDropdown');
                if (profileDropdown) {
                    profileDropdown.classList.remove('show');
                }
            });
        }
        
        // Toggle user profile dropdown
        const dropdown = document.getElementById('userProfileDropdown');
        const toggle = document.getElementById('userProfileToggle');
        
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('show');
                // Close messages dropdown if open
                if (messagesDropdown) {
                    messagesDropdown.classList.remove('show');
                }
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
            if (messagesDropdown && !messagesDropdown.contains(e.target)) {
                messagesDropdown.classList.remove('show');
            }
        });

        // Sidebar dropdown toggle with popup support
        function initSidebarDropdowns() {
            const sidebar = document.getElementById('appSidebar');
            const dropdowns = document.querySelectorAll('.sidebar-nav-dropdown');
            
            // Helper function to check if sidebar is collapsed
            function isSidebarCollapsed() {
                if (!sidebar) return false;
                // Check both class and width
                const hasCollapsedClass = sidebar.classList.contains('collapsed');
                const sidebarWidth = sidebar.offsetWidth || sidebar.clientWidth;
                const isCollapsed = hasCollapsedClass || sidebarWidth <= 80;
                return isCollapsed;
            }
            
            dropdowns.forEach(function(dropdown) {
                const toggle = dropdown.querySelector('.sidebar-nav-dropdown-toggle');
                let popup = dropdown.querySelector('.sidebar-nav-dropdown-popup');
                
                if (!toggle) return;
                
                // If popup is not in dropdown, try to find it in body and move it back
                if (!popup) {
                    // Try to find popup by ID pattern (e.g., laporanDropdownPopup)
                    const dropdownId = dropdown.getAttribute('data-dropdown');
                    if (dropdownId) {
                        const popupId = dropdownId.charAt(0).toUpperCase() + dropdownId.slice(1) + 'DropdownPopup';
                        popup = document.getElementById(popupId);
                        if (popup && popup.parentElement === document.body) {
                            dropdown.appendChild(popup);
                        }
                    }
                }
                
                // If still no popup, try to find by checking all popups in body
                if (!popup) {
                    document.querySelectorAll('.sidebar-nav-dropdown-popup').forEach(function(p) {
                        if (p.parentElement === document.body) {
                            // Try to match by checking if popup ID matches dropdown
                            const pId = p.id || '';
                            const dId = dropdown.id || dropdown.getAttribute('data-dropdown') || '';
                            if (pId.includes(dId) || dId.includes(pId.replace('Popup', '').replace('Dropdown', ''))) {
                                dropdown.appendChild(p);
                                popup = p;
                            }
                        }
                    });
                }
                
                if (!popup) return;
                
                // Store reference to dropdown in popup for easy retrieval later
                if (!popup.dataset.dropdownRef) {
                    popup.dataset.dropdownRef = dropdown.getAttribute('data-dropdown') || dropdown.id || 'dropdown-' + Math.random().toString(36).substr(2, 9);
                }
                
                // Store reference to popup in dropdown for easy access
                dropdown._popup = popup;
                
                // Click handler - only for expanded sidebar (normal dropdown)
                toggle.addEventListener('click', function(e) {
                    const isCollapsed = isSidebarCollapsed();
                    const isDesktop = window.innerWidth >= 992;
                    
                    // If sidebar is collapsed, prevent default click behavior
                    if (isCollapsed && isDesktop) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Normal dropdown toggle for expanded sidebar
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // CRITICAL: Always ensure popup is in correct position first (before toggling)
                    // This fixes the issue where popup might still be in body from collapsed state
                    if (popup) {
                        // ALWAYS move popup back to dropdown if it's not already there
                        // Don't just check for body - check for ANY parent that's not the dropdown
                        if (popup.parentElement !== dropdown) {
                            dropdown.appendChild(popup);
                        }
                        // Reset all popup styles to ensure clean state
                        popup.classList.remove('show');
                        popup.style.display = 'none';
                        popup.style.top = '';
                        popup.style.left = '';
                        popup.style.zIndex = '';
                    }
                    
                    // Close all other dropdowns first
                    document.querySelectorAll('.sidebar-nav-dropdown.open').forEach(function(otherDropdown) {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.classList.remove('open');
                        }
                    });
                    
                    // Toggle current dropdown
                    dropdown.classList.toggle('open');
                    
                    // Force reflow to ensure CSS transition works
                    const menu = dropdown.querySelector('.sidebar-nav-dropdown-menu');
                    if (menu) {
                        // Force browser to recalculate styles immediately
                        void menu.offsetHeight;
                        
                        // CRITICAL: If dropdown is open, always ensure menu is visible
                        // This is a fallback in case CSS doesn't apply correctly
                        if (dropdown.classList.contains('open')) {
                            // Use setTimeout to check after CSS transition
                            setTimeout(function() {
                                const computedStyle = window.getComputedStyle(menu);
                                if (computedStyle.maxHeight === '0px' || 
                                    computedStyle.maxHeight === '0' || 
                                    computedStyle.opacity === '0' ||
                                    computedStyle.visibility === 'hidden') {
                                    // Force apply styles directly via inline style
                                    menu.style.setProperty('max-height', '1000px', 'important');
                                    menu.style.setProperty('opacity', '1', 'important');
                                    menu.style.setProperty('visibility', 'visible', 'important');
                                    menu.style.setProperty('overflow', 'visible', 'important');
                                    menu.style.setProperty('display', 'block', 'important');
                                }
                            }, 10);
                        } else {
                            // If closing, remove inline styles to let CSS handle it
                            menu.style.removeProperty('max-height');
                            menu.style.removeProperty('opacity');
                            menu.style.removeProperty('visibility');
                            menu.style.removeProperty('overflow');
                            menu.style.removeProperty('display');
                        }
                    }
                });
                
                // Hover support for collapsed sidebar (desktop only) - PRIMARY METHOD
                let hoverTimeout;
                let isHovering = false;
                
                dropdown.addEventListener('mouseenter', function() {
                    const isCollapsed = isSidebarCollapsed();
                    const isDesktop = window.innerWidth >= 992;
                    
                    // Only handle hover for collapsed sidebar, exit early if expanded
                    if (!isCollapsed || !isDesktop) {
                        return;
                    }
                    
                    if (isCollapsed && isDesktop) {
                        clearTimeout(hoverTimeout);
                        isHovering = true;
                        
                        // Close all other popups first
                        document.querySelectorAll('.sidebar-nav-dropdown-popup.show').forEach(p => {
                            if (p !== popup) {
                                p.classList.remove('show');
                                p.style.display = 'none';
                                // Move other popups back to their dropdowns
                                const otherDropdown = p.closest('.sidebar-nav-dropdown');
                                if (otherDropdown && p.parentElement === document.body) {
                                    otherDropdown.appendChild(p);
                                }
                            }
                        });
                        
                        // Move popup to body to avoid overflow clipping
                        if (popup.parentElement !== document.body) {
                            document.body.appendChild(popup);
                        }
                        
                        // Position and show popup immediately (no delay for hover)
                        const rect = toggle.getBoundingClientRect();
                        popup.style.top = rect.top + 'px';
                        popup.style.left = '80px';
                        popup.style.zIndex = '1200';
                        popup.style.display = 'block';
                        // Force reflow to ensure display is applied
                        void popup.offsetHeight;
                        popup.classList.add('show');
                    }
                });
                
                dropdown.addEventListener('mouseleave', function(e) {
                    const isCollapsed = isSidebarCollapsed();
                    const isDesktop = window.innerWidth >= 992;
                    
                    if (isCollapsed && isDesktop) {
                        isHovering = false;
                        clearTimeout(hoverTimeout);
                        const relatedTarget = e.relatedTarget;
                        
                        // Don't close if moving to popup
                        if (!popup.contains(relatedTarget)) {
                            hoverTimeout = setTimeout(() => {
                                if (!isHovering) {
                                    popup.classList.remove('show');
                                    popup.style.display = 'none';
                                    // Move popup back to dropdown
                                    if (popup.parentElement !== dropdown && popup.parentElement === document.body) {
                                        dropdown.appendChild(popup);
                                    }
                                }
                            }, 100);
                        }
                    }
                });
                
                // Keep popup open when hovering over it
                popup.addEventListener('mouseenter', function() {
                    clearTimeout(hoverTimeout);
                    isHovering = true;
                });
                
                popup.addEventListener('mouseleave', function() {
                    isHovering = false;
                    popup.classList.remove('show');
                    popup.style.display = 'none';
                    // Move popup back to dropdown
                    if (popup.parentElement !== dropdown && popup.parentElement === document.body) {
                        dropdown.appendChild(popup);
                    }
                });
            });
            
            // Close dropdowns/popups when clicking outside
            document.addEventListener('click', function(e) {
                const clickedInside = Array.from(dropdowns).some(dropdown => {
                    // Check if clicked inside dropdown
                    if (dropdown.contains(e.target)) return true;
                    
                    // Check if clicked inside popup (might be in body)
                    const popup = dropdown.querySelector('.sidebar-nav-dropdown-popup');
                    if (popup && popup.contains(e.target)) return true;
                    
                    // Also check if popup is in body
                    const popupId = popup?.id;
                    if (popupId) {
                        const popupInBody = document.getElementById(popupId);
                        if (popupInBody && popupInBody.contains(e.target)) return true;
                    }
                    
                    return false;
                });
                
                if (!clickedInside) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('open');
                        // Find popup - might be in dropdown or body
                        let popup = dropdown.querySelector('.sidebar-nav-dropdown-popup');
                        if (!popup) {
                            // Try to find by ID in body
                            const popupId = dropdown.getAttribute('data-popup-id') || 
                                          (dropdown.querySelector('.sidebar-nav-dropdown-popup')?.id);
                            if (popupId) {
                                popup = document.getElementById(popupId);
                            }
                        }
                        
                        if (popup) {
                            popup.classList.remove('show');
                            popup.style.display = 'none';
                            // Move popup back to dropdown if it was moved to body
                            if (popup.parentElement !== dropdown && popup.parentElement === document.body) {
                                dropdown.appendChild(popup);
                            }
                        }
                    });
                }
            });
        }
        
        // Function to update title attributes for sidebar items
        function updateSidebarItemTitles() {
            const sidebar = document.getElementById('appSidebar');
            if (!sidebar) return;
            
            const isCollapsed = sidebar.classList.contains('collapsed') || sidebar.offsetWidth <= 80;
            
            // Get all sidebar nav items (excluding dropdown toggles)
            const navItems = document.querySelectorAll('.sidebar-nav-item:not(.sidebar-nav-dropdown-toggle)');
            
            navItems.forEach(function(item) {
                const span = item.querySelector('span');
                if (span) {
                    const text = span.textContent.trim();
                    if (isCollapsed && text) {
                        // Add title when collapsed
                        item.setAttribute('title', text);
                    } else {
                        // Remove title when expanded
                        item.removeAttribute('title');
                    }
                }
            });
        }
        
        // Initialize dropdowns after DOM is ready
        // Use multiple initialization strategies to ensure it runs
        function runInit() {
            try {
                initSidebarDropdowns();
                // Update title attributes on initial load
                updateSidebarItemTitles();
            } catch(e) {
                console.error('Error initializing sidebar dropdowns:', e);
            }
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(runInit, 100);
            });
        } else {
            setTimeout(runInit, 100);
        }
        
        // Also try to initialize after a short delay as fallback
        setTimeout(runInit, 500);
        
        // Sidebar collapse/expand toggle (Desktop)
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebar = document.getElementById('appSidebar');
        
        // Apply collapsed class if state is saved (for consistency with inline styles)
        // The inline style above already handles the visual state, this ensures class is in sync
        if (sidebar && window.innerWidth >= 992) {
            const savedSidebarState = localStorage.getItem('sidebarCollapsed');
            const inlineStyle = document.getElementById('sidebar-collapsed-inline');
            
            const headerLogoSection = document.querySelector('.header-logo-section');
            
            if (savedSidebarState === 'true') {
                if (!sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }
                // Sync header logo section state
                if (headerLogoSection && !headerLogoSection.classList.contains('collapsed')) {
                    headerLogoSection.classList.add('collapsed');
                }
                // Remove inline style after a short delay to let CSS handle it (smoother transition)
                if (inlineStyle) {
                    setTimeout(function() {
                        inlineStyle.remove();
                        // Update title attributes after style is removed
                        updateSidebarItemTitles();
                    }, 100);
                } else {
                    // Update title attributes immediately if no inline style
                    setTimeout(function() {
                        updateSidebarItemTitles();
                    }, 100);
                }
            } else {
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                }
                // Sync header logo section state
                if (headerLogoSection && headerLogoSection.classList.contains('collapsed')) {
                    headerLogoSection.classList.remove('collapsed');
                }
                if (inlineStyle) {
                    inlineStyle.remove();
                }
                // Update title attributes when expanded
                setTimeout(function() {
                    updateSidebarItemTitles();
                }, 100);
            }
        }
        
        // Function to update title attributes for sidebar items
        function updateSidebarItemTitles() {
            const sidebar = document.getElementById('appSidebar');
            if (!sidebar) return;
            
            const isCollapsed = sidebar.classList.contains('collapsed') || sidebar.offsetWidth <= 80;
            
            // Get all sidebar nav items (including dropdown toggles)
            const navItems = document.querySelectorAll('.sidebar-nav-item');
            
            navItems.forEach(function(item) {
                const span = item.querySelector('span');
                if (span) {
                    const text = span.textContent.trim();
                    if (isCollapsed && text) {
                        // Add title when collapsed
                        item.setAttribute('title', text);
                    } else {
                        // Remove title when expanded
                        item.removeAttribute('title');
                    }
                }
            });
        }
        
        if (sidebarToggleBtn && sidebar) {
            sidebarToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                // Only toggle collapse on desktop
                if (window.innerWidth >= 992) {
                    const wasCollapsed = sidebar.classList.contains('collapsed');
                    sidebar.classList.toggle('collapsed');
                    const isNowCollapsed = sidebar.classList.contains('collapsed');
                    
                    // Update title attributes after toggle
                    setTimeout(function() {
                        updateSidebarItemTitles();
                    }, 100);
                    
                    // Close all dropdowns when sidebar is collapsed
                    if (isNowCollapsed) {
                        document.querySelectorAll('.sidebar-nav-dropdown.open').forEach(dropdown => {
                            dropdown.classList.remove('open');
                        });
                        // Close all popups
                        document.querySelectorAll('.sidebar-nav-dropdown-popup.show').forEach(popup => {
                            popup.classList.remove('show');
                            popup.style.display = 'none';
                        });
                    } else {
                        // When expanding sidebar, ensure all popups are moved back to their dropdowns
                        document.querySelectorAll('.sidebar-nav-dropdown-popup').forEach(popup => {
                            popup.classList.remove('show');
                            popup.style.display = 'none';
                            
                            // Find the correct dropdown for this popup
                            let targetDropdown = null;
                            
                            // Method 1: Use stored reference
                            if (popup.dataset.dropdownRef) {
                                targetDropdown = document.querySelector(`[data-dropdown="${popup.dataset.dropdownRef}"]`);
                            }
                            
                            // Method 2: Try to find by ID (e.g., laporanDropdownPopup -> laporanDropdownMenu)
                            if (!targetDropdown && popup.id) {
                                const menuId = popup.id.replace('Popup', 'Menu');
                                const menu = document.getElementById(menuId);
                                if (menu) {
                                    targetDropdown = menu.closest('.sidebar-nav-dropdown');
                                }
                            }
                            
                            // Method 3: Find by matching all dropdowns and check if they have this popup
                            if (!targetDropdown) {
                                document.querySelectorAll('.sidebar-nav-dropdown').forEach(function(dd) {
                                    const ddPopup = dd.querySelector('.sidebar-nav-dropdown-popup');
                                    if (ddPopup === popup) {
                                        targetDropdown = dd;
                                    }
                                });
                            }
                            
                            // CRITICAL: Always move popup back to its dropdown
                            // Don't just check for body - move it regardless of current parent
                            if (targetDropdown) {
                                if (popup.parentElement !== targetDropdown) {
                                    targetDropdown.appendChild(popup);
                                }
                                // Reset all popup styles
                                popup.style.top = '';
                                popup.style.left = '';
                                popup.style.zIndex = '';
                            } else {
                                // Fallback: if we can't find target, move to first dropdown
                                const firstDropdown = document.querySelector('.sidebar-nav-dropdown');
                                if (firstDropdown && popup.parentElement !== firstDropdown) {
                                    firstDropdown.appendChild(popup);
                                }
                            }
                        });
                        // Close all open dropdowns to reset state
                        document.querySelectorAll('.sidebar-nav-dropdown.open').forEach(dropdown => {
                            dropdown.classList.remove('open');
                        });
                    }
                    // Sync header logo section state
                    const headerLogoSection = document.querySelector('.header-logo-section');
                    if (headerLogoSection) {
                        if (sidebar.classList.contains('collapsed')) {
                            headerLogoSection.classList.add('collapsed');
                        } else {
                            headerLogoSection.classList.remove('collapsed');
                        }
                    }
                    // Save state to localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? 'true' : 'false');
                    
                    // Update title attributes after state change
                    setTimeout(function() {
                        updateSidebarItemTitles();
                    }, 100);
                }
            });
        }
        
        // Sidebar toggle (Mobile)
        const hamburgerToggle = document.getElementById('hamburgerMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar() {
            if (sidebar && sidebarOverlay) {
                const isExpanded = hamburgerToggle.getAttribute('aria-expanded') === 'true';
                hamburgerToggle.setAttribute('aria-expanded', !isExpanded);
                hamburgerToggle.classList.toggle('active');
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
                document.body.classList.toggle('sidebar-open');
            }
        }
        
        function closeSidebar() {
            if (sidebar && sidebarOverlay) {
                    hamburgerToggle.setAttribute('aria-expanded', 'false');
                    hamburgerToggle.classList.remove('active');
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        }
        
        if (hamburgerToggle && sidebar) {
            hamburgerToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
            
            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }
            
            // Close sidebar when clicking on a nav link (mobile)
            const sidebarNavLinks = sidebar.querySelectorAll('.sidebar-nav-item');
            sidebarNavLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    // Only close on mobile
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
        }
        
        // Handle window resize - remove collapsed state on mobile
        window.addEventListener('resize', function() {
            if (sidebar) {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('collapsed');
                } else {
                    // Restore saved state on desktop
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                    }
                }
            }
        });

        // Handle dropdown menus
        const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.closest('.nav-dropdown');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                // Close all other dropdowns
                dropdownToggles.forEach(function(otherToggle) {
                    if (otherToggle !== toggle) {
                        otherToggle.setAttribute('aria-expanded', 'false');
                        otherToggle.closest('.nav-dropdown').classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                this.setAttribute('aria-expanded', !isExpanded);
                dropdown.classList.toggle('show', !isExpanded);
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-dropdown')) {
                dropdownToggles.forEach(function(toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.closest('.nav-dropdown').classList.remove('show');
                });
            }
        });

        // Inject mobile back buttons into card headers on small screens
        function setupMobileBackButtons() {
            var isSmall = window.matchMedia('(max-width: 575.98px)').matches;
            if (!isSmall) return;

            var headers = document.querySelectorAll('.card .card-header');
            headers.forEach(function(header) {
                if (header.querySelector('.mobile-back-btn')) return;

                var container = header.querySelector('.d-flex') || header;
                var title = header.querySelector('h4, h3, h2, .card-title');
                if (!title) return;

                // Resolve base URL from PHP for asset path
                var baseUrl = <?= json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mobile-back-btn';
                btn.setAttribute('aria-label', 'Kembali');
                btn.innerHTML = '<img src="' + baseUrl + '/assets/icons/arrow-left.svg" alt="Kembali" width="20" height="20" class="icon-inline">';
                btn.addEventListener('click', function() {
                    // Priority 1: Check for custom back URL from card header data attribute
                    var customBackUrl = header.getAttribute('data-back-url');
                    if (customBackUrl) {
                        window.location.href = customBackUrl;
                        return;
                    }
                    
                    // Priority 2: Check for breadcrumb parent URL
                    var breadcrumbNav = document.querySelector('nav[aria-label="breadcrumb"][data-breadcrumb-parent]');
                    if (breadcrumbNav) {
                        var breadcrumbParent = breadcrumbNav.getAttribute('data-breadcrumb-parent');
                        if (breadcrumbParent) {
                            window.location.href = breadcrumbParent;
                            return;
                        }
                    }
                    
                    // Priority 3: Try to get parent from breadcrumb links (second to last link)
                    var breadcrumbLinks = document.querySelectorAll('nav[aria-label="breadcrumb"] .breadcrumb-item:not(.active) a');
                    if (breadcrumbLinks.length > 0) {
                        // Get the last non-active breadcrumb link (parent page)
                        var parentLink = breadcrumbLinks[breadcrumbLinks.length - 1];
                        if (parentLink && parentLink.href) {
                            window.location.href = parentLink.href;
                            return;
                        }
                    }
                    
                    // Fallback to history.back() or dashboard
                    if (document.referrer && document.referrer !== window.location.href) {
                        history.back();
                    } else {
                        window.location.href = "/dashboard";
                    }
                });

                container.insertBefore(btn, title);
            });
        }

        setupMobileBackButtons();
        window.addEventListener('resize', function() {
            // Re-run to add buttons if layout changes to small
            setupMobileBackButtons();
        });
    });
    </script>
<?php endif; ?>
<?php require __DIR__ . '/../partials/alerts.php'; ?>

