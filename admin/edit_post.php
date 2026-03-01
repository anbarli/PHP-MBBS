<?php
/**
 * Admin Edit Post
 * YazÄ± dÃ¼zenleme sayfasÄ±
 */

define('ADMIN_SECURE', true);

// Ana config dosyasÄ±nÄ± dahil et
require_once '../config.php';

// GÃ¼venlik fonksiyonlarÄ±nÄ± dahil et
require_once '../includes/security.php';

// Session baÅŸlat ve yetki kontrolÃ¼
initSecureSession();
requireAdminAuth();

// Admin config yÃ¼kle
$adminConfig = loadAdminConfig();

// Slug parametresi
$slug = sanitizeInput($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: posts.php');
    exit;
}

$postFile = POSTS_DIR . $slug . '.md';

// Dosya var mÄ±?
if (!file_exists($postFile)) {
    header('Location: posts.php?error=post_not_found');
    exit;
}

// Mesajlar
$message = '';
$messageType = '';

// Form gÃ¶nderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolÃ¼
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'GÃ¼venlik hatasÄ±. LÃ¼tfen tekrar deneyin.';
        $messageType = 'danger';
    } else {
        // Input temizleme
        $title = sanitizeInput($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = sanitizeInput($_POST['category'] ?? 'Genel');
        $tags = sanitizeInput($_POST['tags'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $date = sanitizeInput($_POST['date'] ?? date('Y-m-d'));
        $status = normalizePostStatus($_POST['status'] ?? ($post['meta']['status'] ?? 'published'));
        
        // Validasyon
        if (empty($title)) {
            $message = 'BaÅŸlÄ±k gereklidir.';
            $messageType = 'danger';
        } elseif (empty($content)) {
            $message = 'Ä°Ã§erik gereklidir.';
            $messageType = 'danger';
        } elseif (($altIssues = findMissingImageAltText($content)) && count($altIssues) > 0) {
            $lineNumbers = array_values(array_unique(array_map(function($issue) {
                return (int)$issue['line'];
            }, $altIssues)));
            $linePreview = implode(', ', array_slice($lineNumbers, 0, 8));
            $message = 'Gorsellerde bos/eksik alt metni bulundu. Lutfen duzeltin. Satir(lar): ' . $linePreview;
            if (count($lineNumbers) > 8) {
                $message .= ' ...';
            }
            $messageType = 'danger';
        } else {
            // Meta verileri hazÄ±rla
            $meta = [
                'title' => $title,
                'date' => $date,
                'category' => $category,
                'description' => $description,
                'status' => $status,
                'tags' => array_filter(array_map('trim', explode(',', $tags))),
                'author' => $adminConfig['ADMIN_NAME'] ?? 'Admin'
            ];
            
            // Markdown iÃ§eriÄŸi oluÅŸtur
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
            
            // DosyayÄ± kaydet
            if (file_put_contents($postFile, $markdown)) {
                // Cache'i temizle
                clearCache();
                
                $message = 'YazÄ± baÅŸarÄ±yla gÃ¼ncellendi!';
                $messageType = 'success';
                
                // Log
                logAdminAction('edit_post', "Edited post: $slug");
            } else {
                $message = 'YazÄ± kaydedilirken hata oluÅŸtu.';
                $messageType = 'danger';
            }
        }
    }
}

// Mevcut yazÄ±yÄ± oku
$postContent = file_get_contents($postFile);
$post = parsePost($postContent, $slug);

// CSRF token oluÅŸtur
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YazÄ± DÃ¼zenle - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<link rel="stylesheet" href="<?php echo assetPath('includes/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo assetPath('admin/admin.css'); ?>">
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
                            <i class="bi bi-file-text"></i> YazÄ±lar
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="bi bi-folder"></i> Kategoriler
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear"></i> Ayarlar
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Siteyi GÃ¶rÃ¼ntÃ¼le
                        </a>
                        <form method="POST" action="dashboard.php" class="sidebar-logout-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="nav-link btn btn-link nav-link-logout">
                                <i class="bi bi-box-arrow-right"></i> Ã‡Ä±kÄ±ÅŸ Yap
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <!-- Top Navbar -->
                    <nav class="navbar navbar-expand-lg">
                        <div class="container-fluid">
                            <h4 class="mb-0">YazÄ± DÃ¼zenle: <?php echo htmlspecialchars($post['meta']['title']); ?></h4>
                            <div class="d-flex align-items-center">
                                <a href="<?php echo BASE_PATH . $slug; ?>" target="_blank" class="btn btn-outline-info me-2">
                                    <i class="bi bi-eye"></i> GÃ¶rÃ¼ntÃ¼le
                                </a>
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
                                        <!-- BaÅŸlÄ±k -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label">BaÅŸlÄ±k *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($_POST['title'] ?? $post['meta']['title']); ?>" 
                                                   required>
                                        </div>
                                        
                                        <!-- Ä°Ã§erik -->
                                        <div class="mb-3">
                                            <label for="content" class="form-label">Ä°Ã§erik *</label>
                                            <textarea class="form-control" id="content" name="content" rows="20"><?php echo htmlspecialchars($_POST['content'] ?? $post['content']); ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <!-- Tarih -->
                                        <div class="mb-3">
                                            <label for="date" class="form-label">Tarih</label>
                                            <input type="date" class="form-control" id="date" name="date" 
                                                   value="<?php echo $_POST['date'] ?? $post['meta']['date']; ?>">
                                        </div>
                                        
                                        <!-- Kategori -->
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Kategori</label>
                                            <input type="text" class="form-control" id="category" name="category" 
                                                   value="<?php echo htmlspecialchars($_POST['category'] ?? ($post['meta']['category'] ?? 'Genel')); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Durum</label>
                                            <select class="form-select" id="status" name="status">
                                                <?php $currentStatus = normalizePostStatus($_POST['status'] ?? ($post['meta']['status'] ?? 'published')); ?>
                                                <option value="published" <?php echo $currentStatus === 'published' ? 'selected' : ''; ?>>Yayinda</option>
                                                <option value="draft" <?php echo $currentStatus === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Etiketler -->
                                        <div class="mb-3">
                                            <label for="tags" class="form-label">Etiketler</label>
                                            <input type="text" class="form-control" id="tags" name="tags" 
                                                   value="<?php echo htmlspecialchars($_POST['tags'] ?? (isset($post['meta']['tags']) ? implode(', ', $post['meta']['tags']) : '')); ?>"
                                                   placeholder="etiket1, etiket2, etiket3">
                                            <div class="form-text">VirgÃ¼lle ayÄ±rarak birden fazla etiket ekleyebilirsiniz.</div>
                                        </div>
                                        
                                        <!-- AÃ§Ä±klama -->
                                        <div class="mb-3">
                                            <label for="description" class="form-label">AÃ§Ä±klama</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ($post['meta']['description'] ?? '')); ?></textarea>
                                            <div class="form-text">SEO iÃ§in kÄ±sa aÃ§Ä±klama (160 karakter).</div>
                                        </div>
                                        
                                        <!-- Bilgiler -->
                                        <div class="mb-3">
                                            <label class="form-label">Bilgiler</label>
                                            <div class="border rounded p-3 bg-light">
                                                <small class="text-muted">
                                                    <strong>Slug:</strong> <?php echo $slug; ?><br>
                                                    <strong>Dosya:</strong> <?php echo basename($postFile); ?><br>
                                                    <strong>Son DÃ¼zenleme:</strong> <?php echo date('d.m.Y H:i', filemtime($postFile)); ?>
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
            autoDownloadFontAwesome: false,
            spellChecker: false,
            placeholder: 'YazÄ±nÄ±zÄ± buraya yazÄ±n...',
            toolbar: [
                'bold', 'italic', 'heading', '|',
                'quote', 'unordered-list', 'ordered-list', '|',
                'link', 'image', '|',
                'preview', 'side-by-side', 'fullscreen', '|',
                'guide'
            ]
        });
        
        // Form gÃ¶nderilmeden Ã¶nce kontrol
        document.getElementById('postForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = easyMDE.value().trim();
            
            if (!title) {
                e.preventDefault();
                alert('BaÅŸlÄ±k gereklidir!');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!content) {
                e.preventDefault();
                alert('Ä°Ã§erik gereklidir!');
                easyMDE.codemirror.focus();
                return false;
            }
        });
    </script>
</body>
</html> 


