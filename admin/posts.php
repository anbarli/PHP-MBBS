<?php
/**
 * Admin Posts Management
 * Yazı yönetimi sayfası
 */

// Hata raporlama (geliştirme için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ADMIN_SECURE', true);

// Ana config dosyasını dahil et
require_once '../config.php';

// Güvenlik fonksiyonlarını dahil et
require_once '../includes/security.php';

// Session başlat ve yetki kontrolü
initSecureSession();
requireAdminAuth();

// Admin config yükle
$adminConfig = loadAdminConfig();

// Kategori filtresi
$selectedCategory = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

// Yazıları yeniden yükle
$postFilesWithDates = getCachedPosts();
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

// Posts için yazı verilerini hazırla
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

// Kategori filtresi uygula
if (!empty($selectedCategory)) {
    $posts = array_filter($posts, function($post) use ($selectedCategory) {
        return isset($post['meta']['category']) && $post['meta']['category'] === $selectedCategory;
    });
    $posts = array_values($posts); // Array indexlerini yeniden düzenle
}

// Kategorileri al (filtre için)
$categories = [];
foreach ($posts as $post) {
    if (isset($post['meta']['category']) && !empty($post['meta']['category'])) {
        $category = $post['meta']['category'];
        if (!in_array($category, $categories)) {
            $categories[] = $category;
        }
    }
}
sort($categories);

// Mesajlar
$message = '';
$messageType = '';

// Yazı silme işlemi
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $slug = sanitizeInput($_GET['delete']);
    $postFile = POSTS_DIR . $slug . '.md';
    
    if (file_exists($postFile)) {
        if (unlink($postFile)) {
            // Cache'i temizle
            clearCache();
            $message = 'Yazı başarıyla silindi.';
            $messageType = 'success';
            logAdminAction('delete_post', "Deleted post: $slug");
        } else {
            $message = 'Yazı silinirken hata oluştu.';
            $messageType = 'danger';
        }
    } else {
        $message = 'Yazı bulunamadı.';
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yazı Yönetimi - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">
                            <i class="bi bi-shield-lock"></i> Admin Panel
                        </h5>
                        <small class="text-white-50"><?php echo SITE_NAME; ?></small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="posts.php">
                            <i class="bi bi-file-text"></i> Yazılar
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="bi bi-folder"></i> Kategoriler
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear"></i> Ayarlar
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Siteyi Görüntüle
                        </a>
                        <a class="nav-link" href="dashboard.php?logout=1">
                            <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <!-- Top Navbar -->
                    <nav class="navbar navbar-expand-lg">
                        <div class="container-fluid">
                            <h4 class="mb-0">Yazı Yönetimi</h4>
                            <div class="d-flex align-items-center">
                                <a href="new_post.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Yeni Yazı
                                </a>
                            </div>
                        </div>
                    </nav>
                    
                    <!-- Content -->
                    <div class="p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle"></i> <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($selectedCategory)): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-funnel"></i> 
                                <strong><?php echo htmlspecialchars($selectedCategory); ?></strong> kategorisindeki yazılar gösteriliyor. 
                                <a href="posts.php" class="alert-link">Tüm yazıları görüntüle</a>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Posts Table -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 me-3">
                                        <i class="bi bi-file-text"></i> Tüm Yazılar (<?php echo count($posts); ?>)
                                    </h5>
                                    <?php if (!empty($categories)): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-funnel"></i> 
                                                <?php echo !empty($selectedCategory) ? $selectedCategory : 'Tüm Kategoriler'; ?>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item <?php echo empty($selectedCategory) ? 'active' : ''; ?>" href="posts.php">Tüm Kategoriler</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <?php foreach ($categories as $category): ?>
                                                    <li>
                                                        <a class="dropdown-item <?php echo $selectedCategory === $category ? 'active' : ''; ?>" 
                                                           href="posts.php?category=<?php echo urlencode($category); ?>">
                                                            <?php echo htmlspecialchars($category); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php if (!empty($selectedCategory)): ?>
                                            <a href="posts.php" class="btn btn-outline-danger btn-sm ms-2" title="Filtreyi Temizle">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="new_post.php" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle"></i> Yeni Yazı
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($posts)): ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2">Henüz yazı bulunmuyor.</p>
                                        <a href="new_post.php" class="btn btn-primary">
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
                                                    <th>Etiketler</th>
                                                    <th>Tarih</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($posts as $post): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($post['meta']['title'] ?? 'Başlıksız'); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo $post['slug']; ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo htmlspecialchars($post['meta']['category'] ?? 'Genel'); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($post['meta']['tags']) && is_array($post['meta']['tags'])): ?>
                                                                <?php foreach (array_slice($post['meta']['tags'], 0, 3) as $tag): ?>
                                                                    <span class="badge bg-light text-dark me-1">
                                                                        <?php echo htmlspecialchars($tag); ?>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                                <?php if (count($post['meta']['tags']) > 3): ?>
                                                                    <span class="badge bg-light text-dark">+<?php echo count($post['meta']['tags']) - 3; ?></span>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo date('d.m.Y', strtotime($post['meta']['date'] ?? 'now')); ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="edit_post.php?slug=<?php echo $post['slug']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary btn-action" 
                                                                   title="Düzenle">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <a href="<?php echo BASE_PATH . $post['slug']; ?>"
                                                                   target="_blank" class="btn btn-sm btn-outline-info btn-action">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-danger btn-action"
                                                                        onclick="deletePost('<?php echo $post['slug']; ?>', '<?php echo htmlspecialchars($post['meta']['title'] ?? 'Bu yazı'); ?>')"
                                                                        title="Sil">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deletePost(slug, title) {
            if (confirm(`"${title}" yazısını silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz!`)) {
                window.location.href = `posts.php?delete=${slug}`;
            }
        }
        
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html> 