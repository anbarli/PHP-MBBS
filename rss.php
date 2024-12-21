<?php
include('config.php');

header("Content-Type: application/rss+xml; charset=UTF-8");

$posts = array_diff(scandir(POSTS_DIR), array('..', '.'));

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<rss version="2.0">
    <channel>
        <title><?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?> RSS</title>
        <link><?php echo htmlspecialchars((empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/', ENT_QUOTES, 'UTF-8'); ?></link>
        <description><?php echo htmlspecialchars(DEFAULT_DESCRIPTION, ENT_QUOTES, 'UTF-8'); ?></description>
        <language>tr</language>

        <?php
        foreach ($posts as $post) {
            $postFile = POSTS_DIR . $post;
            $postData = getPostContent($postFile); // Başlık ve içeriği al
            $postSlug = pathinfo($post, PATHINFO_FILENAME);

            if ($postData) {
                // Başlık, içerik, kategori ve etiketleri al
                $title = htmlspecialchars($postData['meta']['title'] ?? 'Başlık Yok', ENT_QUOTES, 'UTF-8');
                $content = htmlspecialchars($postData['content'], ENT_QUOTES, 'UTF-8'); // Markdown içeriğini al
				$content = preg_replace('/\s+/', ' ', $content);
                $category = htmlspecialchars($postData['meta']['category'] ?? 'Genel', ENT_QUOTES, 'UTF-8');
                $tags = implode(', ', $postData['meta']['tags'] ?? []);
                $description = substr(strip_tags($content), 0, 200); // İçeriğin ilk 200 karakterini al

                ?>

                <item>
                    <title><?php echo $title; ?></title>
                    <link><?php echo (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $postSlug; ?></link>
                    <description><![CDATA[
                        <?php echo $description; ?>... 
                    ]]></description>
                    <category><?php echo $category; ?></category>
                    <tags><?php echo $tags; ?></tags>
                    <pubDate><?php echo date("D, d M Y H:i:s O", filemtime($postFile)); ?></pubDate>
                </item>

                <?php
            }
        }
        ?>
    </channel>
</rss>
