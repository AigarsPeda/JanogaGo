<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jg_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 90, 'width' => 280, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array( 'primary' => __( 'Primary navigation', 'janogago' ), 'footer' => __( 'Footer navigation', 'janogago' ) ) );
}
add_action( 'after_setup_theme', 'jg_setup' );

function jg_enqueue_assets() {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'janogago-fonts', 'https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap', array(), null );
	wp_enqueue_style( 'janogago', get_stylesheet_uri(), array( 'janogago-fonts' ), $version );
	wp_enqueue_script( 'janogago', get_template_directory_uri() . '/assets/js/site.js', array(), $version, true );
}
add_action( 'wp_enqueue_scripts', 'jg_enqueue_assets' );

function jg_send_valid_page_status() {
	if ( ! is_admin() && is_page() ) {
		status_header( 200 );
	}
}
add_action( 'template_redirect', 'jg_send_valid_page_status', 99 );

function jg_lang() {
	if ( function_exists( 'pll_current_language' ) ) {
		return pll_current_language( 'slug' ) ?: 'lv';
	}
	return 0 === strpos( get_locale(), 'lv' ) ? 'lv' : 'en';
}

function jg_defaults( $language = null ) {
	$lv = array(
		'hero_eyebrow' => 'ĒDIENS DARBĀ, BEZ RŪPĒM',
		'hero_title' => 'Labs ēdiens darbā. Jebkurā laikā.',
		'hero_text' => 'Pilna servisa ēdienu automāti ar svaigām maltītēm, uzkodām un dzērieniem birojiem, ražotnēm un loģistikas centriem. Mēs uzstādām, piegādājam, papildinām un uzturam.',
		'hero_cta' => 'Pieteikt konsultāciju',
		'hero_cta_url' => '#pieteikties',
		'intro_eyebrow' => 'KĀPĒC JANOGAGO',
		'intro_title' => 'Pusdienas, par kurām nav jādomā.',
		'intro_text' => 'JāņogaGO komanda gatavo ēdienu, regulāri papildina automātus un rūpējas par tehniku. Jūsu komandai atliek izvēlēties to, kas garšo.',
		'stat_one' => 'SVAIGI GATAVOTS KATRU DIENU', 'stat_two' => 'KARTE, TELEFONS VAI VIEDPULKSTENIS', 'stat_three' => 'HIGIĒNA UN TEMPERATŪRA KONTROLĒTA',
		'menu_eyebrow' => 'NE TIKAI UZKODAS',
		'menu_title' => 'Pusdienas, ko gaida, nevis izlaiž.',
		'menu_text' => 'No siltām maltītēm līdz labai kafijai. Sortimentam jāatbilst jūsu cilvēkiem, tāpēc to veidojam kopā ar jums.',
		'menu_one' => 'Siltās pusdienas', 'menu_two' => 'Salāti un bļodas', 'menu_three' => 'Sviestmaizes un deserti', 'menu_four' => 'Kafija un dzērieni',
		'models_eyebrow' => 'DIVI SADARBĪBAS VEIDI',
		'models_title' => 'Izvēlieties modeli. Pārējo izdaram mēs.',
		'model_one_title' => 'Pilna servisa risinājums',
		'model_one_text' => 'Piemērots birojiem, ražotnēm un loģistikas centriem ar 30+ darbiniekiem. Mēs uzstādām automātu, piegādājam svaigu pārtiku, regulāri papildinām sortimentu un rūpējamies par tehniku. Jūsu komandai atliek izvēlēties, kas garšo.',
		'model_two_title' => 'Aprīkojuma noma',
		'model_two_text' => 'Piemērota komandām, kas vēlas pašas veidot un pārvaldīt sortimentu. Mēs nodrošinām iekārtu, tehnisko atbalstu un norēķinu risinājumu.',
		'process_eyebrow' => 'KĀ TAS NOTIEK',
		'process_title' => 'No pirmās sarunas līdz pirmajām pusdienām.',
		'step_one' => 'Iepazīstam jūsu vidi', 'step_one_text' => 'Saprotam cilvēku skaitu, maiņas un to, kas viņiem tiešām noderēs.',
		'step_two' => 'Sagatavojam risinājumu', 'step_two_text' => 'Piedāvājam iekārtu, sortimentu un sadarbības modeli.',
		'step_three' => 'Uzstādām un aprūpējam', 'step_three_text' => 'Piegādājam, papildinām un reaģējam, ja vajadzīga palīdzība.',
		'contact_eyebrow' => 'SĀKSIM AR KONSULTĀCIJU',
		'contact_title' => 'Ienesiet restorāna līmeņa ēdienu savā darba vietā.',
		'contact_text' => 'Pastāstiet par savu uzņēmumu. Atbildēsim ar piemērotu risinājumu un sarunāsim konsultāciju.',
		'contact_email' => 'info@janoga.lv', 'contact_phone' => '+371 28 317 179', 'contact_address' => 'Rīga, Latvija',
		'form_company_label' => 'Uzņēmums', 'form_name_label' => 'Jūsu vārds', 'form_email_label' => 'E-pasts', 'form_phone_label' => 'Tālrunis', 'form_people_label' => 'Darbinieku skaits', 'form_message_label' => 'Ko vēlaties nodrošināt?', 'form_privacy_label' => 'Piekrītu, ka JāņogaGO izmantos manu sniegto informāciju, lai sazinātos par manu pieprasījumu.', 'form_submit_label' => 'Nosūtīt pieteikumu', 'form_success_message' => 'Paldies. Mēs ar jums sazināsimies.', 'form_invalid_message' => 'Lūdzu, aizpildiet visus laukus un atzīmējiet piekrišanu.', 'form_phone_invalid_message' => 'Lūdzu, ievadiet derīgu tālruņa numuru.',
	);
	$en = array(
		'hero_eyebrow' => 'WORKPLACE FOOD, FULLY MANAGED',
		'hero_title' => 'Good food at work. Any time of day.',
		'hero_text' => 'Fully managed food vending with fresh meals, snacks and drinks for offices, production sites and logistics centres. We install, deliver, refill and maintain the service.',
		'hero_cta' => 'Book a consultation', 'hero_cta_url' => '#pieteikties',
		'intro_eyebrow' => 'WHY JANOGAGO',
		'intro_title' => 'Lunch, without the logistics.',
		'intro_text' => 'The JāņogaGO team prepares the food, refills the machines and looks after the equipment. Your team simply chooses what they feel like.',
		'stat_one' => 'FRESHLY PREPARED EVERY DAY', 'stat_two' => 'CARD, PHONE OR SMARTWATCH', 'stat_three' => 'HYGIENE AND TEMPERATURE CONTROLLED',
		'menu_eyebrow' => 'MORE THAN SNACKS',
		'menu_title' => 'Lunch worth taking a break for.',
		'menu_text' => 'Warm meals, good coffee and everything in between. We shape the selection around the people who use it.',
		'menu_one' => 'Hot lunch', 'menu_two' => 'Salads and bowls', 'menu_three' => 'Sandwiches and desserts', 'menu_four' => 'Coffee and drinks',
		'models_eyebrow' => 'TWO WAYS TO WORK TOGETHER',
		'models_title' => 'Choose the model. We handle the rest.',
		'model_one_title' => 'Fully managed service',
		'model_one_text' => 'For offices, production sites and logistics centres with 30+ employees. We install the machine, deliver fresh food, refill the selection regularly and look after the equipment. Your team simply chooses what they feel like.',
		'model_two_title' => 'Equipment lease',
		'model_two_text' => 'For teams that want to build and manage the selection themselves. We provide the equipment, technical support and payment system.',
		'process_eyebrow' => 'HOW IT WORKS',
		'process_title' => 'From the first call to the first lunch.',
		'step_one' => 'We learn about your workplace', 'step_one_text' => 'We look at headcount, shifts and what will actually work for your people.',
		'step_two' => 'We plan the service', 'step_two_text' => 'You get a clear proposal for equipment, food and the right service model.',
		'step_three' => 'We install and run it', 'step_three_text' => 'We deliver, refill and respond whenever you need us.',
		'contact_eyebrow' => 'START WITH A CONSULTATION',
		'contact_title' => 'Bring restaurant-level food to your workplace.',
		'contact_text' => 'Tell us about your company. We will come back with a practical proposal and arrange a consultation.',
		'contact_email' => 'info@janoga.lv', 'contact_phone' => '+371 28 317 179', 'contact_address' => 'Riga, Latvia',
		'form_company_label' => 'Company', 'form_name_label' => 'Your name', 'form_email_label' => 'Email', 'form_phone_label' => 'Phone', 'form_people_label' => 'Number of people', 'form_message_label' => 'What do you need?', 'form_privacy_label' => 'I agree that JāņogaGO may use the information I provide to contact me about my request.', 'form_submit_label' => 'Send enquiry', 'form_success_message' => 'Thank you. We will be in touch.', 'form_invalid_message' => 'Please complete every field and consent checkbox.', 'form_phone_invalid_message' => 'Please enter a valid phone number.',
	);
	return ( $language ?: jg_lang() ) === 'en' ? $en : $lv;
}

function jg_field( $post_id, $key ) {
	$meta_key = '_jg_' . $key;
	if ( metadata_exists( 'post', $post_id, $meta_key ) ) {
		return get_post_meta( $post_id, $meta_key, true );
	}
	$defaults = jg_defaults();
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * The primary CTA is authored once as the first Button block in the page.
 * The header reads that block, so changing it visually in Gutenberg updates
 * both the hero and header button for that language.
 */
function jg_primary_cta( $page_id ) {
	$blocks = parse_blocks( (string) get_post_field( 'post_content', $page_id ) );
	$queue = $blocks;
	while ( $queue ) {
		$block = array_shift( $queue );
		if ( ! empty( $block['innerBlocks'] ) ) {
			$queue = array_merge( $block['innerBlocks'], $queue );
		}
		if ( ( $block['blockName'] ?? '' ) !== 'core/button' ) {
			continue;
		}
		$label = trim( preg_replace( '/\s*↗\s*$/u', '', wp_strip_all_tags( $block['innerHTML'] ) ) );
		$url = $block['attrs']['url'] ?? '';
		if ( $label && $url ) {
			return array( 'label' => $label, 'url' => $url );
		}
	}
	return array(
		'label' => jg_field( $page_id, 'hero_cta' ),
		'url' => jg_field( $page_id, 'hero_cta_url' ),
	);
}

function jg_image_url( $post_id, $key, $fallback ) {
	$image_id = absint( get_post_meta( $post_id, '_jg_' . $key, true ) );
	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, 'large' );
		if ( $url ) {
			return $url;
		}
	}
	return get_template_directory_uri() . '/assets/images/' . $fallback;
}

function jg_editor_fields() {
	return array(
		'hero_eyebrow' => array( 'label' => 'Hero eyebrow', 'type' => 'text' ), 'hero_title' => array( 'label' => 'Hero heading', 'type' => 'textarea' ), 'hero_text' => array( 'label' => 'Hero text', 'type' => 'textarea' ), 'hero_cta' => array( 'label' => 'Hero button label', 'type' => 'text' ), 'hero_cta_url' => array( 'label' => 'Hero button link', 'type' => 'url' ), 'hero_image' => array( 'label' => 'Hero image', 'type' => 'image' ),
		'intro_eyebrow' => array( 'label' => 'Why us eyebrow', 'type' => 'text' ), 'intro_title' => array( 'label' => 'Why us heading', 'type' => 'textarea' ), 'intro_text' => array( 'label' => 'Why us text', 'type' => 'textarea' ), 'stat_one' => array( 'label' => 'Proof point 1', 'type' => 'text' ), 'stat_two' => array( 'label' => 'Proof point 2', 'type' => 'text' ), 'stat_three' => array( 'label' => 'Proof point 3', 'type' => 'text' ),
		'menu_eyebrow' => array( 'label' => 'Menu eyebrow', 'type' => 'text' ), 'menu_title' => array( 'label' => 'Menu heading', 'type' => 'textarea' ), 'menu_text' => array( 'label' => 'Menu text', 'type' => 'textarea' ), 'menu_image' => array( 'label' => 'Menu image', 'type' => 'image' ), 'menu_one' => array( 'label' => 'Menu item 1', 'type' => 'text' ), 'menu_two' => array( 'label' => 'Menu item 2', 'type' => 'text' ), 'menu_three' => array( 'label' => 'Menu item 3', 'type' => 'text' ), 'menu_four' => array( 'label' => 'Menu item 4', 'type' => 'text' ),
		'models_eyebrow' => array( 'label' => 'Service models eyebrow', 'type' => 'text' ), 'models_title' => array( 'label' => 'Service models heading', 'type' => 'textarea' ), 'model_one_title' => array( 'label' => 'Model 1 title', 'type' => 'text' ), 'model_one_text' => array( 'label' => 'Model 1 text', 'type' => 'textarea' ), 'model_two_title' => array( 'label' => 'Model 2 title', 'type' => 'text' ), 'model_two_text' => array( 'label' => 'Model 2 text', 'type' => 'textarea' ),
		'process_eyebrow' => array( 'label' => 'Process eyebrow', 'type' => 'text' ), 'process_title' => array( 'label' => 'Process heading', 'type' => 'textarea' ), 'step_one' => array( 'label' => 'Step 1 title', 'type' => 'text' ), 'step_one_text' => array( 'label' => 'Step 1 text', 'type' => 'textarea' ), 'step_two' => array( 'label' => 'Step 2 title', 'type' => 'text' ), 'step_two_text' => array( 'label' => 'Step 2 text', 'type' => 'textarea' ), 'step_three' => array( 'label' => 'Step 3 title', 'type' => 'text' ), 'step_three_text' => array( 'label' => 'Step 3 text', 'type' => 'textarea' ),
		'contact_eyebrow' => array( 'label' => 'Contact eyebrow', 'type' => 'text' ), 'contact_title' => array( 'label' => 'Contact heading', 'type' => 'textarea' ), 'contact_text' => array( 'label' => 'Contact text', 'type' => 'textarea' ), 'contact_email' => array( 'label' => 'Contact email', 'type' => 'email' ), 'contact_phone' => array( 'label' => 'Contact phone', 'type' => 'text' ), 'contact_address' => array( 'label' => 'Contact address', 'type' => 'text' ),
		'form_company_label' => array( 'label' => 'Form: company label', 'type' => 'text' ), 'form_name_label' => array( 'label' => 'Form: name label', 'type' => 'text' ), 'form_email_label' => array( 'label' => 'Form: email label', 'type' => 'text' ), 'form_phone_label' => array( 'label' => 'Form: phone label', 'type' => 'text' ), 'form_people_label' => array( 'label' => 'Form: people label', 'type' => 'text' ), 'form_message_label' => array( 'label' => 'Form: message label', 'type' => 'text' ), 'form_privacy_label' => array( 'label' => 'Form: privacy consent', 'type' => 'textarea' ), 'form_submit_label' => array( 'label' => 'Form: button label', 'type' => 'text' ), 'form_success_message' => array( 'label' => 'Form: success message', 'type' => 'text' ), 'form_invalid_message' => array( 'label' => 'Form: required fields message', 'type' => 'text' ), 'form_phone_invalid_message' => array( 'label' => 'Form: invalid phone message', 'type' => 'text' ),
	);
}

function jg_add_page_metabox() {
	add_meta_box( 'jg-page-content', __( 'JāņogaGO page content', 'janogago' ), 'jg_render_page_metabox', 'page', 'normal', 'high' );
}
// Homepage copy is kept in native Gutenberg blocks. These legacy helpers stay
// available for old installations, but do not add a second, confusing editor.

function jg_render_page_metabox( $post ) {
	wp_nonce_field( 'jg_save_page', 'jg_page_nonce' );
	echo '<p class="description">These values belong to this page and its current Polylang language. Images use the WordPress Media Library. An empty field stays empty on the website.</p><div class="jg-fields">';
	foreach ( jg_editor_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, '_jg_' . $key, true );
		echo '<p class="jg-field"><label for="jg_' . esc_attr( $key ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label>';
		if ( $field['type'] === 'textarea' ) {
			echo '<textarea class="widefat" rows="3" id="jg_' . esc_attr( $key ) . '" name="jg[' . esc_attr( $key ) . ']">' . esc_textarea( $value ) . '</textarea>';
		} elseif ( $field['type'] === 'image' ) {
			$image_url = $value ? wp_get_attachment_image_url( absint( $value ), 'thumbnail' ) : '';
			echo '<input type="hidden" class="jg-image-id" id="jg_' . esc_attr( $key ) . '" name="jg[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '"><span class="jg-image-preview">' . ( $image_url ? '<img src="' . esc_url( $image_url ) . '" alt="">' : '' ) . '</span> <button type="button" class="button jg-select-image">Choose image</button> <button type="button" class="button-link-delete jg-remove-image">Remove</button>';
		} else {
			echo '<input class="widefat" type="' . esc_attr( $field['type'] ) . '" id="jg_' . esc_attr( $key ) . '" name="jg[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
		}
		echo '</p>';
	}
	echo '</div>';
}

function jg_save_page_metabox( $post_id ) {
	if ( ! isset( $_POST['jg_page_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jg_page_nonce'] ) ), 'jg_save_page' ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_page', $post_id ) || ! isset( $_POST['jg'] ) ) {
		return;
	}
	foreach ( jg_editor_fields() as $key => $field ) {
		$value = isset( $_POST['jg'][ $key ] ) ? wp_unslash( $_POST['jg'][ $key ] ) : '';
		$value = $field['type'] === 'textarea' ? sanitize_textarea_field( $value ) : ( $field['type'] === 'image' ? absint( $value ) : ( $field['type'] === 'url' ? esc_url_raw( $value ) : sanitize_text_field( $value ) ) );
		update_post_meta( $post_id, '_jg_' . $key, $value );
	}
}

function jg_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || get_post_type() !== 'page' ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_style( 'wp-admin', '.jg-field{margin:15px 0}.jg-field label{display:block;margin-bottom:5px}.jg-image-preview img{width:70px;height:70px;object-fit:cover;vertical-align:middle;margin-right:8px}.jg-remove-image{margin-left:8px}' );
	wp_add_inline_script( 'jquery', "jQuery(function($){var frame;$(document).on('click','.jg-select-image',function(e){e.preventDefault();var wrap=$(this).closest('.jg-field'),input=wrap.find('.jg-image-id');frame=wp.media({title:'Choose image',button:{text:'Use image'},multiple:false});frame.on('select',function(){var a=frame.state().get('selection').first().toJSON();input.val(a.id);wrap.find('.jg-image-preview').html('<img src=\"'+a.sizes.thumbnail.url+'\" alt=\"\">');});frame.open();});$(document).on('click','.jg-remove-image',function(){var wrap=$(this).closest('.jg-field');wrap.find('.jg-image-id').val('');wrap.find('.jg-image-preview').empty();});});" );
}

function jg_register_leads() {
	register_post_type( 'janogago_lead', array( 'labels' => array( 'name' => __( 'JāņogaGO enquiries', 'janogago' ), 'singular_name' => __( 'Enquiry', 'janogago' ) ), 'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-email-alt', 'supports' => array( 'title', 'editor' ) ) );
}
add_action( 'init', 'jg_register_leads' );

function jg_is_valid_phone_number( $phone ) {
	$phone = trim( (string) $phone );
	if ( ! preg_match( '/^\+?[0-9().\s-]+$/', $phone ) ) {
		return false;
	}
	$digits = preg_replace( '/\D+/', '', $phone );
	return strlen( $digits ) >= 7 && strlen( $digits ) <= 15;
}

function jg_submit_enquiry() {
	if ( ! isset( $_POST['jg_enquiry_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jg_enquiry_nonce'] ) ), 'jg_submit_enquiry' ) ) {
		wp_die( 'Invalid form submission.' );
	}
	$company = sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) );
	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$people = sanitize_text_field( wp_unslash( $_POST['people'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	if ( ! $company || ! $name || ! is_email( $email ) || ! $phone || ! $people || ! $message || empty( $_POST['privacy_consent'] ) ) {
		wp_safe_redirect( add_query_arg( 'enquiry', 'invalid', wp_get_referer() ?: home_url( '/' ) ) ); exit;
	}
	if ( ! jg_is_valid_phone_number( $phone ) || ! ctype_digit( $people ) || 0 >= (int) $people ) {
		wp_safe_redirect( add_query_arg( 'enquiry', 'invalid', wp_get_referer() ?: home_url( '/' ) ) ); exit;
	}
	$interest = sanitize_key( wp_unslash( $_POST['service_interest'] ?? '' ) );
	$interest_labels = array( 'full-service' => 'Pilna servisa risinājums', 'equipment-lease' => 'Aprīkojuma noma' );
	$interest_label = $interest_labels[ $interest ] ?? 'Nav norādīts';
	$body = "Company: {$company}\nContact: {$name}\nEmail: {$email}\nPhone: {$phone}\nPeople: {$people}\nInterested service: {$interest_label}\n\n{$message}";
	$post_id = wp_insert_post( array( 'post_type' => 'janogago_lead', 'post_status' => 'private', 'post_title' => $company . ' — ' . $name, 'post_content' => $body ) );
	$recipient = sanitize_email( jg_field( absint( $_POST['page_id'] ?? 0 ), 'contact_email' ) );
	if ( $recipient ) { wp_mail( $recipient, 'JāņogaGO website enquiry: ' . $company, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) ); }
	wp_safe_redirect( add_query_arg( 'enquiry', $post_id ? 'sent' : 'failed', wp_get_referer() ?: home_url( '/' ) ) ); exit;
}
add_action( 'admin_post_nopriv_jg_submit_enquiry', 'jg_submit_enquiry' );
add_action( 'admin_post_jg_submit_enquiry', 'jg_submit_enquiry' );

function jg_seed_site() {
	$existing = get_option( 'jg_seeded_pages' );
	if ( $existing ) { return; }
	$lv = wp_insert_post( array( 'post_title' => 'Sākumlapa', 'post_name' => 'sakumlapa', 'post_status' => 'publish', 'post_type' => 'page' ) );
	$en = wp_insert_post( array( 'post_title' => 'Home', 'post_name' => 'home', 'post_status' => 'publish', 'post_type' => 'page' ) );
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $lv, 'lv' ); pll_set_post_language( $en, 'en' );
		if ( function_exists( 'pll_save_post_translations' ) ) { pll_save_post_translations( array( 'lv' => $lv, 'en' => $en ) ); }
	}
	if ( $lv && ! get_option( 'page_on_front' ) ) { update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $lv ); }
	update_option( 'jg_seeded_pages', array( 'lv' => $lv, 'en' => $en ) );
}
add_action( 'after_switch_theme', 'jg_seed_site' );

function jg_seed_attachment( $filename ) {
	$option = 'jg_seed_image_' . md5( $filename );
	$attachment_id = absint( get_option( $option ) );
	if ( $attachment_id && get_post( $attachment_id ) ) {
		return $attachment_id;
	}
	$source = get_template_directory() . '/assets/images/' . $filename;
	if ( ! file_exists( $source ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$upload = wp_upload_bits( wp_basename( $source ), null, file_get_contents( $source ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$attachment_id = wp_insert_attachment( array( 'post_mime_type' => wp_check_filetype( $upload['file'] )['type'], 'post_title' => sanitize_file_name( wp_basename( $source ) ), 'post_status' => 'inherit' ), $upload['file'] );
	if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_option( $option, $attachment_id, false );
	return $attachment_id;
}

function jg_seed_home_content() {
	if ( get_option( 'jg_home_content_v2' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( empty( $pages['lv'] ) || empty( $pages['en'] ) ) {
		$front_page = absint( get_option( 'page_on_front' ) );
		if ( $front_page && function_exists( 'pll_get_post' ) ) {
			$pages = array( 'lv' => pll_get_post( $front_page, 'lv' ), 'en' => pll_get_post( $front_page, 'en' ) );
		}
	}
	$hero_image = jg_seed_attachment( 'se-tsuchiya-JDoyICyNcfg-unsplash.jpg' );
	$menu_image = jg_seed_attachment( 'hennie-stander-8VtJPezUmiE-unsplash.jpg' );
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		if ( ! $page_id ) {
			continue;
		}
		foreach ( jg_defaults( $language ) as $key => $value ) {
			if ( ! metadata_exists( 'post', $page_id, '_jg_' . $key ) ) {
				update_post_meta( $page_id, '_jg_' . $key, $value );
			}
		}
		if ( $hero_image && ! metadata_exists( 'post', $page_id, '_jg_hero_image' ) ) {
			update_post_meta( $page_id, '_jg_hero_image', $hero_image );
		}
		if ( $menu_image && ! metadata_exists( 'post', $page_id, '_jg_menu_image' ) ) {
			update_post_meta( $page_id, '_jg_menu_image', $menu_image );
		}
	}
	update_option( 'jg_home_content_v2', 1, false );
}
add_action( 'admin_init', 'jg_seed_home_content' );

/**
 * Small helpers for the initial, fully native Gutenberg homepage layout.
 * The resulting blocks can be edited, moved, duplicated and deleted in the
 * standard block editor without touching theme code.
 */
function jg_block( $name, $attributes, $html ) {
	$attributes = $attributes ? ' ' . wp_json_encode( $attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '';
	return "<!-- wp:{$name}{$attributes} -->\n{$html}\n<!-- /wp:{$name} -->\n";
}

function jg_block_heading( $text, $level = 2, $class = '' ) {
	$class_attribute = $class ? ' class="wp-block-heading ' . esc_attr( $class ) . '"' : ' class="wp-block-heading"';
	return jg_block( 'heading', array_filter( array( 'level' => $level, 'className' => $class ) ), '<h' . absint( $level ) . $class_attribute . '>' . esc_html( $text ) . '</h' . absint( $level ) . '>' );
}

function jg_block_paragraph( $text, $class = '' ) {
	$class_attribute = $class ? ' class="' . esc_attr( $class ) . '"' : '';
	return jg_block( 'paragraph', array_filter( array( 'className' => $class ) ), '<p' . $class_attribute . '>' . esc_html( $text ) . '</p>' );
}

function jg_contact_detail_blocks( $copy ) {
	$email = sanitize_email( $copy['contact_email'] );
	$phone = preg_replace( '/[^+0-9]/', '', $copy['contact_phone'] );
	$email_block = jg_block( 'paragraph', array( 'className' => 'jg-contact-detail' ), '<p class="jg-contact-detail"><a href="mailto:' . esc_attr( antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a></p>' );
	$phone_block = jg_block( 'paragraph', array( 'className' => 'jg-contact-detail' ), '<p class="jg-contact-detail"><a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $copy['contact_phone'] ) . '</a></p>' );
	return $email_block . $phone_block;
}

function jg_block_button( $label, $url, $class = '' ) {
	$button = jg_block( 'button', array_filter( array( 'url' => $url, 'className' => $class ) ), '<div class="wp-block-button ' . esc_attr( $class ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . ' <span>↗</span></a></div>' );
	return jg_block( 'buttons', array( 'className' => 'jg-buttons' ), '<div class="wp-block-buttons jg-buttons">' . $button . '</div>' );
}

function jg_arrow_icon() {
	return '<span class="jg-arrow-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" focusable="false"><path d="M7 17 17 7M7 7h10v10"/></svg></span>';
}

function jg_render_button_arrow_icon( $content, $block ) {
	if ( 'core/button' !== ( $block['blockName'] ?? '' ) ) {
		return $content;
	}

	return str_replace( '<span>↗</span>', jg_arrow_icon(), $content );
}
add_filter( 'render_block', 'jg_render_button_arrow_icon', 10, 2 );

function jg_block_image( $attachment_id, $alt, $class = '' ) {
	$url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'large' ) : '';
	if ( ! $url ) {
		return '';
	}
	$attributes = array_filter( array( 'id' => absint( $attachment_id ), 'sizeSlug' => 'large', 'linkDestination' => 'none', 'className' => $class ) );
	$html = '<figure class="wp-block-image size-large ' . esc_attr( $class ) . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . absint( $attachment_id ) . '"/></figure>';
	return jg_block( 'image', $attributes, $html );
}

function jg_block_column( $content, $class = '', $vertical_align = '' ) {
	$attributes = array_filter( array( 'verticalAlignment' => $vertical_align, 'className' => $class ) );
	$classes = 'wp-block-column' . ( $vertical_align ? ' is-vertically-aligned-' . esc_attr( $vertical_align ) : '' ) . ( $class ? ' ' . esc_attr( $class ) : '' );
	return jg_block( 'column', $attributes, '<div class="' . $classes . '">' . $content . '</div>' );
}

function jg_block_columns( $columns, $class = '', $vertical_align = '' ) {
	$attributes = array_filter( array( 'verticalAlignment' => $vertical_align, 'className' => $class ) );
	$classes = 'wp-block-columns' . ( $vertical_align ? ' are-vertically-aligned-' . esc_attr( $vertical_align ) : '' ) . ( $class ? ' ' . esc_attr( $class ) : '' );
	return jg_block( 'columns', $attributes, '<div class="' . $classes . '">' . $columns . '</div>' );
}

function jg_block_group( $content, $class, $tag = 'section', $anchor = '' ) {
	$attributes = array_filter( array( 'tagName' => $tag, 'anchor' => $anchor, 'className' => $class, 'layout' => array( 'type' => 'constrained' ) ) );
	$anchor_attribute = $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '';
	return jg_block( 'group', $attributes, '<' . tag_escape( $tag ) . $anchor_attribute . ' class="wp-block-group ' . esc_attr( $class ) . '">' . $content . '</' . tag_escape( $tag ) . '>' );
}

function jg_block_faq_item( $question, $answer ) {
	$html = '<details class="wp-block-details jg-faq-item"><summary>' . esc_html( $question ) . '</summary>' . jg_block_paragraph( $answer ) . '</details>';
	return jg_block( 'details', array( 'className' => 'jg-faq-item' ), $html );
}

function jg_faq_section_blocks( $language ) {
	$is_en = $language === 'en';
	$title = $is_en ? 'Questions, answered.' : 'Biežāk uzdotie jautājumi.';
	$questions = $is_en ? array(
		'How large does our team need to be?' => 'The fully managed service is designed for offices, production sites and logistics centres with 30+ employees.',
		'What is included in the fully managed service?' => 'We install the machine, bring fresh food, refill the selection regularly and look after maintenance.',
		'Who looks after the equipment?' => 'JāņogaGO provides technical support and ongoing maintenance.',
		'What can the selection include?' => 'Hot lunches, salads and bowls, sandwiches, desserts, coffee and drinks. We shape the selection around your people.',
		'How do we get started?' => 'Book a consultation. We will learn about your workplace and prepare a suitable solution.',
	) : array(
		'Cik lielai jābūt komandai?' => 'Pilna servisa risinājums ir piemērots birojiem, ražotnēm un loģistikas centriem ar 30+ darbiniekiem.',
		'Kas ietilpst pilna servisa risinājumā?' => 'Uzstādām automātu, vedam svaigu pārtiku, regulāri papildinām sortimentu un rūpējamies par apkopi.',
		'Kas rūpējas par tehniku?' => 'JāņogaGO nodrošina tehnisko atbalstu un pastāvīgu aprūpi.',
		'Kādu sortimentu var nodrošināt?' => 'Siltas pusdienas, salātus un bļodas, sviestmaizes, desertus, kafiju un dzērienus. Sortimentam jāatbilst jūsu cilvēkiem.',
		'Kā sākt?' => 'Piesakiet konsultāciju. Iepazīsim jūsu vidi un sagatavosim piemērotu risinājumu.',
	);
	$items = '';
	foreach ( $questions as $question => $answer ) {
		$items .= jg_block_faq_item( $question, $answer );
	}
	return jg_block_group( jg_block_heading( $title, 2 ) . '<div class="jg-faq-list">' . $items . '</div>', 'faq section jg-block-section jg-block-faq', 'section', 'biezi-uzdotie-jautajumi' );
}

function jg_home_blocks( $language, $page_id ) {
	$copy = jg_defaults( $language );
	$hero_image = absint( get_post_meta( $page_id, '_jg_hero_image', true ) ) ?: jg_seed_attachment( 'se-tsuchiya-JDoyICyNcfg-unsplash.jpg' );
	$menu_image = absint( get_post_meta( $page_id, '_jg_menu_image', true ) ) ?: jg_seed_attachment( 'hennie-stander-8VtJPezUmiE-unsplash.jpg' );
	$is_en = $language === 'en';
	$more = $is_en ? 'Tell me more' : 'Vēlos uzzināt vairāk';

	$hero_copy = jg_block_heading( $copy['hero_title'], 1 ) . jg_block_paragraph( $copy['hero_text'], 'lede' ) . jg_block_button( $copy['hero_cta'], $copy['hero_cta_url'], 'button button-light' );
	$hero_visual = jg_block_image( $hero_image, $is_en ? 'JāņogaGO food vending machine' : 'JāņogaGO ēdienu automāts' ) . '<div class="hero-badge"><b>24/7</b><span>' . esc_html( $is_en ? 'ready when your team is' : 'gatavs, kad jūsu komanda ir' ) . '</span></div>';
	$hero = jg_block_group( jg_block_columns( jg_block_column( $hero_copy, 'hero-copy', 'center' ) . jg_block_column( $hero_visual, 'hero-visual', 'center' ), 'jg-block-hero-layout', 'center' ), 'hero jg-block-section jg-block-hero' );

	$points = '';
	foreach ( array( 'stat_one', 'stat_two', 'stat_three' ) as $index => $key ) {
		$points .= '<span>' . sprintf( '%02d', $index + 1 ) . ' <b>' . esc_html( $copy[ $key ] ) . '</b></span>';
	}
	$proof_copy = jg_block_paragraph( $copy['intro_text'] ) . '<div class="proof-points">' . $points . '</div>';
	$proof = jg_block_group( jg_block_columns( jg_block_column( jg_block_heading( $copy['intro_title'], 2 ), 'section-intro' ) . jg_block_column( $proof_copy, 'proof-copy' ), 'jg-block-proof-layout' ), 'proof section jg-block-section jg-block-proof', 'section', 'par-mums' );

	$menu_items = '';
	foreach ( array( 'menu_one', 'menu_two', 'menu_three', 'menu_four' ) as $key ) {
		$menu_items .= '<li>' . esc_html( $copy[ $key ] ) . '</li>';
	}
	$menu_copy = jg_block_heading( $copy['menu_title'], 2 ) . jg_block_paragraph( $copy['menu_text'] ) . '<ul class="menu-list">' . $menu_items . '</ul>';
	$menu = jg_block_group( jg_block_columns( jg_block_column( jg_block_image( $menu_image, $is_en ? 'Fresh workplace food' : 'Svaigs ēdiens darba vietā' ), 'menu-image' ) . jg_block_column( $menu_copy, 'menu-copy' ), 'jg-block-menu-layout' ), 'menu-section section jg-block-section jg-block-menu', 'section', 'edieni' );

	$cards = '';
	foreach ( array( array( 'model_one_title', 'model_one_text' ), array( 'model_two_title', 'model_two_text' ) ) as $model ) {
		$card = jg_block_heading( $copy[ $model[0] ], 3 ) . jg_block_paragraph( $copy[ $model[1] ] ) . jg_block_button( $more, '#pieteikties', 'jg-card-link' );
		$cards .= jg_block_column( jg_block_group( $card, 'jg-model-card', 'article' ) );
	}
	$models = jg_block_group( jg_block_heading( $copy['models_title'], 2 ) . jg_block_columns( $cards, 'model-grid' ), 'models section jg-block-section jg-block-models', 'section', 'risinajumi' );

	$steps = '';
	foreach ( array( array( 'step_one', 'step_one_text', '01' ), array( 'step_two', 'step_two_text', '02' ), array( 'step_three', 'step_three_text', '03' ) ) as $step ) {
		$steps .= jg_block_group( jg_block_paragraph( $step[2], 'jg-step-number' ) . jg_block_heading( $copy[ $step[0] ], 3 ) . jg_block_paragraph( $copy[ $step[1] ] ), 'jg-process-step', 'div' );
	}
	$process = jg_block_group( jg_block_columns( jg_block_column( jg_block_heading( $copy['process_title'], 2 ) ) . jg_block_column( '<div class="jg-process-steps">' . $steps . '</div>' ), 'jg-block-process-layout' ), 'process section jg-block-section jg-block-process', 'section', 'ka-tas-notiek' );

	$faq = jg_faq_section_blocks( $language );
	$contact = jg_contact_section_blocks( $language );

	return $hero . $proof . $menu . $models . $process . $faq . $contact;
}

function jg_contact_section_blocks( $language ) {
	$copy = jg_defaults( $language );
	$contact_details = jg_contact_detail_blocks( $copy );
	$form = jg_block( 'shortcode', array(), '<div class="wp-block-shortcode">[janogago_enquiry_form]</div>' );
	return jg_block_group( jg_block_columns( jg_block_column( jg_block_heading( $copy['contact_title'], 2 ) . jg_block_paragraph( $copy['contact_text'] ) . $contact_details ) . jg_block_column( $form ), 'jg-block-contact-layout', 'center' ), 'contact jg-block-section jg-block-contact', 'section', 'pieteikties' );
}

function jg_seed_gutenberg_homepages() {
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( empty( $pages['lv'] ) || empty( $pages['en'] ) ) {
		$front_page = absint( get_option( 'page_on_front' ) );
		if ( $front_page && function_exists( 'pll_get_post' ) ) {
			$pages = array( 'lv' => pll_get_post( $front_page, 'lv' ), 'en' => pll_get_post( $front_page, 'en' ) );
		}
	}
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page || trim( $page->post_content ) !== '' ) {
			continue;
		}
		wp_update_post( array( 'ID' => $page_id, 'post_content' => jg_home_blocks( $language, $page_id ) ) );
	}
}
add_action( 'admin_init', 'jg_seed_gutenberg_homepages', 20 );

function jg_remove_menu_eyebrows() {
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( empty( $pages['lv'] ) || empty( $pages['en'] ) ) {
		$front_page = absint( get_option( 'page_on_front' ) );
		if ( $front_page && function_exists( 'pll_get_post' ) ) {
			$pages = array( 'lv' => pll_get_post( $front_page, 'lv' ), 'en' => pll_get_post( $front_page, 'en' ) );
		}
	}
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page ) {
			continue;
		}
		$content = preg_replace( '#<!-- wp:paragraph \\{"className":"eyebrow"\\} -->\\s*<p class="[^\"]*eyebrow[^\"]*">(?:NE TIKAI UZKODAS|MORE THAN SNACKS)</p>\\s*<!-- /wp:paragraph -->\\s*#u', '', $page->post_content );
		if ( $content !== $page->post_content ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
		}
	}
}
add_action( 'admin_init', 'jg_remove_menu_eyebrows', 30 );

function jg_remove_model_numbers() {
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( empty( $pages['lv'] ) || empty( $pages['en'] ) ) {
		$front_page = absint( get_option( 'page_on_front' ) );
		if ( $front_page && function_exists( 'pll_get_post' ) ) {
			$pages = array( 'lv' => pll_get_post( $front_page, 'lv' ), 'en' => pll_get_post( $front_page, 'en' ) );
		}
	}
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page ) {
			continue;
		}
		$content = preg_replace( '#<!-- wp:paragraph [^>]*jg-card-number[^>]* -->.*?<!-- /wp:paragraph -->\\s*#s', '', $page->post_content );
		if ( $content !== $page->post_content ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
		}
	}
}
add_action( 'admin_init', 'jg_remove_model_numbers', 40 );

function jg_update_brand_name() {
	if ( get_option( 'jg_brand_name_v1' ) ) {
		return;
	}
	update_option( 'blogname', 'JāņogaGO' );
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( empty( $pages['lv'] ) || empty( $pages['en'] ) ) {
		$front_page = absint( get_option( 'page_on_front' ) );
		if ( $front_page && function_exists( 'pll_get_post' ) ) {
			$pages = array( 'lv' => pll_get_post( $front_page, 'lv' ), 'en' => pll_get_post( $front_page, 'en' ) );
		}
	}
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( $page && str_contains( $page->post_content, 'JanogaGo' ) ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => str_replace( 'JanogaGo', 'JāņogaGO', $page->post_content ) ) );
		}
	}
	update_option( 'jg_brand_name_v1', 1, false );
}
add_action( 'admin_init', 'jg_update_brand_name', 50 );

function jg_update_intro_copy() {
	if ( get_option( 'jg_intro_copy_v4' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( empty( $pages['lv'] ) || empty( $pages['en'] ) ) {
		$front_page = absint( get_option( 'page_on_front' ) );
		if ( $front_page && function_exists( 'pll_get_post' ) ) {
			$pages = array( 'lv' => pll_get_post( $front_page, 'lv' ), 'en' => pll_get_post( $front_page, 'en' ) );
		}
	}
	$replacements = array(
		'lv' => array(
			'Svaigs ēdiens darbā. Bez liekām rūpēm.' => 'Pusdienas, par kurām nav jādomā.',
			'JāņogaGO pārvalda visu ciklu. Ēdienu gatavo mūsu komanda, sortimentu papildinām regulāri, un tehnikai sekojam paši. Jūsu komandai atliek paņemt to, kas garšo.' => 'JāņogaGO komanda gatavo ēdienu, regulāri papildina automātus un rūpējas par tehniku. Jūsu komandai atliek izvēlēties to, kas garšo.',
		),
		'en' => array(
			'Fresh food at work. Without the extra work.' => 'Lunch, without the logistics.',
			'JāņogaGO runs the whole service. Our team prepares the food, keeps the selection fresh and looks after the equipment. Your team can simply choose what they want.' => 'The JāņogaGO team prepares the food, refills the machines and looks after the equipment. Your team simply chooses what they feel like.',
		),
	);
	foreach ( $replacements as $language => $copy ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page ) {
			continue;
		}
		$content = str_replace( array_keys( $copy ), array_values( $copy ), $page->post_content );
		if ( $content !== $page->post_content ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
		}
	}
	update_option( 'jg_intro_copy_v4', 1, false );
}
add_action( 'init', 'jg_update_intro_copy', 20 );

function jg_add_contact_details_to_homepages() {
	if ( get_option( 'jg_contact_details_v1' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( empty( $pages['lv'] ) || empty( $pages['en'] ) ) {
		$front_page = absint( get_option( 'page_on_front' ) );
		if ( $front_page && function_exists( 'pll_get_post' ) ) {
			$pages = array( 'lv' => pll_get_post( $front_page, 'lv' ), 'en' => pll_get_post( $front_page, 'en' ) );
		}
	}
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page || str_contains( $page->post_content, 'jg-contact-detail' ) ) {
			continue;
		}
		$copy = jg_defaults( $language );
		$pattern = '#(<!-- wp:paragraph(?: [^>]*)? -->\s*<p(?: [^>]*)?>' . preg_quote( esc_html( $copy['contact_text'] ), '#' ) . '</p>\s*<!-- /wp:paragraph -->)#u';
		$content = preg_replace( $pattern, '$1' . jg_contact_detail_blocks( $copy ), $page->post_content, 1, $count );
		if ( $count ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
		}
	}
	update_option( 'jg_contact_details_v1', 1, false );
}
add_action( 'init', 'jg_add_contact_details_to_homepages', 21 );

function jg_update_consultation_copy() {
	if ( get_option( 'jg_consultation_copy_v1' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( ! is_array( $pages ) ) {
		return;
	}
	$replacements = array(
		'lv' => array(
			'Pieteikt degustāciju' => 'Pieteikt konsultāciju',
			'SĀKSIM AR DEGUSTĀCIJU' => 'SĀKSIM AR KONSULTĀCIJU',
			'sarunāsim degustāciju' => 'sarunāsim konsultāciju',
		),
		'en' => array(
			'Book a tasting' => 'Book a consultation',
			'START WITH A TASTING' => 'START WITH A CONSULTATION',
			'come back with a practical proposal and a tasting' => 'come back with a practical proposal and arrange a consultation',
		),
	);
	foreach ( $replacements as $language => $map ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page ) {
			continue;
		}
		$content = strtr( $page->post_content, $map );
		if ( $content !== $page->post_content ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
		}
		foreach ( array( 'hero_cta', 'contact_eyebrow', 'contact_text' ) as $key ) {
			$meta_key = '_jg_' . $key;
			$value = get_post_meta( $page_id, $meta_key, true );
			if ( $value !== '' ) {
				update_post_meta( $page_id, $meta_key, strtr( $value, $map ) );
			}
		}
	}
	update_option( 'jg_consultation_copy_v1', 1, false );
}
add_action( 'init', 'jg_update_consultation_copy', 22 );

function jg_restore_english_contact_section() {
	if ( get_option( 'jg_english_contact_section_v1' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	$page_id = absint( is_array( $pages ) ? ( $pages['en'] ?? 0 ) : 0 );
	$page = $page_id ? get_post( $page_id ) : null;
	if ( ! $page || str_contains( $page->post_content, 'jg-contact-detail' ) ) {
		return;
	}
	$pattern = '#<!-- wp:group \{"tagName":"section","className":"contact jg-block-section jg-block-contact"[^>]*-->.*?<!-- /wp:group -->\s*$#s';
	$replacement = trim( jg_contact_section_blocks( 'en' ) );
	$content = preg_replace( $pattern, $replacement, $page->post_content, 1, $count );
	if ( 1 !== $count || $content === $page->post_content ) {
		return;
	}
	wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
	update_option( 'jg_english_contact_section_v1', 1, false );
}
add_action( 'init', 'jg_restore_english_contact_section', 23 );

/**
 * Add the second-generation content without replacing any editor changes.
 * These blocks remain ordinary Gutenberg blocks after this one-time migration.
 */
function jg_expand_homepage_content() {
	if ( get_option( 'jg_home_content_expansion_v1' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( ! is_array( $pages ) ) {
		return;
	}
	$hero_replacements = array(
		'lv' => array(
			'Viedie ēdienu automāti ar svaigām maltītēm, uzkodām un dzērieniem. Mēs piegādājam, papildinām un uzturam visu kārtībā.' => jg_defaults( 'lv' )['hero_text'],
		),
		'en' => array(
			'Smart food vending with fresh meals, snacks and drinks. We deliver, refill and maintain the whole service.' => jg_defaults( 'en' )['hero_text'],
		),
	);
	$complete = true;
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page ) {
			$complete = false;
			continue;
		}
		$content = strtr( $page->post_content, $hero_replacements[ $language ] );
		$content = preg_replace_callback(
			'#<ul class="menu-list">.*?</ul>#s',
			static function( $match ) {
				return str_replace( '<span>↗</span>', '', $match[0] );
			},
			$content
		);
		if ( ! str_contains( $content, 'jg-block-comparison' ) ) {
			$content = preg_replace( '#(?=<!-- wp:group [^>]*"className":"process section jg-block-section jg-block-process")#', jg_model_comparison_blocks( $language ), $content, 1, $comparison_count );
			if ( 1 !== $comparison_count ) {
				$complete = false;
			}
		}
		if ( ! str_contains( $content, 'jg-block-faq' ) ) {
			$content = preg_replace( '#(?=<!-- wp:group [^>]*"className":"contact jg-block-section jg-block-contact")#', jg_faq_section_blocks( $language ), $content, 1, $faq_count );
			if ( 1 !== $faq_count ) {
				$complete = false;
			}
		}
		if ( $content !== $page->post_content ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
		}
		$hero_meta = get_post_meta( $page_id, '_jg_hero_text', true );
		if ( isset( $hero_replacements[ $language ][ $hero_meta ] ) ) {
			update_post_meta( $page_id, '_jg_hero_text', $hero_replacements[ $language ][ $hero_meta ] );
		}
	}
	if ( $complete ) {
		update_option( 'jg_home_content_expansion_v1', 1, false );
	}
}
add_action( 'init', 'jg_expand_homepage_content', 24 );

/** Remove the redundant comparison and strengthen the two service cards in place. */
function jg_refine_service_models() {
	if ( get_option( 'jg_service_models_v2' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( ! is_array( $pages ) ) {
		return;
	}
	$replacements = array(
		'lv' => array(
			'Piemērots birojiem, ražotnēm un loģistikas centriem ar 30+ darbiniekiem. Uzstādām automātu, vedam svaigu pārtiku un rūpējamies par apkopi.' => jg_defaults( 'lv' )['model_one_text'],
			'Jūsu komanda pārvalda sortimentu. Mēs nodrošinām iekārtu, tehnisko atbalstu un norēķinu risinājumu.' => jg_defaults( 'lv' )['model_two_text'],
		),
		'en' => array(
			'For offices, production sites and logistics centres with 30+ employees. We install the machine, bring fresh food and keep it running.' => jg_defaults( 'en' )['model_one_text'],
			'Your team manages the selection. We provide the equipment, technical support and payment system.' => jg_defaults( 'en' )['model_two_text'],
		),
	);
	$complete = true;
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page ) {
			$complete = false;
			continue;
		}
		$content = strtr( $page->post_content, $replacements[ $language ] );
		$content = preg_replace( '#<!-- wp:group [^>]*"className":"service-comparison section jg-block-section jg-block-comparison"[^>]*-->.*?<!-- /wp:group -->\s*#s', '', $content, 1, $comparison_count );
		if ( ! str_contains( $content, 'jg-block-comparison' ) && 1 !== $comparison_count && str_contains( $page->post_content, 'jg-block-comparison' ) ) {
			$complete = false;
		}
		if ( $content !== $page->post_content ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
		}
	}
	if ( $complete ) {
		update_option( 'jg_service_models_v2', 1, false );
	}
}
add_action( 'init', 'jg_refine_service_models', 25 );

function jg_seed_form_copy_fields() {
	if ( get_option( 'jg_form_copy_fields_v2' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( ! is_array( $pages ) ) {
		return;
	}
	$form_keys = array( 'form_company_label', 'form_name_label', 'form_email_label', 'form_phone_label', 'form_people_label', 'form_message_label', 'form_privacy_label', 'form_submit_label', 'form_success_message', 'form_invalid_message', 'form_phone_invalid_message' );
	$complete = true;
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		if ( ! $page_id ) {
			$complete = false;
			continue;
		}
		$defaults = jg_defaults( $language );
		foreach ( $form_keys as $key ) {
			if ( ! metadata_exists( 'post', $page_id, '_jg_' . $key ) ) {
				update_post_meta( $page_id, '_jg_' . $key, $defaults[ $key ] );
			}
		}
	}
	if ( $complete ) {
		update_option( 'jg_form_copy_fields_v2', 1, false );
	}
}
add_action( 'init', 'jg_seed_form_copy_fields', 26 );

function jg_enquiry_form_shortcode() {
	$page_id = get_queried_object_id() ?: get_the_ID();
	$form_copy = array();
	foreach ( array( 'form_company_label', 'form_name_label', 'form_email_label', 'form_phone_label', 'form_people_label', 'form_message_label', 'form_privacy_label', 'form_submit_label', 'form_success_message', 'form_invalid_message', 'form_phone_invalid_message' ) as $key ) {
		$form_copy[ $key ] = jg_field( $page_id, $key );
	}
	ob_start();
	?>
	<form class="jg-enquiry-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-required-message="<?php echo esc_attr( $form_copy['form_invalid_message'] ); ?>" data-phone-invalid-message="<?php echo esc_attr( $form_copy['form_phone_invalid_message'] ); ?>">
		<input type="hidden" name="action" value="jg_submit_enquiry"><input type="hidden" name="page_id" value="<?php echo esc_attr( $page_id ); ?>"><input type="hidden" name="service_interest" value=""><?php wp_nonce_field( 'jg_submit_enquiry', 'jg_enquiry_nonce' ); ?>
		<label><?php echo esc_html( $form_copy['form_company_label'] ); ?><input required name="company" type="text" autocomplete="organization" maxlength="120"></label><label><?php echo esc_html( $form_copy['form_name_label'] ); ?><input required name="name" type="text" autocomplete="name" maxlength="120"></label><label><?php echo esc_html( $form_copy['form_email_label'] ); ?><input required name="email" type="email" autocomplete="email" maxlength="254"></label><label><?php echo esc_html( $form_copy['form_phone_label'] ); ?><input required name="phone" type="tel" autocomplete="tel" inputmode="tel" pattern="[0-9+(). -]{7,25}" maxlength="25"></label><label><?php echo esc_html( $form_copy['form_people_label'] ); ?><input required name="people" type="number" inputmode="numeric" min="1" step="1"></label><label class="full"><?php echo esc_html( $form_copy['form_message_label'] ); ?><textarea required name="message" rows="3" maxlength="2000"></textarea></label>
		<label class="full jg-privacy-consent"><input required name="privacy_consent" type="checkbox" value="1"><span><?php echo esc_html( $form_copy['form_privacy_label'] ); ?></span></label>
		<button class="button button-dark" type="submit"><?php echo esc_html( $form_copy['form_submit_label'] ); ?> <?php echo jg_arrow_icon(); ?></button>
		<?php if ( isset( $_GET['enquiry'] ) && $_GET['enquiry'] === 'sent' ) : ?><p class="form-message"><?php echo esc_html( $form_copy['form_success_message'] ); ?></p><?php elseif ( isset( $_GET['enquiry'] ) && $_GET['enquiry'] === 'invalid' ) : ?><p class="form-message"><?php echo esc_html( $form_copy['form_invalid_message'] ); ?></p><?php endif; ?>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'janogago_enquiry_form', 'jg_enquiry_form_shortcode' );

function jg_fallback_menu() {
	$items = jg_lang() === 'en' ? array( '#edieni' => 'Food', '#risinajumi' => 'Solutions', '#ka-tas-notiek' => 'How it works' ) : array( '#edieni' => 'Ēdiens', '#risinajumi' => 'Risinājumi', '#ka-tas-notiek' => 'Kā tas notiek' );
	echo '<ul class="jg-menu">';
	foreach ( $items as $url => $label ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}
