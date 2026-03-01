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
	if (!isset($structuredDataType) || trim((string)$structuredDataType) === '') {
		$structuredDataType = 'WebSite';
	}
	if (!isset($loadMarkdownCss)) {
		$loadMarkdownCss = false;
	}

	if (!function_exists('buildNormalizedCanonicalUrl')) {
		function buildNormalizedCanonicalUrl($protocol, $host, $requestUri) {
			$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
			$query = parse_url($requestUri, PHP_URL_QUERY) ?: '';
			$params = [];

			if ($query !== '') {
				parse_str($query, $params);
			}

			foreach ($params as $key => $value) {
				$lowerKey = strtolower((string)$key);
				$isTrackingParam = strpos($lowerKey, 'utm_') === 0 || in_array($lowerKey, [
					'fbclid',
					'gclid',
					'msclkid',
					'mc_cid',
					'mc_eid',
					'_hsenc',
					'_hsmi'
				], true);

				if ($isTrackingParam) {
					unset($params[$key]);
				}
			}

			if (!empty($params)) {
				ksort($params);
			}

			$normalizedQuery = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
			return $protocol . $host . $path . ($normalizedQuery !== '' ? '?' . $normalizedQuery : '');
		}
	}

	if (!isset($seoCanonical) || trim((string)$seoCanonical) === '') {
		$seoCanonical = buildNormalizedCanonicalUrl($protocol, $host, $_SERVER['REQUEST_URI'] ?? '/');
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
	$seoCanonical = trim(htmlspecialchars($seoCanonical, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
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
	<?php
	$structuredData = [
		'@context' => 'https://schema.org',
		'@type' => $structuredDataType
	];

	if ($structuredDataType === 'BlogPosting') {
		$structuredData['headline'] = $seoTitle;
		$structuredData['description'] = $seoDescription;
		$structuredData['author'] = [
			'@type' => 'Person',
			'name' => AUTHOR_NAME
		];
		if (!empty(AUTHOR_EMAIL)) {
			$structuredData['author']['email'] = AUTHOR_EMAIL;
		}
		$structuredData['publisher'] = [
			'@type' => 'Organization',
			'name' => SITE_NAME,
			'url' => BASE_URL
		];
		$structuredData['mainEntityOfPage'] = [
			'@type' => 'WebPage',
			'@id' => $seoCanonical
		];
		if (isset($date)) {
			$structuredData['datePublished'] = date('Y-m-d\TH:i:sP', strtotime($date));
		}
		if (isset($category)) {
			$structuredData['articleSection'] = $category;
		}
		if (isset($tags) && is_array($tags) && !empty($tags)) {
			$structuredData['keywords'] = implode(', ', $tags);
		}
	} elseif ($structuredDataType === 'CollectionPage') {
		$structuredData['name'] = $seoTitle;
		$structuredData['description'] = $seoDescription;
		$structuredData['url'] = $seoCanonical;
		$structuredData['isPartOf'] = [
			'@type' => 'WebSite',
			'name' => SITE_NAME,
			'url' => BASE_URL
		];
	} else {
		$structuredData['name'] = SITE_NAME;
		$structuredData['url'] = BASE_URL;
		$structuredData['description'] = DEFAULT_DESCRIPTION;
		$structuredData['publisher'] = [
			'@type' => 'Organization',
			'name' => AUTHOR_NAME
		];
		$structuredData['potentialAction'] = [
			'@type' => 'SearchAction',
			'target' => BASE_URL . 'search?q={search_term_string}',
			'query-input' => 'required name=search_term_string'
		];
	}
	?>
	<script type="application/ld+json"><?php echo json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous"/>
	<?php if ($loadMarkdownCss): ?>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.8.1/github-markdown.min.css" integrity="sha512-VE/TgMJnr0xqgrRN5c5DVDX7f7Q3rHRqcq9qHeBxIrHQrLPnM0i6cLJbsBxoQ0VxYUHT+B4Xmo854R3Z0CnSfmQ==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
	<?php endif; ?>
    <link rel="stylesheet" href="<?php echo assetPath('includes/style.css'); ?>">

	<!-- Performance Optimizations -->
	<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
	<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
	<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
	<?php if (defined('GA_TRACKING_ID') && !empty(trim(GA_TRACKING_ID))): ?>
	<link rel="preconnect" href="https://www.googletagmanager.com">
	<link rel="preconnect" href="https://analytics.google.com">
	<link rel="dns-prefetch" href="//www.googletagmanager.com">
	<link rel="dns-prefetch" href="//analytics.google.com">
	<?php endif; ?>

	<!-- Canonical and alternate links -->
	<link rel="canonical" href="<?php echo $seoCanonical; ?>">
	<link rel="alternate" type="application/rss+xml" title="<?php echo SITE_NAME; ?> RSS" href="<?php echo BASE_URL; ?>rss">
	<link rel="sitemap" type="application/xml" title="Sitemap" href="<?php echo BASE_URL; ?>sitemap.xml">

	<!-- Additional meta tags for better SEO -->
	<meta name="theme-color" content="#212529">
	<meta name="msapplication-TileColor" content="#212529">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="<?php echo SITE_NAME; ?>">

	<!-- Performance and security headers -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="format-detection" content="telephone=no">

	<?php if (defined('GA_TRACKING_ID') && !empty(trim(GA_TRACKING_ID))): ?>
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
<script>
	(function () {
		try {
			var storedTheme = localStorage.getItem('theme');
			var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			var theme = storedTheme || (prefersDark ? 'dark' : 'light');
			if (theme === 'dark') {
				document.body.classList.add('dark-mode');
				document.documentElement.setAttribute('data-bs-theme', 'dark');
			} else {
				document.documentElement.setAttribute('data-bs-theme', 'light');
			}
		} catch (e) {
			document.documentElement.setAttribute('data-bs-theme', 'light');
		}
	})();
</script>
<?php
function getCategoryListForMenu() {
	$stats = getBlogStats();
	return $stats['categories'] ?? [];
}
$categoryList = getCategoryListForMenu();
// ...existing code...
?>

<header class="p-3 text-bg-dark site-header">
	<div class="container">
		<div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
			<a href="<?php echo BASE_PATH; ?>" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none site-brand">
				<?php echo SITE_NAME; ?>
			</a>

			<ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
				<li><a href="<?php echo BASE_PATH; ?>" class="nav-link px-2 text-secondary">Home</a></li>
				<li class="nav-item dropdown">
					<a class="nav-link px-2 text-white dropdown-toggle" href="#" id="kategoriDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						Kategoriler
					</a>
					<ul class="dropdown-menu nav-dropdown" aria-labelledby="kategoriDropdown">
						<?php foreach ($categoryList as $cat): ?>
							<li>
								<a class="dropdown-item" href="<?php echo BASE_PATH . 'cat/' . urlencode(strtolower($cat)); ?>">
									<?php echo htmlspecialchars($cat); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
				<li><a href="<?php echo BASE_URL; ?>rss" class="nav-link px-2 text-white">RSS</a></li>
			</ul>

			<form class="col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" role="search" method="get" action="<?php echo BASE_URL; ?>search">
				<input type="search" class="form-control form-control-dark text-bg-dark" name="q" placeholder="Search..." aria-label="Search" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : ''; ?>">
			</form>

			<div class="text-end d-flex gap-2">
				<button id="darkModeToggle" class="btn btn-warning" type="button" title="Tema degistir" aria-label="Tema degistir" aria-pressed="false">
					<i id="moonIcon" class="bi bi-moon-fill" style="display:inline;"></i>
					<i id="sunIcon" class="bi bi-sun-fill" style="display:none;"></i>
				</button>
			</div>
		</div>
	</div>
</header>

<main id="main-content">
	<div class="container p-2">
		<div class="row g-4 py-5">
			<div class="col">

