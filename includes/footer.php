		</div>
	</div>
</div>
</main>

<footer class="footer mt-auto py-3 bg-body-secondary">
    <div class="container">
        <div class="footer-inner">
            <?php
            if (!isset($categoryList) || !is_array($categoryList)) {
                $categoryList = getCategoryListForMenu();
            }
            ?>
            <div class="footer-categories-line">
                <?php if (!empty($categoryList)): ?>
                    <?php foreach ($categoryList as $cat): ?>
                        <a class="footer-category-link" href="<?php echo BASE_PATH . 'cat/' . urlencode(strtolower($cat)); ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted">Kategori bulunmuyor</span>
                <?php endif; ?>
            </div>

            <div class="footer-main-line">
                <div class="footer-meta">
                    <span>&copy; <?php echo date("Y"); ?> <?php echo SITE_NAME; ?></span>
                    <span class="footer-sep">|</span>
                    <span>Powered By <a href="https://github.com/anbarli/PHP-MBBS" class="text-secondary" target="_blank" rel="noopener">PHP-MBBS</a></span>
                </div>
                <div class="footer-meta footer-meta-right">
                    <a href="<?php echo BASE_URL; ?>rss" target="_blank" class="text-secondary footer-rss-link" title="RSS Feed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512">
                            <path d="M0 64C0 46.3 14.3 32 32 32c229.8 0 416 186.2 416 416c0 17.7-14.3 32-32 32s-32-14.3-32-32C384 253.6 226.4 96 32 96C14.3 96 0 81.7 0 64zM0 416a64 64 0 1 1 128 0A64 64 0 1 1 0 416zM32 160c159.1 0 288 128.9 288 288c0 17.7-14.3 32-32 32s-32-14.3-32-32c0-123.7-100.3-224-224-224c-17.7 0-32-14.3-32-32s14.3-32 32-32z"/>
                        </svg>
                        RSS
                    </a>
                    <span class="footer-sep">|</span>
                    <span>Son güncelleme: <?php echo date('d.m.Y H:i'); ?></span>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Performance optimized JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

<script>
window.PHP_MBBS_CONFIG = {
    baseUrl: <?php echo json_encode(BASE_URL, JSON_UNESCAPED_SLASHES); ?>,
    siteName: <?php echo json_encode(SITE_NAME, JSON_UNESCAPED_UNICODE); ?>,
    defaultDescription: <?php echo json_encode(DEFAULT_DESCRIPTION, JSON_UNESCAPED_UNICODE); ?>,
    authorName: <?php echo json_encode(AUTHOR_NAME, JSON_UNESCAPED_UNICODE); ?>,
    gaTrackingId: <?php echo json_encode(GA_TRACKING_ID, JSON_UNESCAPED_UNICODE); ?>,
    prefetchUrls: [
        <?php echo json_encode(BASE_URL . 'rss', JSON_UNESCAPED_SLASHES); ?>,
        <?php echo json_encode(BASE_URL . 'sitemap.xml', JSON_UNESCAPED_SLASHES); ?>
    ]
};
</script>
<script src="<?php echo assetPath('includes/js/theme.js'); ?>" defer></script>
<script src="<?php echo assetPath('includes/js/perf.js'); ?>" defer></script>
<script src="<?php echo assetPath('includes/js/structured-data.js'); ?>" defer></script>

</body>
</html>


