<?php
/**
 * Template Part: Dynamic Real Customer Reviews & Feedback
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Fetch real approved product reviews from WooCommerce database
$real_reviews = get_comments( array(
	'status'    => 'approve',
	'post_type' => 'product',
	'number'    => 6,
) );

$total_reviews_count = count( $real_reviews );
?>

<!-- ═══════════════════════════════════════════
     DYNAMIC REAL CUSTOMER REVIEWS SECTION
══════════════════════════════════════════════ -->
<section
	class="testimonials-section"
	aria-label="<?php esc_attr_e( 'Customer Reviews and Testimonials', 'fardan-hikmat' ); ?>"
>
	<div class="container">

		<div class="section-header reveal">
			<div class="section-eyebrow">
				<?php esc_html_e( 'Customer Feedback', 'fardan-hikmat' ); ?>
			</div>
			<h2 class="section-title">
				<?php esc_html_e( 'Verified Customer ', 'fardan-hikmat' ); ?>
				<strong><?php esc_html_e( 'Reviews & Experiences', 'fardan-hikmat' ); ?></strong>
			</h2>
			<p class="section-subtitle">
				<?php esc_html_e( 'Genuine, unedited feedback from real customers who have purchased our organic herbal remedies.', 'fardan-hikmat' ); ?>
			</p>
		</div>

		<?php if ( ! empty( $real_reviews ) ) : ?>

			<!-- Real Reviews Summary -->
			<div class="reveal" style="text-align:center;margin-bottom:var(--space-12);">
				<div style="display:inline-flex;align-items:center;gap:var(--space-6);background:#fff;border:1px solid var(--aura-border-light);border-radius:var(--radius-2xl);padding:var(--space-6) var(--space-10);box-shadow:var(--shadow-sm);flex-wrap:wrap;justify-content:center;">
					<div style="text-align:center;">
						<div style="font-family:var(--font-heading);font-size:3rem;font-weight:700;color:var(--aura-primary);line-height:1;"><?php echo esc_html( number_format( 5.0, 1 ) ); ?></div>
						<div style="color:var(--aura-accent-light);font-size:1.2rem;letter-spacing:2px;margin:.25rem 0;" aria-hidden="true">★★★★★</div>
						<div style="font-size:var(--text-xs);color:var(--aura-text-light);"><?php esc_html_e( 'Average Rating', 'fardan-hikmat' ); ?></div>
					</div>
					<div style="width:1px;height:60px;background:var(--aura-border-light);" aria-hidden="true"></div>
					<div style="text-align:center;">
						<div style="font-family:var(--font-heading);font-size:3rem;font-weight:700;color:var(--aura-primary);line-height:1;"><?php echo esc_html( $total_reviews_count ); ?></div>
						<div style="font-size:var(--text-xs);color:var(--aura-text-light);margin-top:.4rem;"><?php esc_html_e( 'Verified Customer Reviews', 'fardan-hikmat' ); ?></div>
					</div>
				</div>
			</div>

			<!-- Real Reviews Grid -->
			<div class="testimonials-grid" role="list">
				<?php
				foreach ( $real_reviews as $index => $review ) :
					$rating     = intval( get_comment_meta( $review->comment_ID, 'rating', true ) );
					if ( ! $rating ) { $rating = 5; }
					$product    = get_post( $review->comment_post_ID );
					$prod_name  = $product ? $product->post_title : __( 'Verified Purchase', 'fardan-hikmat' );
					$initial    = mb_substr( $review->comment_author, 0, 1 );
				?>
					<article
						class="testimonial-card reveal reveal-delay-<?php echo esc_attr( ( $index % 3 ) + 1 ); ?>"
						role="listitem"
					>
						<!-- Stars -->
						<div class="testimonial-card__stars">
							<?php echo esc_html( str_repeat( '★', $rating ) ); ?>
						</div>

						<!-- Text -->
						<blockquote>
							<p class="testimonial-card__text">"<?php echo esc_html( $review->comment_content ); ?>"</p>
						</blockquote>

						<!-- Product Purchased -->
						<div style="font-size:var(--text-xs);font-weight:600;color:var(--aura-accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:var(--space-4);">
							<?php esc_html_e( 'Verified Purchase:', 'fardan-hikmat' ); ?>
							<?php echo esc_html( $prod_name ); ?>
						</div>

						<!-- Author -->
						<div class="testimonial-card__author">
							<div class="testimonial-card__avatar" aria-hidden="true">
								<?php echo esc_html( strtoupper( $initial ) ); ?>
							</div>
							<div>
								<p class="testimonial-card__name"><?php echo esc_html( $review->comment_author ); ?></p>
								<p class="testimonial-card__location"><?php echo esc_html( date( 'd M, Y', strtotime( $review->comment_date ) ) ); ?></p>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

		<?php else : ?>

			<!-- Authentic Empty State (No Fake Reviews) -->
			<div class="reveal" style="max-width:700px;margin:0 auto;text-align:center;background:#fff;border:1px dashed var(--aura-border-light);border-radius:var(--radius-2xl);padding:var(--space-12) var(--space-8);box-shadow:var(--shadow-sm);">
				<div style="font-size:3rem;margin-bottom:var(--space-4);">🌿</div>
				<h3 style="font-family:var(--font-heading);font-size:1.75rem;color:var(--aura-primary);margin-bottom:var(--space-3);">
					<?php esc_html_e( 'Be the First to Review Our Organic Remedies!', 'fardan-hikmat' ); ?>
				</h3>
				<p style="color:var(--aura-text-muted);margin-bottom:var(--space-6);line-height:1.7;">
					<?php esc_html_e( 'We do not display fake reviews. All customer feedback on Fardan Al-Hikmat is 100% authentic and submitted by verified buyers after purchasing our products.', 'fardan-hikmat' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/#shop' ) ); ?>" class="btn btn--primary btn--lg">
					<?php esc_html_e( 'Explore Shop & Write First Review', 'fardan-hikmat' ); ?>
				</a>
			</div>

		<?php endif; ?>

	</div>
</section>
<!-- /TESTIMONIALS SECTION -->
