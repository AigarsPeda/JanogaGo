<?php
/*
Template Name: Food catalog
*/
get_header();
?>
<main class="janogago-blocks jg-catalog-page">
	<?php while ( have_posts() ) { the_post(); the_content(); } ?>
</main>
<?php get_footer(); ?>
