<?php
/**
 * Header Template — Fardan Al-Hikmat
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php bloginfo( 'description' ); ?>">
	<meta name="theme-color" content="#2D5016">

	<!-- Open Graph -->
	<meta property="og:type"        content="website">
	<meta property="og:site_name"   content="<?php bloginfo( 'name' ); ?>">
	<meta property="og:title"       content="<?php wp_title( '|', true, 'right' ); ?><?php bloginfo( 'name' ); ?>">
	<meta property="og:description" content="<?php bloginfo( 'description' ); ?>">
	<meta property="og:url"         content="<?php echo esc_url( home_url() ); ?>">

	<!-- Favicon placeholder (replace with actual file) -->
	<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon.ico" type="image/x-icon">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Skip Link (Accessibility) -->
<a class="skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'fardan-hikmat' ); ?></a>

<!-- ═══════════════════════════════════════════
     SITE HEADER / NAVBAR
══════════════════════════════════════════════ -->
<header id="site-header" class="site-header <?php echo is_front_page() ? 'is-transparent' : 'is-scrolled'; ?>" role="banner">
	<nav class="navbar" role="navigation" aria-label="<?php esc_attr_e( 'Main Navigation', 'fardan-hikmat' ); ?>">

		<!-- Brand Logo -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navbar__brand" aria-label="<?php bloginfo( 'name' ); ?> — <?php esc_attr_e( 'Home', 'fardan-hikmat' ); ?>">
			<div class="navbar__logo-icon" aria-hidden="true">
				<!-- Leaf SVG Icon -->
				<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path d="M17 8C8 10 5.9 16.17 3.82 21.15L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 3-8 3" fill="white"/>
					<path d="M3.85 21.12c.3-3 .5-8.5 3.65-11.12" stroke="rgba(255,255,255,0.5)" stroke-width="1" fill="none"/>
				</svg>
			</div>
			<div class="navbar__logo-text">
				<span class="navbar__logo-name"><?php bloginfo( 'name' ); ?></span>
				<span class="navbar__logo-tagline"><?php esc_html_e( 'Herbal Wellness', 'fardan-hikmat' ); ?></span>
			</div>
		</a>

		<!-- Navigation Menu (WordPress Native Dynamic Menu) -->
		<?php
		if ( has_nav_menu( 'primary_header_menu' ) ) :
			wp_nav_menu( array(
				'theme_location' => 'primary_header_menu',
				'container'      => false,
				'menu_id'        => 'navbar-menu',
				'menu_class'     => 'navbar__menu',
				'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
				'depth'          => 2,
				'walker'         => new Fardan_Header_Menu_Walker(),
			) );
		else :
		?>
		<!-- Fallback Menu -->
		<ul id="navbar-menu" class="navbar__menu" role="list">
			<li>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navbar__link <?php echo is_front_page() ? 'active' : ''; ?>">
					<?php esc_html_e( 'Home', 'fardan-hikmat' ); ?>
				</a>
			</li>
			<li>
				<a href="#shop" class="navbar__link"><?php esc_html_e( 'Shop All', 'fardan-hikmat' ); ?></a>
			</li>
			<li class="navbar__item navbar__item--has-dropdown">
				<a href="#products" class="navbar__link">
					<?php esc_html_e( 'Our Product', 'fardan-hikmat' ); ?>
					<svg class="navbar__dropdown-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
				<div class="navbar__dropdown" role="menu">
					<div class="navbar__dropdown-inner">
						<?php
						$term_herbs   = get_term_by( 'slug', 'herbs', 'product_cat' );
						$link_herbs   = ( $term_herbs && ! is_wp_error( $term_herbs ) ) ? get_term_link( $term_herbs ) : home_url( '/#herbs' );

						$term_majoon  = get_term_by( 'slug', 'majoon', 'product_cat' );
						$link_majoon  = ( $term_majoon && ! is_wp_error( $term_majoon ) ) ? get_term_link( $term_majoon ) : home_url( '/#majoon' );

						$term_arqiyat = get_term_by( 'slug', 'arqiyat', 'product_cat' );
						$link_arqiyat = ( $term_arqiyat && ! is_wp_error( $term_arqiyat ) ) ? get_term_link( $term_arqiyat ) : home_url( '/#arqiyat' );

						$term_syrups  = get_term_by( 'slug', 'syrups', 'product_cat' );
						$link_syrups  = ( $term_syrups && ! is_wp_error( $term_syrups ) ) ? get_term_link( $term_syrups ) : home_url( '/#syrups' );
						?>
						<a href="<?php echo esc_url( $link_herbs ); ?>" class="navbar__dropdown-item" data-category-slug="herbs" role="menuitem">
							<span class="navbar__dropdown-icon">🌿</span>
							<div class="navbar__dropdown-text">
								<span class="navbar__dropdown-title"><?php esc_html_e( 'Herbs & Raw Materials', 'fardan-hikmat' ); ?></span>
								<span class="navbar__dropdown-sub"><?php esc_html_e( 'Jari Bootiyan & Pure Botanicals', 'fardan-hikmat' ); ?></span>
							</div>
						</a>
						<a href="<?php echo esc_url( $link_majoon ); ?>" class="navbar__dropdown-item" data-category-slug="majoon" role="menuitem">
							<span class="navbar__dropdown-icon">🏺</span>
							<div class="navbar__dropdown-text">
								<span class="navbar__dropdown-title"><?php esc_html_e( 'Majoon & Khameere', 'fardan-hikmat' ); ?></span>
								<span class="navbar__dropdown-sub"><?php esc_html_e( 'Traditional Herbal Electuaries', 'fardan-hikmat' ); ?></span>
							</div>
						</a>
						<a href="<?php echo esc_url( $link_arqiyat ); ?>" class="navbar__dropdown-item" data-category-slug="arqiyat" role="menuitem">
							<span class="navbar__dropdown-icon">💧</span>
							<div class="navbar__dropdown-text">
								<span class="navbar__dropdown-title"><?php esc_html_e( 'Arqiyat (Pure Extracts)', 'fardan-hikmat' ); ?></span>
								<span class="navbar__dropdown-sub"><?php esc_html_e( 'Distilled Natural Extracts', 'fardan-hikmat' ); ?></span>
							</div>
						</a>
						<a href="<?php echo esc_url( $link_syrups ); ?>" class="navbar__dropdown-item" data-category-slug="syrups" role="menuitem">
							<span class="navbar__dropdown-icon">🧪</span>
							<div class="navbar__dropdown-text">
								<span class="navbar__dropdown-title"><?php esc_html_e( 'Syrups & Tinctures', 'fardan-hikmat' ); ?></span>
								<span class="navbar__dropdown-sub"><?php esc_html_e( 'Concentrated Healing Elixirs', 'fardan-hikmat' ); ?></span>
							</div>
						</a>
					</div>
				</div>
			</li>
			<li class="navbar__item navbar__item--has-dropdown">
				<a href="#health-concerns" class="navbar__link">
					<?php esc_html_e( 'Health Concerns', 'fardan-hikmat' ); ?>
					<svg class="navbar__dropdown-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
				<div class="navbar__dropdown" role="menu">
					<div class="navbar__dropdown-inner">
						<a href="#digestive" class="navbar__dropdown-item" role="menuitem">
							<span class="navbar__dropdown-icon">🌱</span>
							<div class="navbar__dropdown-text">
								<span class="navbar__dropdown-title"><?php esc_html_e( 'Digestive Health', 'fardan-hikmat' ); ?></span>
								<span class="navbar__dropdown-sub"><?php esc_html_e( 'Stomach, Gut & Metabolism', 'fardan-hikmat' ); ?></span>
							</div>
						</a>
						<a href="#immunity" class="navbar__dropdown-item" role="menuitem">
							<span class="navbar__dropdown-icon">🛡️</span>
							<div class="navbar__dropdown-text">
								<span class="navbar__dropdown-title"><?php esc_html_e( 'Immunity Support', 'fardan-hikmat' ); ?></span>
								<span class="navbar__dropdown-sub"><?php esc_html_e( 'Vitality & Daily Resilience', 'fardan-hikmat' ); ?></span>
							</div>
						</a>
						<a href="#skin" class="navbar__dropdown-item" role="menuitem">
							<span class="navbar__dropdown-icon">✨</span>
							<div class="navbar__dropdown-text">
								<span class="navbar__dropdown-title"><?php esc_html_e( 'Skin & Beauty', 'fardan-hikmat' ); ?></span>
								<span class="navbar__dropdown-sub"><?php esc_html_e( 'Natural Glow & Radiance', 'fardan-hikmat' ); ?></span>
							</div>
						</a>
					</div>
				</div>
			</li>
			<li>
				<a href="#about" class="navbar__link"><?php esc_html_e( 'About', 'fardan-hikmat' ); ?></a>
			</li>
			<li>
				<a href="#contact" class="navbar__link"><?php esc_html_e( 'Contact Us', 'fardan-hikmat' ); ?></a>
			</li>
		</ul>
		<?php endif; ?>

		<!-- Action Buttons -->
		<div class="navbar__actions">
			<!-- Search -->
			<button
				id="navbar-search"
				class="navbar__action-btn"
				type="button"
				onclick="if(typeof ahpmOpenSearch==='function') ahpmOpenSearch();"
				aria-label="<?php esc_attr_e( 'Search products', 'fardan-hikmat' ); ?>"
			>
				<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
			</button>

			<!-- Wishlist -->
			<button
				id="navbar-wishlist"
				class="navbar__action-btn"
				type="button"
				aria-label="<?php esc_attr_e( 'Wishlist', 'fardan-hikmat' ); ?>"
			>
				<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
			</button>

			<!-- Cart -->
			<button
				id="navbar-cart"
				class="navbar__action-btn"
				type="button"
				aria-label="<?php esc_attr_e( 'Shopping cart, 0 items', 'fardan-hikmat' ); ?>"
			>
				<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
				<span class="navbar__badge" aria-live="polite" aria-atomic="true">0</span>
			</button>

			<!-- CTA Button -->
			<a href="#shop" class="btn btn-primary btn-sm" id="navbar-cta">
				<?php esc_html_e( 'Shop Now', 'fardan-hikmat' ); ?>
			</a>
		</div>

		<!-- Mobile Toggle -->
		<button
			id="navbar-toggle"
			class="navbar__toggle"
			type="button"
			aria-expanded="false"
			aria-controls="navbar-menu"
			aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'fardan-hikmat' ); ?>"
		>
			<span></span>
			<span></span>
			<span></span>
		</button>

	</nav>
</header>
<!-- /SITE HEADER -->

<!-- ═══════════════════════════════════════════
     PRODUCT DRAWER OVERLAY
     (Always in DOM, toggled via JS)
══════════════════════════════════════════════ -->
<div
	id="drawer-overlay"
	class="drawer-overlay"
	role="presentation"
	aria-hidden="true"
></div>

<aside
	id="product-drawer"
	class="product-drawer"
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'Product Details', 'fardan-hikmat' ); ?>"
	tabindex="-1"
>
	<!-- Drawer Header -->
	<div class="drawer-header">
		<span class="drawer-header__eyebrow"><?php esc_html_e( 'Product Details', 'fardan-hikmat' ); ?></span>
		<button
			id="drawer-close"
			class="drawer-close"
			type="button"
			aria-label="<?php esc_attr_e( 'Close product details', 'fardan-hikmat' ); ?>"
		>
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<line x1="18" y1="6" x2="6" y2="18"/>
				<line x1="6"  y1="6" x2="18" y2="18"/>
			</svg>
		</button>
	</div>

	<!-- Drawer Scrollable Body -->
	<div class="drawer-body">

		<!-- Product Gallery -->
		<div class="drawer-gallery">
			<div class="drawer-gallery__main">
				<img src="" alt="" id="drawer-main-img">
				<div class="drawer-gallery__badge"></div>
			</div>
			<div class="drawer-gallery__thumbnails">
				<button class="drawer-gallery__thumb is-active" type="button" aria-label="<?php esc_attr_e( 'View image 1', 'fardan-hikmat' ); ?>">
					<img src="" alt="">
				</button>
				<button class="drawer-gallery__thumb" type="button" aria-label="<?php esc_attr_e( 'View image 2', 'fardan-hikmat' ); ?>">
					<img src="" alt="">
				</button>
				<button class="drawer-gallery__thumb" type="button" aria-label="<?php esc_attr_e( 'View image 3', 'fardan-hikmat' ); ?>">
					<img src="" alt="">
				</button>
			</div>
		</div>

		<!-- Product Info -->
		<div class="drawer-product-info">
			<p class="drawer-product__category"></p>
			<h2 class="drawer-product__title"></h2>

			<div class="drawer-product__rating-row">
				<span class="drawer-product__stars" aria-hidden="true"></span>
				<span class="drawer-product__rating-count"></span>
				<span class="badge badge-leaf"><?php esc_html_e( '🌿 100% Organic', 'fardan-hikmat' ); ?></span>
			</div>

			<div class="drawer-product__price-row">
				<span class="drawer-price-current drawer-product__price-current"></span>
				<span class="drawer-price-old drawer-product__price-old"></span>
				<span class="drawer-product__save-badge"></span>
			</div>

			<p class="drawer-product__desc"></p>

			<!-- Size Variant Selector -->
			<div class="drawer-variant-section">
				<div class="drawer-variant-label">
					<?php esc_html_e( 'Size', 'fardan-hikmat' ); ?>
					<span style="font-weight:400;color:var(--aura-text-muted)">— 50ml</span>
				</div>
				<div class="drawer-variant-options" role="group" aria-label="<?php esc_attr_e( 'Select size', 'fardan-hikmat' ); ?>">
					<button class="variant-btn is-selected" type="button">50ml</button>
					<button class="variant-btn" type="button">100ml</button>
					<button class="variant-btn" type="button">200ml</button>
				</div>
			</div>
		</div>

		<!-- Add to Cart Actions -->
		<div class="drawer-actions">
			<div class="drawer-actions__qty-row">
				<span class="drawer-actions__qty-label"><?php esc_html_e( 'Quantity', 'fardan-hikmat' ); ?></span>
				<div class="qty-selector" role="group" aria-label="<?php esc_attr_e( 'Quantity selector', 'fardan-hikmat' ); ?>">
					<button class="qty-btn" type="button" data-action="minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'fardan-hikmat' ); ?>">−</button>
					<input
						class="qty-input"
						type="number"
						value="1"
						min="1"
						max="99"
						id="drawer-qty-input"
						aria-label="<?php esc_attr_e( 'Quantity', 'fardan-hikmat' ); ?>"
					>
					<button class="qty-btn" type="button" data-action="plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'fardan-hikmat' ); ?>">+</button>
				</div>
			</div>

			<div class="drawer-actions__buttons">
				<button id="drawer-add-to-cart" class="btn btn-primary drawer-actions__add-to-cart" type="button">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
					<?php esc_html_e( 'Add to Cart', 'fardan-hikmat' ); ?>
				</button>
				<button id="drawer-buy-now" class="btn btn-outline" type="button">
					<?php esc_html_e( 'Buy Now', 'fardan-hikmat' ); ?>
				</button>
			</div>

			<button class="drawer-actions__wishlist" type="button" aria-label="<?php esc_attr_e( 'Add to wishlist', 'fardan-hikmat' ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
				<?php esc_html_e( 'Add to Wishlist', 'fardan-hikmat' ); ?>
			</button>
		</div>

		<!-- Product Details Section -->
		<div class="drawer-details" data-accordion-group>
			<h3 class="drawer-details__heading"><?php esc_html_e( 'Key Benefits', 'fardan-hikmat' ); ?></h3>
			<div class="drawer-benefits-list" role="list"></div>

			<h3 class="drawer-details__heading" style="margin-top:1.5rem;"><?php esc_html_e( 'Herbal Ingredients', 'fardan-hikmat' ); ?></h3>
			<div class="drawer-ingredients-chips" role="list"></div>

			<!-- Delivery Info -->
			<div style="margin-top:1.5rem;padding:1rem 1.25rem;background:var(--aura-primary-muted);border-radius:var(--radius-lg);display:flex;gap:.75rem;align-items:flex-start;">
				<span style="font-size:1.25rem;" aria-hidden="true">🚚</span>
				<div>
					<p style="font-size:.875rem;font-weight:600;color:var(--aura-primary);"><?php esc_html_e( 'Free Shipping over $50', 'fardan-hikmat' ); ?></p>
					<p style="font-size:.75rem;color:var(--aura-text-muted);"><?php esc_html_e( 'Orders ship within 1–2 business days', 'fardan-hikmat' ); ?></p>
				</div>
			</div>
		</div>

	</div><!-- /drawer-body -->
</aside>
<!-- /PRODUCT DRAWER -->

<!-- ═══════════════════════════════════════════
     YOUTUBE-STYLE LIVE PRODUCT SEARCH MODAL OVERLAY
══════════════════════════════════════════════ -->
<div id="ahpm-search-overlay" class="ahpm-search-overlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0, 0, 0, 0.45); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); align-items:flex-start; justify-content:center; padding:70px 20px; overflow-y:auto;">
	<div class="ahpm-search-modal" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; width:100%; max-width:720px; padding:24px 28px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); color:#0f172a; position:relative;">
		
		<!-- Search Form Header -->
		<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; border-bottom:1px solid #e2e8f0; padding-bottom:16px;">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex; align-items:center; gap:14px; width:100%;">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2D5016" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
				<input type="text" id="ahpm-search-input" name="s" placeholder="<?php esc_attr_e( 'Search herbal products, formulas, ingredients...', 'fardan-hikmat' ); ?>" autocomplete="off" style="width:100%; background:transparent; border:none; outline:none; color:#0f172a; font-size:17px; font-weight:500;" />
				<input type="hidden" name="post_type" value="product" />
				<button type="submit" style="background:#2D5016; color:#ffffff; font-weight:700; font-size:12px; padding:9px 20px; border-radius:10px; border:none; cursor:pointer; text-transform:uppercase; letter-spacing:0.8px; box-shadow:0 4px 12px rgba(45,80,22,0.25);">Search</button>
			</form>
			<button type="button" id="ahpm-search-close" style="background:transparent; border:none; color:#64748b; font-size:24px; cursor:pointer; margin-left:14px; line-height:1; transition:color 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">✕</button>
		</div>

		<!-- Live Results Box -->
		<div id="ahpm-search-results" style="max-height:420px; overflow-y:auto; padding-right:4px;">
			<div style="font-size:11px; color:#2D5016; font-weight:800; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">🌿 Quick Suggestions</div>
			<div style="display:flex; flex-wrap:wrap; gap:8px;">
				<button type="button" class="ahpm-search-tag" onclick="ahpmFillSearch('Agave')" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600; transition:all 0.2s ease;">🌿 Agave</button>
				<button type="button" class="ahpm-search-tag" onclick="ahpmFillSearch('Ajwain')" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600; transition:all 0.2s ease;">🌱 Ajwain</button>
				<button type="button" class="ahpm-search-tag" onclick="ahpmFillSearch('Anise')" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600; transition:all 0.2s ease;">🌸 Anise Hyssop</button>
				<button type="button" class="ahpm-search-tag" onclick="ahpmFillSearch('Gooseberry')" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600; transition:all 0.2s ease;">🍒 Gooseberry</button>
				<button type="button" class="ahpm-search-tag" onclick="ahpmFillSearch('Angelica')" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600; transition:all 0.2s ease;">🪵 Angelica Root</button>
			</div>
		</div>

	</div>
</div>

<!-- Toast Notification -->
<div id="aura-toast" class="toast" role="status" aria-live="polite" aria-atomic="true">
	<span aria-hidden="true">🌿</span>
	<span class="toast-message"></span>
</div>

<!-- Main Content Starts -->
<main id="main-content" class="page-content" role="main">
