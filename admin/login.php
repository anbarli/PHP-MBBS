<?php
/**
 * Admin Login Page
 * Güvenli admin giriş sayfası
 */

// Hata raporlama (geliştirme için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ADMIN_SECURE', true);

// Ana config dosyasını dahil et
require_once '../config.php';

// Güvenlik fonksiyonlarını dahil et
require_once '../includes/security.php';

// Session başlat
initSecureSession();

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    secureRedirect('dashboard.php');
}

$error = '';
$success = '';

// Admin config yükle
$adminConfig = loadAdminConfig();

// Klasör adı kontrolü
$currentDir = basename(__DIR__);
if ($currentDir === 'admin') {
    die('<div style="color:red;font-family:monospace;font-size:1.2em;margin:2em auto;text-align:center;max-width:500px;">
    <b>Güvenlik Uyarısı:</b><br>
    Yönetim paneli klasörünüzün adı <b>admin</b> olarak bırakılmış.<br>
    Lütfen bu klasörün adını <b>rastgele ve tahmin edilmesi zor</b> bir isimle değiştirin.<br>
    <br>
    Örnek: <code>my-secret-panel-8f3d2</code><br>
    <br>
    <b>Paneli kullanabilmek için klasör adını değiştirmeniz zorunludur.</b>
    </div>');
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        // Rate limiting kontrolü
        if (!checkRateLimit('login', 5, 300)) {
            $error = 'Çok fazla deneme. Lütfen 5 dakika bekleyin.';
        } else {
            $username = sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Boş alan kontrolü
            if (empty($username) || empty($password)) {
                $error = 'Kullanıcı adı ve şifre gereklidir.';
            } else {
                // Kullanıcı doğrulama
                if ($username === $adminConfig['admin_username'] && 
                    verifyPassword($password, $adminConfig['admin_password'])) {
                    
                    // Giriş başarılı
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['user_id'] = $username;
                    $_SESSION['login_time'] = time();
                    
                    // Log kaydı
                    logAdminAction('login', 'Successful login');
                    
                    // Dashboard'a yönlendir
                    secureRedirect('dashboard.php');
                } else {
                    // Giriş başarısız
                    $error = 'Geçersiz kullanıcı adı veya şifre.';
                    logAdminAction('login_failed', "Failed login attempt for username: $username");
                }
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
    <title>Admin Giriş - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .input-group-text {
            background: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus + .input-group-text {
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <h3><i class="bi bi-shield-lock"></i> Admin Giriş</h3>
                        <p class="mb-0"><?php echo SITE_NAME; ?> Yönetim Paneli</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                           required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Şifre</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="<?php echo BASE_PATH; ?>" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Siteye Dön
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="bi bi-shield-check"></i> Güvenli Bağlantı
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form güvenliği
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Lütfen tüm alanları doldurun.');
                return false;
            }
        });
        
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