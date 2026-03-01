		</div>
	</div>
</div>
</main>

<footer class="footer mt-auto py-3 bg-body-secondary">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="text-center text-body-secondary border-bottom pb-3 mb-3">
                    Powered By <a href="https://github.com/anbarli/PHP-MBBS" class="text-secondary" target="_blank" rel="noopener">PHP-MBBS</a>
                </p>
                <p class="text-center text-body-secondary">
                    &copy; <?php echo date("Y"); ?> <?php echo SITE_NAME; ?> 
                    <a href="<?php echo BASE_URL; ?>rss" target="_blank" class="text-secondary" title="RSS Feed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 448 512">
                            <path d="M0 64C0 46.3 14.3 32 32 32c229.8 0 416 186.2 416 416c0 17.7-14.3 32-32 32s-32-14.3-32-32C384 253.6 226.4 96 32 96C14.3 96 0 81.7 0 64zM0 416a64 64 0 1 1 128 0A64 64 0 1 1 0 416zM32 160c159.1 0 288 128.9 288 288c0 17.7-14.3 32-32 32s-32-14.3-32-32c0-123.7-100.3-224-224-224c-17.7 0-32-14.3-32-32s14.3-32 32-32z"/>
                        </svg>
                    </a>
                </p>
            </div>
            <div class="col-md-6">
                <div class="text-center text-body-secondary">
                    <small>
                        <?php 
                        $postCount = getPostCount();
                        $categoryCount = getCategoryCount();
                        ?>
                        <span class="badge bg-secondary me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-file-text me-1" viewBox="0 0 16 16">
                                <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                            </svg>
                            <?php echo $postCount; ?> Yazı
                        </span>
                        <span class="badge bg-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-folder me-1" viewBox="0 0 16 16">
                                <path d="M.54 3.87L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z"/>
                            </svg>
                            <?php echo $categoryCount; ?> Kategori
                        </span>
                    </small>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Son güncelleme: <?php echo date('d.m.Y H:i'); ?>
                    </small>
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
