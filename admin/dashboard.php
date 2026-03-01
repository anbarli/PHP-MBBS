<?php
/**
 * Admin Dashboard
 * Yönetim paneli ana sayfası
 */

// UTF-8 encoding ayarla
header('Content-Type: text/html; charset=utf-8');

define('ADMIN_SECURE', true);

// Ana config dosyasını dahil et
require_once '../config.php';

// Güvenlik fonksiyonlarını dahil et
require_once '../includes/security.php';

// Session başlat ve yetki kontrolü
initSecureSession();
requireAdminAuth();
$csrfToken = generateCSRFToken();

// Admin config yükle
$adminConfig = loadAdminConfig();

// Tüm yazıları al
$postFilesWithDates = getCachedPosts();

// Cache boşsa veya null ise doğrudan dosyalardan oku
if ($postFilesWithDates === null || empty($postFilesWithDates)) {
    $postFilesWithDates = [];
    $postFiles = array_diff(scandir(POSTS_DIR), array('..', '.'));

    foreach ($postFiles as $post) {
        if (pathinfo($post, PATHINFO_EXTENSION) === 'md') {
            $postFile = POSTS_DIR . $post;
            $lastModified = filemtime($postFile);
            $postFilesWithDates[] = [
                'file' => $postFile,
                'slug' => pathinfo($post, PATHINFO_FILENAME),
                'lastModified' => $lastModified
            ];
        }
    }

    // Tarihe göre sıralama (büyükten küçüğe)
    usort($postFilesWithDates, function ($a, $b) {
        return $b['lastModified'] - $a['lastModified'];
    });

    // Cache'e kaydet
    if (!empty($postFilesWithDates)) {
        setCachedPosts($postFilesWithDates);
    }
}

// Dashboard için yazı verilerini hazırla
$posts = [];
foreach ($postFilesWithDates as $postData) {
    if (!isset($postData['file'], $postData['slug'], $postData['lastModified'])) {
        continue;
    }

    $postFile = $postData['file'];
    $slug = $postData['slug'];
    $contentData = getPostContent($postFile);

    if ($contentData && isset($contentData['meta'])) {
        $posts[] = [
            'slug' => $slug,
            'meta' => $contentData['meta'],
            'content' => $contentData['content']
        ];
    } elseif ($contentData) {
        // Meta verisi yoksa varsayılan değerlerle oluştur
        $posts[] = [
            'slug' => $slug,
            'meta' => [
                'title' => $slug,
                'category' => 'Genel',
                'date' => date('Y-m-d'),
                'tags' => []
            ],
            'content' => $contentData['content'] ?? ''
        ];
    }
}

$postCount = count($posts);
$categoryCount = getCategoryCount();

// Son aktiviteleri al
$recentPosts = array_slice($posts, 0, 5);

// Sistem bilgileri
$cachedPosts = getCachedPosts();
$systemInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time') . 's',
    'memory_limit' => ini_get('memory_limit'),
    'disk_free_space' => (function_exists('disk_free_space') && function_exists('formatBytes')) ? formatBytes(disk_free_space('.')) : 'Unknown',
    'disk_total_space' => (function_exists('disk_total_space') && function_exists('formatBytes')) ? formatBytes(disk_total_space('.')) : 'Unknown',
    'posts_count' => is_array($cachedPosts) ? count($cachedPosts) : 0,
    'cache_size' => function_exists('getCacheSize') ? getCacheSize() : '0 bytes'
];

// Config.local.php durumu
$configLocalPath = '../config.local.php';
$configLocalExists = file_exists($configLocalPath);

// Çıkış işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        secureLogout();
    }
    secureRedirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
<link rel="stylesheet" href="<?php echo assetPath('includes/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo assetPath('admin/admin.css'); ?>">
</head>
<body>
    <!-- Sidebar Wrapper -->
    <div class="sidebar-wrapper">
        <button class="sidebar-toggle nav-link" type="button" title="Menüyü Aç/Kapat">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="sidebar p-3">
            <div class="sidebar-header">
                <h5 class="text-white mb-1">
                    <i class="bi bi-shield-lock"></i> Admin Panel
                </h5>
                <small class="text-white-50"><?php echo SITE_NAME; ?></small>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span class="link-text">Dashboard</span>
                </a>
                <a class="nav-link" href="posts.php">
                    <i class="bi bi-file-text"></i>
                    <span class="link-text">Yazılar</span>
                </a>
                <a class="nav-link" href="categories.php">
                    <i class="bi bi-folder"></i>
                    <span class="link-text">Kategoriler</span>
                </a>
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear"></i>
                    <span class="link-text">Ayarlar</span>
                </a>
                <hr class="text-white-50">
                <a class="nav-link" href="<?php echo BASE_PATH; ?>" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span class="link-text">Siteyi Görüntüle</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="admin-main-wrapper">
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <h4 class="mb-0">Dashboard</h4>
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="admin-user-panel">
                            <div class="admin-user-meta">
                                <div class="admin-user-name">
                                    <i class="bi bi-person-circle"></i>
                                    <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'admin'); ?>
                                </div>
                                <small class="admin-user-login">Son giris: <?php echo isset($_SESSION['login_time']) ? date('d.m.Y H:i', $_SESSION['login_time']) : '-'; ?></small>
                            </div>
                            <form method="POST" action="dashboard.php" class="admin-logout-inline" onsubmit="return confirm('Cikis yapmak istediginizden emin misiniz?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-box-arrow-right me-1"></i>Cikis Yap
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

                    <!-- Content -->
                    <div class="p-4">
                        <!-- Overview + Quick Actions -->
                        <div class="row g-4 mb-4">
                            <div class="col-xl-8">
                                <div class="summary-hero card border-0 h-100">
                                    <div class="card-body p-4 p-lg-5">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                            <div>
                                                <h5 class="mb-1 text-white">Yönetim Özeti</h5>
                                                <p class="mb-0 text-white-50">Blogunun genel durumunu tek ekranda gör.</p>
                                            </div>
                                            <span class="badge text-bg-light">Canlı Durum</span>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-sm-6 col-lg-3">
                                                <div class="summary-item">
                                                    <span class="summary-icon"><i class="bi bi-file-text"></i></span>
                                                    <div class="summary-value"><?php echo $postCount; ?></div>
                                                    <div class="summary-label">Toplam Yazı</div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-3">
                                                <div class="summary-item">
                                                    <span class="summary-icon"><i class="bi bi-folder2-open"></i></span>
                                                    <div class="summary-value"><?php echo $categoryCount; ?></div>
                                                    <div class="summary-label">Kategori</div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-3">
                                                <div class="summary-item">
                                                    <span class="summary-icon"><i class="bi bi-graph-up-arrow"></i></span>
                                                    <div class="summary-value"><?php echo $postCount > 0 ? round($postCount / 7, 1) : 0; ?></div>
                                                    <div class="summary-label">Haftalık Ortalama</div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-3">
                                                <div class="summary-item">
                                                    <span class="summary-icon"><i class="bi bi-shield-check"></i></span>
                                                    <div class="summary-value"><?php echo $configLocalExists ? 'OK' : 'Uyarı'; ?></div>
                                                    <div class="summary-label">Config Durumu</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card quick-actions-stack h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-lightning"></i> Hızlı Araçlar
                                        </h5>
                                    </div>
                                    <div class="card-body d-grid gap-2">
                                        <a href="posts.php?action=new" class="btn btn-primary w-100 text-start">
                                            <i class="bi bi-plus-circle me-2"></i>Yeni Yazı Oluştur
                                        </a>
                                        <a href="posts.php" class="btn btn-outline-primary w-100 text-start">
                                            <i class="bi bi-file-earmark-text me-2"></i>Yazıları Yönet
                                        </a>
                                        <a href="categories.php" class="btn btn-outline-primary w-100 text-start">
                                            <i class="bi bi-collection me-2"></i>Kategorileri Düzenle
                                        </a>
                                        <a href="settings.php" class="btn btn-outline-primary w-100 text-start">
                                            <i class="bi bi-gear me-2"></i>Site Ayarları
                                        </a>
                                        <a href="<?php echo BASE_PATH; ?>" target="_blank" class="btn btn-outline-secondary w-100 text-start">
                                            <i class="bi bi-box-arrow-up-right me-2"></i>Siteyi Görüntüle
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- System Info -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-info-circle"></i> Sistem Bilgileri
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">PHP Sürümü</small>
                                                <div class="fw-bold"><?php echo $systemInfo['php_version']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Sunucu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['server_software']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Maks. Dosya Boyutu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['upload_max_filesize']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">POST Boyutu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['post_max_size']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Çalışma Süresi</small>
                                                <div class="fw-bold"><?php echo $systemInfo['max_execution_time']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Bellek Limiti</small>
                                                <div class="fw-bold"><?php echo $systemInfo['memory_limit']; ?></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Boş Alan</small>
                                                <div class="fw-bold"><?php echo $systemInfo['disk_free_space']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Toplam Alan</small>
                                                <div class="fw-bold"><?php echo $systemInfo['disk_total_space']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Yazı Sayısı</small>
                                                <div class="fw-bold"><?php echo $systemInfo['posts_count']; ?></div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <small class="text-muted">Cache Boyutu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['cache_size']; ?></div>
                                            </div>
                                            <div class="col-md-4 col-12">
                                                <small class="text-muted">Config.local.php Durumu</small>
                                                <div class="fw-bold">
                                                    <?php if ($configLocalExists): ?>
                                                        <span class="text-success">
                                                            <i class="bi bi-check-circle"></i> Mevcut
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-warning">
                                                            <i class="bi bi-exclamation-triangle"></i> Bulunamadı
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Posts -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-clock-history"></i> Son Yazılar
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (empty($recentPosts)): ?>
                                            <div class="p-4 text-center text-muted">
                                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                                <p class="mt-2">Henüz yazı bulunmuyor.</p>
                                                <a href="posts.php" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle"></i> İlk Yazıyı Ekle
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Başlık</th>
                                                            <th>Kategori</th>
                                                            <th>Tarih</th>
                                                            <th>İşlemler</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($recentPosts as $post): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php echo htmlspecialchars($post['meta']['title'] ?? 'Başlıksız'); ?></strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-secondary">
                                                                        <?php echo htmlspecialchars($post['meta']['category'] ?? 'Genel'); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php echo date('d.m.Y', strtotime($post['meta']['date'] ?? 'now')); ?>
                                                                </td>
                                                                <td>
                                                                    <a href="edit_post.php?slug=<?php echo $post['slug']; ?>"
                                                                       class="btn btn-sm btn-outline-primary btn-action">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </a>
                                                                    <a href="<?php echo BASE_PATH . $post['slug']; ?>"
                                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i class="bi bi-eye"></i> Görüntüle
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?php echo assetPath('admin/sidebar.js'); ?>"></script>
    <script>
        // Auto-refresh session
        setInterval(function() {
            fetch('ping.php').catch(() => {
                // Session expired, redirect to login
                window.location.href = 'login.php?error=session_expired';
            });
        }, 300000); // 5 minutes

    </script>
</body>
</html>




