<?php
include('config.php');

$seoTitle = 'Sayfa Bulunamadı - ' . SITE_NAME;
$seoDescription = 'Aradığınız sayfa bulunamadı. Ana sayfaya dönmek için tıklayın.';

include('includes/header.php');
?>

<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="display-1 text-muted">404</h1>
            <h2 class="mb-4">Sayfa Bulunamadı</h2>
            <p class="lead mb-4">Aradığınız sayfa mevcut değil veya taşınmış olabilir.</p>
            <div class="d-grid gap-2 d-md-block">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary me-md-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house me-2" viewBox="0 0 16 16">
                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                    </svg>
                    Ana Sayfa
                </a>
                <a href="<?php echo BASE_URL; ?>rss" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-rss me-2" viewBox="0 0 16 16">
                        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                        <path d="M6.002 12a4 4 0 1 1 8 0 4 4 0 0 1-8 0zM2 4a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm-1 5a1 1 0 0 1 1-1h1.302a1 1 0 0 1 .937.649l.797 2.388a1 1 0 0 1-.234 1.21l-.299.299a1 1 0 0 1-1.21-.234L1.649 9.351A1 1 0 0 1 2 8.5z"/>
                    </svg>
                    RSS Feed
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?> 