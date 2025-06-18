<?php
/**
 * Admin Categories Management
 * Kategoriler yönetimi sayfası
 */

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

// Cache boşsa doğrudan dosyalardan oku
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

// Categories için yazı verilerini hazırla
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
    }
}

// Kategorileri topla
$categories = [];
foreach ($posts as $post) {
    if (isset($post['meta']) && isset($post['meta']['category'])) {
        $category = trim($post['meta']['category']);
        if (!empty($category)) {
            if (!isset($categories[$category])) {
                $categories[$category] = 0;
            }
            $categories[$category]++;
        }
    }
}

// Mesajlar
$message = '';
$messageType = '';

// Kategori silme işlemi
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $categoryToDelete = sanitizeInput($_GET['delete']);
    
    if ($categoryToDelete === 'Genel') {
        $message = 'Genel kategorisi silinemez.';
        $messageType = 'danger';
    } else {
        // Bu kategoriye ait yazıları bul
        $postsToUpdate = [];
        foreach ($posts as $post) {
            if (($post['meta']['category'] ?? 'Genel') === $categoryToDelete) {
                $postsToUpdate[] = $post;
            }
        }
        
        if (empty($postsToUpdate)) {
            $message = 'Bu kategoriye ait yazı bulunamadı.';
            $messageType = 'info';
        } else {
            // Yazıları Genel kategorisine taşı
            $updatedCount = 0;
            foreach ($postsToUpdate as $post) {
                $postFile = POSTS_DIR . $post['slug'] . '.md';
                $postContent = file_get_contents($postFile);
                
                // Meta verileri güncelle
                $post['meta']['category'] = 'Genel';
                
                // Markdown içeriği yeniden oluştur
                $markdown = "---\n";
                foreach ($post['meta'] as $key => $value) {
                    if ($key === 'tags') {
                        $markdown .= "tags: [" . implode(', ', $value) . "]\n";
                    } else {
                        $markdown .= "$key: " . $value . "\n";
                    }
                }
                $markdown .= "---\n\n";
                $markdown .= $post['content'];
                
                if (file_put_contents($postFile, $markdown)) {
                    $updatedCount++;
                }
            }
            
            // Cache'i temizle
            clearCache();
            
            $message = "$categoryToDelete kategorisi silindi ve $updatedCount yazı Genel kategorisine taşındı.";
            $messageType = 'success';
            logAdminAction('delete_category', "Deleted category: $categoryToDelete, moved $updatedCount posts");
        }
    }
    
    // Kategorileri yeniden yükle
    $posts = getCachedPosts();
    $categories = [];
    foreach ($posts as $post) {
        if (isset($post['meta']) && isset($post['meta']['category'])) {
            $category = trim($post['meta']['category']);
            if (!empty($category)) {
                if (!isset($categories[$category])) {
                    $categories[$category] = 0;
                }
                $categories[$category]++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoriler - <?php echo SITE_NAME; ?> Admin</title>
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
        .category-card {
            transition: transform 0.2s ease;
        }
        .category-card:hover {
            transform: translateY(-2px);
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
                        <a class="nav-link" href="posts.php">
                            <i class="bi bi-file-text"></i> Yazılar
                        </a>
                        <a class="nav-link active" href="categories.php">
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
                            <h4 class="mb-0">Kategoriler</h4>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary fs-6">
                                    <?php echo count($categories); ?> Kategori
                                </span>
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
                        
                        <!-- Categories Grid -->
                        <div class="row">
                            <?php if (empty($categories)): ?>
                                <div class="col-12">
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-folder-x" style="font-size: 3rem;"></i>
                                        <p class="mt-2">Henüz kategori bulunmuyor.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($categories as $category => $count): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card category-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="card-title mb-0">
                                                        <i class="bi bi-folder text-primary"></i>
                                                        <?php echo htmlspecialchars($category); ?>
                                                    </h5>
                                                    <span class="badge bg-secondary"><?php echo $count; ?></span>
                                                </div>
                                                
                                                <p class="card-text text-muted">
                                                    <?php echo $count; ?> yazı
                                                </p>
                                                
                                                <div class="d-flex gap-2">
                                                    <a href="posts.php?category=<?php echo urlencode($category); ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Görüntüle
                                                    </a>
                                                    
                                                    <?php if ($category !== 'Genel'): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteCategory('<?php echo htmlspecialchars($category); ?>', <?php echo $count; ?>)">
                                                            <i class="bi bi-trash"></i> Sil
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Category Stats -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-graph-up"></i> Kategori İstatistikleri
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-primary"><?php echo count($categories); ?></h3>
                                                    <p class="text-muted">Toplam Kategori</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-success"><?php echo array_sum($categories); ?></h3>
                                                    <p class="text-muted">Toplam Yazı</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-info">
                                                        <?php 
                                                        if (!empty($categories)) {
                                                            $maxCategory = array_keys($categories, max($categories))[0];
                                                            echo htmlspecialchars($maxCategory);
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </h3>
                                                    <p class="text-muted">En Popüler Kategori</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-warning">
                                                        <?php 
                                                        if (!empty($categories)) {
                                                            echo round(array_sum($categories) / count($categories), 1);
                                                        } else {
                                                            echo '0';
                                                        }
                                                        ?>
                                                    </h3>
                                                    <p class="text-muted">Ortalama Yazı/Kategori</p>
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCategory(category, count) {
            if (confirm(`"${category}" kategorisini silmek istediğinizden emin misiniz?\n\nBu kategorideki ${count} yazı "Genel" kategorisine taşınacak.\n\nBu işlem geri alınamaz!`)) {
                window.location.href = `categories.php?delete=${encodeURIComponent(category)}`;
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