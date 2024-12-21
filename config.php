<?php
// Temel URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$host = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8');
$basePath = '/blog/';

define('SITE_NAME', 'Kendime Notlar');
define('POSTS_DIR', realpath(__DIR__ . '/posts/') . '/');
define('DEFAULT_TITLE', 'Kendime Notlar - Teknoloji ve Yazılım'); // Varsayılan başlık
define('DEFAULT_DESCRIPTION', 'Blogumda yayınladığım teknoloji ve yazılıma dair yazılar!'); // Varsayılan meta açıklama
define('BASE_URL', $protocol . $host . $basePath);

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

function getPostContent($filePath) {
    $content = file_get_contents($filePath);

    // Meta verileri ayırmak için düzenli ifade
    if (preg_match('/^---(.*?)---(.*)$/s', $content, $matches)) {
        $metaRaw = trim($matches[1]);
        $body = trim($matches[2]);

        // Meta verilerini ayrıştır
        $meta = [];
        foreach (explode("\n", $metaRaw) as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }

        // Etiketleri diziye çevir
        if (isset($meta['tags'])) {
            $meta['tags'] = array_map('trim', explode(',', trim($meta['tags'], '[]')));
        }

        return [
            'meta' => $meta,
            'content' => $body
        ];
    }

    return null;
}
?>