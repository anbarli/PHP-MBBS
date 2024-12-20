<?php
include('config.php');
include('includes/markdown.php');

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
            $postData = getPostContent($postFile);
			$postData = preg_replace('/\s+/', ' ', $postData);
            $postSlug = pathinfo($post, PATHINFO_FILENAME);

            if ($postData) {
                ?>
                <item>
                    <title><?php echo trim(htmlspecialchars($postData['title'])); ?></title>
                    <link><?php echo (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://" . $_SERVER[HTTP_HOST] . dirname($_SERVER['PHP_SELF']) . '/' . $postSlug; ?></link>
                    <description><![CDATA[<?php echo substr($postData['content'], 0, 200); ?>...]]></description>
                    <pubDate><?php echo date("D, d M Y H:i:s O", filemtime($postFile)); ?></pubDate>
                </item>
                <?php
            }
        }
        ?>
    </channel>
</rss>
