<?php
include('config.php');

$seoTitle = 'Son Yazılar - ' . SITE_NAME;
$seoDescription = 'Blogumuzda son yazılarımızı keşfedin. Teknoloji, yazılım ve hayat üzerine içerikler burada!';

// URL'den filtre değerini al (case-insensitive)
$filterCategory = isset($_GET['cat']) ? strtolower($_GET['cat']) : null;
$filterTag = isset($_GET['tag']) ? strtolower($_GET['tag']) : null;

// Eğer bir kategori veya etiket filtresi varsa, SEO başlığını ona göre ayarla
if ($filterCategory) {
    $seoTitle = ucwords($filterCategory) . ' Kategorisi - ' . SITE_NAME;
    $seoDescription = ucwords($filterCategory) . ' kategorisindeki yazıları keşfedin.';
} elseif ($filterTag) {
    $seoTitle = ucwords($filterTag) . ' Etiketi - ' . SITE_NAME;
    $seoDescription = ucwords($filterTag) . ' etiketiyle ilgili yazıları keşfedin.';
}

include('includes/header.php');

echo '
	<div class="alert alert-secondary">' . DEFAULT_DESCRIPTION . '</div>
	<h3>Blog Yazıları</h3>';

// Try to get cached posts first
$postFilesWithDates = getCachedPosts();

if ($postFilesWithDates === null) {
    // Cache miss - generate posts data
    $posts = array_diff(scandir(POSTS_DIR), array('..', '.'));
    
    // Dosyaları son düzenlenme tarihine göre sıralamak için bir yardımcı dizi oluştur
    $postFilesWithDates = [];
    foreach ($posts as $post) {
        if (pathinfo($post, PATHINFO_EXTENSION) === 'md') {
            $postFile = POSTS_DIR . $post;
            $lastModified = filemtime($postFile); // Dosyanın son düzenlenme tarihi
            $postFilesWithDates[] = [
                'file' => $postFile,
                'slug' => pathinfo($post, PATHINFO_FILENAME),
                'lastModified' => $lastModified
            ];
        }
    }

    // Tarihe göre sıralama (büyükten küçüğe)
    usort($postFilesWithDates, function ($a, $b) {
        return $b['lastModified'] - $a['lastModified'];
    });
    
    // Cache the results
    setCachedPosts($postFilesWithDates);
}

// Sıralanan yazıları listeleme

// Sayfalama ayarları
$postsPerPage = 5;
$totalPosts = count($postFilesWithDates);
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages = ceil($totalPosts / $postsPerPage);
$startIndex = ($currentPage - 1) * $postsPerPage;
$pagedPosts = array_slice($postFilesWithDates, $startIndex, $postsPerPage);

echo "<ul class='list-group list-group-flush'>";
foreach ($pagedPosts as $postData) {
    if (!isset($postData['file'], $postData['slug'], $postData['lastModified'])) {
        continue;
    }
    $file = $postData['file'];
    $slug = $postData['slug'];
    $lastModified = $postData['lastModified'];
    $contentData = getPostContent($file);

    if ($contentData) {
        $title = htmlspecialchars($contentData['meta']['title']);
        $category = strtolower(htmlspecialchars($contentData['meta']['category'] ?? 'Genel'));
        $tags = array_map('strtolower', $contentData['meta']['tags'] ?? []);
        $date = htmlspecialchars($contentData['meta']['date']);

        if (($filterCategory && $category !== $filterCategory) || ($filterTag && !in_array($filterTag, $tags))) {
            continue;
        }

        $tags = array_map('ucwords', $contentData['meta']['tags'] ?? []);

        echo "
            <li class='list-group-item d-flex justify-content-between align-items-start'>
                <div class='ms-2 me-auto'>
                    <div class='fw-bold'>
                        <a href='" . BASE_PATH . $slug . "' class='text-dark'>" . $title . "</a></div>
                        " . $date . " tarihinde <strong>" . ucwords(strtolower($category)) . "</strong> kategorisinde yayınlandı. Etiketler: " . implode(', ', $tags) . "
                </div>
                <span class='badge text-bg-primary rounded-pill'>" . ucwords(strtolower($category)) . "</span>
            </li>
        ";
    }
}
echo '</ul>';

// Sayfalama linkleri
if ($totalPages > 1) {
    echo '<nav aria-label="Sayfalama" class="mt-4">';
    echo '<ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i === $currentPage) ? ' active' : '';
        $url = $_SERVER['PHP_SELF'] . '?page=' . $i;
        if ($filterCategory) $url .= '&cat=' . urlencode($filterCategory);
        if ($filterTag) $url .= '&tag=' . urlencode($filterTag);
        echo '<li class="page-item' . $active . '"><a class="page-link" href="' . $url . '">' . $i . '</a></li>';
    }
    echo '</ul>';
    echo '</nav>';
}

include('includes/footer.php');
?>
