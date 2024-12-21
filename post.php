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

echo '
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
		  <li class="breadcrumb-item">
			<a class="link-body-emphasis" href="/">
			  <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/></svg>
			</a>
		  </li>
		  <li class="breadcrumb-item">
			<a class="link-body-emphasis fw-semibold text-decoration-none" href="index.php">Blog</a>
		  </li>
		  <li class="breadcrumb-item active" aria-current="page">
			'.$seoTitle.'
		  </li>
		</ol>
	</nav>
';

// İçeriği Göster
if (isset($_GET['slug'])) {
    
    if (file_exists($postFile)) {
        $postContent = convertMarkdownToHTML($postFile);
		// İçerik
        echo "<div class='markdown-body p-4 mb-4'>";
		echo $postContent;
		echo "<hr>";
		echo "</div>";
		echo "<label class='badge bg-secondary-subtle border border-secondary-subtle text-secondary-emphasis rounded-pill'>" . date("d-m-Y", filemtime($postFile)) . "</label>";
    } else {
        echo "<p>Yazı bulunamadı.</p>";
    }
} else {
    echo "<p>Geçersiz yazı.</p>";
}

include('includes/footer.php');
?>
