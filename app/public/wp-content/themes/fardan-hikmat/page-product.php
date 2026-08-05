<?php
/**
 * Product Detail Page (PDP) Template
 * Template Name: Product Detail Page
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Related products & main product
$all_products = fardan_get_products();

if ( ! empty( $all_products ) ) {
	$first_prod = $all_products[0];
	$product    = array(
		'title'        => $first_prod['title'],
		'subtitle'     => $first_prod['category'],
		'category'     => $first_prod['category'],
		'price'        => $first_prod['price'] ? $first_prod['price'] : '$0.00',
		'price_old'    => $first_prod['price_old'],
		'discount'     => '',
		'rating'       => $first_prod['rating'] ? $first_prod['rating'] : 5.0,
		'rating_count' => $first_prod['rating_count'],
		'short_desc'   => $first_prod['description'],
		'sku'          => 'SKU-' . $first_prod['id'],
		'image_main'   => $first_prod['image'],
		'images'       => array_filter( array( $first_prod['image'] ) ),
		'is_bestseller'=> $first_prod['is_bestseller'],
		'is_organic'   => true,
		'stock'        => __( 'In Stock', 'fardan-hikmat' ),
	);
	$related = array_slice( $all_products, 1, 3 );
} else {
	$product = null;
	$related = array();
}

if ( $product ) :
?>

<!-- ─── Breadcrumb ─── -->
<div class="pdp-breadcrumb">
	<div class="container">
		<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'fardan-hikmat' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="breadcrumb__item"><?php esc_html_e( 'Home', 'fardan-hikmat' ); ?></a>
			<span class="breadcrumb__separator" aria-hidden="true">›</span>
			<a href="#" class="breadcrumb__item"><?php esc_html_e( 'Shop', 'fardan-hikmat' ); ?></a>
			<span class="breadcrumb__separator" aria-hidden="true">›</span>
			<a href="#" class="breadcrumb__item"><?php echo esc_html( $product['category'] ); ?></a>
			<span class="breadcrumb__separator" aria-hidden="true">›</span>
			<span class="breadcrumb__current" aria-current="page"><?php echo esc_html( $product['title'] ); ?></span>
		</nav>
	</div>
</div>

<!-- ─── PDP Main Layout ─── -->
<article class="pdp-main" aria-label="<?php echo esc_attr( $product['title'] ); ?>">
	<div class="container">
		<div class="pdp-grid">

			<!-- ══════════════════════════════
			     LEFT: IMAGE GALLERY
			═══════════════════════════════════ -->
			<div class="pdp-gallery">

				<!-- Main Image -->
				<div class="pdp-gallery__main" id="pdp-main-image" aria-label="<?php esc_attr_e( 'Main product image', 'fardan-hikmat' ); ?>">
					<img
						src="<?php echo esc_url( $product['image_main'] ); ?>"
						alt="<?php echo esc_attr( $product['title'] . ' — main image' ); ?>"
						id="pdp-main-img-el"
						loading="eager"
						fetchpriority="high"
						width="600"
						height="600"
					>

					<!-- Gallery Badges -->
					<div class="pdp-gallery__badges">
						<?php if ( $product['is_bestseller'] ) : ?>
							<span class="badge badge-accent"><?php esc_html_e( 'Bestseller', 'fardan-hikmat' ); ?></span>
						<?php endif; ?>
						<?php if ( $product['is_organic'] ) : ?>
							<span class="badge badge-leaf"><?php esc_html_e( '🌿 Organic', 'fardan-hikmat' ); ?></span>
						<?php endif; ?>
					</div>

					<!-- Gallery Nav Arrows -->
					<button class="pdp-gallery__nav pdp-gallery__nav--prev" type="button" id="pdp-prev" aria-label="<?php esc_attr_e( 'Previous image', 'fardan-hikmat' ); ?>">‹</button>
					<button class="pdp-gallery__nav pdp-gallery__nav--next" type="button" id="pdp-next" aria-label="<?php esc_attr_e( 'Next image', 'fardan-hikmat' ); ?>">›</button>
				</div>

				<!-- Thumbnail Strip -->
				<div class="pdp-gallery__thumbnails" role="list" aria-label="<?php esc_attr_e( 'Product image thumbnails', 'fardan-hikmat' ); ?>">
					<?php foreach ( $product['images'] as $index => $img_url ) : ?>
						<button
							class="pdp-gallery__thumb <?php echo 0 === $index ? 'is-active' : ''; ?>"
							type="button"
							data-img="<?php echo esc_url( $img_url ); ?>"
							role="listitem"
							aria-label="<?php echo esc_attr( sprintf( __( 'View image %d', 'fardan-hikmat' ), $index + 1 ) ); ?>"
							aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						>
							<img
								src="<?php echo esc_url( $img_url ); ?>"
								alt="<?php echo esc_attr( $product['title'] . ' — view ' . ( $index + 1 ) ); ?>"
								loading="lazy"
								width="88"
								height="88"
							>
						</button>
					<?php endforeach; ?>
				</div>

				<!-- Trust Badges Strip -->
				<div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:var(--space-4);" aria-label="<?php esc_attr_e( 'Product certifications', 'fardan-hikmat' ); ?>">
					<?php
					$certs = array(
						'🌿 USDA Organic',
						'✓ Non-GMO',
						'⚗️ GMP Certified',
						'🌱 Vegan',
						'🔬 Lab Tested',
					);
					foreach ( $certs as $cert ) :
					?>
						<span style="font-size:.6875rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:.3rem .75rem;background:var(--aura-surface);border:1px solid var(--aura-border);border-radius:var(--radius-full);color:var(--aura-text-muted);">
							<?php echo esc_html( $cert ); ?>
						</span>
					<?php endforeach; ?>
				</div>

			</div>

			<!-- ══════════════════════════════
			     RIGHT: PRODUCT INFORMATION
			═══════════════════════════════════ -->
			<div class="pdp-info">

				<!-- Category -->
				<div class="pdp-info__category">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="var(--aura-accent)" aria-hidden="true"><circle cx="12" cy="12" r="10"/></svg>
					<?php echo esc_html( $product['category'] ); ?>
				</div>

				<!-- Title & Subtitle -->
				<h1 class="pdp-info__title"><?php echo esc_html( $product['title'] ); ?></h1>
				<p class="pdp-info__subtitle"><?php echo esc_html( $product['subtitle'] ); ?></p>

				<!-- Rating Row -->
				<div class="pdp-rating-row">
					<div class="pdp-rating-stars" aria-label="<?php echo esc_attr( $product['rating'] . ' out of 5 stars' ); ?>">
						<?php
						$full = (int) floor( $product['rating'] );
						echo esc_html( str_repeat( '★', $full ) );
						if ( $product['rating'] > $full ) {
							echo '½';
						}
						?>
					</div>
					<span class="pdp-rating-score"><?php echo esc_html( $product['rating'] ); ?></span>
					<span class="pdp-rating-count">
						<a href="#pdp-reviews" aria-label="<?php echo esc_attr( sprintf( __( '%d customer reviews', 'fardan-hikmat' ), $product['rating_count'] ) ); ?>">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %d: number of reviews */
									_n( '%d Review', '%d Reviews', $product['rating_count'], 'fardan-hikmat' ),
									$product['rating_count']
								)
							);
							?>
						</a>
					</span>
					<span class="badge badge-leaf" style="font-size:.6rem;">✓ <?php esc_html_e( 'Verified Reviews', 'fardan-hikmat' ); ?></span>
				</div>

				<!-- Price -->
				<div class="pdp-price">
					<div class="pdp-price__row">
						<span class="pdp-price__current"><?php echo esc_html( $product['price'] ); ?></span>
						<?php if ( $product['price_old'] ) : ?>
							<span class="pdp-price__old"><?php echo esc_html( $product['price_old'] ); ?></span>
							<span class="pdp-price__save">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: discount percentage */
										__( 'Save %s', 'fardan-hikmat' ),
										$product['discount']
									)
								);
								?>
							</span>
						<?php endif; ?>
					</div>
					<span class="pdp-price__tax"><?php esc_html_e( 'Inclusive of all taxes. Free shipping on orders over $50.', 'fardan-hikmat' ); ?></span>
				</div>

				<!-- Short Description -->
				<p class="pdp-short-desc"><?php echo esc_html( $product['short_desc'] ); ?></p>

				<!-- Size/Weight Variant Selector -->
				<div class="pdp-variant">
					<div class="pdp-variant__label">
						<?php esc_html_e( 'Select Weight / Size', 'fardan-hikmat' ); ?>
						<span class="pdp-variant__selected"></span>
					</div>
					<div class="pdp-variant__options" role="group" aria-label="<?php esc_attr_e( 'Select weight', 'fardan-hikmat' ); ?>">
						<?php
						$variations = isset( $first_prod['variations'] ) ? $first_prod['variations'] : array(
							array( 'size' => '50g',   'price' => 'Rs. 499',   'price_old' => 'Rs. 650',   'discount' => '23%' ),
							array( 'size' => '100g',  'price' => 'Rs. 899',   'price_old' => 'Rs. 1,150', 'discount' => '22%' ),
							array( 'size' => '250g',  'price' => 'Rs. 1,950', 'price_old' => 'Rs. 2,400', 'discount' => '19%' ),
							array( 'size' => '500g',  'price' => 'Rs. 3,600', 'price_old' => 'Rs. 4,200', 'discount' => '14%' ),
							array( 'size' => '1000g', 'price' => 'Rs. 6,500', 'price_old' => 'Rs. 7,800', 'discount' => '17%' ),
						);
						foreach ( $variations as $idx => $v ) :
						?>
							<button
								class="variant-card <?php echo 0 === $idx ? 'is-selected' : ''; ?>"
								type="button"
								data-size="<?php echo esc_attr( $v['size'] ); ?>"
								data-price="<?php echo esc_attr( $v['price'] ); ?>"
								data-price-old="<?php echo esc_attr( isset( $v['price_old'] ) ? $v['price_old'] : '' ); ?>"
							>
								<span class="variant-card__weight"><?php echo esc_html( $v['size'] ); ?></span>
								<div class="variant-card__pricing">
									<span class="variant-card__price"><?php echo esc_html( $v['price'] ); ?></span>
									<?php if ( ! empty( $v['price_old'] ) ) : ?>
										<span class="variant-card__old"><?php echo esc_html( $v['price_old'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $v['discount'] ) ) : ?>
										<span class="variant-card__badge">-<?php echo esc_html( $v['discount'] ); ?></span>
									<?php endif; ?>
								</div>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Stock Status -->
				<p style="font-size:var(--text-sm);color:var(--aura-success);font-weight:600;margin-bottom:var(--space-5);">
					✓ <?php echo esc_html( $product['stock'] ); ?>
				</p>

				<!-- Add to Cart -->
				<div class="pdp-add-to-cart">
					<div class="pdp-qty-row">
						<span class="pdp-qty-label"><?php esc_html_e( 'Quantity', 'fardan-hikmat' ); ?></span>
						<div class="qty-selector" role="group" aria-label="<?php esc_attr_e( 'Quantity selector', 'fardan-hikmat' ); ?>">
							<button class="qty-btn" type="button" data-action="minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'fardan-hikmat' ); ?>">−</button>
							<input
								class="qty-input"
								type="number"
								value="1"
								min="1"
								max="99"
								id="pdp-qty-input"
								aria-label="<?php esc_attr_e( 'Product quantity', 'fardan-hikmat' ); ?>"
							>
							<button class="qty-btn" type="button" data-action="plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'fardan-hikmat' ); ?>">+</button>
						</div>
					</div>

					<div class="pdp-cart-buttons">
						<button id="pdp-add-to-cart" class="btn btn-primary btn-lg" type="button">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
							<?php esc_html_e( 'Add to Cart', 'fardan-hikmat' ); ?>
						</button>
						<a href="#" id="pdp-buy-now" class="btn btn-outline btn-lg">
							<?php esc_html_e( 'Buy Now', 'fardan-hikmat' ); ?>
						</a>
					</div>

					<!-- Extras -->
					<div class="pdp-extras">
						<button type="button" class="pdp-extra-item" id="pdp-wishlist" aria-label="<?php esc_attr_e( 'Add to wishlist', 'fardan-hikmat' ); ?>">
							<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
							<?php esc_html_e( 'Wishlist', 'fardan-hikmat' ); ?>
						</button>
						<button type="button" class="pdp-extra-item" id="pdp-compare" aria-label="<?php esc_attr_e( 'Compare product', 'fardan-hikmat' ); ?>">
							<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
							<?php esc_html_e( 'Compare', 'fardan-hikmat' ); ?>
						</button>
						<button type="button" class="pdp-extra-item" id="pdp-share" aria-label="<?php esc_attr_e( 'Share this product', 'fardan-hikmat' ); ?>">
							<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
							<?php esc_html_e( 'Share', 'fardan-hikmat' ); ?>
						</button>
					</div>
				</div>

				<!-- Delivery Info Strip -->
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);margin-top:var(--space-6);padding-top:var(--space-6);border-top:1px solid var(--aura-border-light);">
					<?php
					$perks = array(
						array( '🚚', __( 'Free Shipping', 'fardan-hikmat' ), __( 'On orders over $50', 'fardan-hikmat' ) ),
						array( '↩', __( '30-Day Returns', 'fardan-hikmat' ), __( 'No questions asked', 'fardan-hikmat' ) ),
						array( '🔒', __( 'Secure Checkout', 'fardan-hikmat' ), __( '256-bit SSL encrypted', 'fardan-hikmat' ) ),
						array( '⚗️', __( 'Lab Certified', 'fardan-hikmat' ), __( 'Third-party tested', 'fardan-hikmat' ) ),
					);
					foreach ( $perks as $perk ) :
					?>
						<div style="display:flex;align-items:center;gap:.75rem;">
							<span style="font-size:1.25rem;flex-shrink:0;" aria-hidden="true"><?php echo esc_html( $perk[0] ); ?></span>
							<div>
								<p style="font-size:.8125rem;font-weight:700;color:var(--aura-text);"><?php echo esc_html( $perk[1] ); ?></p>
								<p style="font-size:.75rem;color:var(--aura-text-muted);"><?php echo esc_html( $perk[2] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

			</div>

		</div><!-- /.pdp-grid -->

		<!-- ══════════════════════════════════════
		     ACCORDION DETAILS SECTION
		═══════════════════════════════════════════ -->
		<div class="pdp-accordion-section" style="margin-top:var(--space-16);" data-accordion-group>

			<!-- Benefits -->
			<div class="accordion-item">
				<button
					class="accordion-trigger"
					type="button"
					aria-expanded="true"
					aria-controls="accordion-benefits"
					id="accordion-benefits-btn"
				>
					<?php esc_html_e( 'Key Health Benefits', 'fardan-hikmat' ); ?>
					<span class="accordion-icon" aria-hidden="true">+</span>
				</button>
				<div class="accordion-content is-open" id="accordion-benefits" role="region" aria-labelledby="accordion-benefits-btn">
					<div class="accordion-body">
						<div class="pdp-benefits-list" role="list">
							<?php
							$benefits = array(
								__( 'Clinically shown to reduce cortisol levels by up to 28%, significantly lowering perceived stress and anxiety.', 'fardan-hikmat' ),
								__( 'Enhances sleep quality — reduces time to fall asleep and improves overall sleep efficiency in clinical trials.', 'fardan-hikmat' ),
								__( 'Supports sustained energy and physical endurance without stimulants or caffeine dependency.', 'fardan-hikmat' ),
								__( 'Neuroprotective properties that support cognitive function, memory, and mental clarity.', 'fardan-hikmat' ),
								__( 'Balances thyroid hormone levels and supports healthy adrenal function.', 'fardan-hikmat' ),
								__( 'Anti-inflammatory and immunomodulatory — helps calibrate the immune response.', 'fardan-hikmat' ),
							);
							foreach ( $benefits as $benefit ) :
							?>
								<div class="pdp-benefit" role="listitem">
									<span class="pdp-benefit__icon" aria-hidden="true">✓</span>
									<span><?php echo esc_html( $benefit ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Ingredients -->
			<div class="accordion-item">
				<button
					class="accordion-trigger"
					type="button"
					aria-expanded="false"
					aria-controls="accordion-ingredients"
					id="accordion-ingredients-btn"
				>
					<?php esc_html_e( 'Ingredients & Sourcing', 'fardan-hikmat' ); ?>
					<span class="accordion-icon" aria-hidden="true">+</span>
				</button>
				<div class="accordion-content" id="accordion-ingredients" role="region" aria-labelledby="accordion-ingredients-btn">
					<div class="accordion-body">
						<p style="margin-bottom:var(--space-5);font-size:var(--text-base);color:var(--aura-text-muted);">
							<?php esc_html_e( 'We use only certified organic Ashwagandha roots sourced directly from small family farms in Madhya Pradesh, India — where the soil, climate, and traditional cultivation methods produce the highest withanolide concentrations.', 'fardan-hikmat' ); ?>
						</p>
						<div class="pdp-ingredients-grid">
							<?php
							$ingredients = array(
								array( '🌿', 'Organic Ashwagandha Root (Withania somnifera)', '500mg/ml equivalent' ),
								array( '💧', 'Certified Organic Cane Alcohol (30%)', 'Extraction medium' ),
								array( '💦', 'Distilled Purified Water', 'Diluent' ),
								array( '⚫', 'Black Pepper Extract (Bioperine® 5%)', 'Bioavailability enhancer' ),
							);
							foreach ( $ingredients as $ing ) :
							?>
								<div class="pdp-ingredient">
									<span class="pdp-ingredient__emoji" aria-hidden="true"><?php echo esc_html( $ing[0] ); ?></span>
									<div>
										<strong style="font-size:.8125rem;display:block;"><?php echo esc_html( $ing[1] ); ?></strong>
										<span style="font-size:.75rem;color:var(--aura-text-muted);"><?php echo esc_html( $ing[2] ); ?></span>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<div style="margin-top:var(--space-5);padding:var(--space-4);background:var(--aura-surface);border-radius:var(--radius-md);">
							<p style="font-size:var(--text-sm);color:var(--aura-text-muted);margin:0;">
								<strong style="color:var(--aura-primary);"><?php esc_html_e( 'SKU:', 'fardan-hikmat' ); ?></strong>
								<?php echo esc_html( $product['sku'] ); ?>
								&nbsp;&nbsp;|&nbsp;&nbsp;
								<strong style="color:var(--aura-primary);"><?php esc_html_e( 'Origin:', 'fardan-hikmat' ); ?></strong>
								<?php esc_html_e( 'Madhya Pradesh, India', 'fardan-hikmat' ); ?>
								&nbsp;&nbsp;|&nbsp;&nbsp;
								<strong style="color:var(--aura-primary);"><?php esc_html_e( 'Extraction Ratio:', 'fardan-hikmat' ); ?></strong>
								<?php esc_html_e( '1:2 (Cold Process)', 'fardan-hikmat' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Usage Instructions -->
			<div class="accordion-item">
				<button
					class="accordion-trigger"
					type="button"
					aria-expanded="false"
					aria-controls="accordion-usage"
					id="accordion-usage-btn"
				>
					<?php esc_html_e( 'How to Use', 'fardan-hikmat' ); ?>
					<span class="accordion-icon" aria-hidden="true">+</span>
				</button>
				<div class="accordion-content" id="accordion-usage" role="region" aria-labelledby="accordion-usage-btn">
					<div class="accordion-body">
						<div class="pdp-usage-steps">
							<?php
							$steps = array(
								array(
									'title' => __( 'Measure Your Dose', 'fardan-hikmat' ),
									'desc'  => __( 'Fill the dropper to the 1ml mark (approximately 30 drops) for a standard serving. Beginners may start with 0.5ml and gradually increase.', 'fardan-hikmat' ),
								),
								array(
									'title' => __( 'Add to Your Beverage', 'fardan-hikmat' ),
									'desc'  => __( 'Mix into warm water, herbal tea, golden milk, smoothies, or juice. The slightly bitter taste pairs beautifully with warm oat milk and honey.', 'fardan-hikmat' ),
								),
								array(
									'title' => __( 'Timing for Best Results', 'fardan-hikmat' ),
									'desc'  => __( 'Take once or twice daily. For stress management: morning with breakfast. For sleep support: 30 minutes before bedtime.', 'fardan-hikmat' ),
								),
								array(
									'title' => __( 'Consistency is Key', 'fardan-hikmat' ),
									'desc'  => __( 'Adaptogens work best with consistent daily use over 4–8 weeks. Most users notice meaningful improvements within 2–3 weeks.', 'fardan-hikmat' ),
								),
							);
							foreach ( $steps as $index => $step ) :
							?>
								<div class="pdp-usage-step">
									<div class="pdp-usage-step__num" aria-hidden="true"><?php echo esc_html( $index + 1 ); ?></div>
									<div class="pdp-usage-step__text">
										<strong style="display:block;margin-bottom:.25rem;color:var(--aura-text);"><?php echo esc_html( $step['title'] ); ?></strong>
										<?php echo esc_html( $step['desc'] ); ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<div style="margin-top:var(--space-5);padding:var(--space-4);background:#FFF8E1;border:1px solid #FFD54F;border-radius:var(--radius-md);">
							<p style="font-size:var(--text-sm);color:#E65100;margin:0;">
								⚠️ <strong><?php esc_html_e( 'Caution:', 'fardan-hikmat' ); ?></strong>
								<?php esc_html_e( 'Consult your healthcare provider before use if pregnant, nursing, or taking medications. Not intended to diagnose, treat, cure, or prevent any disease.', 'fardan-hikmat' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Reviews -->
			<div class="accordion-item" id="pdp-reviews">
				<button
					class="accordion-trigger"
					type="button"
					aria-expanded="false"
					aria-controls="accordion-reviews"
					id="accordion-reviews-btn"
				>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: review count */
							__( 'Customer Reviews (%d)', 'fardan-hikmat' ),
							$product['rating_count']
						)
					);
					?>
					<span class="accordion-icon" aria-hidden="true">+</span>
				</button>
				<div class="accordion-content" id="accordion-reviews" role="region" aria-labelledby="accordion-reviews-btn">
					<div class="accordion-body">

						<!-- Rating Summary -->
						<div class="pdp-reviews-summary">
							<div class="pdp-reviews-score">
								<div class="pdp-reviews-score__number"><?php echo esc_html( $product['rating'] ); ?></div>
								<div class="pdp-reviews-score__stars" aria-hidden="true">★★★★★</div>
								<div class="pdp-reviews-score__total">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d: review count */
											__( 'Based on %d reviews', 'fardan-hikmat' ),
											$product['rating_count']
										)
									);
									?>
								</div>
							</div>

							<div class="pdp-reviews-bars" aria-label="<?php esc_attr_e( 'Rating distribution', 'fardan-hikmat' ); ?>">
								<?php
								$bar_data = array(
									array( 5, 82 ),
									array( 4, 12 ),
									array( 3, 4  ),
									array( 2, 1  ),
									array( 1, 1  ),
								);
								foreach ( $bar_data as $bar ) :
								?>
									<div class="pdp-review-bar">
										<span class="pdp-review-bar__label" aria-label="<?php echo esc_attr( $bar[0] . ' stars' ); ?>"><?php echo esc_html( $bar[0] ); ?>★</span>
										<div class="pdp-review-bar__track">
											<div class="pdp-review-bar__fill" style="width:<?php echo esc_attr( $bar[1] ); ?>%;" aria-hidden="true"></div>
										</div>
										<span class="pdp-review-bar__count"><?php echo esc_html( $bar[1] . '%' ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<!-- Individual Reviews -->
						<?php
						$reviews = array(
							array(
								'name'    => 'Sarah M.',
								'initial' => 'S',
								'date'    => 'July 28, 2026',
								'stars'   => 5,
								'title'   => __( 'Life-changing adaptogen!', 'fardan-hikmat' ),
								'text'    => __( 'I\'ve been taking this tincture for 8 weeks now and the difference in my stress levels and sleep quality is remarkable. I fall asleep faster, wake up refreshed, and feel much calmer during the day. The quality is exceptional.', 'fardan-hikmat' ),
							),
							array(
								'name'    => 'James L.',
								'initial' => 'J',
								'date'    => 'July 15, 2026',
								'stars'   => 5,
								'title'   => __( 'Premium quality, noticeable results', 'fardan-hikmat' ),
								'text'    => __( 'After trying several other brands, this one stands out for its potency and taste. I mix it in my evening chamomile tea and sleep like a baby. The ingredients list is transparent and the sourcing story is genuinely impressive.', 'fardan-hikmat' ),
							),
							array(
								'name'    => 'Amina K.',
								'initial' => 'A',
								'date'    => 'June 30, 2026',
								'stars'   => 5,
								'title'   => __( 'Finally found what I was looking for', 'fardan-hikmat' ),
								'text'    => __( 'As someone who\'s tried many adaptogens, this ashwagandha tincture is genuinely the best I\'ve used. My cortisol-related hair loss has reduced significantly, my energy is more stable, and I feel centered throughout the day.', 'fardan-hikmat' ),
							),
						);

						foreach ( $reviews as $review ) :
						?>
							<article class="pdp-review-item">
								<div class="pdp-review-header">
									<div class="pdp-reviewer">
										<div class="pdp-reviewer__avatar" aria-hidden="true"><?php echo esc_html( $review['initial'] ); ?></div>
										<div>
											<p class="pdp-reviewer__name"><?php echo esc_html( $review['name'] ); ?></p>
											<p class="pdp-reviewer__date"><?php echo esc_html( $review['date'] ); ?></p>
										</div>
									</div>
									<div class="pdp-review-stars" aria-label="<?php echo esc_attr( $review['stars'] . ' stars' ); ?>">
										<?php echo esc_html( str_repeat( '★', $review['stars'] ) ); ?>
									</div>
								</div>
								<h4 class="pdp-review-title"><?php echo esc_html( $review['title'] ); ?></h4>
								<p class="pdp-review-text"><?php echo esc_html( $review['text'] ); ?></p>
								<p class="pdp-review-verified">
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
									<?php esc_html_e( 'Verified Purchase', 'fardan-hikmat' ); ?>
								</p>
							</article>
						<?php endforeach; ?>

					</div>
				</div>
			</div>

		</div><!-- /.pdp-accordion-section -->

	</div><!-- /.container -->
</article>

<!-- ─── Related Products ─── -->
<section class="related-products" aria-label="<?php esc_attr_e( 'Related Products', 'fardan-hikmat' ); ?>">
	<div class="container">

		<div class="section-header reveal">
			<div class="section-eyebrow"><?php esc_html_e( 'Complete Your Ritual', 'fardan-hikmat' ); ?></div>
			<h2 class="section-title">
				<?php esc_html_e( 'You May ', 'fardan-hikmat' ); ?>
				<strong><?php esc_html_e( 'Also Love', 'fardan-hikmat' ); ?></strong>
			</h2>
		</div>

		<div class="grid-products reveal" style="grid-template-columns: repeat(3, 1fr);" role="list">
			<?php
			foreach ( $related as $rel_product ) :
				fardan_render_product_card( $rel_product );
			endforeach;
			?>
		</div>

	</div>
</section>
<?php else : ?>
	<div class="container" style="padding-top: calc(var(--navbar-height) + 4rem); padding-bottom: 6rem; text-align: center;">
		<div style="font-size:3.5rem;margin-bottom:1rem;" aria-hidden="true">🌱</div>
		<h2 class="section-title"><?php esc_html_e( 'No Products Found', 'fardan-hikmat' ); ?></h2>
		<p class="section-subtitle"><?php esc_html_e( 'Please add products from your WordPress Dashboard > Products > Add New.', 'fardan-hikmat' ); ?></p>
	</div>
<?php endif; ?>

<?php get_footer(); ?>
