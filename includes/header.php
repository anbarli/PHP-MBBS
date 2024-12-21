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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Tenth navbar example">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
        <ul class="navbar-nav">
          <li class="nav-item h3">
            <a class="nav-link active" aria-current="page" href="<?php echo $basePath; ?>"><?php echo SITE_NAME; ?></a>
          </li>
        </ul>
      </div>
    </div>
</nav>

<div class="container p-2">
	<div class="row g-4 py-5">  
		<div class="col">