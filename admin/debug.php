<?php
/**
 * Debug file to test post loading
 */

define('ADMIN_SECURE', true);

// Ana config dosyasını dahil et
require_once '../config.php';

// Güvenlik fonksiyonlarını dahil et
require_once '../includes/security.php';

// Session başlat ve yetki kontrolü
initSecureSession();
requireAdminAuth();

echo "<h1>Debug - Post Loading Test</h1>";

// Test 1: Check if cache directory exists
echo "<h2>1. Cache Directory Check</h2>";
if (is_dir(CACHE_DIR)) {
    echo "✓ Cache directory exists: " . CACHE_DIR . "<br>";
} else {
    echo "✗ Cache directory does not exist: " . CACHE_DIR . "<br>";
    echo "Creating cache directory...<br>";
    mkdir(CACHE_DIR, 0755, true);
    echo "✓ Cache directory created<br>";
}

// Test 2: Check cached posts
echo "<h2>2. Cached Posts Check</h2>";
$cachedPosts = getCachedPosts();
if ($cachedPosts === null) {
    echo "✗ No cached posts found<br>";
} else {
    echo "✓ Found " . count($cachedPosts) . " cached posts<br>";
}

// Test 3: Check posts directory
echo "<h2>3. Posts Directory Check</h2>";
if (is_dir(POSTS_DIR)) {
    echo "✓ Posts directory exists: " . POSTS_DIR . "<br>";
    $postFiles = array_diff(scandir(POSTS_DIR), array('..', '.'));
    echo "✓ Found " . count($postFiles) . " files in posts directory<br>";
    
    foreach ($postFiles as $file) {
        echo "- " . $file . "<br>";
    }
} else {
    echo "✗ Posts directory does not exist: " . POSTS_DIR . "<br>";
}

// Test 4: Load posts directly from files
echo "<h2>4. Direct Post Loading Test</h2>";
$posts = [];
$postFiles = array_diff(scandir(POSTS_DIR), array('..', '.'));

foreach ($postFiles as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
        $slug = pathinfo($file, PATHINFO_FILENAME);
        $postFile = POSTS_DIR . $file;
        $postData = getPostContent($postFile);
        
        echo "<h3>Processing: " . $file . "</h3>";
        echo "Slug: " . $slug . "<br>";
        
        if ($postData && isset($postData['meta'])) {
            echo "✓ Meta data found<br>";
            echo "Title: " . ($postData['meta']['title'] ?? 'No title') . "<br>";
            echo "Category: " . ($postData['meta']['category'] ?? 'No category') . "<br>";
            echo "Date: " . ($postData['meta']['date'] ?? 'No date') . "<br>";
            
            $posts[] = [
                'slug' => $slug,
                'meta' => $postData['meta'],
                'content' => $postData['content']
            ];
        } elseif ($postData) {
            echo "✓ Content found but no meta<br>";
            $posts[] = [
                'slug' => $slug,
                'meta' => [
                    'title' => $slug,
                    'category' => 'Genel',
                    'date' => date('Y-m-d'),
                    'tags' => []
                ],
                'content' => $postData['content'] ?? ''
            ];
        } else {
            echo "✗ No content found<br>";
        }
    }
}

// Test 5: Sort and display recent posts
echo "<h2>5. Recent Posts Test</h2>";
if (empty($posts)) {
    echo "✗ No posts loaded<br>";
} else {
    echo "✓ Loaded " . count($posts) . " posts<br>";
    
    // Tarihe göre sırala (en yeni önce)
    usort($posts, function($a, $b) {
        $dateA = strtotime($a['meta']['date'] ?? '1970-01-01');
        $dateB = strtotime($b['meta']['date'] ?? '1970-01-01');
        return $dateB - $dateA;
    });
    
    $recentPosts = array_slice($posts, 0, 5);
    echo "<h3>Recent Posts (first 5):</h3>";
    foreach ($recentPosts as $post) {
        echo "- " . ($post['meta']['title'] ?? 'No title') . " (" . ($post['meta']['category'] ?? 'No category') . ")<br>";
    }
}

// Test 6: Cache the posts
echo "<h2>6. Cache Posts Test</h2>";
if (!empty($posts)) {
    setCachedPosts($posts);
    echo "✓ Posts cached successfully<br>";
} else {
    echo "✗ No posts to cache<br>";
}

echo "<br><a href='dashboard.php'>← Back to Dashboard</a>";
?> 