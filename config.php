<?php
// Temel URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$host = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8');
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

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
?>