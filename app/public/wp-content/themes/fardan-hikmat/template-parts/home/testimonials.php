<?php
/**
 * Template Part: Testimonials Section
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$testimonials = array(
	array(
		'name'     => 'Sarah M.',
		'initial'  => 'S',
		'location' => 'Portland, OR',
		'stars'    => 5,
		'text'     => __( '"I\'ve tried many adaptogens over the years, but the Ashwagandha Tincture from Fardan is on a completely different level. The quality is incredible — I noticed a huge difference in my sleep and stress levels within the first week. This is now a permanent part of my wellness routine."', 'fardan-hikmat' ),
		'product'  => __( 'Ashwagandha Root Tincture', 'fardan-hikmat' ),
		'id'       => 'review-sarah',
	),
	array(
		'name'     => 'Ayaan K.',
		'initial'  => 'A',
		'location' => 'London, UK',
		'stars'    => 5,
		'text'     => __( '"The Golden Turmeric Paste is absolutely divine. I add it to my morning latte every day and the difference in my joint health has been remarkable. The fact that everything is organic and transparently sourced makes it even better."', 'fardan-hikmat' ),
		'product'  => __( 'Golden Turmeric Paste', 'fardan-hikmat' ),
		'id'       => 'review-ayaan',
	),
	array(
		'name'     => 'Priya R.',
		'initial'  => 'P',
		'location' => 'Toronto, CA',
		'stars'    => 5,
		'text'     => __( '"I\'ve been using the Moringa Powder for three months now and the energy boost is real — without any caffeine jitters. The quality is outstanding and the packaging is beautiful and sustainable. Highly recommend to anyone on a plant-based wellness journey!"', 'fardan-hikmat' ),
		'product'  => __( 'Organic Moringa Leaf Powder', 'fardan-hikmat' ),
		'id'       => 'review-priya',
	),
);
?>

<!-- ═══════════════════════════════════════════
     TESTIMONIALS SECTION
══════════════════════════════════════════════ -->
<section
	class="testimonials-section"
	aria-label="<?php esc_attr_e( 'Customer Reviews and Testimonials', 'fardan-hikmat' ); ?>"
>
	<div class="container">

		<div class="section-header reveal">
			<div class="section-eyebrow">
				<?php esc_html_e( 'Real Stories', 'fardan-hikmat' ); ?>
			</div>
			<h2 class="section-title">
				<?php esc_html_e( 'Loved by ', 'fardan-hikmat' ); ?>
				<strong><?php esc_html_e( '50,000+ Wellness Seekers', 'fardan-hikmat' ); ?></strong>
			</h2>
			<p class="section-subtitle">
				<?php esc_html_e( 'Don\'t just take our word for it — here\'s what our community is saying about their transformative experiences.', 'fardan-hikmat' ); ?>
			</p>
		</div>

		<!-- Overall Rating Summary -->
		<div class="reveal" style="text-align:center;margin-bottom:var(--space-12);">
			<div style="display:inline-flex;align-items:center;gap:var(--space-6);background:#fff;border:1px solid var(--aura-border-light);border-radius:var(--radius-2xl);padding:var(--space-6) var(--space-10);box-shadow:var(--shadow-sm);flex-wrap:wrap;justify-content:center;">
				<div style="text-align:center;">
					<div style="font-family:var(--font-heading);font-size:3.5rem;font-weight:700;color:var(--aura-primary);line-height:1;">4.9</div>
					<div style="color:var(--aura-accent-light);font-size:1.2rem;letter-spacing:2px;margin:.25rem 0;" aria-hidden="true">★★★★★</div>
					<div style="font-size:var(--text-xs);color:var(--aura-text-light);"><?php esc_html_e( 'Average Rating', 'fardan-hikmat' ); ?></div>
				</div>
				<div style="width:1px;height:60px;background:var(--aura-border-light);" aria-hidden="true"></div>
				<div style="text-align:center;">
					<div style="font-family:var(--font-heading);font-size:3.5rem;font-weight:700;color:var(--aura-primary);line-height:1;">50K+</div>
					<div style="font-size:var(--text-xs);color:var(--aura-text-light);margin-top:.4rem;"><?php esc_html_e( 'Verified Reviews', 'fardan-hikmat' ); ?></div>
				</div>
				<div style="width:1px;height:60px;background:var(--aura-border-light);" aria-hidden="true"></div>
				<div style="text-align:center;">
					<div style="font-family:var(--font-heading);font-size:3.5rem;font-weight:700;color:var(--aura-primary);line-height:1;">98%</div>
					<div style="font-size:var(--text-xs);color:var(--aura-text-light);margin-top:.4rem;"><?php esc_html_e( 'Would Recommend', 'fardan-hikmat' ); ?></div>
				</div>
			</div>
		</div>

		<!-- Testimonials Grid -->
		<div class="testimonials-grid" role="list">
			<?php foreach ( $testimonials as $index => $review ) : ?>
				<article
					id="<?php echo esc_attr( $review['id'] ); ?>"
					class="testimonial-card reveal reveal-delay-<?php echo esc_attr( $index + 1 ); ?>"
					role="listitem"
					aria-label="<?php echo esc_attr( sprintf( __( 'Review by %s', 'fardan-hikmat' ), $review['name'] ) ); ?>"
				>
					<!-- Stars -->
					<div class="testimonial-card__stars" aria-label="<?php echo esc_attr( $review['stars'] . ' out of 5 stars' ); ?>">
						<?php echo esc_html( str_repeat( '★', $review['stars'] ) ); ?>
					</div>

					<!-- Text -->
					<blockquote>
						<p class="testimonial-card__text"><?php echo esc_html( $review['text'] ); ?></p>
					</blockquote>

					<!-- Product Purchased -->
					<div style="font-size:var(--text-xs);font-weight:600;color:var(--aura-accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:var(--space-4);">
						<?php esc_html_e( 'Verified Purchase:', 'fardan-hikmat' ); ?>
						<?php echo esc_html( $review['product'] ); ?>
					</div>

					<!-- Author -->
					<div class="testimonial-card__author">
						<div class="testimonial-card__avatar" aria-hidden="true">
							<?php echo esc_html( $review['initial'] ); ?>
						</div>
						<div>
							<p class="testimonial-card__name"><?php echo esc_html( $review['name'] ); ?></p>
							<p class="testimonial-card__location"><?php echo esc_html( $review['location'] ); ?></p>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
<!-- /TESTIMONIALS SECTION -->
