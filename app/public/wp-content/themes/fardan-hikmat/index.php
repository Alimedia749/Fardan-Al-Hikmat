<?php
/**
 * Main Template File — Fallback
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container" style="padding-top: calc(var(--navbar-height) + 4rem); padding-bottom: 6rem;">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="section-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No content found.', 'fardan-hikmat' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
