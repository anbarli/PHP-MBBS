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
	?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
	<meta name="keywords" content="<?php echo htmlspecialchars($seoKeywords); ?>">
	<meta name="author" content="<?php echo AUTHOR_NAME; ?>">
	<meta name="robots" content="index, follow">
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/includes/style.css">
	
	<!-- Performance Optimizations -->
	<link rel="preconnect" href="https://cdn.jsdelivr.net">
	<link rel="preconnect" href="https://cdnjs.cloudflare.com">
	<link rel="preconnect" href="https://www.googletagmanager.com">
	<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
	<link rel="dns-prefetch" href="//www.googletagmanager.com">
	
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
		/* Critical dark mode styles */
		body.dark-mode {
			background-color: #1a1a1a !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .navbar,
		body.dark-mode .navbar-dark {
			background-color: #2d2d2d !important;
			border-bottom: 1px solid #404040 !important;
		}
		
		body.dark-mode .navbar .nav-link,
		body.dark-mode .navbar .navbar-brand {
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .card {
			background-color: #2d2d2d !important;
			border-color: #404040 !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .card-header {
			background-color: #1a1a1a !important;
			border-bottom-color: #404040 !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .card-body {
			background-color: #2d2d2d !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .list-group-item {
			background-color: #2d2d2d !important;
			border-color: #404040 !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .alert {
			background-color: #2d2d2d !important;
			border-color: #404040 !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .markdown-body {
			color: #e0e0e0 !important;
			background-color: transparent !important;
		}
		
		body.dark-mode .markdown-body h1,
		body.dark-mode .markdown-body h2,
		body.dark-mode .markdown-body h3,
		body.dark-mode .markdown-body h4,
		body.dark-mode .markdown-body h5,
		body.dark-mode .markdown-body h6 {
			color: #ffffff !important;
			border-bottom-color: #404040 !important;
		}
		
		body.dark-mode .markdown-body code,
		body.dark-mode .markdown-body pre {
			background-color: #1a1a1a !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .markdown-body blockquote {
			border-left-color: #404040 !important;
			color: #b0b0b0 !important;
		}
		
		body.dark-mode .markdown-body a {
			color: #4a9eff !important;
		}
		
		body.dark-mode .markdown-body a:hover {
			color: #6bb6ff !important;
		}
		
		body.dark-mode .form-control {
			background-color: #2d2d2d !important;
			border-color: #404040 !important;
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .form-control:focus {
			background-color: #2d2d2d !important;
			border-color: #4a9eff !important;
			color: #e0e0e0 !important;
			box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25) !important;
		}
		
		body.dark-mode .text-muted {
			color: #b0b0b0 !important;
		}
		
		body.dark-mode .bg-light {
			background-color: #2d2d2d !important;
		}
		
		body.dark-mode .bg-body-secondary {
			background-color: #1a1a1a !important;
		}
		
		body.dark-mode .breadcrumb {
			background-color: #2d2d2d !important;
			border: 1px solid #404040 !important;
			color: #e0e0e0 !important;
		}
		
		/* Fix breadcrumb links in dark mode */
		body.dark-mode .breadcrumb a {
			color: #4a9eff !important;
		}
		
		body.dark-mode .breadcrumb a:hover {
			color: #6bb6ff !important;
		}
		
		body.dark-mode .breadcrumb-item.active {
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .breadcrumb-item + .breadcrumb-item::before {
			color: #888 !important;
		}
		
		body.dark-mode .footer {
			background-color: #1a1a1a !important;
			color: #e0e0e0 !important;
		}
		
		/* Fix for text-dark links in dark mode */
		body.dark-mode .text-dark {
			color: #e0e0e0 !important;
		}
		
		body.dark-mode .text-dark:hover {
			color: #4a9eff !important;
		}
		
		body.dark-mode a.text-dark {
			color: #4a9eff !important;
		}
		
		body.dark-mode a.text-dark:hover {
			color: #6bb6ff !important;
		}
		
		/* Fix for text-secondary in dark mode */
		body.dark-mode .text-secondary {
			color: #b0b0b0 !important;
		}
		
		body.dark-mode a.text-secondary {
			color: #4a9eff !important;
		}
		
		body.dark-mode a.text-secondary:hover {
			color: #6bb6ff !important;
		}
		
		/* Fix for text-body-secondary in dark mode */
		body.dark-mode .text-body-secondary {
			color: #b0b0b0 !important;
		}
		
		body.dark-mode a.text-body-secondary {
			color: #4a9eff !important;
		}
		
		body.dark-mode a.text-body-secondary:hover {
			color: #6bb6ff !important;
		}
		
		/* Smooth transitions */
		* {
			transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
		}
	</style>
</head>
<body>
<?php
// Kategorileri ve etiketleri topla
$categoryList = [];
$tagList = [];
$postFiles = array_diff(scandir(POSTS_DIR), array('..', '.'));
foreach ($postFiles as $post) {
	if (pathinfo($post, PATHINFO_EXTENSION) === 'md') {
		$postFile = POSTS_DIR . $post;
		$postData = getPostContent($postFile);
		if ($postData) {
			if (isset($postData['meta']['category'])) {
				$category = trim($postData['meta']['category']);
				if (!empty($category) && !in_array($category, $categoryList)) {
					$categoryList[] = $category;
				}
			}
			if (isset($postData['meta']['tags']) && is_array($postData['meta']['tags'])) {
				foreach ($postData['meta']['tags'] as $tag) {
					$tag = trim($tag);
					if (!empty($tag) && !in_array($tag, $tagList)) {
						$tagList[] = $tag;
					}
				}
			}
		}
	}
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Tenth navbar example">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbara" aria-controls="navbara" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-md-center" id="navbara">
				<ul class="navbar-nav mb-2 mb-lg-0">
					<li class="nav-item h3">
						<a class="nav-link active" aria-current="page" href="<?php echo BASE_PATH; ?>"><?php echo SITE_NAME; ?></a>
					</li>
				</ul>
				<div class="d-flex align-items-center ms-auto" style="gap: 1.5rem;">
					<!-- Kategoriler Dropdown -->
					<div class="dropdown">
						<a class="btn btn-lg btn-outline-light dropdown-toggle px-4 py-2 fw-bold" href="#" id="kategoriDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:1.2rem;">
							<i class="bi bi-folder me-2"></i> Kategoriler
						</a>
						<ul class="dropdown-menu dropdown-menu-end shadow-lg rounded" aria-labelledby="kategoriDropdown" style="min-width: 220px; font-size:1.1rem;">
							<?php foreach ($categoryList as $cat): ?>
								<li><a class="dropdown-item py-2" href="<?php echo BASE_PATH . 'cat/' . urlencode(strtolower($cat)); ?>" style="font-size:1.1rem;"><i class="bi bi-tag-fill text-primary me-2"></i><?php echo htmlspecialchars($cat); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<!-- Search Form -->
					<form class="d-flex" role="search" method="get" action="<?php echo BASE_URL; ?>search">
						<input class="form-control me-2" type="search" name="q" placeholder="Ara..." aria-label="Ara" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : ''; ?>" style="min-width: 180px; font-size:1.1rem; height:48px;">
						<button class="btn btn-outline-light" type="submit" style="height:48px; font-size:1.1rem;">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
								<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
							</svg>
						</button>
					</form>
					<!-- Dark Mode Toggle -->
					<button id="darkModeToggle" class="btn btn-outline-light" type="button" title="Karanlık Modu Aç/Kapat" aria-label="Karanlık Mod" style="height:48px; font-size:1.1rem;">
						<i id="moonIcon" class="bi bi-moon-fill" style="display:inline;"></i>
						<i id="sunIcon" class="bi bi-sun-fill" style="display:none;"></i>
					</button>
				</div>
        
      </div>
    </div>
</nav>

<div class="container p-2">
	<div class="row g-4 py-5">  
		<div class="col">