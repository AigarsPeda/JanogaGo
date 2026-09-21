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
		'hero_text' => 'Viedie ēdienu automāti ar svaigām maltītēm, uzkodām un dzērieniem. Mēs piegādājam, papildinām un uzturam visu kārtībā.',
		'hero_cta' => 'Pieteikt degustāciju',
		'hero_cta_url' => '#pieteikties',
		'intro_eyebrow' => 'KĀPĒC JANOGAGO',
		'intro_title' => 'Mēs neieliekam automātu un nepazūdam.',
		'intro_text' => 'JanogaGo pārvalda visu ciklu. Ēdienu gatavo mūsu komanda, sortimentu papildinām regulāri, un tehnikai sekojam paši. Jūsu komandai atliek paņemt to, kas garšo.',
		'stat_one' => 'SVAIGI GATAVOTS KATRU DIENU', 'stat_two' => 'KARTE, TELEFONS VAI VIEDPULKSTENIS', 'stat_three' => 'HIGIĒNA UN TEMPERATŪRA KONTROLĒTA',
		'menu_eyebrow' => 'NE TIKAI UZKODAS',
		'menu_title' => 'Pusdienas, ko gaida, nevis izlaiž.',
		'menu_text' => 'No siltām maltītēm līdz labai kafijai. Sortimentam jāatbilst jūsu cilvēkiem, tāpēc to veidojam kopā ar jums.',
		'menu_one' => 'Siltās pusdienas', 'menu_two' => 'Salāti un bļodas', 'menu_three' => 'Sviestmaizes un deserti', 'menu_four' => 'Kafija un dzērieni',
		'models_eyebrow' => 'DIVI SADARBĪBAS VEIDI',
		'models_title' => 'Izvēlieties modeli. Pārējo izdaram mēs.',
		'model_one_title' => 'Pilna servisa risinājums',
		'model_one_text' => 'Piemērots birojiem, ražotnēm un loģistikas centriem ar 30+ darbiniekiem. Uzstādām automātu, vedam svaigu pārtiku un rūpējamies par apkopi.',
		'model_two_title' => 'Aprīkojuma noma',
		'model_two_text' => 'Jūsu komanda pārvalda sortimentu. Mēs nodrošinām iekārtu, tehnisko atbalstu un norēķinu risinājumu.',
		'process_eyebrow' => 'KĀ TAS NOTIEK',
		'process_title' => 'No pirmās sarunas līdz pirmajām pusdienām.',
		'step_one' => 'Iepazīstam jūsu vidi', 'step_one_text' => 'Saprotam cilvēku skaitu, maiņas un to, kas viņiem tiešām noderēs.',
		'step_two' => 'Sagatavojam risinājumu', 'step_two_text' => 'Piedāvājam iekārtu, sortimentu un sadarbības modeli.',
		'step_three' => 'Uzstādām un aprūpējam', 'step_three_text' => 'Piegādājam, papildinām un reaģējam, ja vajadzīga palīdzība.',
		'contact_eyebrow' => 'SĀKSIM AR DEGUSTĀCIJU',
		'contact_title' => 'Ienesiet restorāna līmeņa ēdienu savā darba vietā.',
		'contact_text' => 'Pastāstiet par savu uzņēmumu. Atbildēsim ar piemērotu risinājumu un sarunāsim degustāciju.',
		'contact_email' => 'info@janoga.lv', 'contact_phone' => '+371 0000 0000', 'contact_address' => 'Rīga, Latvija',
		'form_company_label' => 'Uzņēmums', 'form_name_label' => 'Jūsu vārds', 'form_email_label' => 'E-pasts', 'form_phone_label' => 'Tālrunis', 'form_people_label' => 'Darbinieku skaits', 'form_message_label' => 'Ko vēlaties nodrošināt?', 'form_submit_label' => 'Nosūtīt pieteikumu',
	);
	$en = array(
		'hero_eyebrow' => 'WORKPLACE FOOD, FULLY MANAGED',
		'hero_title' => 'Good food at work. Any time of day.',
		'hero_text' => 'Smart food vending with fresh meals, snacks and drinks. We deliver, refill and maintain the whole service.',
		'hero_cta' => 'Book a tasting', 'hero_cta_url' => '#pieteikties',
		'intro_eyebrow' => 'WHY JANOGAGO',
		'intro_title' => 'We do not place a machine and disappear.',
		'intro_text' => 'JanogaGo runs the whole service. Our team prepares the food, keeps the selection fresh and looks after the equipment. Your team can simply choose what they want.',
		'stat_one' => 'FRESHLY PREPARED EVERY DAY', 'stat_two' => 'CARD, PHONE OR SMARTWATCH', 'stat_three' => 'HYGIENE AND TEMPERATURE CONTROLLED',
		'menu_eyebrow' => 'MORE THAN SNACKS',
		'menu_title' => 'Lunch worth taking a break for.',
		'menu_text' => 'Warm meals, good coffee and everything in between. We shape the selection around the people who use it.',
		'menu_one' => 'Hot lunch', 'menu_two' => 'Salads and bowls', 'menu_three' => 'Sandwiches and desserts', 'menu_four' => 'Coffee and drinks',
		'models_eyebrow' => 'TWO WAYS TO WORK TOGETHER',
		'models_title' => 'Choose the model. We handle the rest.',
		'model_one_title' => 'Fully managed service',
		'model_one_text' => 'For offices, production sites and logistics centres with 30+ employees. We install the machine, bring fresh food and keep it running.',
		'model_two_title' => 'Equipment lease',
		'model_two_text' => 'Your team manages the selection. We provide the equipment, technical support and payment system.',
		'process_eyebrow' => 'HOW IT WORKS',
		'process_title' => 'From the first call to the first lunch.',
		'step_one' => 'We learn about your workplace', 'step_one_text' => 'We look at headcount, shifts and what will actually work for your people.',
		'step_two' => 'We plan the service', 'step_two_text' => 'You get a clear proposal for equipment, food and the right service model.',
		'step_three' => 'We install and run it', 'step_three_text' => 'We deliver, refill and respond whenever you need us.',
		'contact_eyebrow' => 'START WITH A TASTING',
		'contact_title' => 'Bring restaurant-level food to your workplace.',
		'contact_text' => 'Tell us about your company. We will come back with a practical proposal and a tasting.',
		'contact_email' => 'info@janoga.lv', 'contact_phone' => '+371 0000 0000', 'contact_address' => 'Riga, Latvia',
		'form_company_label' => 'Company', 'form_name_label' => 'Your name', 'form_email_label' => 'Email', 'form_phone_label' => 'Phone', 'form_people_label' => 'Number of people', 'form_message_label' => 'What do you need?', 'form_submit_label' => 'Send enquiry',
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
		'form_company_label' => array( 'label' => 'Form: company label', 'type' => 'text' ), 'form_name_label' => array( 'label' => 'Form: name label', 'type' => 'text' ), 'form_email_label' => array( 'label' => 'Form: email label', 'type' => 'text' ), 'form_phone_label' => array( 'label' => 'Form: phone label', 'type' => 'text' ), 'form_people_label' => array( 'label' => 'Form: people label', 'type' => 'text' ), 'form_message_label' => array( 'label' => 'Form: message label', 'type' => 'text' ), 'form_submit_label' => array( 'label' => 'Form: button label', 'type' => 'text' ),
	);
}

function jg_add_page_metabox() {
	add_meta_box( 'jg-page-content', __( 'JanogaGo page content', 'janogago' ), 'jg_render_page_metabox', 'page', 'normal', 'high' );
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
	register_post_type( 'janogago_lead', array( 'labels' => array( 'name' => __( 'JanogaGo enquiries', 'janogago' ), 'singular_name' => __( 'Enquiry', 'janogago' ) ), 'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-email-alt', 'supports' => array( 'title', 'editor' ) ) );
}
add_action( 'init', 'jg_register_leads' );

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
	if ( ! $company || ! $name || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'enquiry', 'invalid', wp_get_referer() ?: home_url( '/' ) ) ); exit;
	}
	$body = "Company: {$company}\nContact: {$name}\nEmail: {$email}\nPhone: {$phone}\nPeople: {$people}\n\n{$message}";
	$post_id = wp_insert_post( array( 'post_type' => 'janogago_lead', 'post_status' => 'private', 'post_title' => $company . ' — ' . $name, 'post_content' => $body ) );
	$recipient = sanitize_email( jg_field( absint( $_POST['page_id'] ?? 0 ), 'contact_email' ) );
	if ( $recipient ) { wp_mail( $recipient, 'JanogaGo website enquiry: ' . $company, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) ); }
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

function jg_block_button( $label, $url, $class = '' ) {
	$button = jg_block( 'button', array_filter( array( 'url' => $url, 'className' => $class ) ), '<div class="wp-block-button ' . esc_attr( $class ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . ' <span>↗</span></a></div>' );
	return jg_block( 'buttons', array( 'className' => 'jg-buttons' ), '<div class="wp-block-buttons jg-buttons">' . $button . '</div>' );
}

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

function jg_home_blocks( $language, $page_id ) {
	$copy = jg_defaults( $language );
	$hero_image = absint( get_post_meta( $page_id, '_jg_hero_image', true ) ) ?: jg_seed_attachment( 'se-tsuchiya-JDoyICyNcfg-unsplash.jpg' );
	$menu_image = absint( get_post_meta( $page_id, '_jg_menu_image', true ) ) ?: jg_seed_attachment( 'hennie-stander-8VtJPezUmiE-unsplash.jpg' );
	$is_en = $language === 'en';
	$more = $is_en ? 'Tell me more' : 'Vēlos uzzināt vairāk';

	$hero_copy = jg_block_heading( $copy['hero_title'], 1 ) . jg_block_paragraph( $copy['hero_text'], 'lede' ) . jg_block_button( $copy['hero_cta'], $copy['hero_cta_url'], 'button button-light' );
	$hero_visual = jg_block_image( $hero_image, $is_en ? 'JanogaGo food vending machine' : 'JanogaGo ēdienu automāts' ) . '<div class="hero-badge"><b>24/7</b><span>' . esc_html( $is_en ? 'ready when your team is' : 'gatavs, kad jūsu komanda ir' ) . '</span></div>';
	$hero = jg_block_group( jg_block_columns( jg_block_column( $hero_copy, 'hero-copy', 'center' ) . jg_block_column( $hero_visual, 'hero-visual', 'center' ), 'jg-block-hero-layout', 'center' ), 'hero jg-block-section jg-block-hero' );

	$points = '';
	foreach ( array( 'stat_one', 'stat_two', 'stat_three' ) as $index => $key ) {
		$points .= '<span>' . sprintf( '%02d', $index + 1 ) . ' <b>' . esc_html( $copy[ $key ] ) . '</b></span>';
	}
	$proof_copy = jg_block_paragraph( $copy['intro_text'] ) . '<div class="proof-points">' . $points . '</div>';
	$proof = jg_block_group( jg_block_columns( jg_block_column( jg_block_heading( $copy['intro_title'], 2 ), 'section-intro' ) . jg_block_column( $proof_copy, 'proof-copy' ), 'jg-block-proof-layout' ), 'proof section jg-block-section jg-block-proof', 'section', 'par-mums' );

	$menu_items = '';
	foreach ( array( 'menu_one', 'menu_two', 'menu_three', 'menu_four' ) as $key ) {
		$menu_items .= '<li>' . esc_html( $copy[ $key ] ) . '<span>↗</span></li>';
	}
	$menu_copy = jg_block_paragraph( $copy['menu_eyebrow'], 'eyebrow' ) . jg_block_heading( $copy['menu_title'], 2 ) . jg_block_paragraph( $copy['menu_text'] ) . '<ul class="menu-list">' . $menu_items . '</ul>';
	$menu = jg_block_group( jg_block_columns( jg_block_column( jg_block_image( $menu_image, $is_en ? 'Fresh workplace food' : 'Svaigs ēdiens darba vietā' ), 'menu-image' ) . jg_block_column( $menu_copy, 'menu-copy' ), 'jg-block-menu-layout' ), 'menu-section section jg-block-section jg-block-menu', 'section', 'edieni' );

	$cards = '';
	foreach ( array( array( 'model_one_title', 'model_one_text', '01' ), array( 'model_two_title', 'model_two_text', '02' ) ) as $model ) {
		$card = jg_block_paragraph( $model[2], 'jg-card-number' ) . jg_block_heading( $copy[ $model[0] ], 3 ) . jg_block_paragraph( $copy[ $model[1] ] ) . jg_block_button( $more, '#pieteikties', 'jg-card-link' );
		$cards .= jg_block_column( jg_block_group( $card, 'jg-model-card', 'article' ) );
	}
	$models = jg_block_group( jg_block_heading( $copy['models_title'], 2 ) . jg_block_columns( $cards, 'model-grid' ), 'models section jg-block-section jg-block-models', 'section', 'risinajumi' );

	$steps = '';
	foreach ( array( array( 'step_one', 'step_one_text', '01' ), array( 'step_two', 'step_two_text', '02' ), array( 'step_three', 'step_three_text', '03' ) ) as $step ) {
		$steps .= jg_block_group( jg_block_paragraph( $step[2], 'jg-step-number' ) . jg_block_heading( $copy[ $step[0] ], 3 ) . jg_block_paragraph( $copy[ $step[1] ] ), 'jg-process-step', 'div' );
	}
	$process = jg_block_group( jg_block_columns( jg_block_column( jg_block_heading( $copy['process_title'], 2 ) ) . jg_block_column( '<div class="jg-process-steps">' . $steps . '</div>' ), 'jg-block-process-layout' ), 'process section jg-block-section jg-block-process', 'section', 'ka-tas-notiek' );

	$contact_links = '<div class="jg-contact-links"><a href="mailto:' . esc_attr( antispambot( $copy['contact_email'] ) ) . '">' . esc_html( antispambot( $copy['contact_email'] ) ) . '</a><a href="tel:' . esc_attr( preg_replace( '/[^+0-9]/', '', $copy['contact_phone'] ) ) . '">' . esc_html( $copy['contact_phone'] ) . '</a></div>';
	$form = jg_block( 'shortcode', array(), '<div class="wp-block-shortcode">[janogago_enquiry_form]</div>' );
	$contact = jg_block_group( jg_block_columns( jg_block_column( jg_block_heading( $copy['contact_title'], 2 ) . jg_block_paragraph( $copy['contact_text'] ) . $contact_links ) . jg_block_column( $form ), 'jg-block-contact-layout', 'center' ), 'contact jg-block-section jg-block-contact', 'section', 'pieteikties' );

	return $hero . $proof . $menu . $models . $process . $contact;
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

function jg_enquiry_form_shortcode() {
	$page_id = get_queried_object_id() ?: get_the_ID();
	$copy = jg_defaults( jg_lang() );
	ob_start();
	?>
	<form class="jg-enquiry-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="jg_submit_enquiry"><input type="hidden" name="page_id" value="<?php echo esc_attr( $page_id ); ?>"><?php wp_nonce_field( 'jg_submit_enquiry', 'jg_enquiry_nonce' ); ?>
		<label><?php echo esc_html( $copy['form_company_label'] ); ?><input required name="company" type="text"></label><label><?php echo esc_html( $copy['form_name_label'] ); ?><input required name="name" type="text"></label><label><?php echo esc_html( $copy['form_email_label'] ); ?><input required name="email" type="email"></label><label><?php echo esc_html( $copy['form_phone_label'] ); ?><input name="phone" type="tel"></label><label><?php echo esc_html( $copy['form_people_label'] ); ?><input name="people" type="text"></label><label class="full"><?php echo esc_html( $copy['form_message_label'] ); ?><textarea name="message" rows="3"></textarea></label><button class="button button-dark" type="submit"><?php echo esc_html( $copy['form_submit_label'] ); ?> <span>↗</span></button>
		<?php if ( isset( $_GET['enquiry'] ) && $_GET['enquiry'] === 'sent' ) : ?><p class="form-message"><?php echo esc_html( jg_lang() === 'en' ? 'Thank you. We will be in touch.' : 'Paldies. Mēs ar jums sazināsimies.' ); ?></p><?php endif; ?>
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
