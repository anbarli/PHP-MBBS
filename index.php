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
echo "<ul class='list-group list-group-flush list-group-numbered'>";
foreach ($postFilesWithDates as $postData) {
    if (!isset($postData['file'], $postData['slug'], $postData['lastModified'])) {
        continue; // Eksik anahtar varsa atla
    }
    $file = $postData['file'];
    $slug = $postData['slug'];
    $lastModified = $postData['lastModified'];
    $contentData = getPostContent($file);

    if ($contentData) {
        $title = htmlspecialchars($contentData['meta']['title']);
        $category = strtolower(htmlspecialchars($contentData['meta']['category'] ?? 'Genel')); // Category'yi küçük harfe çevir
		$tags = array_map('strtolower', $contentData['meta']['tags'] ?? []); // Etiketleri küçük harfe çevir
		$date = htmlspecialchars($contentData['meta']['date']);
		
        // Kategori veya etikete göre filtrele (case-insensitive)
        if (($filterCategory && $category !== $filterCategory) || ($filterTag && !in_array($filterTag, $tags))) {
            continue;
        }
		
		$tags = array_map('ucwords', $contentData['meta']['tags'] ?? []); // Etiketleri ucwords harfe çevir
		
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

include('includes/footer.php');
?>
