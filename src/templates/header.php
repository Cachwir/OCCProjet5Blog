<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/html">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title><?php echo $App->getConfig()['site_title']; ?></title>

    <!-- Maren One Page Template CSS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,600">
    <link rel="stylesheet" href="assets/maren-one-page-template/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/maren-one-page-template/assets/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/maren-one-page-template/assets/css/animate.css">
    <link rel="stylesheet" href="assets/maren-one-page-template/assets/css/form-elements.css">
    <link rel="stylesheet" href="assets/maren-one-page-template/assets/css/style.css">
    <link rel="stylesheet" href="assets/maren-one-page-template/assets/css/media-queries.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Maren One Page Template Favicon and touch icons -->
    <link rel="shortcut icon" href="assets/maren-one-page-template/assets/ico/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/maren-one-page-template/assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/maren-one-page-template/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/maren-one-page-template/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/maren-one-page-template/assets/ico/apple-touch-icon-57-precomposed.png">


	<link rel="stylesheet" type="text/css" href="assets/css/jquery.qtip.css" media="all">
	<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="<?php echo $App->getCurrentPage(); ?>">

<!-- Loader -->
<div class="loader">
    <div class="loader-img"></div>
</div>

<!-- Top menu -->
<nav class="navbar navbar-inverse navbar-fixed-top navbar-no-bg" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-navbar-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="top-navbar-1">
            <ul class="nav navbar-nav">
                <li><a class="scroll-link" href="/?page=home">Accueil</a></li>
<!--                <li><a class="scroll-link" href="/?page=blog">Blog</a></li>-->
            </ul>
            <div class="navbar-text navbar-right">
                <a href="https://github.com/Cachwir" target="_blank" title="Github"><i class="fa fa-github"></i></a>
                <a href="https://twitter.com/Cachwir" target="_blank" title="Twitter"><i class="fa fa-twitter"></i></a>
                <a href="https://www.linkedin.com/in/antoine-bernay-0a4138143/" target="_blank" title="LinkedIn"><i class="fa fa-linkedin"></i></a>
                <a href="pdf/cv.pdf" target="_blank" title="CV"><i class="fa fa-file-pdf-o"></i></a>
                <a href="mailto:a.bernay@protonmail.com" target="_blank" title="E-mail"><i class="fa fa-envelope-o"></i></a>
            </div>
        </div>
    </div>
</nav>