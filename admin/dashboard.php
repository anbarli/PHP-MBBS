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

// Çıkış işlemi
if (isset($_GET['logout'])) {
    secureLogout();
    secureRedirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?> Admin</title>
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
        .stat-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="posts.php">
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
                        <a class="nav-link" href="?logout=1">
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
                            <h4 class="mb-0">Dashboard</h4>
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    <i class="bi bi-person-circle"></i> 
                                    <?php echo htmlspecialchars($_SESSION['user_id']); ?>
                                </span>
                                <small class="text-muted">
                                    Son giriş: <?php echo date('d.m.Y H:i', $_SESSION['login_time']); ?>
                                </small>
                            </div>
                        </div>
                    </nav>
                    
                    <!-- Content -->
                    <div class="p-4">
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="stat-card p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary me-3">
                                            <i class="bi bi-file-text"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo $postCount; ?></h3>
                                            <p class="text-muted mb-0">Toplam Yazı</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="stat-card p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success me-3">
                                            <i class="bi bi-folder"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo $categoryCount; ?></h3>
                                            <p class="text-muted mb-0">Kategori</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="stat-card p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info me-3">
                                            <i class="bi bi-eye"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo $postCount > 0 ? round($postCount / 7, 1) : 0; ?></h3>
                                            <p class="text-muted mb-0">Haftalık Ortalama</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Posts -->
                        <div class="row">
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
                        
                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-lightning"></i> Hızlı İşlemler
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <a href="posts.php?action=new" class="btn btn-primary w-100">
                                                    <i class="bi bi-plus-circle"></i> Yeni Yazı
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <a href="settings.php" class="btn btn-info w-100">
                                                    <i class="bi bi-gear"></i> Site Ayarları
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <a href="<?php echo BASE_PATH; ?>" target="_blank" class="btn btn-outline-secondary w-100">
                                                    <i class="bi bi-box-arrow-up-right"></i> Siteyi Görüntüle
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh session
        setInterval(function() {
            fetch('ping.php').catch(() => {
                // Session expired, redirect to login
                window.location.href = 'login.php?error=session_expired';
            });
        }, 300000); // 5 minutes
        
        // Confirm logout
        document.querySelector('a[href*="logout"]').addEventListener('click', function(e) {
            if (!confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 