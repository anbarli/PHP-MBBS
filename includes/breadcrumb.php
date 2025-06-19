<?php
/**
 * Breadcrumb (Yol Gösterici) Fonksiyonu
 *
 * @param array $items ['Yazı' => '/yazi', 'Kategori' => '/kategori', ...]
 * Son eleman aktif olarak gösterilir.
 */
function renderBreadcrumb($items = []) {
    if (empty($items) || !is_array($items)) return;
    echo '<nav aria-label="breadcrumb">';
    echo '<ol class="breadcrumb">';
    $lastKey = array_key_last($items);
    $i = 0;
    foreach ($items as $label => $url) {
        $i++;
        if ($i - 1 === $lastKey) {
            // Son eleman (aktif)
            echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($label) . '</li>';
        } else {
            echo '<li class="breadcrumb-item"><a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($label) . '</a></li>';
        }
    }
    echo '</ol>';
    echo '</nav>';
} 