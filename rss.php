<?php
include('config.php');

header("Content-Type: application/rss+xml; charset=UTF-8");

$posts = array_diff(scandir(POSTS_DIR), array('..', '.'));

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title><?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?> RSS</title>
        <link><?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?></link>
        <description><?php echo htmlspecialchars(DEFAULT_DESCRIPTION, ENT_QUOTES, 'UTF-8'); ?></description>
        <language>tr</language>
        <lastBuildDate><?php echo date("D, d M Y H:i:s O"); ?></lastBuildDate>
        <pubDate><?php echo date("D, d M Y H:i:s O"); ?></pubDate>
        <ttl>60</ttl>
        <atom:link href="<?php echo htmlspecialchars(BASE_URL . 'rss', ENT_QUOTES, 'UTF-8'); ?>" rel="self" type="application/rss+xml" />

        <?php
        $postCount = 0;
        $maxPosts = 20; // Maximum number of posts in RSS feed
        
        foreach ($posts as $post) {
            if ($postCount >= $maxPosts) break;
            
            $postFile = POSTS_DIR . $post;
            $postData = getPostContent($postFile);
            $postSlug = pathinfo($post, PATHINFO_FILENAME);

            if ($postData) {
                $postCount++;
                
                // Başlık, içerik, kategori ve etiketleri al
                $title = htmlspecialchars($postData['meta']['title'] ?? 'Başlık Yok', ENT_QUOTES, 'UTF-8');
                $content = htmlspecialchars($postData['content'], ENT_QUOTES, 'UTF-8');
                $content = preg_replace('/\s+/', ' ', $content);
                $category = htmlspecialchars($postData['meta']['category'] ?? 'Genel', ENT_QUOTES, 'UTF-8');
                $tags = implode(', ', $postData['meta']['tags'] ?? []);
                $description = substr(strip_tags($content), 0, 200);
                $pubDate = date("D, d M Y H:i:s O", filemtime($postFile));
                $guid = BASE_URL . $postSlug;

                ?>

                <item>
                    <title><?php echo $title; ?></title>
                    <link><?php echo BASE_URL . $postSlug; ?></link>
                    <guid><?php echo htmlspecialchars($guid, ENT_QUOTES, 'UTF-8'); ?></guid>
                    <description><![CDATA[
                        <?php echo $description; ?>... 
                    ]]></description>
                    <category><?php echo $category; ?></category>
                    <tags><?php echo $tags; ?></tags>
                    <pubDate><?php echo $pubDate; ?></pubDate>
                    <author>anbarli.com.tr</author>
                </item>

                <?php
            }
        }
        
        if ($postCount === 0) {
            ?>
            <item>
                <title>Henüz yazı bulunmuyor</title>
                <link><?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?></link>
                <description>Blog henüz yazı içermiyor.</description>
                <pubDate><?php echo date("D, d M Y H:i:s O"); ?></pubDate>
            </item>
            <?php
        }
        ?>
    </channel>
</rss>
