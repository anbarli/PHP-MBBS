<?php
/**
 * Admin Settings
 * Ayarlar sayfasi
 */

// UTF-8 encoding ayarla
header('Content-Type: text/html; charset=utf-8');

// Admin guvenlik sabiti tanimla
define('ADMIN_SECURE', true);

// Ana config dosyasini dahil et
require_once '../config.php';

// Guvenlik fonksiyonlarini dahil et
require_once '../includes/security.php';

// Session baslat ve yetki kontrolu
initSecureSession();
requireAdminAuth();

// Admin config yukle
$adminConfig = loadAdminConfig();

// Config.local.php dosyasini yukle
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

// Form gonderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolu
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Guvenlik hatasi. Lutfen tekrar deneyin.';
        $messageType = 'danger';
    } else {
        $action = sanitizeInput($_POST['action'] ?? '');
        
        if ($action === 'update_admin') {
            // Admin bilgilerini guncelle
            $newAdminUsername = sanitizeInput($_POST['admin_username'] ?? '');
            $newAdminName = sanitizeInput($_POST['admin_name'] ?? '');
            $newAdminEmail = sanitizeInput($_POST['admin_email'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validasyon
            if (empty($newAdminUsername)) {
                $message = 'Kullanici adi gereklidir.';
                $messageType = 'danger';
            } elseif (empty($newAdminName)) {
                $message = 'Admin adi gereklidir.';
                $messageType = 'danger';
            } elseif (empty($newAdminEmail) || !filter_var($newAdminEmail, FILTER_VALIDATE_EMAIL)) {
                $message = 'Gecerli bir e-posta adresi girin.';
                $messageType = 'danger';
            } else {
                $configUpdated = false;
                
                // Admin kullanici adi, adi ve e-posta guncelle
                $adminConfig['ADMIN_USERNAME'] = $newAdminUsername;
                $adminConfig['ADMIN_NAME'] = $newAdminName;
                $adminConfig['ADMIN_EMAIL'] = $newAdminEmail;
                $configUpdated = true;
                
                // Sifre degisikligi
                if (!empty($currentPassword)) {
                    if (!verifyPassword($currentPassword, $adminConfig['ADMIN_PASSWORD'])) {
                        $message = 'Mevcut sifre yanlis.';
                        $messageType = 'danger';
                    } elseif (empty($newPassword)) {
                        $message = 'Yeni sifre gereklidir.';
                        $messageType = 'danger';
                    } elseif (strlen($newPassword) < 8) {
                        $message = 'Yeni sifre en az 8 karakter olmalidir.';
                        $messageType = 'danger';
                    } elseif ($newPassword !== $confirmPassword) {
                        $message = 'Sifreler eslesmiyor.';
                        $messageType = 'danger';
                    } else {
                        $adminConfig['ADMIN_PASSWORD'] = hashPassword($newPassword);
                        $configUpdated = true;
                        $message .= ' Sifre guncellendi.';
                    }
                }
                
                if ($configUpdated) {
                    // Admin klasorunu dinamik olarak bul
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
                    
                    // Admin config dosyasini guncelle
                    if (updateAdminConfig($adminConfig)) {
                        // Config'i yeniden yukle
                        $adminConfig = loadAdminConfig();
                        $message = 'Admin ayarlari basariyla guncellendi.' . $message;
                        $messageType = 'success';
                        logAdminAction('update_admin_settings', 'Updated admin settings');
                    } else {
                        $message = 'Admin ayarlari kaydedilirken hata olustu.';
                        $messageType = 'danger';
                        logError('Admin settings update failed', ['file' => 'admin/settings.php']);
                    }
                }
            }
        } elseif ($action === 'update_site_config') {
            // Site ayarlarini guncelle
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
            
            // Debug: Dosya yolu kontrolu
            $configLocalPath = '../config.local.php';
            $configLocalExists = file_exists($configLocalPath);
            $configLocalWritable = is_writable($configLocalPath);
            
            // Config.local.php icerigini olustur
            $configContent = "<?php
// Kisisel Ayarlar - Bu dosya git'e dahil edilmez
// Kendi ayarlarinizi buraya yazin

// Temel URL ayarlari
define('BASE_PATH', '$basePath'); // Kendi dizin yapiniza gore degistirin

// Site bilgileri
define('SITE_NAME', '$siteName');
define('DEFAULT_TITLE', '$defaultTitle');
define('DEFAULT_DESCRIPTION', '$defaultDescription');

// Google Analytics ID (istege bagli)
define('GA_TRACKING_ID', '$gaTrackingId');

// Twitter kullanici adi (istege bagli)
define('TWITTER_USERNAME', '$twitterUsername');

// Yazar bilgileri
define('AUTHOR_NAME', '$authorName');
define('AUTHOR_EMAIL', '$authorEmail');

// Sosyal medya linkleri (istege bagli)
define('SOCIAL_TWITTER', '$socialTwitter');
define('SOCIAL_GITHUB', '$socialGithub');
define('SOCIAL_LINKEDIN', '$socialLinkedin');

// Cache ayarlari
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 saat

// Guvenlik ayarlari
define('ENABLE_ERROR_LOGGING', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 1800); // 30 dakika

// SEO ayarlari
define('DEFAULT_LANGUAGE', 'tr');
define('DEFAULT_LOCALE', 'tr_TR');
define('SITE_KEYWORDS', '$siteKeywords');
?>";
            
            // Config.local.php dosyasini guncelle
            if (file_put_contents($configLocalPath, $configContent)) {
                $message = 'Site ayarlari basariyla guncellendi.';
                $messageType = 'success';
                logAdminAction('update_site_config', 'Updated site configuration');
                
                // Cache'i temizle
                clearCache();
            } else {
                $message = 'Site ayarlari kaydedilirken hata olustu.';
                $messageType = 'danger';
                logError('Site config write failed', ['file' => $configLocalPath]);
            }
        } elseif ($action === 'clear_cache') {
            // Cache temizle
            if (clearCache()) {
                $message = 'Cache basariyla temizlendi.';
                $messageType = 'success';
                logAdminAction('clear_cache', 'Cleared application cache');
            } else {
                $message = 'Cache temizlenirken hata olustu.';
                $messageType = 'danger';
            }
        } elseif ($action === 'generate_sitemap') {
            // Sitemap olustur
            if (generateSitemap()) {
                $message = 'Sitemap basariyla olusturuldu.';
                $messageType = 'success';
                logAdminAction('generate_sitemap', 'Generated sitemap');
            } else {
                $message = 'Sitemap olusturulurken hata olustu.';
                $messageType = 'danger';
            }
        } elseif ($action === 'regenerate_cache') {
            // Cache'i yeniden olustur
            if (clearCache()) {
                // Cache'i yeniden olustur
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
                $message = 'Cache basariyla yeniden olusturuldu. Yazi sayisi: ' . count($postFilesWithDates);
                $messageType = 'success';
                logAdminAction('regenerate_cache', 'Regenerated cache with ' . count($postFilesWithDates) . ' posts');
            } else {
                $message = 'Cache yeniden olusturulurken hata olustu.';
                $messageType = 'danger';
            }
        } elseif ($action === 'create_backup') {
            if (!class_exists('ZipArchive')) {
                $message = 'ZipArchive destegi bulunamadi. Yedek olusturulamadi.';
                $messageType = 'danger';
            } else {
                if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
                    $message = 'Yedek klasoru olusturulamadi.';
                    $messageType = 'danger';
                } else {
                    $backupFile = 'backup-' . date('Ymd-His') . '.zip';
                    $backupPath = $backupDir . $backupFile;

                    $zip = new ZipArchive();
                    if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                        $message = 'Yedek dosyasi olusturulamadi.';
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
                        $message = 'Yedek olusturuldu: ' . $backupFile . ' (' . $addedCount . ' dosya).';
                        $messageType = 'success';
                        logAdminAction('create_backup', 'Created backup: ' . $backupFile);
                    }
                }
            }
        } elseif ($action === 'restore_backup') {
            if (!class_exists('ZipArchive')) {
                $message = 'ZipArchive destegi bulunamadi. Geri yukleme yapilamadi.';
                $messageType = 'danger';
            } elseif (!isset($_FILES['backup_file']) || ($_FILES['backup_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $message = 'Lutfen gecerli bir yedek dosyasi secin.';
                $messageType = 'danger';
            } else {
                $uploadName = strtolower((string)($_FILES['backup_file']['name'] ?? ''));
                $uploadTmp = (string)($_FILES['backup_file']['tmp_name'] ?? '');
                $uploadSize = (int)($_FILES['backup_file']['size'] ?? 0);

                if (pathinfo($uploadName, PATHINFO_EXTENSION) !== 'zip') {
                    $message = 'Sadece .zip uzantili yedek dosyalari kabul edilir.';
                    $messageType = 'danger';
                } elseif ($uploadSize <= 0 || $uploadSize > 50 * 1024 * 1024) {
                    $message = 'Yedek dosyasi boyutu gecersiz (maksimum 50MB).';
                    $messageType = 'danger';
                } else {
                    $zip = new ZipArchive();
                    if ($zip->open($uploadTmp) !== true) {
                        $message = 'Yedek dosyasi acilamadi.';
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
                        $message = 'Geri yukleme tamamlandi. Guncellenen dosya sayisi: ' . $restoredCount;
                        $messageType = $restoredCount > 0 ? 'success' : 'warning';
                        logAdminAction('restore_backup', 'Restored backup file upload, files: ' . $restoredCount);
                    }
                }
            }
        } elseif ($action === 'delete_backup') {
            $backupFilename = basename((string)($_POST['backup_filename'] ?? ''));
            if (!isValidBackupFilename($backupFilename)) {
                $message = 'Gecersiz yedek dosyasi adi.';
                $messageType = 'danger';
            } else {
                $targetBackup = $backupDir . $backupFilename;
                if (!is_file($targetBackup)) {
                    $message = 'Yedek dosyasi bulunamadi.';
                    $messageType = 'danger';
                } elseif (@unlink($targetBackup)) {
                    $message = 'Yedek dosyasi silindi: ' . $backupFilename;
                    $messageType = 'success';
                    logAdminAction('delete_backup', 'Deleted backup: ' . $backupFilename);
                } else {
                    $message = 'Yedek dosyasi silinemedi.';
                    $messageType = 'danger';
                }
            }
        }
    }
}

// CSRF token olustur
$csrfToken = generateCSRFToken();
$backupFiles = listBackupFiles($backupDir);
$backupCount = count($backupFiles);
$backupDirWritable = is_dir($backupDir) && is_writable($backupDir);
$configLocalWritable = $configLocalExists ? is_writable($configLocalPath) : is_writable(dirname($configLocalPath));
$zipArchiveAvailable = class_exists('ZipArchive');

// Mevcut site ayarlarini al
$currentSiteConfig = [];
if ($configLocalExists) {
    // Config.local.php'den mevcut degerleri al
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
                            <i class="bi bi-file-text"></i> Yazilar
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="bi bi-folder"></i> Kategoriler
                        </a>
                        <a class="nav-link active" href="settings.php">
                            <i class="bi bi-gear"></i> Ayarlar
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="../" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Siteyi Goruntule
                        </a>
                        <form method="POST" action="dashboard.php" class="sidebar-logout-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="nav-link btn btn-link nav-link-logout">
                                <i class="bi bi-box-arrow-right"></i> Cikis Yap
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
                                    <i class="bi bi-gear"></i> Yonetim
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
                        <div class="settings-page-intro mb-4">
                            <h5 class="mb-1">Ayar Merkezi</h5>
                            <p class="text-muted mb-0">Hesap bilgileri, site konfigurasyonu ve bakim islemlerini tek ekrandan yonetebilirsiniz.</p>
                        </div>

                        <div class="row g-3 mb-4 settings-status-grid">
                            <div class="col-sm-6 col-xl-3">
                                <div class="settings-status-card">
                                    <small class="text-muted">Config Durumu</small>
                                    <div class="fw-semibold mt-1">
                                        <?php if ($configLocalExists): ?>
                                            <span class="badge bg-success">config.local.php var</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">config.local.php yok</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="settings-status-card">
                                    <small class="text-muted">Yazma Izni</small>
                                    <div class="fw-semibold mt-1">
                                        <?php if ($configLocalWritable): ?>
                                            <span class="badge bg-success">Config yazilabilir</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Config yazilamiyor</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="settings-status-card">
                                    <small class="text-muted">Yedekler</small>
                                    <div class="fw-semibold mt-1">
                                        <span class="badge bg-primary"><?php echo $backupCount; ?> dosya</span>
                                        <?php if (!$backupDirWritable): ?>
                                            <span class="badge bg-danger ms-1">Klasor yazilamiyor</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="settings-status-card">
                                    <small class="text-muted">ZipArchive</small>
                                    <div class="fw-semibold mt-1">
                                        <?php if ($zipArchiveAvailable): ?>
                                            <span class="badge bg-success">Hazir</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Eksik</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 settings-layout">
                            <!-- Admin Settings - 1. Sutun -->
                            <div class="col-lg-7">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-person-gear"></i> Admin Bilgileri
                                        </h5>
                                        <small class="text-muted">Kullanici bilgileri ve sifre islemleri</small>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="update_admin">
                                            
                                            <div class="mb-3">
                                                <label for="admin_username" class="form-label">Kullanici Adi</label>
                                                <input type="text" class="form-control" id="admin_username" name="admin_username" 
                                                       value="<?php echo htmlspecialchars($adminConfig['ADMIN_USERNAME'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="admin_name" class="form-label">Admin Adi</label>
                                                <input type="text" class="form-control" id="admin_name" name="admin_name" 
                                                       value="<?php echo htmlspecialchars($adminConfig['ADMIN_NAME'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="admin_email" class="form-label">E-posta</label>
                                                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                                       value="<?php echo htmlspecialchars($adminConfig['ADMIN_EMAIL'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <hr>
                                            <h6>Sifre Degistir</h6>
                                            <small class="text-muted">Sifrenizi degistirmek istemiyorsaniz bos birakin.</small>
                                            
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Mevcut Sifre</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">Yeni Sifre</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                                       minlength="8">
                                                <div class="form-text">En az 8 karakter</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Yeni Sifre (Tekrar)</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                       minlength="8">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle"></i> Guncelle
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card mt-4 settings-ops-card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-sliders"></i> Sistem Islemleri
                                        </h5>
                                        <small class="text-muted">Bakim araclari ve yedekleme adimlari</small>
                                    </div>
                                    <div class="card-body">
                                        <ul class="nav nav-tabs settings-ops-tabs mb-3" id="settingsOpsTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="ops-maintenance-tab" data-bs-toggle="tab" data-bs-target="#ops-maintenance-pane" type="button" role="tab" aria-controls="ops-maintenance-pane" aria-selected="true">
                                                    Bakim
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="ops-backup-tab" data-bs-toggle="tab" data-bs-target="#ops-backup-pane" type="button" role="tab" aria-controls="ops-backup-pane" aria-selected="false">
                                                    Yedekleme
                                                </button>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="settingsOpsTabContent">
                                            <div class="tab-pane fade show active" id="ops-maintenance-pane" role="tabpanel" aria-labelledby="ops-maintenance-tab" tabindex="0">
                                                <div class="accordion" id="maintenanceAccordion">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="maintenanceHeadingOne">
                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#maintenanceCollapseOne" aria-expanded="true" aria-controls="maintenanceCollapseOne">
                                                                Hizli Bakim Araclari
                                                            </button>
                                                        </h2>
                                                        <div id="maintenanceCollapseOne" class="accordion-collapse collapse show" aria-labelledby="maintenanceHeadingOne" data-bs-parent="#maintenanceAccordion">
                                                            <div class="accordion-body">
                                                                <div class="d-grid gap-2">
                                                                    <form method="POST">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                                        <input type="hidden" name="action" value="clear_cache">
                                                                        <button type="submit" class="btn btn-outline-warning w-100">
                                                                            <i class="bi bi-trash"></i> Cache Temizle
                                                                        </button>
                                                                    </form>

                                                                    <form method="POST">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                                        <input type="hidden" name="action" value="generate_sitemap">
                                                                        <button type="submit" class="btn btn-outline-info w-100">
                                                                            <i class="bi bi-sitemap"></i> Sitemap Olustur
                                                                        </button>
                                                                    </form>

                                                                    <form method="POST">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                                        <input type="hidden" name="action" value="regenerate_cache">
                                                                        <button type="submit" class="btn btn-outline-success w-100">
                                                                            <i class="bi bi-arrow-clockwise"></i> Cache Yeniden Olustur
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="ops-backup-pane" role="tabpanel" aria-labelledby="ops-backup-tab" tabindex="0">
                                                <div class="accordion" id="backupAccordion">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="backupHeadingOne">
                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#backupCollapseOne" aria-expanded="true" aria-controls="backupCollapseOne">
                                                                Yedek Olustur ve Geri Yukle
                                                            </button>
                                                        </h2>
                                                        <div id="backupCollapseOne" class="accordion-collapse collapse show" aria-labelledby="backupHeadingOne" data-bs-parent="#backupAccordion">
                                                            <div class="accordion-body">
                                                                <form method="POST" class="mb-3">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                                    <input type="hidden" name="action" value="create_backup">
                                                                    <button type="submit" class="btn btn-outline-primary w-100">
                                                                        <i class="bi bi-download"></i> Yeni Yedek Olustur
                                                                    </button>
                                                                    <div class="form-text mt-2">
                                                                        <code>posts/*.md</code>, <code>config.local.php</code> ve <code>admin/admin.env</code> tek zip icinde saklanir.
                                                                    </div>
                                                                </form>

                                                                <form method="POST" enctype="multipart/form-data">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                                    <input type="hidden" name="action" value="restore_backup">
                                                                    <label for="backup_file" class="form-label">Yedek Dosyasindan Geri Yukle (.zip)</label>
                                                                    <input type="file" class="form-control mb-2" id="backup_file" name="backup_file" accept=".zip" required>
                                                                    <button type="submit" class="btn btn-outline-danger w-100"
                                                                            onclick="return confirm('Secilen yedek dosyasindaki icerikler mevcut dosyalarin uzerine yazilacaktir. Devam edilsin mi?');">
                                                                        <i class="bi bi-arrow-counterclockwise"></i> Geri Yuklemeyi Baslat
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="backupHeadingTwo">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#backupCollapseTwo" aria-expanded="false" aria-controls="backupCollapseTwo">
                                                                Mevcut Yedekler (<?php echo $backupCount; ?>)
                                                            </button>
                                                        </h2>
                                                        <div id="backupCollapseTwo" class="accordion-collapse collapse" aria-labelledby="backupHeadingTwo" data-bs-parent="#backupAccordion">
                                                            <div class="accordion-body">
                                                                <?php if (empty($backupFiles)): ?>
                                                                    <p class="text-muted mb-0">Henuz yedek dosyasi bulunmuyor.</p>
                                                                <?php else: ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm align-middle">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Dosya</th>
                                                                                    <th>Boyut</th>
                                                                                    <th>Tarih</th>
                                                                                    <th class="text-end">Islem</th>
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
                                                                                                        onclick="return confirm('Bu yedek dosyasi silinsin mi?');">
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Site Settings - 2. Sutun -->
                            <div class="col-lg-5">
                                <div class="settings-sticky-stack">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-globe"></i> Site Ayarlari
                                        </h5>
                                        <small class="text-muted">SEO, yazar bilgileri ve sosyal medya adresleri</small>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="update_site_config">
                                            
                                            <div class="mb-3">
                                                <label for="site_name" class="form-label">Site Adi</label>
                                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['site_name'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="default_title" class="form-label">Varsayilan Baslik</label>
                                                <input type="text" class="form-control" id="default_title" name="default_title" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['default_title'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="default_description" class="form-label">Varsayilan Aciklama</label>
                                                <textarea class="form-control" id="default_description" name="default_description" rows="3"><?php echo htmlspecialchars($currentSiteConfig['default_description'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="author_name" class="form-label">Yazar Adi</label>
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
                                                <div class="form-text">Ornek: /blog/ veya /</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="site_keywords" class="form-label">Site Anahtar Kelimeleri</label>
                                                <input type="text" class="form-control" id="site_keywords" name="site_keywords" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['site_keywords'] ?? ''); ?>">
                                                <div class="form-text">Virgulle ayirarak yazin</div>
                                            </div>
                                            
                                            <hr>
                                            <h6>Sosyal Medya</h6>
                                            
                                            <div class="mb-3">
                                                <label for="ga_tracking_id" class="form-label">Google Analytics ID</label>
                                                <input type="text" class="form-control" id="ga_tracking_id" name="ga_tracking_id" 
                                                       value="<?php echo htmlspecialchars($currentSiteConfig['ga_tracking_id'] ?? ''); ?>">
                                                <div class="form-text">Ornek: G-XXXXXXXXXX</div>
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
                                                <i class="bi bi-check-circle"></i> Site Ayarlarini Guncelle
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 


