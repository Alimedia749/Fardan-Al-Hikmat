<?php
/**
 * Template Part: Newsletter Section
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- ═══════════════════════════════════════════
     NEWSLETTER SECTION
══════════════════════════════════════════════ -->
<section
	class="newsletter-section"
	aria-label="<?php esc_attr_e( 'Newsletter Signup', 'fardan-hikmat' ); ?>"
>
	<div class="container">
		<div class="newsletter-inner reveal">

			<div class="section-eyebrow" style="color:rgba(196,154,26,.9);justify-content:center;margin-bottom:var(--space-4);">
				<?php esc_html_e( 'Stay Connected', 'fardan-hikmat' ); ?>
			</div>

			<h2 class="newsletter-title">
				<?php esc_html_e( 'Join the ', 'fardan-hikmat' ); ?>
				<strong><?php esc_html_e( 'Wellness Circle', 'fardan-hikmat' ); ?></strong>
			</h2>

			<p class="newsletter-subtitle">
				<?php esc_html_e( 'Get 10% off your first order plus exclusive access to new product launches, seasonal rituals, and expert herbal wellness guides.', 'fardan-hikmat' ); ?>
			</p>

			<form
				id="newsletter-form"
				class="newsletter-form"
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
				novalidate
				aria-label="<?php esc_attr_e( 'Email newsletter signup form', 'fardan-hikmat' ); ?>"
			>
				<?php wp_nonce_field( 'fardan_nonce', 'fardan_newsletter_nonce' ); ?>
				<input type="hidden" name="action" value="fardan_subscribe">

				<label for="newsletter-email" class="sr-only">
					<?php esc_html_e( 'Your email address', 'fardan-hikmat' ); ?>
				</label>
				<input
					type="email"
					id="newsletter-email"
					name="email"
					class="newsletter-input"
					placeholder="<?php esc_attr_e( 'Enter your email address…', 'fardan-hikmat' ); ?>"
					required
					autocomplete="email"
					aria-required="true"
					aria-describedby="newsletter-note"
				>

				<button
					type="submit"
					class="btn btn-accent btn-lg"
					id="newsletter-submit"
				>
					<?php esc_html_e( 'Get 10% Off', 'fardan-hikmat' ); ?>
				</button>
			</form>

			<p id="newsletter-note" class="newsletter-note">
				<?php esc_html_e( 'By subscribing you agree to our Privacy Policy. Unsubscribe at any time.', 'fardan-hikmat' ); ?>
				<?php esc_html_e( 'No spam, ever — we promise.', 'fardan-hikmat' ); ?>
			</p>

			<!-- Trust Indicators -->
			<div style="display:flex;align-items:center;justify-content:center;gap:var(--space-8);margin-top:var(--space-8);flex-wrap:wrap;" aria-hidden="true">
				<span style="font-size:.75rem;color:rgba(255,255,255,.4);font-weight:500;letter-spacing:.04em;">🔒 <?php esc_html_e( 'SSL Secured', 'fardan-hikmat' ); ?></span>
				<span style="font-size:.75rem;color:rgba(255,255,255,.4);font-weight:500;letter-spacing:.04em;">🌿 <?php esc_html_e( '50,000+ Members', 'fardan-hikmat' ); ?></span>
				<span style="font-size:.75rem;color:rgba(255,255,255,.4);font-weight:500;letter-spacing:.04em;">✓ <?php esc_html_e( 'GDPR Compliant', 'fardan-hikmat' ); ?></span>
			</div>

		</div>
	</div>
</section>
<!-- /NEWSLETTER SECTION -->
