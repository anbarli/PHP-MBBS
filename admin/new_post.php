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
        $status = normalizePostStatus($_POST['status'] ?? 'published');

        // Validasyon
        if (empty($title)) {
            $message = 'Başlık gereklidir.';
            $messageType = 'danger';
        } elseif (empty($content)) {
            $message = 'İçerik gereklidir.';
            $messageType = 'danger';
        } elseif ($status === 'published' && ($seoIssues = getSeoChecklistIssues($title, $description, $content)) && count($seoIssues) > 0) {
            $message = 'Yayin oncesi SEO kontrol listesi tamamlanmadi: ' . implode(' | ', $seoIssues);
            $messageType = 'danger';
        } else {
            // Slug oluştur (benzersizlik dahil)
            $slug = generateUniqueSlug($title);
            $postFile = POSTS_DIR . $slug . '.md';

            // Meta verileri hazırla
            $meta = [
                'title' => $title,
                'date' => $date,
                'category' => $category,
                'description' => $description,
                'status' => $status,
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css" integrity="sha384-GlbiKsvSqFd+S4VYQauDeZC0et72uYBVm5KKtNWMq9B0V0tB9vjtZqAQs7S1OxZN" crossorigin="anonymous">
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
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span class="link-text">Dashboard</span>
                </a>
                <a class="nav-link active" href="posts.php">
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
                            <h4 class="mb-0">Yeni Yazı Ekle</h4>
                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                <a href="posts.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Geri
                                </a>
                                <button type="submit" form="postForm" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Kaydet
                                </button>
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
                                            <textarea class="form-control" id="content" name="content" rows="20"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
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

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Durum</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="published" <?php echo (($_POST['status'] ?? 'published') === 'published') ? 'selected' : ''; ?>>Yayında</option>
                                                <option value="draft" <?php echo (($_POST['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Taslak</option>
                                            </select>
                                            <div class="form-text">Yayina almadan once SEO kontrol listesi (aciklama, H1, gorsel alt metni) dogrulanir.</div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?php echo assetPath('admin/sidebar.js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js" integrity="sha384-jJlrXJ2qADSJUTE9QP5XqJJNJz6wJhJXmBD6vKAJNpfKJRnJuJSLOLMsHsqJ7Esd" crossorigin="anonymous"></script>
    <script>
        // Markdown Editor
        const easyMDE = new EasyMDE({
            element: document.getElementById('content'),
            autoDownloadFontAwesome: false,
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
                .replace(/[ç]/g, 'c')
                .replace(/[ğ]/g, 'g')
                .replace(/[ı]/g, 'i')
                .replace(/[ö]/g, 'o')
                .replace(/[ş]/g, 's')
                .replace(/[ü]/g, 'u')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');

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



