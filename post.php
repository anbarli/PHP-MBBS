<?php
include('config.php');

$seoTitle = 'Yazı Bulunamadı - ' . SITE_NAME;
$seoDescription = 'Bu yazı bulunamadı. Farklı bir yazı deneyebilirsiniz.';

if (isset($_GET['slug'])) {
    $slug = htmlspecialchars($_GET['slug'], ENT_QUOTES, 'UTF-8');
    $postFile = POSTS_DIR . basename($slug) . '.md';

    if (file_exists($postFile)) {
		
        $postData = getPostContent($postFile);
		
        if ($postData) {
			$title = htmlspecialchars($postData['meta']['title']);
			$category = htmlspecialchars($postData['meta']['category'] ?? 'Genel');
			$tags = $postData['meta']['tags'] ?? [];
			$date = htmlspecialchars($postData['meta']['date']);
            // SEO için değişkenler
            $seoTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' - ' . SITE_NAME;
            $seoDescription = htmlspecialchars(substr(strip_tags($postData['content']), 0, 150), ENT_QUOTES, 'UTF-8');
        }
    }
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
			<a class="link-body-emphasis fw-semibold text-decoration-none" href="' . $basePath . '">Blog</a>
		  </li>
		  <li class="breadcrumb-item">
			<a class="link-body-emphasis fw-semibold text-decoration-none" href="' . $basePath . 'cat/' . strtolower(rawurlencode($category)) . '">' . $category . '</a>
		  </li>
		  <li class="breadcrumb-item active" aria-current="page">
			'.$title.'
		  </li>
		</ol>
	</nav>
';

// İçeriği Göster
if (isset($_GET['slug'])) {
    
    if (file_exists($postFile)) {
		
		$body = $postData['content'];

		// Markdown içeriği HTML'ye dönüştür
		require_once 'includes/Parsedown.php';
		$Parsedown = new Parsedown();
		$htmlContent = $Parsedown->text($body);
		
		// İçerik
        echo "<div class='markdown-body p-4 mb-4'>" . $htmlContent . "</div>";
		
		// Metadata
		echo "
			<div class='alert alert-secondary'>
				<strong>Tarih:</strong> " . $date;
				
		if (!empty($category)) {
			echo " / <strong>Kategori:</strong> <a href='" . $basePath . "cat/" . strtolower(rawurlencode($category)) . "' class='text-dark text-decoration-none'>" . htmlspecialchars(ucwords(strtolower($category))) . "</a>";
		}

		if (!empty($tags)) {
			echo " / <strong>Etiketler:</strong> ";
			foreach ($tags as $tag) {
				echo "<a href='" . $basePath . "tag/" . strtolower(rawurlencode($tag)) . "' class='text-dark text-decoration-none'>" . htmlspecialchars(ucwords(strtolower($tag))) . "</a> ";
			}
		}

		echo "
			</div>";

		
    } else {
        echo "<div class='alert alert-warning'>Yazı bulunamadı.</div>";
    }
} else {
    echo "<div class='alert alert-warning'>Geçersiz yazı.</div>";
}

include('includes/footer.php');
?>
