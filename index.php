<?php
include('config.php');

$seoTitle = 'Son Yazılar - ' . SITE_NAME;
$seoDescription = 'Blogumuzda son yazılarımızı keşfedin. Teknoloji, yazılım ve hayat üzerine içerikler burada!';

include('includes/header.php');

$posts = array_diff(scandir(POSTS_DIR), array('..', '.')); // posts klasöründeki dosyaları listele

echo '
	<div class="alert alert-secondary">' . DEFAULT_DESCRIPTION . '</div>
	<h3>Blog Yazıları</h3>';

// URL'den filtre değerini al
$filterCategory = $_GET['cat'] ?? null;
$filterTag = $_GET['tag'] ?? null;

// Yazı dosyalarını al
$posts = array_diff(scandir(POSTS_DIR), array('..', '.'));

// Dosyaları son düzenlenme tarihine göre sıralamak için bir yardımcı dizi oluştur
$postFilesWithDates = [];
foreach ($posts as $post) {
    $postFile = POSTS_DIR . $post;
    $lastModified = filemtime($postFile); // Dosyanın son düzenlenme tarihi
    $postFilesWithDates[] = [
        'file' => $postFile,
        'slug' => pathinfo($post, PATHINFO_FILENAME),
        'lastModified' => $lastModified
    ];
}

// Tarihe göre sıralama (büyükten küçüğe)
usort($postFilesWithDates, function ($a, $b) {
    return $b['lastModified'] - $a['lastModified'];
});

// Sıralanan yazıları listeleme
echo "<ul class='list-group list-group-flush list-group-numbered'>";
foreach ($postFilesWithDates as $postData) {
    $file = $postData['file'];
    $slug = $postData['slug'];
	$lastModified = $postData['lastModified'];
    $contentData = getPostContent($file);

    if ($contentData) {
        $title = htmlspecialchars($contentData['meta']['title']);
        $category = htmlspecialchars($contentData['meta']['category'] ?? 'Genel');
        $tags = $contentData['meta']['tags'] ?? [];
		
        // Kategori veya etikete göre filtrele
        if (($filterCategory && $category !== $filterCategory) || ($filterTag && !in_array($filterTag, $tags))) {
            continue;
        }
		
		echo "
		  <li class='list-group-item d-flex justify-content-between align-items-start'>
			<div class='ms-2 me-auto'>
			  <div class='fw-bold'>
				<a href='" . $basePath . "/" . $slug . "' class='text-dark'>" . $title . "</a></div>
				Published at " . date("d-m-Y", $lastModified) . " under <strong>" . $category . "</strong> / Tags: " . implode(', ', $tags) . "
			</div>
			<span class='badge text-bg-primary rounded-pill'>" . $category . "</span>
		  </li>
		";
    }
}
echo '</ul>';

include('includes/footer.php');
?>
