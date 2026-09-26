<footer class="site-footer">
	<div class="site-brand">
		<?php if ( has_custom_logo() ) {
			the_custom_logo();
		} else {
			$brand_name   = get_bloginfo( 'name' );
			$brand_prefix = str_ends_with( $brand_name, 'GO' ) ? substr( $brand_name, 0, -2 ) : $brand_name;
			?>
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $brand_name ); ?>"><?php echo esc_html( $brand_prefix ); ?><?php if ( $brand_prefix !== $brand_name ) { ?><span>GO</span><?php } ?></a>
		<?php } ?>
	</div>
	<?php jg_page_footer_content( get_queried_object_id() ); ?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
