<?php
include('config.php');
include('includes/markdown.php');

if (isset($_GET['slug'])) {
    $slug = htmlspecialchars($_GET['slug'], ENT_QUOTES, 'UTF-8');
    $postFile = POSTS_DIR . basename($slug) . '.md';

    if (file_exists($postFile)) {
        $postData = getPostContent($postFile);

        if ($postData) {
            // SEO için değişkenler
            $seoTitle = htmlspecialchars($postData['title'], ENT_QUOTES, 'UTF-8') . ' - ' . SITE_NAME;
            $seoDescription = htmlspecialchars(substr(strip_tags($postData['content']), 0, 150), ENT_QUOTES, 'UTF-8');
        } else {
            $seoTitle = 'Yazı Bulunamadı - ' . SITE_NAME;
            $seoDescription = 'Bu yazı bulunamadı. Farklı bir yazı deneyebilirsiniz.';
        }
    } else {
        $seoTitle = 'Yazı Bulunamadı - ' . SITE_NAME;
        $seoDescription = 'Bu yazı bulunamadı. Farklı bir yazı deneyebilirsiniz.';
    }
} else {
    $seoTitle = 'Geçersiz Yazı - ' . SITE_NAME;
    $seoDescription = 'Geçersiz bir yazı istendi. Lütfen doğru bir URL giriniz.';
}

include('includes/header.php');

// İçeriği Göster
if (isset($_GET['slug'])) {
    
    if (file_exists($postFile)) {
        $postContent = convertMarkdownToHTML($postFile);
		// İçerik
        echo "<h2>" . htmlspecialchars($postData['title']) . "</h2>";
        echo "<small>" . date("D, d M Y H:i:s O", filemtime($postFile)) . "</small>";
        echo "<hr>";
        echo $postContent;
    } else {
        echo "<p>Yazı bulunamadı.</p>";
    }
} else {
    echo "<p>Geçersiz yazı.</p>";
}

include('includes/footer.php');
?>
