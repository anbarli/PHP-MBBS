<?php
// ÖRNEK: config.local.php dosyası
// Bu dosyayı config.local.php olarak kopyalayın ve kendi ayarlarınızı yazın
// config.local.php dosyası git'e dahil edilmez (güvenlik için)

// Temel URL ayarları - Otomatik algılama
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '/') {
    $basePath = '/';
} else {
    $basePath = rtrim($basePath, '/') . '/';
}
define('BASE_PATH', $basePath);
// Örnekler:
// define('BASE_PATH', '/'); // Ana dizinde ise
// define('BASE_PATH', '/my-blog/'); // Alt dizinde ise

// Site bilgileri
define('SITE_NAME', 'Benim Blogum');
define('DEFAULT_TITLE', 'Benim Blogum - Teknoloji ve Yazılım');
define('DEFAULT_DESCRIPTION', 'Blogumda yayınladığım teknoloji ve yazılıma dair yazılar!');

// Google Analytics ID (isteğe bağlı)
// define('GA_TRACKING_ID', 'G-XXXXXXXXXX'); // Kendi GA ID'nizi yazın

// Twitter kullanıcı adı (isteğe bağlı)
// define('TWITTER_USERNAME', '@kullaniciadi');

// Yazar bilgileri
define('AUTHOR_NAME', 'Blog Yazarı');
// define('AUTHOR_EMAIL', 'email@example.com');

// Sosyal medya linkleri (isteğe bağlı)
// define('SOCIAL_TWITTER', 'https://twitter.com/kullaniciadi');
// define('SOCIAL_GITHUB', 'https://github.com/kullaniciadi');
// define('SOCIAL_LINKEDIN', 'https://linkedin.com/in/kullaniciadi');

// Cache ayarları
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 saat (saniye cinsinden)

// Güvenlik ayarları
define('ENABLE_ERROR_LOGGING', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 1800); // 30 dakika

// SEO ayarları
define('DEFAULT_LANGUAGE', 'tr');
define('DEFAULT_LOCALE', 'tr_TR');
define('SITE_KEYWORDS', 'teknoloji, yazılım, blog, php, markdown');
?> 