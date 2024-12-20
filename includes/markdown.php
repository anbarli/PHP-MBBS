<?php
// Parsedown kütüphanesini dahil et
include_once('Parsedown.php');

function convertMarkdownToHTML($filePath) {
    if (file_exists($filePath)) {
        $markdown = file_get_contents($filePath);
        
        // Parsedown ile markdown'u HTML'ye çevir
        $Parsedown = new Parsedown();
        return $Parsedown->text($markdown); // Markdown'ı HTML'ye çevir
    }
    return null;
}

function getPostContent($filePath) {
    if (file_exists($filePath)) {
        $markdown = file($filePath); // Dosyayı satır satır oku
        $title = trim($markdown[0]); // İlk satırı başlık olarak al
        $content = implode("", array_slice($markdown, 1)); // Geri kalanını içerik olarak al
        return ['title' => ltrim($title, '# '), 'content' => nl2br(htmlspecialchars($content))];
    }
    return null;
}
?>
