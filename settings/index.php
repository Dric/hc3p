<?php
// On vérifie l'existence de config.php
if (file_exists('config.php')){
	require('config.php');
}else{
	die('<h1>HC3P</h1>
				<p>Vous n\'avez apparemment pas d&eacute;fini votre fichier config.php, sans quoi vous ne verriez pas ce message.<br />
				Voici la proc&eacute;dure &agrave; accomplir pour un param&eacute;trage correct :</p>
				<ul>
					<li>Remplissez (avec soin) le fichier config-sample.php.</li>
					<li>Renommez-le en config.php</li>
					<li>Vous pouvez rafra&icirc;chir cette page !</li>
				</ul>
	');
}
# Install PSR-0-compatible class autoloader
spl_autoload_register(function($class){
	require_once preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, './../include/'.ltrim($class, '\\')).'.php';
});

# Get Markdown class
use \Michelf\MarkdownExtra;

require_once('include/sections.class.php');
//require_once('include/settings.class.php');


$is_logged_in = false;
$page= 'sections';
// Cookie check !
if (isset($_COOKIE[COOKIE_NAME])){
  $cookie = unserialize($_COOKIE[COOKIE_NAME]);
  if (sha1(ADMIN_PWD.SALT) == $cookie){
		$is_logged_in = true;
	}
}

$sections = new SectionManager;
$action = $sections->requestActions();
if ($action){
	exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion du site</title>
    <!-- Bootstrap -->
    <link href="./../css/bootstrap.min.css" rel="stylesheet">
    <link href="include/jquery-minicolors/jquery.minicolors.css" rel="stylesheet">
    <link href="include/bootstrap-slider/dist/css/bootstrap-slider.min.css" rel="stylesheet">
    <link href="include/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" rel="stylesheet">
    <link href="include/bootstrapvalidator/css/bootstrapValidator.min.css" rel="stylesheet">
    <link href="include/md-editor/editor.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
   </head>
   <body>
   	<nav class="navbar navbar-default" role="navigation">
		  <div class="container-fluid">
		    <!-- Brand and toggle get grouped for better mobile display -->
		    <div class="navbar-header">
		      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
		        <span class="sr-only">Menu</span>
		      </button>
		      <a class="navbar-brand" href="#"><img id="brand-logo" class="img-responsive" style="height: 100%" alt="HC3P Logo" src="<?php echo './../'.LOGO_IMG; ?>" /></a>
		    </div>

		    <!-- Collect the nav links, forms, and other content for toggling -->
		    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		      <ul class="nav navbar-nav">
		        <li class="<?php echo ($page == 'sections')?'active':''; ?>"><a href="#">Sections</a></li>
		        <li class="<?php echo ($page == 'settings')?'active':''; ?>"><a href="#">Paramétrage</a></li>
			      <li><a href="./../">Voir le site</a></li>
		      </ul>
		    </div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
		</nav>
		<div class="container-fluid">
	   	<?php
	   	switch ($page) {
				case 'sections':
					$sections->display();
					break;
				case 'settings':
					$settings = new Settings;
					$settings->display();
					break;
			}
	   	?>
   	</div>
   	<script src="./../js/jquery-1.10.1.min.js"></script>
		<script src="./../js/bootstrap.min.js"></script>
		<script src="include/jquery-minicolors/jquery.minicolors.min.js"></script>
		<script src="include/bootstrap-slider/dist/bootstrap-slider.min.js"></script>
		<script src="include/bootstrap-switch/js/bootstrap-switch.min.js"></script>
		<script src="include/bootstrapvalidator/js/bootstrapValidator.min.js"></script>
		<script src="include/md-editor/editor.js"></script>
		<script src="include/md-editor/marked.js"></script>
		<script src="include/bootstrap-confirmation.js"></script>
		<script src="settings.js"></script>
   </body>
</html>
