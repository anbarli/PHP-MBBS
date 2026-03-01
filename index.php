<?php
include('config.php');

$seoRobots = 'index, follow';
$structuredDataType = 'CollectionPage';

$seoTitle = 'Son Yazilar - ' . SITE_NAME;
$seoDescription = 'Blogumuzda son yazilarimizi kesfedin. Teknoloji, yazilim ve hayat uzerine icerikler burada!';

// URL'den filtre degerini al (case-insensitive)
$filterCategory = isset($_GET['cat']) ? strtolower($_GET['cat']) : null;
$filterTag = isset($_GET['tag']) ? strtolower($_GET['tag']) : null;

if ($filterCategory) {
    $seoTitle = ucwords($filterCategory) . ' Kategorisi - ' . SITE_NAME;
    $seoDescription = ucwords($filterCategory) . ' kategorisindeki yazilari kesfedin.';
} elseif ($filterTag) {
    $seoTitle = ucwords($filterTag) . ' Etiketi - ' . SITE_NAME;
    $seoDescription = ucwords($filterTag) . ' etiketiyle ilgili yazilari kesfedin.';
}

include('includes/header.php');

echo '
    <div class="alert alert-secondary">' . DEFAULT_DESCRIPTION . '</div>
    <h1>Blog Yazilari</h1>';

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

$postsPerPage = 5;
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
                " . $postItem['date'] . " tarihinde <strong>" . ucwords(strtolower($postItem['category'])) . "</strong> kategorisinde yayinlandi. Etiketler: " . implode(', ', $postItem['tags']) . "
            </div>
            <span class='badge text-bg-primary rounded-pill'>" . ucwords(strtolower($postItem['category'])) . "</span>
        </li>
    ";
}
echo '</ul>';

if ($totalPages > 1) {
    echo '<nav aria-label="Sayfalama" class="mt-4">';
    echo '<ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i === $currentPage) ? ' active' : '';
        $url = $_SERVER['PHP_SELF'] . '?page=' . $i;
        if ($filterCategory) {
            $url .= '&cat=' . urlencode($filterCategory);
        }
        if ($filterTag) {
            $url .= '&tag=' . urlencode($filterTag);
        }
        echo '<li class="page-item' . $active . '"><a class="page-link" href="' . $url . '">' . $i . '</a></li>';
    }
    echo '</ul>';
    echo '</nav>';
}

include('includes/footer.php');
?>
