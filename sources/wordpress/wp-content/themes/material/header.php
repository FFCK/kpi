<?php
/**
 * header.php
 *
 * The header for the theme.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<!DOCTYPE html>
<!--[if IE 8]> <html <?php language_attributes(); ?> class="ie8"> <![endif]-->
<!--[if !IE]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php wp_title( '|', true, 'right' ); ?></title>   
	<meta name="description" content="<?php bloginfo( 'description' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link rel="profile" href="http://gmpg.org/xfn/11">
	<!-- Mobile Specific Meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- FB Meta -->
    <meta property="og:image" content="https://kayak-polo.info/img/newKPI2.jpg" />
    <link rel="image_src" href="https://kayak-polo.info/img/newKPI2.jpg" />
    <meta property="og:title" content="kayak-polo.info" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://www.kayak-polo.info"/>
    <meta property="og:description" content="FFCK - Commission Nationale d'Activité Kayak-Polo" />
    <meta property="og:site_name" content="KAYAK-POLO.INFO" />
    
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<!-- HEADER -->
	<header class="site-header" role="banner">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12 banner">
					<img class="site-banner" src="<?php header_image(); ?>" width="<?php echo get_custom_header()->width; ?>"  alt="" />
				</div><!-- /.col-xs-12 -->
			</div><!-- /.row -->
		</div> <!-- end banner -->
		<div class="container header-contents">
			<div class="row">
				<div class="col-xs-9 sitelogo">
					<div class="site-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a> <div class="tagline"><?php echo get_bloginfo ( 'description' );?></div>
					</div> <!-- end site-logo -->
				</div> <!-- end col-xs-3 -->
				<div class="col-xs-12">
					<nav class="site-navigation navbar navbar-default navbar-mv-up" role="navigation">
					<div class="menu-short-container container-fluid">
						<!-- Brand and toggle get grouped for better mobile display -->
					    <div class="navbar-header">
					      <button type="button" class="navbar-toggle collapsed navbar-color-mod" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					        <span class="sr-only"><?php echo __( 'Toggle navigation', 'material' )  ?></span>
					        <span class="icon-bar"></span>
					        <span class="icon-bar"></span>
					        <span class="icon-bar"></span>
					      </button>
					    </div>
					    <!-- Collect the nav links, forms, and other content for toggling -->
				    	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<?php 
							wp_nav_menu(
								array(
									'theme_location' => 'main-menu',
									'menu_class' => 'site-menu'
								)
							);
						?>
						</div><!-- /.navbar-collapse -->
					  </div><!-- /.container-fluid -->
					</nav>
				</div> <!-- end col-xs-9 -->
			</div> <!-- end row -->
		</div> <!-- end container -->
	</header> <!-- end site-header -->
	

	<!-- MAIN CONTENT AREA -->
	<div class="container">
		<div class="row">