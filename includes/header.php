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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/includes/style.css">
	<link rel="canonical" href="<?php echo $protocol . $host . $_SERVER[REQUEST_URI]; ?>">
	<meta property="og:title" content="<?php echo isset($seoTitle) ? htmlspecialchars($seoTitle) : DEFAULT_TITLE; ?>">
	<meta property="og:description" content="<?php echo isset($seoDescription) ? htmlspecialchars($seoDescription) : DEFAULT_DESCRIPTION; ?>">
	<meta property="og:url" content="<?php echo BASE_URL . "/" . ($_GET['slug'] ?? ''); ?>">
	<meta property="og:type" content="article">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo isset($seoTitle) ? htmlspecialchars($seoTitle) : DEFAULT_TITLE; ?>">
	<meta name="twitter:description" content="<?php echo isset($seoDescription) ? htmlspecialchars($seoDescription) : DEFAULT_DESCRIPTION; ?>">
</head>
<body>
    <header>
        <h1><?php echo SITE_NAME; ?></h1>
        <nav>
            <a href="<?php echo BASE_URL; ?>/index.php">Blog</a>
            <a href="<?php echo BASE_URL; ?>/rss.php">RSS</a>
        </nav>
    </header>
