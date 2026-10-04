<footer class="site-footer">
	<div class="site-brand">
		<?php if ( has_custom_logo() ) {
			the_custom_logo();
		} else {
			$brand_name   = get_bloginfo( 'name' );
			?>
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/JanogaGoLogoDarkNew.png' ); ?>" alt="<?php echo esc_attr( $brand_name ); ?>" width="1305" height="310"></a>
		<?php } ?>
	</div>
	<?php jg_page_footer_content( get_queried_object_id() ); ?>
	<?php jg_privacy_footer_links(); ?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
