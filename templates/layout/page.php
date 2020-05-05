<!DOCTYPE HTML>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $title . (isset($page_title) ? " : $page_title" : '') ?></title>
<link href="<?php echo $config['site_home'] ?>css/style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $config['site_home'] ?>images/silk_theme.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $config['common_library_url'] ?>bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo $config['common_library_url'] ?>bower_components/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet">
<link href="<?php echo $config['site_home'] ?>js/library/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $config['common_library_url'] ?>css/style.css" rel="stylesheet" type="text/css" />
<?php echo $css_includes ?>
</head>
<body>
<div id="wrapper" class="container">
<div id="header" class="navbar navbar-fixed-top navbar-inverse" role="navigation">
<div id="nav" class="container">
	<div class="navbar-brand"><a href="<?php echo $config['site_home'] ?>"><?php echo $config['site_title'] ?></a></div>

	<div class="collapse navbar-collapse">
	<ul id="nav-links" class="nav navbar-nav pull-right">
	<li><a href="index.php">Home</a></li>
	</ul>
	</div>
</div>
</div>
<div id="content" class="container">
<div id="error-message" <?php echo ($QUERY['error']) ? '':'style="display:none;"';?>><?php
	if(i($PARAM, 'error')) print strip_tags($PARAM['error']); //It comes from the URL
	else print $QUERY['error']; //Its set in the code(validation error or something.
?></div>
<div id="success-message" <?php echo ($QUERY['success']) ? '':'style="display:none;"';?>><?php echo strip_tags(stripslashes($QUERY['success']))?></div>

<?php include($GLOBALS['template']->template); ?>

</div>
<div id="footer"></div>
</div>

<script src="<?php echo $config['site_home'] ?>bower_components/jquery/dist/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $config['site_home'] ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?php echo $config['site_home'] ?>js/application.js" type="text/javascript"></script>

<?php echo $js_includes; ?>
</body>
</html>