<?php
/**
 * Template Part: Hero Section
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$hero_img   = get_template_directory_uri() . '/assets/images/hero-banner.jpg';
$product_img = get_template_directory_uri() . '/assets/images/product-ashwagandha.jpg';
?>

<!-- ═══════════════════════════════════════════
     HERO SECTION
══════════════════════════════════════════════ -->
<section
	id="hero"
	class="hero"
	aria-label="<?php esc_attr_e( 'Hero — Fardan Al-Hikmat Herbal Wellness', 'fardan-hikmat' ); ?>"
>
	<!-- Background -->
	<div class="hero__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( $hero_img ); ?>"
			alt=""
			class="hero__bg-image"
			loading="eager"
			fetchpriority="high"
		>
		<div class="hero__bg-overlay"></div>
	</div>

	<!-- Decorative Elements -->
	<div class="hero__decoration hero__decoration--circle-1" aria-hidden="true"></div>
	<div class="hero__decoration hero__decoration--circle-2" aria-hidden="true"></div>
	<div class="hero__decoration hero__decoration--dots"    aria-hidden="true"></div>

	<!-- Content Grid -->
	<div class="hero__inner">

		<!-- Left: Copy -->
		<div class="hero__content">
			<div class="hero__eyebrow">
				<span class="hero__eyebrow-dot"></span>
				<span class="hero__eyebrow-line" aria-hidden="true"></span>
				<?php esc_html_e( 'Certified Organic &amp; Wildcrafted', 'fardan-hikmat' ); ?>
			</div>

			<h1 class="hero__title">
				<?php esc_html_e( 'Ancient Wisdom,', 'fardan-hikmat' ); ?>
				<strong><?php esc_html_e( 'Modern Healing', 'fardan-hikmat' ); ?></strong>
			</h1>

			<p class="hero__subtitle">
				<?php esc_html_e( 'Discover our curated collection of premium herbal remedies, adaptogenic formulas, and botanical wellness products — crafted with integrity, rooted in nature.', 'fardan-hikmat' ); ?>
			</p>

			<div class="hero__actions">
				<a href="#shop" class="btn btn-accent btn-lg" id="hero-cta-primary">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
					<?php esc_html_e( 'Explore Our Botanicals', 'fardan-hikmat' ); ?>
				</a>
				<a href="#about" class="btn btn-outline-light btn-lg" id="hero-cta-secondary">
					<?php esc_html_e( 'Our Story', 'fardan-hikmat' ); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
				</a>
			</div>

			<!-- Authentic Product Badges -->
			<div class="hero__stats" aria-label="<?php esc_attr_e( 'Key features', 'fardan-hikmat' ); ?>">
				<div class="hero__stat">
					<span class="hero__stat-number" style="font-size:1.5rem;">🌿</span>
					<span class="hero__stat-label" style="margin-top:0.25rem;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.05em;"><?php esc_html_e( '100% Organic', 'fardan-hikmat' ); ?></span>
				</div>
				<div class="hero__stat">
					<span class="hero__stat-number" style="font-size:1.5rem;">⚗️</span>
					<span class="hero__stat-label" style="margin-top:0.25rem;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.05em;"><?php esc_html_e( 'Lab Tested', 'fardan-hikmat' ); ?></span>
				</div>
				<div class="hero__stat">
					<span class="hero__stat-number" style="font-size:1.5rem;">🚚</span>
					<span class="hero__stat-label" style="margin-top:0.25rem;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.05em;"><?php esc_html_e( 'Fast Delivery', 'fardan-hikmat' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Right: Visual (hidden on mobile) -->
		<div class="hero__visual" aria-hidden="true">
			<div class="hero__image-frame">
				<img
					src="<?php echo esc_url( $hero_img ); ?>"
					alt="<?php esc_attr_e( 'Premium herbal wellness products', 'fardan-hikmat' ); ?>"
					class="hero__image-main"
					loading="eager"
				>

				<!-- Floating Badge: Certified -->
				<div class="hero__image-badge hero__image-badge--cert">
					<div class="hero__badge-icon">🌿</div>
					<div class="hero__badge-text">
						<strong><?php esc_html_e( 'USDA Certified', 'fardan-hikmat' ); ?></strong>
						<span><?php esc_html_e( '100% Organic', 'fardan-hikmat' ); ?></span>
					</div>
				</div>

				<!-- Floating Badge: Pure Botanicals -->
				<div class="hero__image-badge hero__image-badge--rating">
					<div class="hero__badge-icon" style="background:var(--aura-accent-muted);">🌱</div>
					<div class="hero__badge-text">
						<strong><?php esc_html_e( 'Pure Remedies', 'fardan-hikmat' ); ?></strong>
						<span><?php esc_html_e( 'Unani & Botanical', 'fardan-hikmat' ); ?></span>
					</div>
				</div>
			</div>
		</div>

	</div><!-- /.hero__inner -->

	<!-- Scroll Indicator -->
	<a
		href="#trust"
		class="hero__scroll-cta"
		aria-label="<?php esc_attr_e( 'Scroll down', 'fardan-hikmat' ); ?>"
		style="position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:.5rem;color:rgba(255,255,255,.5);font-size:.75rem;letter-spacing:.1em;text-transform:uppercase;z-index:2;animation:fadeIn 1s ease 1s both;"
	>
		<?php esc_html_e( 'Scroll', 'fardan-hikmat' ); ?>
		<span style="width:1px;height:40px;background:linear-gradient(to bottom,rgba(255,255,255,.4),transparent);animation:float 2s ease-in-out infinite;" aria-hidden="true"></span>
	</a>

</section>
<!-- /HERO SECTION -->
