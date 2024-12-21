<!DOCTYPE html>
<html lang="tr">
<head>
	<?php
	$seoTitle = preg_replace('/\s+/', ' ', $seoTitle);
	$seoTitle = trim(htmlspecialchars($seoTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	$seoDescription = preg_replace('/\s+/', ' ', $seoDescription);
	$seoDescription = trim(htmlspecialchars($seoDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo isset($seoTitle) ? htmlspecialchars($seoTitle) : DEFAULT_TITLE; ?></title>
    <meta name="description" content="<?php echo isset($seoDescription) ? htmlspecialchars($seoDescription) : DEFAULT_DESCRIPTION; ?>">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.8.1/github-markdown.min.css"/>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/includes/style.css">
	<link rel="canonical" href="<?php echo $protocol . $host . $_SERVER['REQUEST_URI']; ?>">
	<meta property="og:title" content="<?php echo isset($seoTitle) ? htmlspecialchars($seoTitle) : DEFAULT_TITLE; ?>">
	<meta property="og:description" content="<?php echo isset($seoDescription) ? htmlspecialchars($seoDescription) : DEFAULT_DESCRIPTION; ?>">
	<meta property="og:url" content="<?php echo BASE_URL . "/" . ($_GET['slug'] ?? ''); ?>">
	<meta property="og:type" content="article">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo isset($seoTitle) ? htmlspecialchars($seoTitle) : DEFAULT_TITLE; ?>">
	<meta name="twitter:description" content="<?php echo isset($seoDescription) ? htmlspecialchars($seoDescription) : DEFAULT_DESCRIPTION; ?>">
</head>
<body>

<header data-bs-theme="dark">
  <div class="collapse text-bg-dark" id="navbarHeader">
    <div class="container">
      <div class="row">
        <div class="col-sm-8 col-md-7 py-4">
          <h4><?php echo SITE_NAME; ?></h4>
          <p class="text-body-secondary"><?php echo DEFAULT_DESCRIPTION; ?></p>
        </div>
        <div class="col-sm-4 offset-md-1 py-4">
          <ul class="list-unstyled">
            <li><a href="<?php echo BASE_URL; ?>/index.php" class="text-white">Blog</a></li>
            <li><a href="<?php echo BASE_URL; ?>/rss.php" target="_blank" class="text-white">RSS</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a href="index.php" class="navbar-brand d-flex align-items-center">
        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/></svg>
        <strong><?php echo SITE_NAME; ?></strong>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </div>
</header>

<div class="container p-2">
	<div class="row g-4 py-5">  
		<div class="col">