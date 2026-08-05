<?php
/**
 * Footer Template — Fardan Al-Hikmat
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

</main><!-- /#main-content -->

<!-- ═══════════════════════════════════════════
     SITE FOOTER
══════════════════════════════════════════════ -->
<footer id="site-footer" class="site-footer" role="contentinfo">
	<div class="container">
		<div class="footer__top">

			<!-- Brand Column -->
			<div class="footer__brand-col">
				<div class="footer__brand">
					<p class="footer__brand-name"><?php bloginfo( 'name' ); ?></p>
					<p class="footer__brand-tagline"><?php esc_html_e( 'Nature\'s Finest Remedies', 'fardan-hikmat' ); ?></p>
				</div>
				<p class="footer__desc">
					<?php esc_html_e( 'Crafting premium herbal wellness products rooted in ancient wisdom and validated by modern science. Every product is certified organic, ethically sourced, and third-party tested.', 'fardan-hikmat' ); ?>
				</p>

				<!-- Social Links -->
				<div class="footer__socials" role="list" aria-label="<?php esc_attr_e( 'Social Media Links', 'fardan-hikmat' ); ?>">
					<a href="#" class="footer__social-link" role="listitem" aria-label="<?php esc_attr_e( 'Follow us on Instagram', 'fardan-hikmat' ); ?>" rel="noopener noreferrer">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
					</a>
					<a href="#" class="footer__social-link" role="listitem" aria-label="<?php esc_attr_e( 'Follow us on Facebook', 'fardan-hikmat' ); ?>" rel="noopener noreferrer">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
					</a>
					<a href="#" class="footer__social-link" role="listitem" aria-label="<?php esc_attr_e( 'Follow us on Twitter', 'fardan-hikmat' ); ?>" rel="noopener noreferrer">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg>
					</a>
					<a href="#" class="footer__social-link" role="listitem" aria-label="<?php esc_attr_e( 'Watch us on YouTube', 'fardan-hikmat' ); ?>" rel="noopener noreferrer">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 00-1.94-1.96C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.4 19.54C5.12 20 12 20 12 20s6.88 0 8.6-.46a2.78 2.78 0 001.94-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
					</a>
					<a href="#" class="footer__social-link" role="listitem" aria-label="<?php esc_attr_e( 'Connect on Pinterest', 'fardan-hikmat' ); ?>" rel="noopener noreferrer">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.65 7.86 6.39 9.29-.09-.78-.17-1.98.04-2.83.18-.74 1.22-5.16 1.22-5.16s-.31-.63-.31-1.56c0-1.47.85-2.57 1.91-2.57.9 0 1.34.68 1.34 1.49 0 .91-.58 2.26-.88 3.52-.25 1.06.53 1.92 1.57 1.92 1.88 0 3.14-2.4 3.14-5.24 0-2.16-1.46-3.77-4.1-3.77-2.99 0-4.85 2.23-4.85 4.73 0 .86.25 1.46.64 1.93.18.21.2.29.14.53-.05.18-.15.61-.19.78-.06.24-.25.33-.45.24-1.25-.52-1.84-1.92-1.84-3.49 0-2.59 2.2-5.7 6.56-5.7 3.52 0 5.83 2.56 5.83 5.31 0 3.63-2.01 6.33-4.96 6.33-.99 0-1.93-.54-2.25-1.14l-.65 2.59c-.19.75-.7 1.69-1.04 2.27.78.24 1.61.37 2.47.37 5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
					</a>
				</div>
			</div>

			<!-- Quick Links (WordPress Dynamic Footer Menu Location) -->
			<div>
				<h3 class="footer__col-title"><?php esc_html_e( 'Quick Links', 'fardan-hikmat' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer_quick_links' ) ) :
					wp_nav_menu( array(
						'theme_location' => 'footer_quick_links',
						'container'      => false,
						'menu_class'     => 'footer__links',
						'items_wrap'     => '<ul class="%2$s" role="list">%3$s</ul>',
						'depth'          => 1,
					) );
				else :
				?>
				<!-- Fallback Menu Links -->
				<ul class="footer__links" role="list">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Home', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/#shop' ) ); ?>" class="footer__link"><?php esc_html_e( 'Shop All', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/#categories' ) ); ?>" class="footer__link"><?php esc_html_e( 'Collections', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Our Story', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/wholesale/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Wholesale', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/affiliates/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Affiliates', 'fardan-hikmat' ); ?></a></li>
				</ul>
				<?php endif; ?>
			</div>

			<!-- Our Products (Categories Column) -->
			<div>
				<h3 class="footer__col-title"><?php esc_html_e( 'Our Products', 'fardan-hikmat' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer_products' ) ) :
					wp_nav_menu( array(
						'theme_location' => 'footer_products',
						'container'      => false,
						'menu_class'     => 'footer__links',
						'items_wrap'     => '<ul class="%2$s" role="list">%3$s</ul>',
						'depth'          => 1,
					) );
				else :
					$term_herbs   = get_term_by( 'slug', 'herbs', 'product_cat' );
					$url_herbs   = ( $term_herbs && ! is_wp_error( $term_herbs ) ) ? get_term_link( $term_herbs ) : home_url( '/#herbs' );

					$term_majoon  = get_term_by( 'slug', 'majoon', 'product_cat' );
					$url_majoon   = ( $term_majoon && ! is_wp_error( $term_majoon ) ) ? get_term_link( $term_majoon ) : home_url( '/#majoon' );

					$term_arqiyat = get_term_by( 'slug', 'arqiyat', 'product_cat' );
					$url_arqiyat  = ( $term_arqiyat && ! is_wp_error( $term_arqiyat ) ) ? get_term_link( $term_arqiyat ) : home_url( '/#arqiyat' );

					$term_syrups  = get_term_by( 'slug', 'syrups', 'product_cat' );
					$url_syrups   = ( $term_syrups && ! is_wp_error( $term_syrups ) ) ? get_term_link( $term_syrups ) : home_url( '/#syrups' );
				?>
				<!-- Fallback Categories Menu -->
				<ul class="footer__links" role="list">
					<li><a href="<?php echo esc_url( $url_herbs ); ?>" class="footer__link" data-category-slug="herbs"><?php esc_html_e( 'Herbs & Raw Materials', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( $url_majoon ); ?>" class="footer__link" data-category-slug="majoon"><?php esc_html_e( 'Majoon & Khameere', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( $url_arqiyat ); ?>" class="footer__link" data-category-slug="arqiyat"><?php esc_html_e( 'Arqiyat (Pure Extracts)', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( $url_syrups ); ?>" class="footer__link" data-category-slug="syrups"><?php esc_html_e( 'Syrups & Tinctures', 'fardan-hikmat' ); ?></a></li>
				</ul>
				<?php endif; ?>
			</div>

			<!-- Support (WordPress Dynamic Footer Menu Location) -->
			<div>
				<h3 class="footer__col-title"><?php esc_html_e( 'Support', 'fardan-hikmat' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer_support' ) ) :
					wp_nav_menu( array(
						'theme_location' => 'footer_support',
						'container'      => false,
						'menu_class'     => 'footer__links',
						'items_wrap'     => '<ul class="%2$s" role="list">%3$s</ul>',
						'depth'          => 1,
					) );
				else :
				?>
				<!-- Fallback Menu Links -->
				<ul class="footer__links" role="list">
					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="footer__link"><?php esc_html_e( 'My Account', 'fardan-hikmat' ); ?></a></li>
						<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="footer__link"><?php esc_html_e( 'Order Tracking', 'fardan-hikmat' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( home_url( '/returns-refunds/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Returns & Refunds', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/shipping-policy/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Shipping Policy', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="footer__link"><?php esc_html_e( 'FAQ', 'fardan-hikmat' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Contact Us', 'fardan-hikmat' ); ?></a></li>
				</ul>
				<?php endif; ?>
			</div>

			<!-- Contact -->
			<div id="contact">
				<h3 class="footer__col-title"><?php esc_html_e( 'Get in Touch', 'fardan-hikmat' ); ?></h3>

				<address style="font-style:normal;">
					<div class="footer__contact-item">
						<span class="footer__contact-icon" aria-hidden="true">📍</span>
						<span><?php esc_html_e( "Main Herbal Market\nLahore, Pakistan", 'fardan-hikmat' ); ?></span>
					</div>

					<div class="footer__contact-item">
						<span class="footer__contact-icon" aria-hidden="true">📞</span>
						<a href="tel:+923001234567" style="color:inherit;">+92 300 1234567</a>
					</div>

					<div class="footer__contact-item">
						<span class="footer__contact-icon" aria-hidden="true">✉️</span>
						<a href="mailto:info@fardanalhikmat.com" style="color:inherit;">info@fardanalhikmat.com</a>
					</div>
				</address>

				<!-- Certifications -->
				<div style="margin-top:1.5rem;display:flex;flex-wrap:wrap;gap:.5rem;">
					<?php
					$certs = array(
						'🌿 USDA Organic',
						'✓ Non-GMO',
						'⚗️ GMP Certified',
						'🌎 Fair Trade',
					);
					foreach ( $certs as $cert ) :
					?>
						<span style="font-size:0.625rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;padding:.25rem .6rem;border:1px solid rgba(255,255,255,.12);border-radius:4px;color:rgba(255,255,255,.5);">
							<?php echo esc_html( $cert ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			</div>

		</div><!-- /.footer__top -->

		<!-- Footer Bottom Bar -->
		<div class="footer__bottom">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
				<?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'fardan-hikmat' ); ?>
			</p>

			<div class="footer__bottom-links">
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" class="footer__bottom-link"><?php esc_html_e( 'Privacy Policy', 'fardan-hikmat' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>" class="footer__bottom-link"><?php esc_html_e( 'Terms of Service', 'fardan-hikmat' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/shipping-policy/' ) ); ?>" class="footer__bottom-link"><?php esc_html_e( 'Shipping Policy', 'fardan-hikmat' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/returns-refunds/' ) ); ?>" class="footer__bottom-link"><?php esc_html_e( 'Returns & Refunds', 'fardan-hikmat' ); ?></a>
			</div>

			<p style="font-size:.625rem;opacity:.4;">
				<?php esc_html_e( 'Powered by WordPress & Fardan Theme', 'fardan-hikmat' ); ?>
			</p>
		</div>

	</div><!-- /.container -->
</footer>
<!-- /SITE FOOTER -->

<?php wp_footer(); ?>
</body>
</html>
