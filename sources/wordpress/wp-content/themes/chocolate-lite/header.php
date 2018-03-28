<?php
include_once('commun/MyPage.php');	 
include_once('commun/MyBdd.php');	 
include_once('commun/MyTools.php');
//session_start();

// Langues
$lang = utyGetSession('lang', 'FR');
$lang = utyGetGet('lang', $lang);
$_SESSION['lang'] = $lang;
if($lang=='EN')
{
	$Accueil = 'Home';
	$Calendrier = 'Schedule';
	$Matchs = 'Games';
	$Classements = 'Standings';
	$Historique = 'History';
	$Palmares = 'Awards';
	$Administration = 'Admin';
}
else
{
	$Accueil = 'Accueil';
	$Calendrier = 'Calendrier';
	$Matchs = 'Matchs';
	$Classements = 'Classements';
	$Historique = 'Historique';
	$Palmares = 'Palmarès';
	$Administration = 'Administration ';
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<?php $the_title = wp_title('-', false); if ($the_title != '') : ?>
    <title><?php echo wp_title('',false); ?> | <?php bloginfo('name'); ?></title>
<?php else : ?>
    <title><?php bloginfo('name'); ?><?php if ( $paged > 1 ) echo ( ' - page '.$paged ); ?></title>
<?php endif; ?>
	<?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>
	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>
	
	<!-- Ajout Laurent -->
		<meta property="og:image" content="https://kayak-polo.info/img/KPI.png" />
		<link rel="image_src" href="https://kayak-polo.info/img/KPI.png" />
		<meta property="og:title" content="kayak-polo.info" />
		<meta property="og:type" content="article" />
		<!--<meta property="og:url" content="https://kayak-polo.info" />-->
		<meta property="og:description" content="kayak polo français" />
		<meta property="og:site_name" content="KAYAK-POLO.INFO" />
		
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script type='text/javascript' src='js/iframes.js'></script>
	<!-- Fin ajout -->

<script>
function testframe()
{	
	if (top.location != self.document.location)
	{
		document.getElementById('banniere').style.display='none';
		//top.location = self.document.location;
	}
}


</script>

</head>
<body <?php body_class(); ?> onLoad="testframe();">
<div id="header_wrap">
	<?php $options = get_option('chocolate_theme_options'); ?>
	<div id="header">
		<h1>
			<?php if ($options['logo_src']!='') { ?>
			<a class="logo_img" href="<?php echo home_url(); ?>" title="<?php bloginfo('name'); ?>"><img src="<?php echo $options['logo_src']; ?>" /></a>
			<?php } else { ?>
			<a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a>
			<?php } ?>
		</h1>
		<h2><?php bloginfo('description');?></h2>
		<div id="menus">
			<?php wp_nav_menu( array( 'container' => 'none', 'theme_location' => 'primary' ) ); ?>
			<ul><li<?php if ( is_home() ) { echo ' class="current_page_item"'; }?>><a href="<?php echo home_url(); ?>"><?php _e('Home', 'chocolate'); ?></a></li></ul>
		</div>
		<div id="rss_search">
			<div id="rss"><a href="<?php if($options['rss_url'] != '') { echo($options['rss_url']); } else { bloginfo('rss2_url'); } ?>" rel="nofollow" title="<?php _e('RSS Feed', 'chocolate'); ?>"><?php _e('RSS Feed', 'chocolate'); ?></a></div>
			<div id="search"><?php get_search_form(); ?></div>
		</div>
	</div>
</div>

<!-- Entête KPI -->

<div id="banniere">
	<img src="img/FFCK1.gif" height=99 alt="FFCK" title="FFCK" border="0">
</div>
<ul id="nav"> 
	<li class="current"><a href="./"><? echo $Accueil ?></a></li>
	<li ><a href="Calendrier.php"><? echo $Calendrier ?></a></li>
	<li ><a href="Journees.php"><? echo $Matchs ?></a></li>
	<li ><a href="Classements.php"><? echo $Classements ?></a></li>
	<li ><a href="Historique.php"><? echo $Historique ?></a></li>
	<li ><a href="Palmares.php"><? echo $Palmares?></a></li>
	<li ><a href="Cartographie.php">Clubs</a></li>
	<!--<li class="forum"><a href="http://www.poloweb.org/forum/">Forum</a></li>-->
	<li ><a href="admin/GestionCompetition.php"><? echo $Administration ?></a></li>
	<li ><a href="?lang=EN&cat=5"><img vspace="3" width="22" height="14" src="img/Pays/GBR.png" alt="EN" title="EN" border="0"></a></li>
	<li ><a href="?lang=FR&cat=1"><img vspace="3" width="22" height="14" src="img/Pays/FRA.png" alt="FR" title="FR" border="0"></a></li>
	
</ul>


<!-- /Entête KPI -->

<div id="wrapper">