<?php
include('config.php');

$seoRobots = 'index, follow';
$structuredDataType = 'CollectionPage';

$seoTitle = 'Son Yazılar - ' . SITE_NAME;
$seoDescription = 'Blogumuzda son yazılarımızı keşfedin. Teknoloji, yazılım ve hayat üzerine içerikler burada!';

// URL'den filtre degerini al (case-insensitive)
$filterCategory = isset($_GET['cat']) ? strtolower($_GET['cat']) : null;
$filterTag = isset($_GET['tag']) ? strtolower($_GET['tag']) : null;

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
    <h1>Blog Yazıları</h1>';

$postFilesWithDates = getCachedPosts();

if ($postFilesWithDates === null) {
    $posts = array_diff(scandir(POSTS_DIR), array('..', '.'));
    $postFilesWithDates = [];

    foreach ($posts as $post) {
        if (pathinfo($post, PATHINFO_EXTENSION) !== 'md') {
            continue;
        }

        $postFile = POSTS_DIR . $post;
        $postFilesWithDates[] = [
            'file' => $postFile,
            'slug' => pathinfo($post, PATHINFO_FILENAME),
            'lastModified' => filemtime($postFile)
        ];
    }

    usort($postFilesWithDates, function ($a, $b) {
        return $b['lastModified'] - $a['lastModified'];
    });

    setCachedPosts($postFilesWithDates);
}

$visiblePosts = [];
foreach ($postFilesWithDates as $postData) {
    if (!isset($postData['file'], $postData['slug'])) {
        continue;
    }

    $contentData = getPostContent($postData['file']);
    if (!$contentData || !isPostPublished($contentData)) {
        continue;
    }

    $title = htmlspecialchars($contentData['meta']['title'] ?? $postData['slug']);
    $category = strtolower(htmlspecialchars($contentData['meta']['category'] ?? 'Genel'));
    $tagsLower = array_map('strtolower', $contentData['meta']['tags'] ?? []);

    if (($filterCategory && $category !== $filterCategory) || ($filterTag && !in_array($filterTag, $tagsLower, true))) {
        continue;
    }

    $visiblePosts[] = [
        'slug' => $postData['slug'],
        'title' => $title,
        'category' => $category,
        'date' => htmlspecialchars($contentData['meta']['date'] ?? date('Y-m-d')),
        'tags' => array_map('ucwords', $contentData['meta']['tags'] ?? [])
    ];
}

$postsPerPage = 10;
$totalPosts = count($visiblePosts);
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages = max(1, ceil($totalPosts / $postsPerPage));
$startIndex = ($currentPage - 1) * $postsPerPage;
$pagedPosts = array_slice($visiblePosts, $startIndex, $postsPerPage);

echo "<ul class='list-group list-group-flush'>";
foreach ($pagedPosts as $postItem) {
    echo "
        <li class='list-group-item d-flex justify-content-between align-items-start'>
            <div class='ms-2 me-auto'>
                <div class='fw-bold'>
                    <a href='" . BASE_PATH . $postItem['slug'] . "' class='text-dark'>" . $postItem['title'] . "</a>
                </div>
                " . $postItem['date'] . " tarihinde <strong>" . ucwords(strtolower($postItem['category'])) . "</strong> kategorisinde yayınlandı. Etiketler: " . implode(', ', $postItem['tags']) . "
            </div>
            <span class='badge text-bg-primary rounded-pill'>" . ucwords(strtolower($postItem['category'])) . "</span>
        </li>
    ";
}
echo '</ul>';

if ($totalPages > 1) {
    echo '<nav aria-label="Sayfalama" class="mt-4">';
    echo '<ul class="pagination justify-content-center flex-wrap">';

    // Base URL için helper
    $baseUrl = $_SERVER['PHP_SELF'] . '?page=';
    $queryParams = '';
    if ($filterCategory) {
        $queryParams .= '&cat=' . urlencode($filterCategory);
    }
    if ($filterTag) {
        $queryParams .= '&tag=' . urlencode($filterTag);
    }

    // Previous button
    if ($currentPage > 1) {
        echo '<li class="page-item">';
        echo '<a class="page-link" href="' . $baseUrl . ($currentPage - 1) . $queryParams . '" aria-label="Önceki">';
        echo '<i class="bi bi-chevron-left"></i> <span class="d-none d-sm-inline">Önceki</span>';
        echo '</a></li>';
    } else {
        echo '<li class="page-item disabled">';
        echo '<span class="page-link"><i class="bi bi-chevron-left"></i> <span class="d-none d-sm-inline">Önceki</span></span>';
        echo '</li>';
    }

    // Page numbers with smart ellipsis
    $range = 2; // Aktif sayfanın her iki yanında gösterilecek sayfa sayısı

    for ($i = 1; $i <= $totalPages; $i++) {
        // İlk sayfa, son sayfa veya aktif sayfaya yakın sayfaları göster
        if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)) {
            $active = ($i === $currentPage) ? ' active' : '';
            echo '<li class="page-item' . $active . '">';
            echo '<a class="page-link" href="' . $baseUrl . $i . $queryParams . '">' . $i . '</a>';
            echo '</li>';
        } elseif ($i == $currentPage - $range - 1 || $i == $currentPage + $range + 1) {
            // Ellipsis göster
            echo '<li class="page-item disabled">';
            echo '<span class="page-link">...</span>';
            echo '</li>';
        }
    }

    // Next button
    if ($currentPage < $totalPages) {
        echo '<li class="page-item">';
        echo '<a class="page-link" href="' . $baseUrl . ($currentPage + 1) . $queryParams . '" aria-label="Sonraki">';
        echo '<span class="d-none d-sm-inline">Sonraki</span> <i class="bi bi-chevron-right"></i>';
        echo '</a></li>';
    } else {
        echo '<li class="page-item disabled">';
        echo '<span class="page-link"><span class="d-none d-sm-inline">Sonraki</span> <i class="bi bi-chevron-right"></i></span>';
        echo '</li>';
    }

    echo '</ul>';

    // Sayfa bilgisi
    echo '<div class="text-center mt-2 text-muted small">';
    echo 'Sayfa ' . $currentPage . ' / ' . $totalPages . ' (Toplam ' . $totalPosts . ' yazı)';
    echo '</div>';

    echo '</nav>';
}

include('includes/footer.php');
?>
