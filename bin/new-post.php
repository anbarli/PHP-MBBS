<?php
declare(strict_types=1);

// CLI post scaffold generator:
// php bin/new-post.php "Post Title" [--category=Genel] [--tags=php,blog] [--status=draft]

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must run in CLI mode.\n");
    exit(1);
}

$argv = $_SERVER['argv'] ?? [];
array_shift($argv); // script name

if (count($argv) === 0) {
    fwrite(STDERR, "Usage: new-post \"Title\" [--category=Genel] [--tags=php,blog] [--status=draft] [--description=Text] [--date=YYYY-MM-DD]\n");
    exit(1);
}

$title = null;
$options = [
    'category' => 'Genel',
    'tags' => '',
    'status' => 'draft',
    'description' => '',
    'date' => date('Y-m-d'),
    'author' => 'CLI'
];

foreach ($argv as $arg) {
    if (strpos($arg, '--') === 0 && strpos($arg, '=') !== false) {
        [$rawKey, $rawValue] = explode('=', substr($arg, 2), 2);
        $key = strtolower(trim($rawKey));
        $value = trim($rawValue);
        if (array_key_exists($key, $options)) {
            $options[$key] = $value;
        }
        continue;
    }

    if ($title === null) {
        $title = trim($arg);
    }
}

if ($title === null || $title === '') {
    fwrite(STDERR, "Error: Title is required.\n");
    exit(1);
}

if (!in_array($options['status'], ['draft', 'published'], true)) {
    fwrite(STDERR, "Error: --status must be draft or published.\n");
    exit(1);
}

$projectRoot = dirname(__DIR__);
$postsDir = $projectRoot . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR;

if (!is_dir($postsDir) && !mkdir($postsDir, 0755, true)) {
    fwrite(STDERR, "Error: Cannot create posts directory.\n");
    exit(1);
}

function slugify(string $text): string
{
    $normalized = function_exists('mb_strtolower')
        ? mb_strtolower($text, 'UTF-8')
        : strtolower($text);
    $normalized = strtr($normalized, [
        'ç' => 'c',
        'ğ' => 'g',
        'ı' => 'i',
        'ö' => 'o',
        'ş' => 's',
        'ü' => 'u'
    ]);
    $normalized = preg_replace('/[^\p{L}\p{N}]+/u', '-', $normalized) ?? '';
    $normalized = trim($normalized, '-');
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
    $ascii = strtolower((string)$ascii);
    $ascii = preg_replace('/[^a-z0-9]+/', '-', $ascii) ?? '';
    $ascii = trim($ascii, '-');
    return $ascii !== '' ? $ascii : 'post';
}

function uniqueSlug(string $base, string $postsDir): string
{
    $slug = $base;
    $counter = 2;
    while (file_exists($postsDir . $slug . '.md')) {
        $slug = $base . '-' . $counter;
        $counter++;
    }
    return $slug;
}

$baseSlug = slugify($title);
$slug = uniqueSlug($baseSlug, $postsDir);
$postPath = $postsDir . $slug . '.md';

$tags = array_filter(array_map('trim', explode(',', (string)$options['tags'])));
$tagsLine = '[' . implode(', ', $tags) . ']';

$meta = [
    'title' => str_replace(["\r", "\n"], ' ', $title),
    'date' => str_replace(["\r", "\n"], ' ', (string)$options['date']),
    'category' => str_replace(["\r", "\n"], ' ', (string)$options['category']),
    'description' => str_replace(["\r", "\n"], ' ', (string)$options['description']),
    'status' => $options['status'],
    'tags' => $tagsLine,
    'author' => str_replace(["\r", "\n"], ' ', (string)$options['author'])
];

$content = "---\n";
foreach ($meta as $key => $value) {
    $content .= $key . ': ' . $value . "\n";
}
$content .= "---\n\n";
$content .= '# ' . $meta['title'] . "\n\n";
$content .= "Write your content here.\n";

if (file_put_contents($postPath, $content) === false) {
    fwrite(STDERR, "Error: Could not write file: {$postPath}\n");
    exit(1);
}

fwrite(STDOUT, "Created: {$postPath}\n");
fwrite(STDOUT, "Slug: {$slug}\n");
exit(0);
