<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="icon" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/favicon-go.svg' ); ?>" type="image/svg+xml" sizes="any">
	<link rel="apple-touch-icon" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/favicon-go.png' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<header class="site-header">
	<div class="site-brand">
		<?php if ( has_custom_logo() ) {
			the_custom_logo();
		} else {
			$brand_name = get_bloginfo( 'name' );
			$brand_prefix = str_ends_with( $brand_name, 'GO' ) ? substr( $brand_name, 0, -2 ) : $brand_name;
			?>
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $brand_name ); ?>"><?php echo esc_html( $brand_prefix ); ?><?php if ( $brand_prefix !== $brand_name ) { ?><span>GO</span><?php } ?></a>
		<?php } ?>
	</div>
	<button class="menu-toggle" aria-label="Open navigation" aria-expanded="false"><i></i><i></i></button>
	<nav class="site-nav" aria-label="Primary navigation"><?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'jg-menu', 'fallback_cb' => 'jg_fallback_menu', 'depth' => 1 ) ); ?><?php if ( function_exists( 'pll_the_languages' ) ) { $languages = pll_the_languages( array( 'raw' => 1, 'hide_current' => 0 ) ); if ( is_array( $languages ) ) { echo '<ul class="language-switcher">'; foreach ( $languages as $language ) { echo '<li' . ( ! empty( $language['current_lang'] ) ? ' class="current-lang"' : '' ) . '><a lang="' . esc_attr( $language['slug'] ) . '" href="' . esc_url( $language['url'] ) . '">' . esc_html( strtoupper( $language['slug'] ) ) . '</a></li>'; } echo '</ul>'; } } ?><a class="nav-cta" href="<?php echo esc_url( jg_field( get_queried_object_id(), 'hero_cta_url' ) ); ?>"><?php echo esc_html( jg_field( get_queried_object_id(), 'hero_cta' ) ); ?></a>
	</nav>
</header>
