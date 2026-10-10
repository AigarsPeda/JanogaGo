<?php
/** Local-only pricing preview. Run with WP-CLI eval-file and the theme loaded. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	throw new RuntimeException( 'This pricing preview may only run on Local.' );
}

function jg_preview_pricing_copy( $language ) {
	return $language === 'en' ? array(
		'title' => 'How much does JāņogaGO cost?',
		'intro' => 'The service fee depends on foot traffic and expected sales at your location.',
		'large' => 'Locations with high foot traffic',
		'free' => 'Free of charge',
		'free_unit' => 'Service fee for your company',
		'free_text' => 'For large companies, production sites and busy offices. Installation, technical maintenance and daily restocking are free of charge.',
		'small' => 'Smaller companies and offices',
		'paid_unit' => '+ VAT per month',
		'paid_text' => 'For locations with lower daily sales. The fixed monthly fee includes full machine upkeep and servicing.',
		'note' => 'These prices cover machine servicing. We assess your location and confirm the fee in a proposal before you decide.',
		'hero' => '<strong>No service fee at locations with high foot traffic.</strong> Smaller locations: EUR 150–350 + VAT per month.',
		'link' => 'See service costs',
		'faq' => 'How much does the service cost?',
		'answer' => 'At locations with high foot traffic, installation, maintenance and daily restocking are free of charge. Smaller companies and offices pay EUR 150–350 + VAT per month for full machine upkeep and servicing. We assess your location and confirm the fee in a proposal.',
	) : array(
		'title' => 'Cik maksā JāņogaGO?',
		'intro' => 'Servisa maksa ir atkarīga no cilvēku plūsmas un paredzamā apgrozījuma jūsu atrašanās vietā.',
		'large' => 'Vietām ar lielu cilvēku plūsmu',
		'free' => 'Bez maksas',
		'free_unit' => 'Servisa maksa uzņēmumam',
		'free_text' => 'Lieliem uzņēmumiem, ražotnēm un noslogotiem birojiem. Automāta uzstādīšana, tehniskā apkope un ikdienas uzpilde bez maksas.',
		'small' => 'Mazākiem uzņēmumiem un birojiem',
		'paid_unit' => '+ PVN mēnesī',
		'paid_text' => 'Vietām ar nelielu ikdienas apgrozījumu. Fiksētajā mēneša maksā iekļauta pilna automāta uzturēšana un apkalpošana.',
		'note' => 'Norādītā maksa attiecas uz automāta servisu. Izvērtējam jūsu atrašanās vietu un precizējam maksu piedāvājumā pirms sadarbības sākuma.',
		'hero' => '<strong>Bez servisa maksas vietās ar lielu cilvēku plūsmu.</strong> Mazākām vietām: 150–350 EUR + PVN mēnesī.',
		'link' => 'Apskatīt servisa izmaksas',
		'faq' => 'Kādas ir pakalpojuma izmaksas?',
		'answer' => 'Vietās ar lielu cilvēku plūsmu automāta uzstādīšana, tehniskā apkope un ikdienas uzpilde ir bez maksas. Mazākiem uzņēmumiem un birojiem servisa maksa ir 150–350 EUR + PVN mēnesī, iekļaujot pilnu automāta uzturēšanu un apkalpošanu. Izvērtējam jūsu atrašanās vietu un precizējam maksu piedāvājumā.',
	);
}

function jg_preview_pricing_section( $copy, $language ) {
	$free = jg_block_heading( $copy['large'], 3 ) . jg_block_paragraph( $copy['free'], 'jg-pricing-amount' ) . jg_block_paragraph( $copy['free_unit'], 'jg-pricing-unit' ) . jg_block_paragraph( $copy['free_text'], 'jg-pricing-description' );
	$paid = jg_block_heading( $copy['small'], 3 ) . jg_block_paragraph( $language === 'en' ? 'EUR 150–350' : '150–350 EUR', 'jg-pricing-amount' ) . jg_block_paragraph( $copy['paid_unit'], 'jg-pricing-unit' ) . jg_block_paragraph( $copy['paid_text'], 'jg-pricing-description' );
	return jg_block_group( jg_block_heading( $copy['title'] ) . jg_block_paragraph( $copy['intro'], 'jg-section-lede' ) . jg_block_columns( jg_block_column( $free ) . jg_block_column( $paid ), 'jg-pricing-options' ) . jg_block_paragraph( $copy['note'], 'jg-pricing-note' ), 'pricing section jg-block-section jg-block-pricing', 'section', 'izmaksas' );
}

/** Locate a native block without rebuilding its ancestors. */
function jg_preview_find_block( $blocks, $predicate ) {
	foreach ( $blocks as $block ) {
		if ( $predicate( $block ) ) { return $block; }
		$found = jg_preview_find_block( $block['innerBlocks'] ?? array(), $predicate );
		if ( $found ) { return $found; }
	}
	return null;
}

$pages = get_option( 'jg_seeded_pages', array() );
$updates = array();
$before = array();
foreach ( array( 'lv', 'en' ) as $language ) {
	$id = absint( $pages[ $language ] ?? 0 );
	$content = $id ? get_post_field( 'post_content', $id, 'raw' ) : '';
	if ( ! $content ) { WP_CLI::error( "Missing $language homepage." ); }
	if ( str_contains( $content, 'jg-block-pricing' ) ) {
		WP_CLI::log( "Pricing already present on $language; preserving editor changes." );
		continue;
	}
	$copy = jg_preview_pricing_copy( $language );
	$blocks = parse_blocks( $content );
	$hero = jg_preview_find_block( $blocks, static fn( $block ) => in_array( 'jg-block-hero', explode( ' ', $block['attrs']['className'] ?? '' ), true ) );
	$button = $hero ? jg_preview_find_block( $hero['innerBlocks'], static fn( $block ) => $block['blockName'] === 'core/buttons' ) : null;
	$faq = jg_preview_find_block( $blocks, static fn( $block ) => $block['blockName'] === 'core/details' && str_contains( $block['innerHTML'], '<summary>' . $copy['faq'] . '</summary>' ) );
	if ( ! $hero || ! $button || ! $faq || count( $faq['innerBlocks'] ) !== 1 ) { WP_CLI::error( "Unexpected $language layout; no pages changed." ); }
	$before[ $id ] = $content;
	$hero_html = serialize_block( $hero );
	$button_html = serialize_block( $button );
	$summary = jg_block( 'paragraph', array( 'className' => 'jg-hero-pricing' ), '<p class="jg-hero-pricing">' . $copy['hero'] . ' <a href="#izmaksas">' . esc_html( $copy['link'] ) . '</a></p>' );
	$new_hero = str_replace( $button_html, $summary . "\n" . $button_html, $hero_html );
	$content = str_replace( $hero_html, $new_hero . "\n\n" . jg_preview_pricing_section( $copy, $language ), $content );
	$content = str_replace( serialize_block( $faq['innerBlocks'][0] ), trim( jg_block_paragraph( $copy['answer'] ) ), $content );
	if ( substr_count( $content, 'id="izmaksas"' ) !== 1 || $content === $before[ $id ] ) { WP_CLI::error( "Pricing insertion failed on $language; no pages changed." ); }
	$updates[ $id ] = $content;
}

if ( ! $updates ) { return; }
$backup = sys_get_temp_dir() . '/janogago-pricing-before-' . gmdate( 'Ymd-His' ) . '.json';
if ( ! file_put_contents( $backup, wp_json_encode( $before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) { WP_CLI::error( 'Could not save content backup.' ); }
foreach ( $updates as $id => $content ) {
	$result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true );
	if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() . "; restore from $backup" ); }
	if ( get_post_field( 'post_content', $id, 'raw' ) !== $content ) { WP_CLI::error( "Saved page $id differs from prepared content; restore from $backup" ); }
	WP_CLI::success( "Added editable pricing to Local page $id." );
}
WP_CLI::log( "Homepage backup: $backup" );
