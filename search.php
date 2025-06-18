<?php
include('config.php');

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchQuery = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8');

$seoTitle = 'Arama - ' . SITE_NAME;
$seoDescription = 'Blog yazılarında arama yapın.';

if (!empty($searchQuery)) {
    $seoTitle = '"' . $searchQuery . '" için arama sonuçları - ' . SITE_NAME;
    $seoDescription = '"' . $searchQuery . '" için arama sonuçları.';
}

include('includes/header.php');

echo '<div class="alert alert-secondary">Blog yazılarında arama yapın. Yazı başlığı, içerik, etiket veya kategori ile arama yapabilirsiniz.</div>';

if (!empty($searchQuery)) {
    echo '<h3>"' . htmlspecialchars($searchQuery) . '" için Arama Sonuçları</h3>';
    
    // Search functionality
    $posts = array_diff(scandir(POSTS_DIR), array('..', '.'));
    $searchResults = [];
    $searchTerms = explode(' ', strtolower($searchQuery));
    
    foreach ($posts as $post) {
        $postFile = POSTS_DIR . $post;
        $postData = getPostContent($postFile);
        
        if ($postData) {
            $title = strtolower($postData['meta']['title']);
            $content = strtolower(strip_tags($postData['content']));
            $tags = array_map('strtolower', $postData['meta']['tags'] ?? []);
            $category = strtolower($postData['meta']['category'] ?? '');
            
            $score = 0;
            $matchedTerms = [];
            
            foreach ($searchTerms as $term) {
                if (strlen($term) < 2) continue; // Skip very short terms
                
                // Title match (highest priority)
                if (strpos($title, $term) !== false) {
                    $score += 10;
                    $matchedTerms[] = $term;
                }
                
                // Tag match (high priority)
                foreach ($tags as $tag) {
                    if (strpos($tag, $term) !== false) {
                        $score += 8;
                        $matchedTerms[] = $term;
                        break;
                    }
                }
                
                // Category match (medium priority)
                if (strpos($category, $term) !== false) {
                    $score += 6;
                    $matchedTerms[] = $term;
                }
                
                // Content match (lower priority)
                if (strpos($content, $term) !== false) {
                    $score += 2;
                    $matchedTerms[] = $term;
                }
            }
            
            if ($score > 0) {
                $searchResults[] = [
                    'file' => $postFile,
                    'slug' => pathinfo($post, PATHINFO_FILENAME),
                    'data' => $postData,
                    'score' => $score,
                    'matchedTerms' => array_unique($matchedTerms)
                ];
            }
        }
    }
    
    // Sort by score (highest first)
    usort($searchResults, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    if (empty($searchResults)) {
        echo '<div class="alert alert-info">"<strong>' . htmlspecialchars($searchQuery) . '</strong>" için sonuç bulunamadı. Lütfen farklı anahtar kelimeler deneyin.</div>';
    } else {
        echo '<ul class="list-group list-group-flush list-group-numbered">';
        foreach ($searchResults as $result) {
            $title = htmlspecialchars($result['data']['meta']['title']);
            $category = htmlspecialchars($result['data']['meta']['category'] ?? 'Genel');
            $tags = $result['data']['meta']['tags'] ?? [];
            $date = htmlspecialchars($result['data']['meta']['date']);
            $slug = $result['slug'];
            
            echo '
              <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                  <div class="fw-bold">
                    <a href="' . BASE_PATH . $slug . '" class="text-dark">' . $title . '</a>
                  </div>
                  ' . $date . ' tarihinde <strong>' . ucwords(strtolower($category)) . '</strong> kategorisinde yayınlandı. 
                  Etiketler: ' . implode(', ', array_map('ucwords', $tags)) . '
                  <br><small class="text-muted">Arama puanı: ' . $result['score'] . '</small>
                </div>
                <span class="badge text-bg-primary rounded-pill">' . ucwords(strtolower($category)) . '</span>
              </li>
            ';
        }
        echo '</ul>';
    }
} else {
    echo '<h3>Blog Arama</h3>';
    echo '<div class="alert alert-info">Arama yapmak için yukarıdaki arama kutusunu kullanın.</div>';
}

include('includes/footer.php');
?> 