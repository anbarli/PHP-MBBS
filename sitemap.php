<?php
include('config.php');

// Sitemap cache kontrolü
$sitemapCacheFile = CACHE_DIR . 'sitemap.xml';
$sitemapCacheTime = 86400; // 24 saat

// Cache varsa ve güncel ise cache'den döndür
if (CACHE_ENABLED && file_exists($sitemapCacheFile) && (time() - filemtime($sitemapCacheFile)) < $sitemapCacheTime) {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=3600'); // 1 saat browser cache
    echo file_get_contents($sitemapCacheFile);
    exit;
}

// XML header
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

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
    file_put_contents($sitemapCacheFile, $xml);
}

echo $xml;
?> 