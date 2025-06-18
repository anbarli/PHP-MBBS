<?php
/**
 * Admin Settings
 * Ayarlar sayfası
 */

// UTF-8 encoding ayarla
header('Content-Type: text/html; charset=utf-8');

// Admin güvenlik sabiti tanımla
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

// Config.local.php dosyasını yükle
$configLocalPath = '../config.local.php';
$configLocalExists = file_exists($configLocalPath);

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
        $action = sanitizeInput($_POST['action'] ?? '');
        
        if ($action === 'update_admin') {
            // Admin bilgilerini güncelle
            $newAdminName = sanitizeInput($_POST['admin_name'] ?? '');
            $newAdminEmail = sanitizeInput($_POST['admin_email'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validasyon
            if (empty($newAdminName)) {
                $message = 'Admin adı gereklidir.';
                $messageType = 'danger';
            } elseif (empty($newAdminEmail) || !filter_var($newAdminEmail, FILTER_VALIDATE_EMAIL)) {
                $message = 'Geçerli bir e-posta adresi girin.';
                $messageType = 'danger';
            } else {
                $configUpdated = false;
                
                // Admin adı ve e-posta güncelle
                $adminConfig['admin_name'] = $newAdminName;
                $adminConfig['admin_email'] = $newAdminEmail;
                $configUpdated = true;
                
                // Şifre değişikliği
                if (!empty($currentPassword)) {
                    if (!password_verify($currentPassword, $adminConfig['admin_password'])) {
                        $message = 'Mevcut şifre yanlış.';
                        $messageType = 'danger';
                    } elseif (empty($newPassword)) {
                        $message = 'Yeni şifre gereklidir.';
                        $messageType = 'danger';
                    } elseif (strlen($newPassword) < 8) {
                        $message = 'Yeni şifre en az 8 karakter olmalıdır.';
                        $messageType = 'danger';
                    } elseif ($newPassword !== $confirmPassword) {
                        $message = 'Şifreler eşleşmiyor.';
                        $messageType = 'danger';
                    } else {
                        $adminConfig['admin_password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                        $configUpdated = true;
                        $message .= ' Şifre güncellendi.';
                    }
                }
                
                if ($configUpdated) {
                    // Config dosyasını güncelle
                    if (updateAdminConfig($adminConfig)) {
                        $message = 'Ayarlar başarıyla güncellendi.' . $message;
                        $messageType = 'success';
                        logAdminAction('update_settings', 'Updated admin settings');
                    } else {
                        $message = 'Ayarlar kaydedilirken hata oluştu.';
                        $messageType = 'danger';
                    }
                }
            }
        } elseif ($action === 'update_site_config') {
            // Site ayarlarını güncelle
            $siteName = sanitizeInput($_POST['site_name'] ?? '');
            $defaultTitle = sanitizeInput($_POST['default_title'] ?? '');
            $defaultDescription = sanitizeInput($_POST['default_description'] ?? '');
            $authorName = sanitizeInput($_POST['author_name'] ?? '');
            $authorEmail = sanitizeInput($_POST['author_email'] ?? '');
            $gaTrackingId = sanitizeInput($_POST['ga_tracking_id'] ?? '');
            $twitterUsername = sanitizeInput($_POST['twitter_username'] ?? '');
            $socialTwitter = sanitizeInput($_POST['social_twitter'] ?? '');
            $socialGithub = sanitizeInput($_POST['social_github'] ?? '');
            $socialLinkedin = sanitizeInput($_POST['social_linkedin'] ?? '');
            $siteKeywords = sanitizeInput($_POST['site_keywords'] ?? '');
            $basePath = sanitizeInput($_POST['base_path'] ?? '');
            
            // Debug: Dosya yolu kontrolü
            $configLocalPath = '../config.local.php';
            $configLocalExists = file_exists($configLocalPath);
            $configLocalWritable = is_writable($configLocalPath);
            
            // Config.local.php içeriğini oluştur
            $configContent = "<?php
// Kişisel Ayarlar - Bu dosya git'e dahil edilmez
// Kendi ayarlarınızı buraya yazın

// Temel URL ayarları
\$basePath = '{$basePath}'; // Kendi dizin yapınıza göre değiştirin

// Site bilgileri
define('SITE_NAME', '{$siteName}');
define('DEFAULT_TITLE', '{$defaultTitle}');
define('DEFAULT_DESCRIPTION', '{$defaultDescription}');

// Google Analytics ID (isteğe bağlı)
define('GA_TRACKING_ID', '{$gaTrackingId}');

// Twitter kullanıcı adı (isteğe bağlı)
define('TWITTER_USERNAME', '{$twitterUsername}');

// Yazar bilgileri
define('AUTHOR_NAME', '{$authorName}');
define('AUTHOR_EMAIL', '{$authorEmail}');

// Sosyal medya linkleri (isteğe bağlı)
define('SOCIAL_TWITTER', '{$socialTwitter}');
define('SOCIAL_GITHUB', '{$socialGithub}');
define('SOCIAL_LINKEDIN', '{$socialLinkedin}');

// Cache ayarları
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 saat

// Güvenlik ayarları
define('ENABLE_ERROR_LOGGING', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 1800); // 30 dakika

// SEO ayarları
define('DEFAULT_LANGUAGE', 'tr');
define('DEFAULT_LOCALE', 'tr_TR');
define('SITE_KEYWORDS', '{$siteKeywords}');
?>";
            
            // Debug bilgileri
            $debugInfo = "Dosya yolu: $configLocalPath\n";
            $debugInfo .= "Dosya mevcut: " . ($configLocalExists ? 'Evet' : 'Hayır') . "\n";
            $debugInfo .= "Dosya yazılabilir: " . ($configLocalWritable ? 'Evet' : 'Hayır') . "\n";
            $debugInfo .= "Site adı: $siteName\n";
            
            // Config.local.php dosyasını güncelle
            if (file_put_contents($configLocalPath, $configContent)) {
                $message = 'Site ayarları başarıyla güncellendi. Debug: ' . $debugInfo;
                $messageType = 'success';
                logAdminAction('update_site_config', 'Updated site configuration');
                
                // Cache'i temizle
                clearCache();
            } else {
                $message = 'Site ayarları kaydedilirken hata oluştu. Debug: ' . $debugInfo;
                $messageType = 'danger';
            }
        } elseif ($action === 'clear_cache') {
            // Cache temizle
            if (clearCache()) {
                $message = 'Cache başarıyla temizlendi.';
                $messageType = 'success';
                logAdminAction('clear_cache', 'Cleared application cache');
            } else {
                $message = 'Cache temizlenirken hata oluştu.';
                $messageType = 'danger';
            }
        } elseif ($action === 'generate_sitemap') {
            // Sitemap oluştur
            if (generateSitemap()) {
                $message = 'Sitemap başarıyla oluşturuldu.';
                $messageType = 'success';
                logAdminAction('generate_sitemap', 'Generated sitemap');
            } else {
                $message = 'Sitemap oluşturulurken hata oluştu.';
                $messageType = 'danger';
            }
        }
    }
}

// CSRF token oluştur
$csrfToken = generateCSRFToken();

// Sistem bilgileri
$systemInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time') . 's',
    'memory_limit' => ini_get('memory_limit'),
    'disk_free_space' => formatBytes(disk_free_space('.')),
    'disk_total_space' => formatBytes(disk_total_space('.')),
    'posts_count' => count(getCachedPosts()),
    'cache_size' => getCacheSize()
];

// Mevcut site ayarlarını al
$currentSiteConfig = [];
if ($configLocalExists) {
    // Config.local.php'den mevcut değerleri al
    $currentSiteConfig = [
        'site_name' => defined('SITE_NAME') ? SITE_NAME : '',
        'default_title' => defined('DEFAULT_TITLE') ? DEFAULT_TITLE : '',
        'default_description' => defined('DEFAULT_DESCRIPTION') ? DEFAULT_DESCRIPTION : '',
        'author_name' => defined('AUTHOR_NAME') ? AUTHOR_NAME : '',
        'author_email' => defined('AUTHOR_EMAIL') ? AUTHOR_EMAIL : '',
        'ga_tracking_id' => defined('GA_TRACKING_ID') ? GA_TRACKING_ID : '',
        'twitter_username' => defined('TWITTER_USERNAME') ? TWITTER_USERNAME : '',
        'social_twitter' => defined('SOCIAL_TWITTER') ? SOCIAL_TWITTER : '',
        'social_github' => defined('SOCIAL_GITHUB') ? SOCIAL_GITHUB : '',
        'social_linkedin' => defined('SOCIAL_LINKEDIN') ? SOCIAL_LINKEDIN : '',
        'site_keywords' => defined('SITE_KEYWORDS') ? SITE_KEYWORDS : '',
        'base_path' => isset($basePath) ? $basePath : '/blog/'
    ];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - <?php echo SITE_NAME; ?> Admin</title>
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
        .info-card {
            transition: transform 0.2s ease;
        }
        .info-card:hover {
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
                        <a class="nav-link" href="categories.php">
                            <i class="bi bi-folder"></i> Kategoriler
                        </a>
                        <a class="nav-link" href="uploads.php">
                            <i class="bi bi-upload"></i> Dosya Yükleme
                        </a>
                        <a class="nav-link active" href="settings.php">
                            <i class="bi bi-gear"></i> Ayarlar
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="../" target="_blank">
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
                            <h4 class="mb-0">Ayarlar</h4>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary fs-6">
                                    <i class="bi bi-gear"></i> Yönetim
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
                        
                        <div class="row">
                            <!-- Admin Settings -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-person-gear"></i> Admin Bilgileri
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="update_admin">
                                            
                                            <div class="mb-3">
                                                <label for="admin_name" class="form-label">Admin Adı</label>
                                                <input type="text" class="form-control" id="admin_name" name="admin_name" 
                                                       value="<?php echo htmlspecialchars($adminConfig['admin_name'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="admin_email" class="form-label">E-posta</label>
                                                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                                       value="<?php echo htmlspecialchars($adminConfig['admin_email'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <hr>
                                            <h6>Şifre Değiştir</h6>
                                            <small class="text-muted">Şifrenizi değiştirmek istemiyorsanız boş bırakın.</small>
                                            
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Mevcut Şifre</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">Yeni Şifre</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                                       minlength="8">
                                                <div class="form-text">En az 8 karakter</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                       minlength="8">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle"></i> Güncelle
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Site Settings -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-globe"></i> Site Ayarları
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="update_site_config">
                                            
                                            <div class="mb-3">
                                                <label for="site_name" class="form-label">Site Adı</label>
                                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['site_name'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="default_title" class="form-label">Varsayılan Başlık</label>
                                                <input type="text" class="form-control" id="default_title" name="default_title" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['default_title'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="default_description" class="form-label">Varsayılan Açıklama</label>
                                                <textarea class="form-control" id="default_description" name="default_description" rows="3"><?php echo htmlspecialchars($currentSiteConfig['default_description'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="author_name" class="form-label">Yazar Adı</label>
                                                <input type="text" class="form-control" id="author_name" name="author_name" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['author_name'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="author_email" class="form-label">Yazar E-posta</label>
                                                <input type="email" class="form-control" id="author_email" name="author_email" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['author_email'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="base_path" class="form-label">Temel URL Yolu</label>
                                                <input type="text" class="form-control" id="base_path" name="base_path" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['base_path'] ?? '/blog/'); ?>">
                                                <div class="form-text">Örnek: /blog/ veya /</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="site_keywords" class="form-label">Site Anahtar Kelimeleri</label>
                                                <input type="text" class="form-control" id="site_keywords" name="site_keywords" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['site_keywords'] ?? ''); ?>">
                                                <div class="form-text">Virgülle ayırarak yazın</div>
                                            </div>
                                            
                                            <hr>
                                            <h6>Sosyal Medya</h6>
                                            
                                            <div class="mb-3">
                                                <label for="ga_tracking_id" class="form-label">Google Analytics ID</label>
                                                <input type="text" class="form-control" id="ga_tracking_id" name="ga_tracking_id" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['ga_tracking_id'] ?? ''); ?>">
                                                <div class="form-text">Örnek: G-XXXXXXXXXX</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="twitter_username" class="form-label">Twitter Kullanıcı Adı</label>
                                                <input type="text" class="form-control" id="twitter_username" name="twitter_username" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['twitter_username'] ?? ''); ?>">
                                                <div class="form-text">Örnek: @kullaniciadi</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="social_twitter" class="form-label">Twitter URL</label>
                                                <input type="url" class="form-control" id="social_twitter" name="social_twitter" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['social_twitter'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="social_github" class="form-label">GitHub URL</label>
                                                <input type="url" class="form-control" id="social_github" name="social_github" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['social_github'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="social_linkedin" class="form-label">LinkedIn URL</label>
                                                <input type="url" class="form-control" id="social_linkedin" name="social_linkedin" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['social_linkedin'] ?? ''); ?>">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Site Ayarlarını Güncelle
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- System Tools -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-tools"></i> Sistem Araçları
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="clear_cache">
                                                <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                                                    <i class="bi bi-trash"></i> Cache Temizle
                                                </button>
                                            </form>
                                            
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="generate_sitemap">
                                                <button type="submit" class="btn btn-outline-info w-100 mb-2">
                                                    <i class="bi bi-sitemap"></i> Sitemap Oluştur
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- System Info -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-info-circle"></i> Sistem Bilgileri
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">PHP Sürümü</small>
                                                <div class="fw-bold"><?php echo $systemInfo['php_version']; ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Sunucu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['server_software']; ?></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Maks. Dosya Boyutu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['upload_max_filesize']; ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">POST Boyutu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['post_max_size']; ?></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Çalışma Süresi</small>
                                                <div class="fw-bold"><?php echo $systemInfo['max_execution_time']; ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Bellek Limiti</small>
                                                <div class="fw-bold"><?php echo $systemInfo['memory_limit']; ?></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Boş Alan</small>
                                                <div class="fw-bold"><?php echo $systemInfo['disk_free_space']; ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Toplam Alan</small>
                                                <div class="fw-bold"><?php echo $systemInfo['disk_total_space']; ?></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Yazı Sayısı</small>
                                                <div class="fw-bold"><?php echo $systemInfo['posts_count']; ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Cache Boyutu</small>
                                                <div class="fw-bold"><?php echo $systemInfo['cache_size']; ?></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 