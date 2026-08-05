<?php
/**
 * Template Part: Trust Bar (Marquee)
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$trust_items = array(
	array( 'icon' => '🌿', 'text' => __( '100% USDA Organic Certified', 'fardan-hikmat' ) ),
	array( 'icon' => '✓',  'text' => __( 'Third-Party Lab Tested', 'fardan-hikmat' ) ),
	array( 'icon' => '🚚', 'text' => __( 'Free Shipping Over $50', 'fardan-hikmat' ) ),
	array( 'icon' => '↩',  'text' => __( '30-Day Money-Back Guarantee', 'fardan-hikmat' ) ),
	array( 'icon' => '🌍', 'text' => __( 'Sustainably & Ethically Sourced', 'fardan-hikmat' ) ),
	array( 'icon' => '⚗️', 'text' => __( 'GMP Certified Facility', 'fardan-hikmat' ) ),
	array( 'icon' => '🌱', 'text' => __( 'Non-GMO Verified', 'fardan-hikmat' ) ),
	array( 'icon' => '💚', 'text' => __( 'Vegan & Cruelty-Free', 'fardan-hikmat' ) ),
);
?>

<section id="trust" class="trust-bar" aria-label="<?php esc_attr_e( 'Trust Certifications', 'fardan-hikmat' ); ?>">
	<!-- Duplicated for seamless marquee loop -->
	<div class="trust-bar__track" aria-hidden="true">
		<?php for ( $i = 0; $i < 2; $i++ ) : ?>
			<?php foreach ( $trust_items as $item ) : ?>
				<div class="trust-bar__item">
					<span><?php echo esc_html( $item['icon'] ); ?></span>
					<span><?php echo esc_html( $item['text'] ); ?></span>
					<span style="color:var(--aura-border);margin-left:.5rem;">•</span>
				</div>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</section>
