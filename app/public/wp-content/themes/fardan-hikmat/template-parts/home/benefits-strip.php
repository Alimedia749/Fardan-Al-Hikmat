<?php
/**
 * Template Part: Benefits Strip
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$benefits = array(
	array(
		'icon'  => '🌿',
		'title' => __( '100% Organic', 'fardan-hikmat' ),
		'desc'  => __( 'All herbs are USDA certified organic, wildcrafted, and free from synthetic pesticides, herbicides, or GMOs.', 'fardan-hikmat' ),
		'id'    => 'benefit-organic',
	),
	array(
		'icon'  => '⚗️',
		'title' => __( 'Lab Verified Purity', 'fardan-hikmat' ),
		'desc'  => __( 'Every batch is independently tested for potency, heavy metals, microbial contaminants, and purity standards.', 'fardan-hikmat' ),
		'id'    => 'benefit-purity',
	),
	array(
		'icon'  => '🌍',
		'title' => __( 'Ethically Sourced', 'fardan-hikmat' ),
		'desc'  => __( 'We partner directly with small farms worldwide, ensuring fair wages, sustainable practices, and biodiversity preservation.', 'fardan-hikmat' ),
		'id'    => 'benefit-sourced',
	),
	array(
		'icon'  => '💚',
		'title' => __( 'Transparent Formulas', 'fardan-hikmat' ),
		'desc'  => __( 'No proprietary blends. Every ingredient, dose, and source is clearly listed — because you deserve full transparency.', 'fardan-hikmat' ),
		'id'    => 'benefit-transparent',
	),
);
?>

<!-- ═══════════════════════════════════════════
     BENEFITS STRIP
══════════════════════════════════════════════ -->
<section
	class="benefits-strip"
	aria-label="<?php esc_attr_e( 'Why Choose Fardan Al-Hikmat', 'fardan-hikmat' ); ?>"
>
	<div class="container">

		<div class="section-header reveal" style="margin-bottom:var(--space-12);">
			<div class="section-eyebrow" style="color:rgba(196,154,26,.9);">
				<?php esc_html_e( 'The Fardan Difference', 'fardan-hikmat' ); ?>
			</div>
			<h2 class="section-title" style="color:#fff;">
				<?php esc_html_e( 'Why Thousands Trust ', 'fardan-hikmat' ); ?>
				<strong style="color:var(--aura-accent-light);"><?php esc_html_e( 'Our Botanicals', 'fardan-hikmat' ); ?></strong>
			</h2>
		</div>

		<div class="benefits-grid">
			<?php foreach ( $benefits as $index => $benefit ) : ?>
				<div
					id="<?php echo esc_attr( $benefit['id'] ); ?>"
					class="benefit-item reveal reveal-delay-<?php echo esc_attr( $index + 1 ); ?>"
				>
					<div class="benefit-item__icon" aria-hidden="true">
						<?php echo esc_html( $benefit['icon'] ); ?>
					</div>
					<h3 class="benefit-item__title"><?php echo esc_html( $benefit['title'] ); ?></h3>
					<p class="benefit-item__desc"><?php echo esc_html( $benefit['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
<!-- /BENEFITS STRIP -->
