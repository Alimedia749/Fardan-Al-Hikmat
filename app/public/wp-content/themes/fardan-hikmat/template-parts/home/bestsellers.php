<?php
/**
 * Template Part: Bestsellers Section (Second Product Grid)
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$products = fardan_get_products();
$bestsellers = array_slice( $products, 0, 3 );
?>

<!-- ═══════════════════════════════════════════
     BESTSELLERS SECTION
══════════════════════════════════════════════ -->
<section
	class="products-section products-section--alt"
	aria-label="<?php esc_attr_e( 'Top-Rated Products', 'fardan-hikmat' ); ?>"
>
	<div class="container">

		<?php if ( ! empty( $bestsellers ) ) : ?>
			<div class="section-header reveal">
				<div class="section-eyebrow">
					<?php esc_html_e( 'Community Favourites', 'fardan-hikmat' ); ?>
				</div>
				<h2 class="section-title">
					<?php esc_html_e( 'Top-Rated ', 'fardan-hikmat' ); ?>
					<strong><?php esc_html_e( 'This Season', 'fardan-hikmat' ); ?></strong>
				</h2>
				<p class="section-subtitle">
					<?php esc_html_e( 'Chosen by wellness seekers worldwide — these are the formulas our community keeps coming back to.', 'fardan-hikmat' ); ?>
				</p>
			</div>

			<!-- Bestsellers Grid (3 columns) -->
			<div class="grid-products reveal" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));" role="list">
				<?php
				foreach ( $bestsellers as $product ) :
					fardan_render_product_card( $product );
				endforeach;
				?>
			</div>
		<?php endif; ?>

		<!-- Bottom Banner CTA -->
		<div class="reveal" style="margin-top:var(--space-12);background:linear-gradient(135deg,var(--aura-primary),var(--aura-primary-dark));border-radius:var(--radius-2xl);padding:var(--space-10) var(--space-12);display:flex;align-items:center;justify-content:space-between;gap:var(--space-8);flex-wrap:wrap;overflow:hidden;position:relative;">
			<!-- Background pattern -->
			<div style="position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);background-size:20px 20px;pointer-events:none;" aria-hidden="true"></div>

			<div style="position:relative;z-index:1;">
				<div class="section-eyebrow" style="color:var(--aura-accent-light);justify-content:flex-start;margin-bottom:.75rem;">
					<?php esc_html_e( 'Limited Time Offer', 'fardan-hikmat' ); ?>
				</div>
				<h3 style="font-family:var(--font-heading);font-size:clamp(1.5rem,3vw,2.5rem);font-weight:600;color:#fff;line-height:1.2;margin-bottom:.5rem;">
					<?php esc_html_e( 'New Customer Special:', 'fardan-hikmat' ); ?>
					<span style="color:var(--aura-accent-light);"><?php esc_html_e( '15% Off', 'fardan-hikmat' ); ?></span>
				</h3>
				<p style="color:rgba(255,255,255,.7);font-size:var(--text-base);">
					<?php esc_html_e( 'Use code WELCOME15 at checkout. Valid on your first order over $30.', 'fardan-hikmat' ); ?>
				</p>
			</div>

			<a href="#shop" class="btn btn-accent btn-lg" id="promo-banner-cta" style="position:relative;z-index:1;flex-shrink:0;">
				<?php esc_html_e( 'Claim Your Discount', 'fardan-hikmat' ); ?>
			</a>
		</div>

	</div>
</section>
<!-- /BESTSELLERS SECTION -->
