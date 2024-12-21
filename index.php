<?php
include('config.php');

$seoTitle = 'Son Yazılar - ' . SITE_NAME;
$seoDescription = 'Blogumuzda son yazılarımızı keşfedin. Teknoloji, yazılım ve hayat üzerine içerikler burada!';

include('includes/header.php');
include('includes/markdown.php');

$posts = array_diff(scandir(POSTS_DIR), array('..', '.')); // posts klasöründeki dosyaları listele

echo '<h2>Blog Yazıları</h2>';

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
    $contentData = getPostContent($file); // Başlık ve içeriği al

    if ($contentData) {
        echo "<a href='$slug' class='list-group-item list-group-item-action'>" . htmlspecialchars($contentData['title']) . "<label class='badge bg-secondary-subtle border border-secondary-subtle text-secondary-emphasis rounded-pill float-end'>".date("d-m-Y", $lastModified)."</label></a>";
    } else {
        echo "<li class='list-group-item'>Bir hata oluştu: $file</li>";
    }
}

echo '</ul>';

include('includes/footer.php');
?>
