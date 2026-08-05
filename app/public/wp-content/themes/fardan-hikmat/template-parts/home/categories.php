<?php
/**
 * Template Part: Categories Section
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$categories = fardan_get_categories();
?>

<!-- ═══════════════════════════════════════════
     CATEGORIES SECTION
══════════════════════════════════════════════ -->
<section
	id="categories"
	class="categories-section"
	aria-label="<?php esc_attr_e( 'Product Categories', 'fardan-hikmat' ); ?>"
>
	<div class="container">

		<!-- Section Header -->
		<div class="section-header reveal">
			<div class="section-eyebrow">
				<?php esc_html_e( 'Explore by Collection', 'fardan-hikmat' ); ?>
			</div>
			<h2 class="section-title">
				<?php esc_html_e( 'Curated ', 'fardan-hikmat' ); ?>
				<strong><?php esc_html_e( 'Botanical Collections', 'fardan-hikmat' ); ?></strong>
			</h2>
			<p class="section-subtitle">
				<?php esc_html_e( 'From ancient Ayurvedic herbs to modern adaptogenic formulas — discover nature\'s full spectrum of healing.', 'fardan-hikmat' ); ?>
			</p>
		</div>

		<?php if ( ! empty( $categories ) ) : ?>
			<!-- Categories Grid -->
			<div class="categories-grid">
				<?php foreach ( $categories as $index => $cat ) : ?>
					<article
						id="<?php echo esc_attr( $cat['id'] ); ?>"
						class="category-card <?php echo esc_attr( $cat['style'] ); ?> reveal reveal-delay-<?php echo esc_attr( ( $index % 4 ) + 1 ); ?>"
						aria-label="<?php echo esc_attr( $cat['name'] ); ?>"
					>
						<!-- Background Image -->
						<div class="category-card__bg">
							<?php if ( ! empty( $cat['img'] ) ) : ?>
								<img
									src="<?php echo esc_url( $cat['img'] ); ?>"
									alt="<?php echo esc_attr( $cat['name'] ); ?>"
									class="category-card__img"
									loading="lazy"
									width="400"
									height="530"
								>
							<?php endif; ?>
						</div>

						<!-- Gradient Overlay -->
						<div class="category-card__gradient" aria-hidden="true"></div>

						<!-- Content -->
						<div class="category-card__content">
							<div class="category-card__icon" aria-hidden="true">
								<?php echo esc_html( $cat['icon'] ); ?>
							</div>
							<h3 class="category-card__name"><?php echo esc_html( $cat['name'] ); ?></h3>
							<p class="category-card__count"><?php echo esc_html( $cat['count'] ); ?></p>
							<div class="category-card__cta" aria-hidden="true">
								<?php esc_html_e( 'Shop Now', 'fardan-hikmat' ); ?>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
							</div>
						</div>

						<!-- Accessible Link -->
						<a
							href="<?php echo esc_url( isset( $cat['url'] ) && ! is_wp_error( $cat['url'] ) ? $cat['url'] : '#shop' ); ?>"
							class="category-card__link"
							aria-label="<?php echo esc_attr( sprintf( __( 'Browse %s products', 'fardan-hikmat' ), $cat['name'] ) ); ?>"
							style="position:absolute;inset:0;z-index:5;"
						>
							<span class="sr-only"><?php echo esc_html( $cat['name'] ); ?></span>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="reveal" style="text-align:center;padding:var(--space-12);background:#fff;border-radius:var(--radius-xl);border:1px solid var(--aura-border-light);">
				<p style="font-size:var(--text-lg);color:var(--aura-text-muted);">
					<?php esc_html_e( 'No categories found. Create categories from your WordPress Dashboard > Products > Categories.', 'fardan-hikmat' ); ?>
				</p>
			</div>
		<?php endif; ?>

	</div>
</section>
<!-- /CATEGORIES SECTION -->
