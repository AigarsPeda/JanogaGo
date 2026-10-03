<?php
/**
 * Plugin Name: JāņogaGO Site Kit consent guard
 * Description: Keeps Site Kit account connections and reports available while the JāņogaGO consent controls own Analytics tag loading.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Site Kit must not add an unconditional or duplicate Analytics/Tag Manager tag.
add_filter( 'googlesitekit_analytics-4_tag_blocked', '__return_true', PHP_INT_MAX );
add_filter( 'googlesitekit_analytics-4_tag_amp_blocked', '__return_true', PHP_INT_MAX );
add_filter( 'googlesitekit_tagmanager_tag_blocked', '__return_true', PHP_INT_MAX );
add_filter( 'googlesitekit_tagmanager_tag_amp_blocked', '__return_true', PHP_INT_MAX );

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! current_user_can( 'manage_options' ) || ! $screen || ! str_contains( $screen->id, 'googlesitekit' ) ) { return; }
	echo '<div class="notice notice-info"><p>JāņogaGO uses its own consent controls to load Analytics. Site Kit can connect your Google account and show reports, but its automatic Analytics and Tag Manager code placement is blocked to prevent tracking before consent or duplicate tags. Configure the GA4 ID under Settings → Privacy &amp; Analytics after the consent controls and privacy pages are ready on the live website. Complete Google account setup on janogago.lv; Site Kit does not support Google account setup on a local-only .local website.</p></div>';
} );
