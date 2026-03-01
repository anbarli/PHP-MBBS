<!DOCTYPE html>
<html lang="tr">
<head>
	<?php
	// Ensure these variables are always defined
	if (!isset($protocol)) {
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
	}
	if (!isset($host)) {
		$host = htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
	}
	
	// Ensure SEO variables are defined
	if (!isset($seoTitle)) {
		$seoTitle = DEFAULT_TITLE;
	}
	if (!isset($seoDescription)) {
		$seoDescription = DEFAULT_DESCRIPTION;
	}
	if (!isset($seoRobots) || trim((string)$seoRobots) === '') {
		$seoRobots = 'index, follow';
	}
	
	// Generate keywords from tags if available
	$seoKeywords = '';
	if (isset($tags) && is_array($tags)) {
		$seoKeywords = implode(', ', $tags);
	}
	
	$seoTitle = preg_replace('/\s+/', ' ', $seoTitle);
	$seoTitle = trim(htmlspecialchars($seoTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	$seoDescription = preg_replace('/\s+/', ' ', $seoDescription);
	$seoDescription = trim(htmlspecialchars($seoDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	$seoKeywords = trim(htmlspecialchars($seoKeywords, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	$seoRobots = trim(htmlspecialchars($seoRobots, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
	<meta name="keywords" content="<?php echo htmlspecialchars($seoKeywords); ?>">
	<meta name="author" content="<?php echo AUTHOR_NAME; ?>">
	<meta name="robots" content="<?php echo $seoRobots; ?>">
	<meta name="language" content="<?php echo DEFAULT_LANGUAGE; ?>">
	<meta name="revisit-after" content="7 days">
	<meta name="distribution" content="global">
	<meta name="rating" content="general">
	
	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="article">
	<meta property="og:url" content="<?php echo BASE_URL . "/" . ($_GET['slug'] ?? ''); ?>">
	<meta property="og:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
	<meta property="og:description" content="<?php echo htmlspecialchars($seoDescription); ?>">
	<meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
	<meta property="og:locale" content="<?php echo DEFAULT_LOCALE; ?>">
	<?php if (isset($date)): ?>
	<meta property="article:published_time" content="<?php echo date('Y-m-d\TH:i:sP', strtotime($date)); ?>">
	<?php endif; ?>
	<?php if (isset($category)): ?>
	<meta property="article:section" content="<?php echo htmlspecialchars($category); ?>">
	<?php endif; ?>
	<?php if (isset($tags) && is_array($tags)): foreach($tags as $tag): ?>
	<meta property="article:tag" content="<?php echo htmlspecialchars($tag); ?>">
	<?php endforeach; endif; ?>
	
	<!-- Twitter -->
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
	<meta name="twitter:description" content="<?php echo htmlspecialchars($seoDescription); ?>">
	<?php if (!empty(TWITTER_USERNAME)): ?>
	<meta name="twitter:site" content="<?php echo TWITTER_USERNAME; ?>">
	<?php endif; ?>
	
	<!-- Structured Data -->
	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "BlogPosting",
		"headline": "<?php echo htmlspecialchars($seoTitle); ?>",
		"description": "<?php echo htmlspecialchars($seoDescription); ?>",
		"author": {
			"@type": "Person",
			"name": "<?php echo AUTHOR_NAME; ?>"
			<?php if (!empty(AUTHOR_EMAIL)): ?>,
			"email": "<?php echo AUTHOR_EMAIL; ?>"
			<?php endif; ?>
		},
		"publisher": {
			"@type": "Organization",
			"name": "<?php echo SITE_NAME; ?>",
			"url": "<?php echo BASE_URL; ?>"
		},
		"mainEntityOfPage": {
			"@type": "WebPage",
			"@id": "<?php echo BASE_URL . "/" . ($_GET['slug'] ?? ''); ?>"
		}
		<?php if (isset($date)): ?>,
		"datePublished": "<?php echo date('Y-m-d\TH:i:sP', strtotime($date)); ?>"
		<?php endif; ?>
		<?php if (isset($category)): ?>,
		"articleSection": "<?php echo htmlspecialchars($category); ?>"
		<?php endif; ?>
		<?php if (isset($tags) && is_array($tags)): ?>,
		"keywords": "<?php echo htmlspecialchars(implode(', ', $tags)); ?>"
		<?php endif; ?>
	}
	</script>
	
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.8.1/github-markdown.min.css"/>
    <link rel="stylesheet" href="<?php echo assetPath('includes/style.css'); ?>">
	
	<!-- Performance Optimizations -->
	<link rel="preconnect" href="https://cdn.jsdelivr.net">
	<link rel="preconnect" href="https://cdnjs.cloudflare.com">
	<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
	<?php if (!empty(GA_TRACKING_ID)): ?>
	<link rel="preconnect" href="https://www.googletagmanager.com">
	<link rel="dns-prefetch" href="//www.googletagmanager.com">
	<?php endif; ?>
	
	<!-- Preload critical resources -->
	<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style">
	<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style">
	
	<!-- Canonical and alternate links -->
	<link rel="canonical" href="<?php echo $protocol . $host . $_SERVER['REQUEST_URI']; ?>">
	<link rel="alternate" type="application/rss+xml" title="<?php echo SITE_NAME; ?> RSS" href="<?php echo BASE_URL; ?>rss">
	<link rel="sitemap" type="application/xml" title="Sitemap" href="<?php echo BASE_URL; ?>sitemap.xml">
	
	<!-- Additional meta tags for better SEO -->
	<meta name="theme-color" content="#212529">
	<meta name="msapplication-TileColor" content="#212529">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="<?php echo SITE_NAME; ?>">
	
	<!-- Performance and security headers -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="format-detection" content="telephone=no">
	
	<?php if (!empty(GA_TRACKING_ID)): ?>
	<!-- Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA_TRACKING_ID; ?>"></script>
	<script>function gtag(){dataLayer.push(arguments)}window.dataLayer=window.dataLayer||[],gtag("js",new Date),gtag("config","<?php echo GA_TRACKING_ID; ?>")</script>
	<?php endif; ?>
	
	<!-- Fallback dark mode styles in case CSS file doesn't load -->
	<style>
		/* Palette: 3A015C / 32004F / 220135 / 190028 / 11001C */
		body.dark-mode { background: #11001c !important; color: #f2ebff !important; }
		body.dark-mode .navbar,
		body.dark-mode .navbar-dark,
		body.dark-mode .footer { background: #190028 !important; border-color: #32004f !important; }
		body.dark-mode .card,
		body.dark-mode .card-body,
		body.dark-mode .list-group-item,
		body.dark-mode .alert,
		body.dark-mode .breadcrumb,
		body.dark-mode .dropdown-menu { background: #190028 !important; border-color: #32004f !important; color: #f2ebff !important; }
		body.dark-mode .card-header { background: #220135 !important; border-color: #32004f !important; color: #f2ebff !important; }
		body.dark-mode .form-control { background: #220135 !important; border-color: #32004f !important; color: #f2ebff !important; }
		body.dark-mode a,
		body.dark-mode .text-dark,
		body.dark-mode a.text-dark,
		body.dark-mode a.text-secondary,
		body.dark-mode a.text-body-secondary { color: #c8b7e6 !important; }
		body.dark-mode a:hover { color: #f2ebff !important; }
		body.dark-mode .text-muted,
		body.dark-mode .text-secondary,
		body.dark-mode .text-body-secondary { color: #c8b7e6 !important; }
		body.dark-mode .markdown-body code,
		body.dark-mode .markdown-body pre { background: #220135 !important; color: #f2ebff !important; }
		body.dark-mode .markdown-body blockquote { border-left-color: #3a015c !important; color: #c8b7e6 !important; }
		body.dark-mode .share-panel { background: #220135 !important; border: 1px solid #32004f !important; }
	</style>
</head>
<body>
<?php
function getCategoryListForMenu() {
	$stats = getBlogStats();
	return $stats['categories'] ?? [];
}
$categoryList = getCategoryListForMenu();
// ...existing code...
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark site-nav" aria-label="Ana menü">
    <div class="container">
      <a class="navbar-brand site-brand" href="<?php echo BASE_PATH; ?>"><?php echo SITE_NAME; ?></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbara" aria-controls="navbara" aria-expanded="false" aria-label="Menüyü aç/kapat">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbara">
                <div class="ms-auto d-flex align-items-center nav-tools">
                    <div class="dropdown nav-categories">
                        <button class="btn btn-outline-light nav-btn dropdown-toggle" type="button" id="kategoriDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-folder me-2"></i>Kategoriler
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end nav-dropdown" aria-labelledby="kategoriDropdown">
                            <?php foreach ($categoryList as $cat): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo BASE_PATH . 'cat/' . urlencode(strtolower($cat)); ?>">
                                        <i class="bi bi-tag-fill text-primary me-2"></i><?php echo htmlspecialchars($cat); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <form class="d-flex nav-search" role="search" method="get" action="<?php echo BASE_URL; ?>search">
                        <input class="form-control nav-search-input" type="search" name="q" placeholder="Yazı ara..." aria-label="Yazı ara" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        <button class="btn btn-outline-light nav-btn nav-search-btn" type="submit" aria-label="Ara">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    <button id="darkModeToggle" class="btn btn-outline-light nav-btn nav-theme-toggle" type="button" title="Karanlık modu aç/kapat" aria-label="Karanlık mod">
                        <i id="moonIcon" class="bi bi-moon-fill" style="display:inline;"></i>
                        <i id="sunIcon" class="bi bi-sun-fill" style="display:none;"></i>
                    </button>
                </div>
      </div>
    </div>
</nav>

<main id="main-content">
	<div class="container p-2">
		<div class="row g-4 py-5">
			<div class="col">
