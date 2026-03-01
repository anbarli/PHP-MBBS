<?php
/**
 * Admin Login Page
 * Guvenli admin giris sayfasi
 */

ini_set('display_errors', 0);

define('ADMIN_SECURE', true);
header('Content-Type: text/html; charset=utf-8');

require_once '../config.php';
require_once '../includes/security.php';

initSecureSession();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    secureRedirect('dashboard.php');
}

$error = '';
$success = '';
$adminConfig = loadAdminConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'G&uuml;venlik hatas&#305;. L&uuml;tfen tekrar deneyin.';
    } else {
        if (!checkRateLimit('login', 5, 300)) {
            $error = '&#199;ok fazla deneme. L&uuml;tfen 5 dakika bekleyin.';
        } else {
            $username = sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Kullan&#305;c&#305; ad&#305; ve &#351;ifre gereklidir.';
            } else {
                if ($username === $adminConfig['ADMIN_USERNAME'] && verifyPassword($password, $adminConfig['ADMIN_PASSWORD'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['user_id'] = $username;
                    $_SESSION['login_time'] = time();

                    logAdminAction('login', 'Successful login');
                    secureRedirect('dashboard.php');
                } else {
                    $error = 'Ge&ccedil;ersiz kullan&#305;c&#305; ad&#305; veya &#351;ifre.';
                    logAdminAction('login_failed', "Failed login attempt for username: $username");
                }
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giri&#351; - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo assetPath('includes/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo assetPath('admin/admin.css'); ?>">
</head>
<body class="admin-login">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <h3><i class="bi bi-shield-lock"></i> Admin Giri&#351;</h3>
                        <p class="mb-0"><?php echo SITE_NAME; ?> Y&ouml;netim Paneli</p>
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
                                <label for="username" class="form-label">Kullan&#305;c&#305; Ad&#305;</label>
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
                                <label for="password" class="form-label">&#350;ifre</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Giri&#351; Yap
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="<?php echo BASE_PATH; ?>" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Siteye D&ouml;n
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="bi bi-shield-check"></i> G&uuml;venli Ba&#287;lant&#305;
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                e.preventDefault();
                alert('L\u00fctfen t\u00fcm alanlar\u0131 doldurun.');
                return false;
            }
        });

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
