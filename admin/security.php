<?php
/**
 * Security Functions for Admin Panel
 * Güvenlik fonksiyonları
 */

// Prevent direct access
if (!defined('ADMIN_SECURE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Session başlatma ve güvenlik ayarları
 */
function initSecureSession() {
    // Session başlamadan önce güvenli session ayarları
    if (session_status() === PHP_SESSION_NONE) {
        // Güvenli session ayarları
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Session başlat
        session_start();
    }
    
    // Session hijacking koruması
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        session_destroy();
        return false;
    }
    
    // Session timeout kontrolü (2 saat)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * CSRF token oluşturma
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrulama
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Basit şifre hash'leme (GÜVENLİ DEĞİLDİR!)
 * Sadece uyumluluk/test amaçlı md5 kullanılır.
 */
function hashPassword($password) {
    return md5($password);
}

/**
 * Basit şifre doğrulama (GÜVENLİ DEĞİLDİR!)
 */
function verifyPassword($password, $hash) {
    return md5($password) === $hash;
}

/**
 * Güvenli dosya upload kontrolü
 */
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'], $maxSize = 5242880) {
    $errors = [];
    
    // Dosya yükleme hatası kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Dosya yükleme hatası: ' . $file['error'];
        return $errors;
    }
    
    // Dosya boyutu kontrolü
    if ($file['size'] > $maxSize) {
        $errors[] = 'Dosya boyutu çok büyük. Maksimum: ' . formatBytes($maxSize);
    }
    
    // Dosya türü kontrolü
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, $allowedTypes)) {
        $errors[] = 'Geçersiz dosya türü. İzin verilen: ' . implode(', ', $allowedTypes);
    }
    
    // MIME type kontrolü
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];
    
    if (!isset($allowedMimes[$extension]) || $allowedMimes[$extension] !== $mimeType) {
        $errors[] = 'Geçersiz MIME türü';
    }
    
    return $errors;
}

/**
 * Güvenli dosya adı oluşturma
 */
function generateSafeFileName($originalName, $extension) {
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    return $timestamp . '_' . $random . '.' . $extension;
}

/**
 * XSS koruması için input temizleme
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Rate limiting kontrolü
 */
function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
    }
    
    $rateData = $_SESSION[$key];
    
    // Zaman penceresi kontrolü
    if (time() - $rateData['first_attempt'] > $timeWindow) {
        $_SESSION[$key] = ['attempts' => 1, 'first_attempt' => time()];
        return true;
    }
    
    // Deneme sayısı kontrolü
    if ($rateData['attempts'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$key]['attempts']++;
    return true;
}

/**
 * Güvenli log kaydı
 */
function logAdminAction($action, $details = '', $userId = null) {
    $logFile = __DIR__ . '/../logs/admin.log';
    $logDir = dirname($logFile);
    
    // Log dizini oluştur
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $userId = $userId ?? ($_SESSION['user_id'] ?? 'unknown');
    
    $logEntry = sprintf(
        "[%s] User: %s | IP: %s | Action: %s | Details: %s | UA: %s\n",
        $timestamp,
        $userId,
        $ip,
        $action,
        $details,
        $userAgent
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Admin yetki kontrolü
 */
function requireAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
    
    // Session güvenlik kontrolü
    if (!initSecureSession()) {
        session_destroy();
        header('Location: login.php?error=session_expired');
        exit;
    }
}

/**
 * Güvenli çıkış
 */
function secureLogout() {
    // Log kaydı
    logAdminAction('logout', 'User logged out');
    
    // Session temizleme
    $_SESSION = array();
    
    // Session cookie silme
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Dosya boyutu formatı
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Güvenli redirect
 */
function secureRedirect($url) {
    // URL doğrulama
    if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9\/\-_\.]+$/', $url)) {
        $url = 'dashboard.php';
    }
    
    header("Location: $url");
    exit;
}

/**
 * Admin config dosyası kontrolü
 */
function loadAdminConfig() {
    // Admin klasörünü dinamik olarak bul
    $adminDir = dirname(__DIR__) . '/admin';
    
    $envFile = $adminDir . '/admin.env';
    
    // Varsayılan config
    $config = [
        'ADMIN_USERNAME' => 'admin',
        'ADMIN_PASSWORD' => hashPassword('yeni_sifreniz123'),
        'ADMIN_EMAIL' => 'admin@example.com',
        'ADMIN_NAME' => 'Admin',
        'SESSION_TIMEOUT' => 7200,
        'MAX_UPLOAD_SIZE' => 5242880,
        'ALLOWED_FILE_TYPES' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ];
    
    // admin.env dosyasını oku
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        $lines = explode("\n", $envContent);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Yorum satırlarını ve boş satırları atla
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // KEY=VALUE formatını parse et
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Değeri config'e ekle
                switch ($key) {
                    case 'ADMIN_USERNAME':
                        $config['ADMIN_USERNAME'] = $value;
                        break;
                    case 'ADMIN_PASSWORD':
                        $config['ADMIN_PASSWORD'] = $value;
                        break;
                    case 'ADMIN_EMAIL':
                        $config['ADMIN_EMAIL'] = $value;
                        break;
                    case 'ADMIN_NAME':
                        $config['ADMIN_NAME'] = $value;
                        break;
                    case 'SESSION_TIMEOUT':
                        $config['SESSION_TIMEOUT'] = (int)$value;
                        break;
                    case 'MAX_UPLOAD_SIZE':
                        $config['MAX_UPLOAD_SIZE'] = (int)$value;
                        break;
                    case 'ALLOWED_FILE_TYPES':
                        $config['ALLOWED_FILE_TYPES'] = explode(',', $value);
                        break;
                }
            }
        }
    }
    
    return $config;
}

/**
 * Varsayılan kimlik bilgilerini kontrol et
 */
function checkDefaultCredentials($username, $password) {
    $defaultUsernames = ['admin', 'administrator', 'root', 'user', 'test'];
    $defaultPasswords = [
        'admin', 'admin123', 'password', '123456', '12345678', 'qwerty', 
        'abc123', 'password123', 'admin123456', 'root', 'test', 'guest',
        '1234', '12345', '123456789', 'letmein', 'welcome', 'monkey',
        'dragon', 'master', 'hello', 'freedom', 'whatever', 'qwerty123'
    ];
    
    $isDefaultUsername = in_array(strtolower($username), array_map('strtolower', $defaultUsernames));
    $isDefaultPassword = in_array(strtolower($password), array_map('strtolower', $defaultPasswords));
    
    return [
        'is_default' => $isDefaultUsername || $isDefaultPassword,
        'username_issue' => $isDefaultUsername,
        'password_issue' => $isDefaultPassword
    ];
}

/**
 * Şifre güvenlik kontrolü
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    // Minimum uzunluk kontrolü
    if (strlen($password) < 8) {
        $errors[] = 'Şifre en az 8 karakter olmalıdır.';
    }
    
    // Büyük harf kontrolü
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Şifre en az bir büyük harf içermelidir.';
    }
    
    // Küçük harf kontrolü
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Şifre en az bir küçük harf içermelidir.';
    }
    
    // Rakam kontrolü
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Şifre en az bir rakam içermelidir.';
    }
    
    // Özel karakter kontrolü
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = 'Şifre en az bir özel karakter içermelidir (!@#$%^&* vb.).';
    }
    
    // Basit şifre kontrolü
    $simplePasswords = [
        'password', '123456', '12345678', 'qwerty', 'abc123', 'password123',
        'admin', 'admin123', 'root', 'test', 'guest', '1234', '12345',
        'letmein', 'welcome', 'monkey', 'dragon', 'master', 'hello',
        'freedom', 'whatever', 'qwerty123', 'admin123456'
    ];
    
    if (in_array(strtolower($password), $simplePasswords)) {
        $errors[] = 'Bu şifre çok yaygın, daha güçlü bir şifre seçin.';
    }
    
    // Ardışık karakter kontrolü
    if (preg_match('/(.)\1{2,}/', $password)) {
        $errors[] = 'Şifre aynı karakterin 3 veya daha fazla tekrarını içermemelidir.';
    }
    
    // Ardışık sayı kontrolü
    if (preg_match('/123|234|345|456|567|678|789|012/', $password)) {
        $errors[] = 'Şifre ardışık sayılar içermemelidir (123, 234 vb.).';
    }
    
    return $errors;
}

/**
 * Kullanıcı adı güvenlik kontrolü
 */
function validateUsername($username) {
    $errors = [];
    
    // Minimum uzunluk kontrolü
    if (strlen($username) < 3) {
        $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
    }
    
    // Maksimum uzunluk kontrolü
    if (strlen($username) > 20) {
        $errors[] = 'Kullanıcı adı en fazla 20 karakter olabilir.';
    }
    
    // Karakter kontrolü
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors[] = 'Kullanıcı adı sadece harf, rakam, tire (-) ve alt çizgi (_) içerebilir.';
    }
    
    // Varsayılan kullanıcı adı kontrolü
    $defaultUsernames = ['admin', 'administrator', 'root', 'user', 'test', 'guest'];
    if (in_array(strtolower($username), $defaultUsernames)) {
        $errors[] = 'Bu kullanıcı adı çok yaygın, daha güvenli bir kullanıcı adı seçin.';
    }
    
    return $errors;
}

/**
 * Admin config güncelleme
 */
function updateAdminConfig($newConfig) {
    // Admin klasörünü dinamik olarak bul
    $adminDir = dirname(__DIR__) . '/admin';
    if (!is_dir($adminDir)) {
        // admin1, admin2 gibi alternatif isimleri dene
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
    
    // Debug bilgisi
    error_log("updateAdminConfig: Admin dizini: " . basename($adminDir));
    error_log("updateAdminConfig: Dosya yolu: " . basename($envFile));
    error_log("updateAdminConfig: Dosya mevcut: " . (file_exists($envFile) ? 'Evet' : 'Hayır'));
    error_log("updateAdminConfig: Dosya yazılabilir: " . (is_writable($envFile) ? 'Evet' : 'Hayır'));
    error_log("updateAdminConfig: Güncellenecek anahtar sayısı: " . count($newConfig));
    
    // Mevcut admin.env dosyasını oku
    if (!file_exists($envFile)) {
        error_log("updateAdminConfig: admin.env dosyası bulunamadı");
        return false;
    }
    
    if (!is_writable($envFile)) {
        error_log("updateAdminConfig: admin.env dosyası yazılabilir değil");
        return false;
    }
    
    $envContent = file_get_contents($envFile);
    if ($envContent === false) {
        error_log("updateAdminConfig: admin.env dosyası okunamadı");
        return false;
    }
    
    $lines = explode("\n", $envContent);
    $updatedLines = [];
    
    // Mevcut satırları işle
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Yorum satırlarını ve boş satırları koru
        if (empty($line) || strpos($line, '#') === 0) {
            $updatedLines[] = $line;
            continue;
        }
        
        // KEY=VALUE formatını kontrol et
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            
            // Güncellenecek anahtar var mı?
            $updated = false;
            foreach ($newConfig as $newKey => $newValue) {
                // Anahtar zaten doğru formatta (ADMIN_NAME, ADMIN_EMAIL)
                error_log("updateAdminConfig: Karşılaştırma - Mevcut: '$key', Aranan: '$newKey'");
                if ($key === $newKey) {
                    // Değeri güncelle
                    if (is_array($newValue)) {
                        $updatedLines[] = $key . '=' . implode(',', $newValue);
                    } else {
                        $updatedLines[] = $key . '=' . $newValue;
                    }
                    $updated = true;
                    error_log("updateAdminConfig: $key güncellendi: $newValue");
                    break;
                }
            }
            
            // Güncellenmediyse mevcut satırı koru
            if (!$updated) {
                $updatedLines[] = $line;
            }
        } else {
            $updatedLines[] = $line;
        }
    }
    
    // Yeni içeriği dosyaya yaz
    $newContent = implode("\n", $updatedLines);
    $result = file_put_contents($envFile, $newContent);
    
    if ($result === false) {
        error_log("updateAdminConfig: Dosya yazma hatası");
        return false;
    }
    
    error_log("updateAdminConfig: Başarılı - $result byte yazıldı");
    return true;
}
?> 