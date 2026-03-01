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
$projectRoot = dirname(__DIR__);
$backupDir = CACHE_DIR . 'backups' . DIRECTORY_SEPARATOR;

if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0755, true);
}

function isValidBackupFilename($filename) {
    return (bool)preg_match('/^backup-\d{8}-\d{6}\.zip$/', (string)$filename);
}

function listBackupFiles($backupDir) {
    if (!is_dir($backupDir)) {
        return [];
    }

    $files = glob($backupDir . 'backup-*.zip') ?: [];
    $result = [];
    foreach ($files as $filePath) {
        $filename = basename($filePath);
        if (!isValidBackupFilename($filename)) {
            continue;
        }

        $result[] = [
            'filename' => $filename,
            'path' => $filePath,
            'size' => filesize($filePath) ?: 0,
            'modified' => filemtime($filePath) ?: 0
        ];
    }

    usort($result, function ($a, $b) {
        return $b['modified'] <=> $a['modified'];
    });

    return $result;
}

function addFileToBackupZip($zip, $absolutePath, $archivePath) {
    if (!is_file($absolutePath)) {
        return false;
    }

    return $zip->addFile($absolutePath, str_replace('\\', '/', $archivePath));
}

function canRestoreEntry($entryName) {
    $entryName = str_replace('\\', '/', (string)$entryName);
    if ($entryName === 'config.local.php') {
        return true;
    }
    if ($entryName === 'admin/admin.env') {
        return true;
    }
    if (preg_match('#^posts/[a-z0-9-]+\.md$#i', $entryName)) {
        return true;
    }

    return false;
}

// Backup indirme
if (isset($_GET['download_backup'])) {
    $downloadFile = basename((string)$_GET['download_backup']);
    if (!isValidBackupFilename($downloadFile)) {
        http_response_code(400);
        exit('Invalid backup file.');
    }

    $downloadPath = $backupDir . $downloadFile;
    if (!is_file($downloadPath)) {
        http_response_code(404);
        exit('Backup not found.');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $downloadFile . '"');
    header('Content-Length: ' . filesize($downloadPath));
    readfile($downloadPath);
    exit;
}

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
            $newAdminUsername = sanitizeInput($_POST['admin_username'] ?? '');
            $newAdminName = sanitizeInput($_POST['admin_name'] ?? '');
            $newAdminEmail = sanitizeInput($_POST['admin_email'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validasyon
            if (empty($newAdminUsername)) {
                $message = 'Kullanıcı adı gereklidir.';
                $messageType = 'danger';
            } elseif (empty($newAdminName)) {
                $message = 'Admin adı gereklidir.';
                $messageType = 'danger';
            } elseif (empty($newAdminEmail) || !filter_var($newAdminEmail, FILTER_VALIDATE_EMAIL)) {
                $message = 'Geçerli bir e-posta adresi girin.';
                $messageType = 'danger';
            } else {
                $configUpdated = false;
                
                // Admin kullanıcı adı, adı ve e-posta güncelle
                $adminConfig['ADMIN_USERNAME'] = $newAdminUsername;
                $adminConfig['ADMIN_NAME'] = $newAdminName;
                $adminConfig['ADMIN_EMAIL'] = $newAdminEmail;
                $configUpdated = true;
                
                // Şifre değişikliği
                if (!empty($currentPassword)) {
                    if (!verifyPassword($currentPassword, $adminConfig['ADMIN_PASSWORD'])) {
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
                        $adminConfig['ADMIN_PASSWORD'] = hashPassword($newPassword);
                        $configUpdated = true;
                        $message .= ' Şifre güncellendi.';
                    }
                }
                
                if ($configUpdated) {
                    // Admin klasörünü dinamik olarak bul
                    $adminDir = dirname(__DIR__) . '/admin';
                    if (!is_dir($adminDir)) {
                        $possibleDirs = ['admin1', 'admin2', 'admin3', 'admin4', 'admin5'];
                        foreach ($possibleDirs as $dir) {
                            $testDir = dirname(__DIR__) . '/' . $dir;
                            if (is_dir($testDir)) {
                                $adminDir = $testDir;
                                break;
                            }
                        }
                    }
                    $envFile = $adminDir . '/admin.env';
                    
                    // Admin config dosyasını güncelle
                    if (updateAdminConfig($adminConfig)) {
                        // Config'i yeniden yükle
                        $adminConfig = loadAdminConfig();
                        $message = 'Admin ayarları başarıyla güncellendi.' . $message;
                        $messageType = 'success';
                        logAdminAction('update_admin_settings', 'Updated admin settings');
                    } else {
                        $message = 'Admin ayarları kaydedilirken hata oluştu.';
                        $messageType = 'danger';
                        logError('Admin settings update failed', ['file' => 'admin/settings.php']);
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
            $twitterUsername = sanitizeInput($_POST['social_twitter'] ?? '');
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
define('BASE_PATH', '$basePath'); // Kendi dizin yapınıza göre değiştirin

// Site bilgileri
define('SITE_NAME', '$siteName');
define('DEFAULT_TITLE', '$defaultTitle');
define('DEFAULT_DESCRIPTION', '$defaultDescription');

// Google Analytics ID (isteğe bağlı)
define('GA_TRACKING_ID', '$gaTrackingId');

// Twitter kullanıcı adı (isteğe bağlı)
define('TWITTER_USERNAME', '$twitterUsername');

// Yazar bilgileri
define('AUTHOR_NAME', '$authorName');
define('AUTHOR_EMAIL', '$authorEmail');

// Sosyal medya linkleri (isteğe bağlı)
define('SOCIAL_TWITTER', '$socialTwitter');
define('SOCIAL_GITHUB', '$socialGithub');
define('SOCIAL_LINKEDIN', '$socialLinkedin');

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
define('SITE_KEYWORDS', '$siteKeywords');
?>";
            
            // Config.local.php dosyasını güncelle
            if (file_put_contents($configLocalPath, $configContent)) {
                $message = 'Site ayarları başarıyla güncellendi.';
                $messageType = 'success';
                logAdminAction('update_site_config', 'Updated site configuration');
                
                // Cache'i temizle
                clearCache();
            } else {
                $message = 'Site ayarları kaydedilirken hata oluştu.';
                $messageType = 'danger';
                logError('Site config write failed', ['file' => $configLocalPath]);
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
        } elseif ($action === 'regenerate_cache') {
            // Cache'i yeniden oluştur
            if (clearCache()) {
                // Cache'i yeniden oluştur
                $posts = array_diff(scandir(POSTS_DIR), array('..', '.'));
                $postFilesWithDates = [];
                
                foreach ($posts as $post) {
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
                
                usort($postFilesWithDates, function ($a, $b) {
                    return $b['lastModified'] - $a['lastModified'];
                });
                
                setCachedPosts($postFilesWithDates);
                $message = 'Cache başarıyla yeniden oluşturuldu. Yazı sayısı: ' . count($postFilesWithDates);
                $messageType = 'success';
                logAdminAction('regenerate_cache', 'Regenerated cache with ' . count($postFilesWithDates) . ' posts');
            } else {
                $message = 'Cache yeniden oluşturulurken hata oluştu.';
                $messageType = 'danger';
            }
        } elseif ($action === 'create_backup') {
            if (!class_exists('ZipArchive')) {
                $message = 'ZipArchive desteği bulunamadı. Yedek oluşturulamadı.';
                $messageType = 'danger';
            } else {
                if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
                    $message = 'Yedek klasörü oluşturulamadı.';
                    $messageType = 'danger';
                } else {
                    $backupFile = 'backup-' . date('Ymd-His') . '.zip';
                    $backupPath = $backupDir . $backupFile;

                    $zip = new ZipArchive();
                    if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                        $message = 'Yedek dosyası oluşturulamadı.';
                        $messageType = 'danger';
                    } else {
                        $addedCount = 0;

                        foreach (glob(POSTS_DIR . '*.md') ?: [] as $postFile) {
                            if (addFileToBackupZip($zip, $postFile, 'posts/' . basename($postFile))) {
                                $addedCount++;
                            }
                        }

                        if (addFileToBackupZip($zip, $projectRoot . '/config.local.php', 'config.local.php')) {
                            $addedCount++;
                        }
                        if (addFileToBackupZip($zip, $projectRoot . '/admin/admin.env', 'admin/admin.env')) {
                            $addedCount++;
                        }

                        $manifest = [
                            'created_at' => date('c'),
                            'source' => 'php-mbbs-admin',
                            'files_added' => $addedCount
                        ];
                        $zip->addFromString('backup-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        $zip->close();

                        clearCache();
                        $message = 'Yedek oluşturuldu: ' . $backupFile . ' (' . $addedCount . ' dosya).';
                        $messageType = 'success';
                        logAdminAction('create_backup', 'Created backup: ' . $backupFile);
                    }
                }
            }
        } elseif ($action === 'restore_backup') {
            if (!class_exists('ZipArchive')) {
                $message = 'ZipArchive desteği bulunamadı. Geri yükleme yapılamadı.';
                $messageType = 'danger';
            } elseif (!isset($_FILES['backup_file']) || ($_FILES['backup_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $message = 'Lütfen geçerli bir yedek dosyası seçin.';
                $messageType = 'danger';
            } else {
                $uploadName = strtolower((string)($_FILES['backup_file']['name'] ?? ''));
                $uploadTmp = (string)($_FILES['backup_file']['tmp_name'] ?? '');
                $uploadSize = (int)($_FILES['backup_file']['size'] ?? 0);

                if (pathinfo($uploadName, PATHINFO_EXTENSION) !== 'zip') {
                    $message = 'Sadece .zip uzantılı yedek dosyaları kabul edilir.';
                    $messageType = 'danger';
                } elseif ($uploadSize <= 0 || $uploadSize > 50 * 1024 * 1024) {
                    $message = 'Yedek dosyası boyutu geçersiz (maksimum 50MB).';
                    $messageType = 'danger';
                } else {
                    $zip = new ZipArchive();
                    if ($zip->open($uploadTmp) !== true) {
                        $message = 'Yedek dosyası açılamadı.';
                        $messageType = 'danger';
                    } else {
                        $restoredCount = 0;
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $entry = $zip->getNameIndex($i);
                            if (!is_string($entry)) {
                                continue;
                            }

                            $entry = str_replace('\\', '/', $entry);
                            if ($entry === 'backup-manifest.json' || substr($entry, -1) === '/') {
                                continue;
                            }

                            if (!canRestoreEntry($entry)) {
                                continue;
                            }

                            $stream = $zip->getStream($entry);
                            if (!$stream) {
                                continue;
                            }

                            $targetPath = $projectRoot . '/' . $entry;
                            $targetDir = dirname($targetPath);
                            if (!is_dir($targetDir)) {
                                @mkdir($targetDir, 0755, true);
                            }

                            $content = stream_get_contents($stream);
                            fclose($stream);
                            if ($content === false) {
                                continue;
                            }

                            if (file_put_contents($targetPath, $content) !== false) {
                                $restoredCount++;
                            }
                        }
                        $zip->close();

                        clearCache();
                        $message = 'Geri yükleme tamamlandı. Güncellenen dosya sayısı: ' . $restoredCount;
                        $messageType = $restoredCount > 0 ? 'success' : 'warning';
                        logAdminAction('restore_backup', 'Restored backup file upload, files: ' . $restoredCount);
                    }
                }
            }
        } elseif ($action === 'delete_backup') {
            $backupFilename = basename((string)($_POST['backup_filename'] ?? ''));
            if (!isValidBackupFilename($backupFilename)) {
                $message = 'Geçersiz yedek dosyası adı.';
                $messageType = 'danger';
            } else {
                $targetBackup = $backupDir . $backupFilename;
                if (!is_file($targetBackup)) {
                    $message = 'Yedek dosyası bulunamadı.';
                    $messageType = 'danger';
                } elseif (@unlink($targetBackup)) {
                    $message = 'Yedek dosyası silindi: ' . $backupFilename;
                    $messageType = 'success';
                    logAdminAction('delete_backup', 'Deleted backup: ' . $backupFilename);
                } else {
                    $message = 'Yedek dosyası silinemedi.';
                    $messageType = 'danger';
                }
            }
        }
    }
}

// CSRF token oluştur
$csrfToken = generateCSRFToken();
$backupFiles = listBackupFiles($backupDir);

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
        'base_path' => defined('BASE_PATH') ? BASE_PATH : '/blog/'
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
                        <a class="nav-link" href="posts.php">
                            <i class="bi bi-file-text"></i> Yazılar
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="bi bi-folder"></i> Kategoriler
                        </a>
                        <a class="nav-link active" href="settings.php">
                            <i class="bi bi-gear"></i> Ayarlar
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="../" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Siteyi Görüntüle
                        </a>
                        <form method="POST" action="dashboard.php" class="sidebar-logout-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="nav-link btn btn-link nav-link-logout">
                                <i class="bi bi-box-arrow-right"></i> Çıkış Yap
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
                            <!-- Admin Settings - 1. Sütun -->
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
                                                <label for="admin_username" class="form-label">Kullanıcı Adı</label>
                                                <input type="text" class="form-control" id="admin_username" name="admin_username" 
                                                       value="<?php echo htmlspecialchars($adminConfig['ADMIN_USERNAME'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="admin_name" class="form-label">Admin Adı</label>
                                                <input type="text" class="form-control" id="admin_name" name="admin_name" 
                                                       value="<?php echo htmlspecialchars($adminConfig['ADMIN_NAME'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="admin_email" class="form-label">E-posta</label>
                                                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                                       value="<?php echo htmlspecialchars($adminConfig['ADMIN_EMAIL'] ?? ''); ?>" required>
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
                                
                                <!-- System Tools - Admin Bilgileri altinda -->
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
                                            
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="regenerate_cache">
                                                <button type="submit" class="btn btn-outline-success w-100 mb-2">
                                                    <i class="bi bi-arrow-clockwise"></i> Cache Yeniden Oluştur
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-hdd-stack"></i> Yedekleme ve Geri Yükleme
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" class="mb-3">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="create_backup">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                <i class="bi bi-download"></i> Yeni Yedek Oluştur
                                            </button>
                                            <div class="form-text mt-2">
                                                <code>posts/*.md</code>, <code>config.local.php</code> ve <code>admin/admin.env</code> tek zip içinde saklanır.
                                            </div>
                                        </form>

                                        <form method="POST" enctype="multipart/form-data" class="mb-3">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="restore_backup">
                                            <label for="backup_file" class="form-label">Yedek Dosyasından Geri Yükle (.zip)</label>
                                            <input type="file" class="form-control mb-2" id="backup_file" name="backup_file" accept=".zip" required>
                                            <button type="submit" class="btn btn-outline-danger w-100"
                                                    onclick="return confirm('Seçilen yedek dosyasındaki içerikler mevcut dosyaların üzerine yazılacaktır. Devam edilsin mi?');">
                                                <i class="bi bi-arrow-counterclockwise"></i> Geri Yüklemeyi Başlat
                                            </button>
                                        </form>

                                        <hr>
                                        <h6 class="mb-3">Mevcut Yedekler</h6>
                                        <?php if (empty($backupFiles)): ?>
                                            <p class="text-muted mb-0">Henüz yedek dosyası bulunmuyor.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Dosya</th>
                                                            <th>Boyut</th>
                                                            <th>Tarih</th>
                                                            <th class="text-end">İşlem</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($backupFiles as $backup): ?>
                                                            <tr>
                                                                <td><small><?php echo htmlspecialchars($backup['filename']); ?></small></td>
                                                                <td><small><?php echo number_format($backup['size'] / 1024, 1); ?> KB</small></td>
                                                                <td><small><?php echo date('d.m.Y H:i', (int)$backup['modified']); ?></small></td>
                                                                <td class="text-end">
                                                                    <a href="settings.php?download_backup=<?php echo urlencode($backup['filename']); ?>"
                                                                       class="btn btn-sm btn-outline-secondary">
                                                                        <i class="bi bi-cloud-arrow-down"></i>
                                                                    </a>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                                        <input type="hidden" name="action" value="delete_backup">
                                                                        <input type="hidden" name="backup_filename" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                                onclick="return confirm('Bu yedek dosyası silinsin mi?');">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </form>
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
                            
                            <!-- Site Settings - 2. Sütun -->
                            <div class="col-md-6">
                                <div class="card mb-4">
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
                                                <label for="social_twitter" class="form-label">X URL</label>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 

