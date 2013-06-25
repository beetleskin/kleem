<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7 ]> <html class="ie ie7" <?php language_attributes();?>> <![endif]-->
<!--[if IE 8 ]> <html class="ie ie8" <?php language_attributes();?>> <![endif]-->
<!--[if IE 9 ]> <html class="ie ie9" <?php language_attributes();?>> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class="" <?php language_attributes();?>> <!--<![endif]-->

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width" />
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
	<![endif]-->
	<?php wp_head(); ?>
	<link rel="shortcut icon" href="/wp-content/themes/kleem_theme/images/favicon.ico"/>
</head>

<body <?php body_class(); ?>>
	<img id="background-fix" src="/wp-content/themes/kleem_theme/images/background.jpg" alt="Hintergrundbild">  
	<nav id="user-menu">
		<div class="inner">
			<?php get_sidebar('header') ?>
		</div>
	</nav>
	<div id="page" class="hfeed site">
		<a title="gruene.de" href="http://www.gruene.de/"><img src="/wp-content/themes/kleem_theme/images/logo_gruene.png" alt="Startseite" width="15%" height="auto"></a>
		<div class="clearfix"></div>
		<header id="masthead" class="site-header" role="banner">
			<hgroup>
				<img id="header-img" src="/wp-content/themes/kleem_theme/images/HaraldKleem.png">
				<h1 id="site-title"><span><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></span></h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
			</hgroup>
			<nav id="site-navigation" class="main-navigation" role="navigation">
				<h3 class="menu-toggle"><?php _e( 'Menu', 'twentytwelve' ); ?></h3>
				<a class="assistive-text" href="#content" title="<?php esc_attr_e( 'Skip to content', 'twentytwelve' ); ?>"><?php _e( 'Skip to content', 'twentytwelve' ); ?></a>
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
			</nav><!-- #site-navigation -->
			<?php $header_image = get_header_image();
			if ( ! empty( $header_image ) ) : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $header_image ); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" /></a>
			<?php endif; ?>
		</header><!-- #masthead -->
		<div id="main" class="wrapper">