<?php
/**
 * Admin New Post
 * Yeni yazı ekleme sayfası
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

// Mesajlar
$message = '';
$messageType = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        $messageType = 'danger';
    } else {
        // Input temizleme
        $title = sanitizeInput($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = sanitizeInput($_POST['category'] ?? 'Genel');
        $tags = sanitizeInput($_POST['tags'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $date = sanitizeInput($_POST['date'] ?? date('Y-m-d'));
        
        // Validasyon
        if (empty($title)) {
            $message = 'Başlık gereklidir.';
            $messageType = 'danger';
        } elseif (empty($content)) {
            $message = 'İçerik gereklidir.';
            $messageType = 'danger';
        } else {
            // Slug oluştur
            $slug = createSlug($title);
            
            // Dosya adı kontrolü
            $postFile = POSTS_DIR . $slug . '.md';
            $counter = 1;
            while (file_exists($postFile)) {
                $slug = createSlug($title) . '-' . $counter;
                $postFile = POSTS_DIR . $slug . '.md';
                $counter++;
            }
            
            // Meta verileri hazırla
            $meta = [
                'title' => $title,
                'date' => $date,
                'category' => $category,
                'description' => $description,
                'tags' => array_filter(array_map('trim', explode(',', $tags))),
                'author' => $adminConfig['ADMIN_NAME'] ?? 'Admin'
            ];
            
            // Markdown içeriği oluştur
            $markdown = "---\n";
            foreach ($meta as $key => $value) {
                if ($key === 'tags') {
                    $markdown .= "tags: [" . implode(', ', $value) . "]\n";
                } else {
                    $markdown .= "$key: " . $value . "\n";
                }
            }
            $markdown .= "---\n\n";
            $markdown .= $content;
            
            // Dosyayı kaydet
            if (file_put_contents($postFile, $markdown)) {
                // Cache'i temizle
                clearCache();
                
                $message = 'Yazı başarıyla oluşturuldu!';
                $messageType = 'success';
                
                // Log
                logAdminAction('create_post', "Created post: $slug");
                
                // Başarılı ise yazılar sayfasına yönlendir
                header("Location: posts.php?success=1");
                exit;
            } else {
                $message = 'Yazı kaydedilirken hata oluştu.';
                $messageType = 'danger';
            }
        }
    }
}

// CSRF token oluştur
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Yazı - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
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
        .CodeMirror {
            height: 400px;
            border-radius: 8px;
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
                            <h4 class="mb-0">Yeni Yazı Ekle</h4>
                            <div class="d-flex align-items-center">
                                <a href="posts.php" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-arrow-left"></i> Geri
                                </a>
                                <button type="submit" form="postForm" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Kaydet
                                </button>
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
                        
                        <form id="postForm" method="POST" class="card">
                            <div class="card-body">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <!-- Başlık -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Başlık *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                                   required>
                                        </div>
                                        
                                        <!-- İçerik -->
                                        <div class="mb-3">
                                            <label for="content" class="form-label">İçerik *</label>
                                            <textarea class="form-control" id="content" name="content" rows="20" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <!-- Tarih -->
                                        <div class="mb-3">
                                            <label for="date" class="form-label">Tarih</label>
                                            <input type="date" class="form-control" id="date" name="date" 
                                                   value="<?php echo $_POST['date'] ?? date('Y-m-d'); ?>">
                                        </div>
                                        
                                        <!-- Kategori -->
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Kategori</label>
                                            <input type="text" class="form-control" id="category" name="category" 
                                                   value="<?php echo htmlspecialchars($_POST['category'] ?? 'Genel'); ?>">
                                        </div>
                                        
                                        <!-- Etiketler -->
                                        <div class="mb-3">
                                            <label for="tags" class="form-label">Etiketler</label>
                                            <input type="text" class="form-control" id="tags" name="tags" 
                                                   value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>"
                                                   placeholder="etiket1, etiket2, etiket3">
                                            <div class="form-text">Virgülle ayırarak birden fazla etiket ekleyebilirsiniz.</div>
                                        </div>
                                        
                                        <!-- Açıklama -->
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Açıklama</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                            <div class="form-text">SEO için kısa açıklama (160 karakter).</div>
                                        </div>
                                        
                                        <!-- Önizleme -->
                                        <div class="mb-3">
                                            <label class="form-label">Önizleme</label>
                                            <div class="border rounded p-3 bg-light">
                                                <small class="text-muted">
                                                    <strong>URL:</strong> <span id="previewUrl">-</span><br>
                                                    <strong>Slug:</strong> <span id="previewSlug">-</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
    <script>
        // Markdown Editor
        const easyMDE = new EasyMDE({
            element: document.getElementById('content'),
            spellChecker: false,
            placeholder: 'Yazınızı buraya yazın...',
            toolbar: [
                'bold', 'italic', 'heading', '|',
                'quote', 'unordered-list', 'ordered-list', '|',
                'link', 'image', '|',
                'preview', 'side-by-side', 'fullscreen', '|',
                'guide'
            ]
        });
        
        // Başlık değiştiğinde slug önizlemesi
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            
            document.getElementById('previewSlug').textContent = slug || '-';
            document.getElementById('previewUrl').textContent = slug ? window.location.origin + '/' + slug : '-';
        });
        
        // Form gönderilmeden önce kontrol
        document.getElementById('postForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = easyMDE.value().trim();
            
            if (!title) {
                e.preventDefault();
                alert('Başlık gereklidir!');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!content) {
                e.preventDefault();
                alert('İçerik gereklidir!');
                easyMDE.codemirror.focus();
                return false;
            }
        });
    </script>
</body>
</html> 