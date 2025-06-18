<?php
include('config.php');

$seoTitle = 'Yazı Bulunamadı - ' . SITE_NAME;
$seoDescription = 'Bu yazı bulunamadı. Farklı bir yazı deneyebilirsiniz.';

if (isset($_GET['slug'])) {
    $slug = htmlspecialchars($_GET['slug'], ENT_QUOTES, 'UTF-8');
    
    // Validate slug format - only allow alphanumeric, hyphens, and underscores
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $slug)) {
        $slug = '';
    }
    
    $postFile = POSTS_DIR . $slug . '.md';

    if (file_exists($postFile)) {
		
        $postData = getPostContent($postFile);
		
        if ($postData) {
			$title = htmlspecialchars($postData['meta']['title']);
			$category = htmlspecialchars($postData['meta']['category'] ?? 'Genel');
			$tags = $postData['meta']['tags'] ?? [];
			$date = htmlspecialchars($postData['meta']['date']);
			
            // SEO için değişkenler
            $seoTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' - ' . SITE_NAME;
            
            // Daha iyi meta description oluştur
            $contentText = strip_tags($postData['content']);
            $contentText = preg_replace('/\s+/', ' ', $contentText); // Fazla boşlukları temizle
            $seoDescription = htmlspecialchars(substr($contentText, 0, 160), ENT_QUOTES, 'UTF-8');
            if (strlen($contentText) > 160) {
                $seoDescription .= '...';
            }
        }
    }
}

include('includes/header.php');

// Only show breadcrumb if we have valid post data
if (isset($postData) && $postData) {
    echo '
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
              <li class="breadcrumb-item">
                <a class="link-body-emphasis" href="/">
                  <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/></svg>
                </a>
              </li>
              <li class="breadcrumb-item">
                <a class="link-body-emphasis fw-semibold text-decoration-none" href="' . BASE_PATH . '">Blog</a>
              </li>
              <li class="breadcrumb-item">
                <a class="link-body-emphasis fw-semibold text-decoration-none" href="' . BASE_PATH . 'cat/' . strtolower(rawurlencode($category)) . '">' . $category . '</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                '.$title.'
              </li>
            </ol>
        </nav>
    ';
}

// İçeriği Göster
if (isset($_GET['slug']) && !empty($slug)) {
    
    if (file_exists($postFile) && isset($postData) && $postData) {
		
		$body = $postData['content'];

		// Markdown içeriği HTML'ye dönüştür
		require_once 'includes/Parsedown.php';
		$Parsedown = new Parsedown();
		$htmlContent = $Parsedown->text($body);
		
		// İçerik - Semantic HTML ile
        echo '<article class="markdown-body p-4 mb-4">' . $htmlContent . '</article>';

        // Sosyal Medya Paylaşım Butonları
        $shareUrl = BASE_URL . $slug;
        $shareTitle = $title;
        echo '<div class="mb-4 p-3 bg-light rounded">';
        echo '<h6 class="mb-3"><i class="bi bi-share me-2"></i>Bu yazıyı paylaş:</h6>';
        echo '<div class="d-flex flex-wrap gap-2">';
        
        // Twitter/X
        echo '<a href="https://twitter.com/intent/tweet?text=' . rawurlencode($shareTitle) . '&url=' . rawurlencode($shareUrl) . '" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm" title="Twitter/X\'te paylaş">';
        echo '<i class="bi bi-twitter-x me-1"></i>Twitter';
        echo '</a>';
        
        // Facebook
        echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($shareUrl) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm" title="Facebook\'ta paylaş">';
        echo '<i class="bi bi-facebook me-1"></i>Facebook';
        echo '</a>';
        
        // LinkedIn
        echo '<a href="https://www.linkedin.com/shareArticle?mini=true&url=' . rawurlencode($shareUrl) . '&title=' . rawurlencode($shareTitle) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm" title="LinkedIn\'de paylaş">';
        echo '<i class="bi bi-linkedin me-1"></i>LinkedIn';
        echo '</a>';
        
        // WhatsApp
        echo '<a href="https://wa.me/?text=' . rawurlencode($shareTitle . ' ' . $shareUrl) . '" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm" title="WhatsApp\'ta paylaş">';
        echo '<i class="bi bi-whatsapp me-1"></i>WhatsApp';
        echo '</a>';
        
        // Email
        echo '<a href="mailto:?subject=' . rawurlencode($shareTitle) . '&body=' . rawurlencode($shareTitle . ' - ' . $shareUrl) . '" class="btn btn-outline-secondary btn-sm" title="E-posta ile paylaş">';
        echo '<i class="bi bi-envelope me-1"></i>E-posta';
        echo '</a>';
        
        echo '</div>';
        echo '</div>';
		
		// Metadata
		echo '
			<footer class="alert alert-secondary">
				<strong>Tarih:</strong> ' . $date;
				
		if (!empty($category)) {
			echo ' / <strong>Kategori:</strong> <a href="' . BASE_PATH . 'cat/' . strtolower(rawurlencode($category)) . '" class="text-dark text-decoration-none">' . htmlspecialchars(ucwords(strtolower($category))) . '</a>';
		}

		if (!empty($tags)) {
			echo ' / <strong>Etiketler:</strong> ';
			foreach ($tags as $tag) {
				echo '<a href="' . BASE_PATH . 'tag/' . strtolower(rawurlencode($tag)) . '" class="text-dark text-decoration-none">' . htmlspecialchars(ucwords(strtolower($tag))) . '</a> ';
			}
		}

		echo '
			</footer>';

		
    } else {
        echo '<div class="alert alert-warning">Yazı bulunamadı.</div>';
    }
} else {
    echo '<div class="alert alert-warning">Geçersiz yazı.</div>';
}

include('includes/footer.php');
?>
