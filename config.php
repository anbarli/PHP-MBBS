<?php
// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Temel URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$host = htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'localhost', ENT_QUOTES, 'UTF-8');

// Kişisel ayarları dahil et
if (file_exists(__DIR__ . '/config.local.php')) {
    include_once __DIR__ . '/config.local.php';
} else {
    // Varsayılan ayarlar (config.local.php yoksa)
    define('BASE_PATH', '/blog/');
    define('SITE_NAME', 'Blog');
    define('DEFAULT_TITLE', 'Blog - Teknoloji ve Yazılım');
    define('DEFAULT_DESCRIPTION', 'Blog yazıları');
    define('GA_TRACKING_ID', '');
    define('TWITTER_USERNAME', '');
    define('AUTHOR_NAME', 'Blog Yazarı');
    define('AUTHOR_EMAIL', '');
    define('SOCIAL_TWITTER', '');
    define('SOCIAL_GITHUB', '');
    define('SOCIAL_LINKEDIN', '');
    define('CACHE_ENABLED', true);
    define('CACHE_DURATION', 3600);
    define('ENABLE_ERROR_LOGGING', true);
    define('MAX_LOGIN_ATTEMPTS', 5);
    define('SESSION_TIMEOUT', 1800);
    define('DEFAULT_LANGUAGE', 'tr');
    define('DEFAULT_LOCALE', 'tr_TR');
    define('SITE_KEYWORDS', 'blog, yazılım, teknoloji');
}

// Sistem sabitleri
define('POSTS_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR);
define('CACHE_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);
define('BASE_URL', $protocol . $host . BASE_PATH);

// Ensure cache directory exists
if (!is_dir(CACHE_DIR)) {
    if (!mkdir(CACHE_DIR, 0755, true)) {
        error_log("Failed to create cache directory: " . CACHE_DIR);
    }
}

// Ensure cache directory is writable
if (!is_writable(CACHE_DIR)) {
    error_log("Cache directory is not writable: " . CACHE_DIR);
}

// Prevent direct access to config file
if (basename($_SERVER['SCRIPT_NAME']) === 'config.php') {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access not allowed.");
}

function generateSlug($string) {
    // Türkçe karakterleri dönüştür
    $string = mb_strtolower($string, 'UTF-8');
    $string = str_replace(
        ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'],
        ['c', 'g', 'i', 'o', 's', 'u'],
        $string
    );

    // Harf ve rakam dışındaki karakterleri tire ile değiştir
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return trim($string, '-');
}

function getCachedPosts() {
    if (!CACHE_ENABLED) {
        return null;
    }
    
    $cacheFile = CACHE_DIR . 'posts.json';
    $cacheTime = CACHE_DURATION;
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached !== null) {
            return $cached;
        }
    }
    
    return null;
}

function setCachedPosts($posts) {
    if (!CACHE_ENABLED) {
        return;
    }
    
    $cacheFile = CACHE_DIR . 'posts.json';
    file_put_contents($cacheFile, json_encode($posts));
}

function getPostContent($filePath) {
    // Check if file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return null;
    }
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        return null;
    }

    // Meta verileri ayırmak için düzenli ifade
    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
        $metaRaw = trim($matches[1]);
        $body = trim($matches[2]);

        // Meta verilerini ayrıştır
        $meta = [];
        foreach (explode("\n", $metaRaw) as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Etiketler için özel işlem
                if ($key === 'tags') {
                    // [tag1, tag2, tag3] formatını temizle
                    $value = trim($value, '[]');
                    $meta[$key] = array_map('trim', explode(',', $value));
                } else {
                    $meta[$key] = $value;
                }
            }
        }

        return [
            'meta' => $meta,
            'content' => $body
        ];
    }

    return null;
}

function logError($message, $context = []) {
    if (!ENABLE_ERROR_LOGGING) {
        return;
    }
    
    $logFile = CACHE_DIR . 'error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message";
    if (!empty($context)) {
        $logEntry .= " - " . json_encode($context);
    }
    $logEntry .= PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function validateInput($input, $type = 'string') {
    switch ($type) {
        case 'slug':
            return preg_match('/^[a-zA-Z0-9_-]+$/', $input) ? $input : '';
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : '';
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL) ? $input : '';
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

function getPostCount() {
    $posts = array_diff(scandir(POSTS_DIR), array('..', '.'));
    return count($posts);
}

function getCategoryCount() {
    $categories = [];
    $postFiles = array_diff(scandir(POSTS_DIR), array('..', '.'));
    
    foreach ($postFiles as $post) {
        if (pathinfo($post, PATHINFO_EXTENSION) === 'md') {
            $postFile = POSTS_DIR . $post;
            $postData = getPostContent($postFile);
            
            if ($postData && isset($postData['meta']['category'])) {
                $category = trim($postData['meta']['category']);
                if (!empty($category) && !in_array($category, $categories)) {
                    $categories[] = $category;
                }
            }
        }
    }
    
    return count($categories);
}

function clearCache() {
    $cacheFile = CACHE_DIR . 'posts.json';
    $sitemapCacheFile = CACHE_DIR . 'sitemap.xml';
    
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
    
    if (file_exists($sitemapCacheFile)) {
        unlink($sitemapCacheFile);
    }
    
    return true;
}

function generateSitemap() {
    // sitemap.php dosyasını çalıştırarak sitemap oluştur
    $sitemapCacheFile = CACHE_DIR . 'sitemap.xml';
    
    // sitemap.php'nin içeriğini simüle et
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    // Ana sayfa
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . BASE_URL . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>daily</changefreq>' . "\n";
    $xml .= '    <priority>1.0</priority>' . "\n";
    $xml .= '  </url>' . "\n";

    // Arama sayfası
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . BASE_URL . 'search</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>weekly</changefreq>' . "\n";
    $xml .= '    <priority>0.8</priority>' . "\n";
    $xml .= '  </url>' . "\n";

    // RSS sayfası
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . BASE_URL . 'rss</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>daily</changefreq>' . "\n";
    $xml .= '    <priority>0.9</priority>' . "\n";
    $xml .= '  </url>' . "\n";

    // Blog yazıları
    $posts = array_diff(scandir(POSTS_DIR), array('..', '.'));
    $categories = [];
    $tags = [];

    foreach ($posts as $post) {
        if (pathinfo($post, PATHINFO_EXTENSION) === 'md') {
            $postFile = POSTS_DIR . $post;
            $postData = getPostContent($postFile);
            
            if ($postData) {
                $slug = pathinfo($post, PATHINFO_FILENAME);
                $lastModified = filemtime($postFile);
                $category = strtolower($postData['meta']['category'] ?? 'genel');
                $postTags = $postData['meta']['tags'] ?? [];
                
                // Kategori ve etiketleri topla
                if (!in_array($category, $categories)) {
                    $categories[] = $category;
                }
                foreach ($postTags as $tag) {
                    if (!in_array($tag, $tags)) {
                        $tags[] = $tag;
                    }
                }
                
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . BASE_URL . $slug . '</loc>' . "\n";
                $xml .= '    <lastmod>' . date('Y-m-d\TH:i:sP', $lastModified) . '</lastmod>' . "\n";
                $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                $xml .= '    <priority>0.8</priority>' . "\n";
                
                // News sitemap için ek bilgiler
                $xml .= '    <news:news>' . "\n";
                $xml .= '      <news:publication>' . "\n";
                $xml .= '        <news:name>' . htmlspecialchars(SITE_NAME) . '</news:name>' . "\n";
                $xml .= '        <news:language>' . DEFAULT_LANGUAGE . '</news:language>' . "\n";
                $xml .= '      </news:publication>' . "\n";
                $xml .= '      <news:publication_date>' . date('Y-m-d\TH:i:sP', strtotime($postData['meta']['date'])) . '</news:publication_date>' . "\n";
                $xml .= '      <news:title>' . htmlspecialchars($postData['meta']['title']) . '</news:title>' . "\n";
                $xml .= '      <news:keywords>' . htmlspecialchars(implode(', ', $postTags)) . '</news:keywords>' . "\n";
                $xml .= '    </news:news>' . "\n";
                
                $xml .= '  </url>' . "\n";
            }
        }
    }

    // Kategori sayfaları
    foreach ($categories as $category) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . BASE_URL . 'cat/' . rawurlencode($category) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.6</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }

    // Etiket sayfaları
    foreach ($tags as $tag) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . BASE_URL . 'tag/' . rawurlencode($tag) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.5</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }

    $xml .= '</urlset>';

    // Cache'e kaydet
    if (CACHE_ENABLED) {
        return file_put_contents($sitemapCacheFile, $xml);
    }
    
    return false;
}

function parsePost($content, $slug) {
    // Meta verileri ayırmak için düzenli ifade
    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
        $metaRaw = trim($matches[1]);
        $body = trim($matches[2]);

        // Meta verilerini ayrıştır
        $meta = [];
        foreach (explode("\n", $metaRaw) as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Etiketler için özel işlem
                if ($key === 'tags') {
                    // [tag1, tag2, tag3] formatını temizle
                    $value = trim($value, '[]');
                    $meta[$key] = array_map('trim', explode(',', $value));
                } else {
                    $meta[$key] = $value;
                }
            }
        }

        return [
            'slug' => $slug,
            'meta' => $meta,
            'content' => $body
        ];
    }

    return null;
}

function createSlug($string) {
    // Türkçe karakterleri dönüştür
    $string = mb_strtolower($string, 'UTF-8');
    $string = str_replace(
        ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'],
        ['c', 'g', 'i', 'o', 's', 'u'],
        $string
    );

    // Harf ve rakam dışındaki karakterleri tire ile değiştir
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return trim($string, '-');
}

function getCacheSize() {
    if (!is_dir(CACHE_DIR)) {
        return '0 bytes';
    }
    
    $size = 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(CACHE_DIR));
    foreach ($files as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    
    if ($size >= 1073741824) {
        return number_format($size / 1073741824, 2) . ' GB';
    } elseif ($size >= 1048576) {
        return number_format($size / 1048576, 2) . ' MB';
    } elseif ($size >= 1024) {
        return number_format($size / 1024, 2) . ' KB';
    } else {
        return $size . ' bytes';
    }
}
?>