<?php
/**
 * Front Page Template — Fardan Al-Hikmat
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php get_template_part( 'template-parts/home/hero' ); ?>
<?php get_template_part( 'template-parts/home/trust-bar' ); ?>
<?php get_template_part( 'template-parts/home/categories' ); ?>
<?php get_template_part( 'template-parts/home/featured-products' ); ?>
<?php get_template_part( 'template-parts/home/benefits-strip' ); ?>
<?php get_template_part( 'template-parts/home/brand-story' ); ?>
<?php get_template_part( 'template-parts/home/bestsellers' ); ?>
<?php get_template_part( 'template-parts/home/testimonials' ); ?>
<?php get_template_part( 'template-parts/home/newsletter' ); ?>

<?php get_footer(); ?>
