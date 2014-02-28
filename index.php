<?php
require_once('settings/config.php');
require_once('include/contact-form.class.php');
require_once('include/cache.class.php');
require_once('include/sectionsFrontend.class.php');

# Install PSR-0-compatible class autoloader
spl_autoload_register(function($class){
	require_once preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, 'include/'.ltrim($class, '\\')).'.php';
});

# Get Markdown class
use \Michelf\MarkdownExtra;

$sections = new SectionsFrontend;

//Gestion du cache
$do_cache = false;
if (empty($_REQUEST)){
	$cache = new cache();
	if ($cache->get() !== false){
		exit;
	}else{
		$do_cache = true;
		$cache->init();
	}
}elseif(isset($_REQUEST['del_cache'])){
	$cache = new cache();
	$cache->del();
}elseif(isset($_REQUEST['ajax'])){
	switch (htmlspecialchars($_REQUEST['ajax'])){
		
	}
	exit();
}

//Gestion du formulaire de contact
$dests = array(
					'Le bureau'	=> 'bureau@hc3p.fr',
					'L\'équipe'		=> 'hautcommetroispommes@neuf.fr'
						);

$contact_form = new contactform($dests); 
if (isset($_POST['contact_send'])){
	$ret = $contact_form->send_mail($_POST['dest'], $_POST['name'], $_POST['email'], $_POST['message'], $_POST['subject']);
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<title>Crèche Haut Comme Trois Pommes</title>
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
	<!--<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.0.2/css/font-awesome.min.css">-->
	<!--<link rel="stylesheet" href="css/font-awesome.min.css">-->
	<!--[if IE 7]>
	<link rel="stylesheet" href="css/font-awesome-ie7.min.css">
	<![endif]-->
	<!--<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,600' rel='stylesheet' type='text/css'>-->
	<?php $sections->globalStyle(); ?>
</head>

<body data-spy="scroll" data-target=".menu" data-offset="120">

<div class="menu">
	<nav class="navbar navbar-default" role="navigation">
		<div class="head">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle btn" data-toggle="collapse" data-target="#navbar-collapse">
	        <span>Menu</span>
	      </button>
		    <a class="navbar-brand" href="."><img alt="Haut comme trois pommes" src="images/logo.png"></a>
			</div>
		</div>
		<div class="collapse navbar-collapse" id="navbar-collapse">	
		  <ul id="navbar" class="nav navbar-nav navbar-right">
				<?php echo $sections->getMenu(); ?>
			</ul>
		</div>
	</nav>
</div>

<div id="wrap">
	<?php
	$sections->display();
	?>
</div>
	<!-- Le javascript -->
	<!--<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>-->
	<script src="js/jquery-1.10.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
	<script src="js/jQueryRotateCompressed.2.2.js"></script>
	<script src="js/jquery.backstretch.js"></script>
	<script src="js/hc3p.js"></script>
</body>
</html>
<?php
if ($do_cache){
	$cache->to_file();
}
 ?>