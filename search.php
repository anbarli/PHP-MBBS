<?php
include('config.php');

$searchQuery = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$searchQuery = mb_substr($searchQuery, 0, 120, 'UTF-8');
$searchQueryDisplay = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8');

function normalizeSearchText($text) {
    $normalized = mb_strtolower((string)$text, 'UTF-8');
    $normalized = strtr($normalized, [
        'ç' => 'c',
        'ğ' => 'g',
        'ı' => 'i',
        'ö' => 'o',
        'ş' => 's',
        'ü' => 'u'
    ]);
    $normalized = preg_replace('/\s+/u', ' ', trim((string)$normalized));
    return (string)$normalized;
}

function tokenizeSearchText($text) {
    $normalized = normalizeSearchText($text);
    $tokens = preg_split('/[^a-z0-9]+/u', $normalized) ?: [];
    $tokens = array_values(array_filter(array_unique($tokens), function ($token) {
        return strlen($token) >= 2;
    }));
    return $tokens;
}

function getClosestDistance($term, $tokens) {
    if (empty($tokens)) {
        return PHP_INT_MAX;
    }

    $closest = PHP_INT_MAX;
    foreach ($tokens as $token) {
        $distance = levenshtein($term, $token);
        if ($distance < $closest) {
            $closest = $distance;
        }
        if ($closest === 0) {
            break;
        }
    }

    return $closest;
}

$seoTitle = 'Arama - ' . SITE_NAME;
$seoDescription = 'Blog yazilarinda arama yapin.';
$seoRobots = 'noindex, follow';
$seoCanonical = BASE_URL . 'search';
$structuredDataType = 'CollectionPage';

if ($searchQuery !== '') {
    $seoTitle = '"' . $searchQuery . '" icin arama sonuclari - ' . SITE_NAME;
    $seoDescription = '"' . $searchQuery . '" icin arama sonuclari.';
}

include('includes/header.php');

echo '<div class="alert alert-secondary">Blog yazilarinda arama yapin. Yazi basligi, icerik, etiket veya kategori ile arama yapabilirsiniz.</div>';

if ($searchQuery !== '') {
    echo '<h1>"' . $searchQueryDisplay . '" icin Arama Sonuclari</h1>';

    $posts = array_diff(scandir(POSTS_DIR), array('..', '.'));
    $searchResults = [];
    $searchTerms = tokenizeSearchText($searchQuery);

    foreach ($posts as $post) {
        if (pathinfo($post, PATHINFO_EXTENSION) !== 'md') {
            continue;
        }

        $postFile = POSTS_DIR . $post;
        $postData = getPostContent($postFile);
        if (!$postData || !isPostPublished($postData)) {
            continue;
        }

        $title = normalizeSearchText($postData['meta']['title'] ?? '');
        $content = normalizeSearchText(strip_tags($postData['content'] ?? ''));
        $tags = array_map('normalizeSearchText', $postData['meta']['tags'] ?? []);
        $category = normalizeSearchText($postData['meta']['category'] ?? '');

        $titleTokens = tokenizeSearchText($title);
        $categoryTokens = tokenizeSearchText($category);
        $tagTokens = [];
        foreach ($tags as $tag) {
            $tagTokens = array_merge($tagTokens, tokenizeSearchText($tag));
        }
        $tagTokens = array_values(array_unique($tagTokens));

        $score = 0;
        $matchedTerms = [];

        foreach ($searchTerms as $term) {
            if (in_array($term, $titleTokens, true)) {
                $score += 20;
                $matchedTerms[] = 'title:' . $term;
            } elseif (strpos($title, $term) !== false) {
                $score += 12;
                $matchedTerms[] = 'title~' . $term;
            }

            if (in_array($term, $tagTokens, true)) {
                $score += 16;
                $matchedTerms[] = 'tag:' . $term;
            } else {
                foreach ($tags as $tag) {
                    if (strpos($tag, $term) !== false) {
                        $score += 9;
                        $matchedTerms[] = 'tag~' . $term;
                        break;
                    }
                }
            }

            if (in_array($term, $categoryTokens, true)) {
                $score += 7;
                $matchedTerms[] = 'cat:' . $term;
            } elseif (strpos($category, $term) !== false) {
                $score += 5;
                $matchedTerms[] = 'cat~' . $term;
            }

            if (strpos($content, $term) !== false) {
                $score += 3;
                $matchedTerms[] = 'body:' . $term;
            }

            // Lightweight typo tolerance on title/tags.
            if (strlen($term) >= 4) {
                $titleDistance = getClosestDistance($term, $titleTokens);
                if ($titleDistance === 1) {
                    $score += 6;
                    $matchedTerms[] = 'title-typo:' . $term;
                }

                $tagDistance = getClosestDistance($term, $tagTokens);
                if ($tagDistance === 1) {
                    $score += 5;
                    $matchedTerms[] = 'tag-typo:' . $term;
                }
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

    usort($searchResults, function ($a, $b) {
        return $b['score'] - $a['score'];
    });

    if (empty($searchResults)) {
        echo '<div class="alert alert-info">"<strong>' . $searchQueryDisplay . '</strong>" icin sonuc bulunamadi. Lutfen farkli anahtar kelimeler deneyin.</div>';
    } else {
        echo '<ul class="list-group list-group-flush list-group-numbered">';
        foreach ($searchResults as $result) {
            $title = htmlspecialchars((string)($result['data']['meta']['title'] ?? 'Basliksiz'));
            $category = htmlspecialchars((string)($result['data']['meta']['category'] ?? 'Genel'));
            $tags = $result['data']['meta']['tags'] ?? [];
            $date = htmlspecialchars((string)($result['data']['meta']['date'] ?? ''));
            $slug = $result['slug'];

            echo '
              <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                  <div class="fw-bold">
                    <a href="' . BASE_PATH . $slug . '" class="text-dark">' . $title . '</a>
                  </div>
                  ' . $date . ' tarihinde <strong>' . ucwords(strtolower($category)) . '</strong> kategorisinde yayinlandi.
                  Etiketler: ' . implode(', ', array_map('ucwords', $tags)) . '
                  <br><small class="text-muted">Arama puani: ' . $result['score'] . '</small>
                </div>
                <span class="badge text-bg-primary rounded-pill">' . ucwords(strtolower($category)) . '</span>
              </li>
            ';
        }
        echo '</ul>';
    }
} else {
    echo '<h1>Blog Arama</h1>';
    echo '<div class="alert alert-info">Arama yapmak icin yukaridaki arama kutusunu kullanin.</div>';
}

include('includes/footer.php');
?>
