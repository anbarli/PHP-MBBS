<?php
include('config.php');

$seoTitle = 'Son Yazılar - ' . SITE_NAME;
$seoDescription = 'Blogumuzda son yazılarımızı keşfedin. Teknoloji, yazılım ve hayat üzerine içerikler burada!';

include('includes/header.php');
include('includes/markdown.php');

$posts = array_diff(scandir(POSTS_DIR), array('..', '.')); // posts klasöründeki dosyaları listele

echo '<h2>Blog Yazıları</h2>';
echo '<ul>';
foreach ($posts as $post) {
    $postFile = POSTS_DIR . $post;
    $postData = getPostContent($postFile); // Başlık ve içeriği al
    $postSlug = pathinfo($post, PATHINFO_FILENAME);

    if ($postData) {
        echo "<li><a href='$postSlug'>" . htmlspecialchars($postData['title']) . "</a></li>";
    } else {
        echo "<li>Bir hata oluştu: $postFile</li>";
    }
}
echo '</ul>';

include('includes/footer.php');
?>
