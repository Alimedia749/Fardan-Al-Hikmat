<?php
/**
 * Template Part: Featured Products Section
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$products   = fardan_get_products();
$categories = fardan_get_categories();

$tabs = array(
	array( 'slug' => 'all', 'label' => __( 'All Products', 'fardan-hikmat' ) ),
);

foreach ( $categories as $cat ) {
	if ( ! empty( $cat['slug'] ) ) {
		$tabs[] = array(
			'slug'  => $cat['slug'],
			'label' => $cat['name'],
		);
	}
}
?>

<!-- ═══════════════════════════════════════════
     FEATURED PRODUCTS SECTION
══════════════════════════════════════════════ -->
<section
	id="shop"
	class="products-section"
	aria-label="<?php esc_attr_e( 'Featured Products', 'fardan-hikmat' ); ?>"
>
	<div class="container">

		<!-- Section Header -->
		<div class="section-header reveal">
			<div class="section-eyebrow">
				<?php esc_html_e( 'Our Collection', 'fardan-hikmat' ); ?>
			</div>
			<h2 class="section-title">
				<?php esc_html_e( 'Handcrafted ', 'fardan-hikmat' ); ?>
				<strong><?php esc_html_e( 'Herbal Formulas', 'fardan-hikmat' ); ?></strong>
			</h2>
			<p class="section-subtitle">
				<?php esc_html_e( 'Each product is meticulously formulated using the highest-quality organic botanicals, tested for purity and potency.', 'fardan-hikmat' ); ?>
			</p>
		</div>

		<?php if ( ! empty( $products ) ) : ?>

			<?php if ( count( $tabs ) > 1 ) : ?>
				<!-- Tab Filter -->
				<div
					class="products-section__tabs reveal"
					role="tablist"
					aria-label="<?php esc_attr_e( 'Filter products by category', 'fardan-hikmat' ); ?>"
				>
					<?php foreach ( $tabs as $index => $tab ) : ?>
						<button
							class="tab-btn <?php echo 0 === $index ? 'is-active' : ''; ?>"
							type="button"
							data-tab="<?php echo esc_attr( $tab['slug'] ); ?>"
							role="tab"
							aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							id="tab-<?php echo esc_attr( $tab['slug'] ); ?>"
						>
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Products Grid -->
			<div
				class="grid-products"
				role="list"
				aria-label="<?php esc_attr_e( 'Product listings', 'fardan-hikmat' ); ?>"
			>
				<?php
				foreach ( $products as $product ) :
					fardan_render_product_card( $product );
				endforeach;
				?>
			</div>

			<!-- View All CTA -->
			<div class="reveal" style="text-align:center;margin-top:var(--space-12);">
				<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '#shop' ); ?>" class="btn btn-outline btn-lg" id="view-all-products">
					<?php esc_html_e( 'View All Products', 'fardan-hikmat' ); ?>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
				</a>
			</div>

		<?php else : ?>

			<div class="reveal" style="text-align:center;padding:var(--space-16);background:#fff;border-radius:var(--radius-2xl);border:1px solid var(--aura-border-light);max-width:640px;margin-inline:auto;">
				<div style="font-size:3rem;margin-bottom:var(--space-4);" aria-hidden="true">🌱</div>
				<h3 style="font-family:var(--font-heading);font-size:var(--text-3xl);color:var(--aura-primary);margin-bottom:var(--space-3);">
					<?php esc_html_e( 'No Products Found', 'fardan-hikmat' ); ?>
				</h3>
				<p style="font-size:var(--text-base);color:var(--aura-text-muted);margin-bottom:var(--space-6);">
					<?php esc_html_e( 'Demo products have been cleared. You can now add your own real products from WordPress Dashboard > Products > Add New.', 'fardan-hikmat' ); ?>
				</p>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( '+ Add New Product', 'fardan-hikmat' ); ?>
					</a>
				<?php endif; ?>
			</div>

		<?php endif; ?>

	</div>
</section>
<!-- /FEATURED PRODUCTS SECTION -->
