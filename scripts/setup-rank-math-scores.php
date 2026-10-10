<?php
/** Enable Rank Math's existing settings and seed missing page analysis keywords. */
if ( ! defined( 'ABSPATH' ) || ! class_exists( '\RankMath\Helper' ) ) {
	throw new RuntimeException( 'Run with WordPress and Rank Math loaded.' );
}

// This is Rank Math's own "Skip Step" flag. No external account is needed.
update_option( 'rank_math_registration_skip', true );

foreach ( get_users( [ 'role' => 'administrator', 'fields' => 'ID' ] ) as $user_id ) {
	$hidden = get_user_meta( $user_id, 'manageedit-pagecolumnshidden', true );
	if ( is_array( $hidden ) ) {
		update_user_meta( $user_id, 'manageedit-pagecolumnshidden', array_values( array_diff( $hidden, [ 'rank_math_seo_details' ] ) ) );
	}
}

$keywords = [
	'Sākumlapa' => 'ēdienu automātus',
	'Home' => 'food vending',
	'Ēdieni' => 'ēdieni',
	'Food' => 'food',
	'Privātums un sīkdatnes' => 'personas datu',
	'Privacy & cookies' => 'personal data',
	'Privacy Policy' => 'privacy policy',
	'Sample Page' => 'example page',
];
foreach ( get_posts( [ 'post_type' => 'page', 'post_status' => [ 'publish', 'draft' ], 'numberposts' => -1 ] ) as $page ) {
	if ( isset( $keywords[ $page->post_title ] ) && ! get_post_meta( $page->ID, 'rank_math_focus_keyword', true ) ) {
		update_post_meta( $page->ID, 'rank_math_focus_keyword', $keywords[ $page->post_title ] );
	}
	echo wp_json_encode( [ 'id' => $page->ID, 'title' => $page->post_title, 'keyword' => get_post_meta( $page->ID, 'rank_math_focus_keyword', true ), 'score' => get_post_meta( $page->ID, 'rank_math_seo_score', true ) ] ) . PHP_EOL;
}
// Run Rank Math SEO > Status & Tools > Database Tools > Recalculate Scores next.
// Never assign an invented score to rank_math_seo_score.
