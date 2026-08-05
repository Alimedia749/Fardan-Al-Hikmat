<?php
/**
 * Template Part: Brand Story Section
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$assets  = get_template_directory_uri() . '/assets/images/';
$img_1   = $assets . 'product-ashwagandha.jpg';
$img_2   = $assets . 'product-turmeric.jpg';

$features = array(
	array( 'icon' => '🌱', 'title' => __( 'Seed to Shelf', 'fardan-hikmat' ), 'desc' => __( 'Full supply chain transparency from farm to your doorstep.', 'fardan-hikmat' ) ),
	array( 'icon' => '🔬', 'title' => __( 'Science-Backed', 'fardan-hikmat' ), 'desc' => __( 'Every formula is grounded in peer-reviewed research and traditional wisdom.', 'fardan-hikmat' ) ),
	array( 'icon' => '🌍', 'title' => __( 'Planet-Positive', 'fardan-hikmat' ), 'desc' => __( '1% of every sale goes to global reforestation and regenerative farming.', 'fardan-hikmat' ) ),
	array( 'icon' => '💛', 'title' => __( 'Community First', 'fardan-hikmat' ), 'desc' => __( 'We support small farmers, women cooperatives, and indigenous communities.', 'fardan-hikmat' ) ),
);
?>

<!-- ═══════════════════════════════════════════
     BRAND STORY SECTION
══════════════════════════════════════════════ -->
<section
	id="about"
	class="brand-story"
	aria-label="<?php esc_attr_e( 'Our Brand Story', 'fardan-hikmat' ); ?>"
>
	<div class="container">
		<div class="brand-story__inner">

			<!-- Visual Side -->
			<div class="brand-story__visual reveal-left">
				<!-- Year Badge -->
				<div class="brand-story__badge">
					<div class="brand-story__badge-number">15</div>
					<div class="brand-story__badge-text"><?php esc_html_e( 'Years\nof Craft', 'fardan-hikmat' ); ?></div>
				</div>

				<img
					src="<?php echo esc_url( $img_1 ); ?>"
					alt="<?php esc_attr_e( 'Our organic herb garden and production facility', 'fardan-hikmat' ); ?>"
					class="brand-story__image-main"
					loading="lazy"
					width="500"
					height="625"
				>

				<img
					src="<?php echo esc_url( $img_2 ); ?>"
					alt="<?php esc_attr_e( 'Premium herbal ingredients close-up', 'fardan-hikmat' ); ?>"
					class="brand-story__image-accent"
					loading="lazy"
					width="225"
					height="225"
				>
			</div>

			<!-- Content Side -->
			<div class="brand-story__content reveal-right">
				<div class="section-eyebrow" style="justify-content:flex-start;margin-bottom:var(--space-4);">
					<?php esc_html_e( 'Our Philosophy', 'fardan-hikmat' ); ?>
				</div>

				<h2 class="brand-story__title">
					<?php esc_html_e( 'Rooted in ', 'fardan-hikmat' ); ?>
					<strong><?php esc_html_e( 'Ancient Wisdom,', 'fardan-hikmat' ); ?></strong>
					<?php esc_html_e( ' Perfected by Science', 'fardan-hikmat' ); ?>
				</h2>

				<p class="brand-story__text">
					<?php esc_html_e( 'Fardan Al-Hikmat was born from a deep respect for traditional healing systems — Ayurveda, Traditional Chinese Medicine, and Unani — combined with an unwavering commitment to modern scientific rigor.', 'fardan-hikmat' ); ?>
				</p>

				<p class="brand-story__text">
					<?php esc_html_e( 'For over 15 years, we have sourced the world\'s finest botanicals directly from small, organic farms across India, Sri Lanka, Morocco, and Peru — building relationships built on trust, fairness, and shared values.', 'fardan-hikmat' ); ?>
				</p>

				<!-- Feature Grid -->
				<div class="brand-story__features">
					<?php foreach ( $features as $feature ) : ?>
						<div class="brand-story__feature">
							<div class="brand-story__feature-icon" aria-hidden="true"><?php echo esc_html( $feature['icon'] ); ?></div>
							<div>
								<p class="brand-story__feature-title"><?php echo esc_html( $feature['title'] ); ?></p>
								<p class="brand-story__feature-desc"><?php echo esc_html( $feature['desc'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div style="display:flex;gap:var(--space-4);flex-wrap:wrap;">
					<a href="#" class="btn btn-primary btn-lg" id="about-cta">
						<?php esc_html_e( 'Read Our Full Story', 'fardan-hikmat' ); ?>
					</a>
					<a href="#" class="btn btn-ghost btn-lg" id="certifications-cta">
						<?php esc_html_e( 'Our Certifications', 'fardan-hikmat' ); ?>
					</a>
				</div>
			</div>

		</div>
	</div>
</section>
<!-- /BRAND STORY SECTION -->
