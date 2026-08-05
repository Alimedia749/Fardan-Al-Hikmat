<?php
/**
 * Fardan Al-Hikmat Theme — Functions & Setup
 *
 * @package fardan-hikmat
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ─────────────────────────────────────────────
   Constants
───────────────────────────────────────────── */
define( 'FARDAN_VERSION',   '1.0.0' );
define( 'FARDAN_DIR',       get_template_directory() );
define( 'FARDAN_URI',       get_template_directory_uri() );
define( 'FARDAN_ASSETS',    FARDAN_URI . '/assets' );

/* ─────────────────────────────────────────────
   Theme Setup
───────────────────────────────────────────── */
add_action( 'after_setup_theme', 'fardan_theme_setup' );

function fardan_theme_setup() {
	// Make theme available for translation.
	load_theme_textdomain( 'fardan-hikmat', FARDAN_DIR . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails on posts and pages.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 800, 800, true );
	add_image_size( 'fardan-product-thumb', 300, 300, true );
	add_image_size( 'fardan-product-full',  800, 800, false );
	add_image_size( 'fardan-hero',          1920, 900, true );

	// HTML5 support.
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Custom logo support.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// WooCommerce support.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Register navigation menus.
	register_nav_menus(
		array(
			'primary_header_menu' => esc_html__( 'Primary Header Menu', 'fardan-hikmat' ),
			'primary'             => esc_html__( 'Primary Navigation', 'fardan-hikmat' ),
			'footer_quick_links'  => esc_html__( 'Footer Quick Links Menu', 'fardan-hikmat' ),
			'footer_support'      => esc_html__( 'Footer Support Menu', 'fardan-hikmat' ),
			'footer_products'     => esc_html__( 'Footer Products & Categories Menu', 'fardan-hikmat' ),
			'footer'              => esc_html__( 'Footer Navigation', 'fardan-hikmat' ),
		)
	);
}

/**
 * Add 'footer__link' CSS class to footer menu links
 */
add_filter( 'nav_menu_link_attributes', function( $atts, $item, $args ) {
	if ( isset( $args->theme_location ) && in_array( $args->theme_location, array( 'footer_quick_links', 'footer_support', 'footer_products' ), true ) ) {
		$atts['class'] = isset( $atts['class'] ) ? $atts['class'] . ' footer__link' : 'footer__link';
	}
	return $atts;
}, 10, 3 );

/**
 * Custom Nav Walker for Fardan Al-Hikmat Header Menu
 */
class Fardan_Header_Menu_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="navbar__dropdown" role="menu"><div class="navbar__dropdown-inner">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</div></div>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );

		if ( 0 === $depth ) {
			$li_classes = array( 'navbar__item' );
			if ( $has_children ) {
				$li_classes[] = 'navbar__item--has-dropdown';
			}

			$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $li_classes ), $item, $args, $depth ) );
			$output .= '<li class="' . esc_attr( $class_names ) . '">';

			$atts           = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
			$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
			$atts['href']   = ! empty( $item->url )        ? $item->url        : '';
			$atts['class']  = 'navbar__link' . ( $item->current ? ' active' : '' );

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
				}
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );

			$item_output  = isset($args->before) ? $args->before : '';
			$item_output .= '<a' . $attributes . '>';
			$item_output .= (isset($args->link_before) ? $args->link_before : '') . $title . (isset($args->link_after) ? $args->link_after : '');

			if ( $has_children ) {
				$item_output .= ' <svg class="navbar__dropdown-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			}

			$item_output .= '</a>';
			$item_output .= isset($args->after) ? $args->after : '';

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		} else {
			$atts           = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
			$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
			$atts['href']   = ! empty( $item->url )        ? $item->url        : '';
			$atts['class']  = 'navbar__dropdown-item';
			$atts['role']   = 'menuitem';

			$slug = sanitize_title( $item->title );
			$atts['data-category-slug'] = $slug;

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
				}
			}

			$title    = apply_filters( 'the_title', $item->title, $item->ID );
			$sub_text = ! empty( $item->description ) ? $item->description : '';

			$item_output  = isset($args->before) ? $args->before : '';
			$item_output .= '<a' . $attributes . '>';
			$item_output .= '<span class="navbar__dropdown-icon">🌿</span>';
			$item_output .= '<div class="navbar__dropdown-text">';
			$item_output .= '<span class="navbar__dropdown-title">' . $title . '</span>';
			if ( $sub_text ) {
				$item_output .= '<span class="navbar__dropdown-sub">' . esc_html( $sub_text ) . '</span>';
			}
			$item_output .= '</div>';
			$item_output .= '</a>';
			$item_output .= isset($args->after) ? $args->after : '';

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</li>';
		}
	}
}

/* ─────────────────────────────────────────────
   Content Width
───────────────────────────────────────────── */
if ( ! isset( $content_width ) ) {
	$content_width = 1280;
}

/* ─────────────────────────────────────────────
   Enqueue Styles
───────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'fardan_enqueue_styles' );

function fardan_enqueue_styles() {
	// Google Fonts — Cormorant Garamond + Inter
	wp_enqueue_style(
		'fardan-google-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600&family=Inter:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	// Aura Design System (design tokens, utilities, components)
	wp_enqueue_style(
		'fardan-aura-design-system',
		FARDAN_ASSETS . '/css/aura-design-system.css',
		array( 'fardan-google-fonts' ),
		FARDAN_VERSION
	);

	// Main Stylesheet (navbar, hero, sections, footer)
	wp_enqueue_style(
		'fardan-main',
		FARDAN_ASSETS . '/css/main.css',
		array( 'fardan-aura-design-system' ),
		FARDAN_VERSION
	);

	// Product Drawer Styles
	wp_enqueue_style(
		'fardan-product-drawer',
		FARDAN_ASSETS . '/css/product-drawer.css',
		array( 'fardan-main' ),
		FARDAN_VERSION
	);

	// PDP Styles — only on product detail page
	if ( is_page_template( 'page-product.php' ) || is_singular( 'product' ) ) {
		wp_enqueue_style(
			'fardan-pdp',
			FARDAN_ASSETS . '/css/pdp.css',
			array( 'fardan-main' ),
			FARDAN_VERSION
		);
	}

	// Base theme stylesheet (required by WP, minimal usage)
	wp_enqueue_style(
		'fardan-style',
		get_stylesheet_uri(),
		array( 'fardan-main' ),
		FARDAN_VERSION
	);
}

/* ─────────────────────────────────────────────
   Enqueue Scripts
───────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'fardan_enqueue_scripts' );

function fardan_enqueue_scripts() {
	// Custom cursor
	wp_enqueue_script(
		'fardan-cursor',
		FARDAN_ASSETS . '/js/cursor.js',
		array(),
		FARDAN_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	// Product Drawer
	wp_enqueue_script(
		'fardan-drawer',
		FARDAN_ASSETS . '/js/drawer.js',
		array(),
		FARDAN_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	// Main JS (navbar, animations, forms, PDP interactions)
	wp_enqueue_script(
		'fardan-main',
		FARDAN_ASSETS . '/js/main.js',
		array( 'fardan-drawer' ),
		FARDAN_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	// Localize script data for AJAX / nonces
	wp_localize_script(
		'fardan-main',
		'fardanData',
		array(
			'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'   => wp_create_nonce( 'fardan_nonce' ),
			'siteUrl' => esc_url( home_url() ),
			'i18n'    => array(
				'addedToCart' => esc_html__( 'Added to cart!', 'fardan-hikmat' ),
				'outOfStock'  => esc_html__( 'Out of stock', 'fardan-hikmat' ),
				'emailError'  => esc_html__( 'Please enter a valid email.', 'fardan-hikmat' ),
				'subscribed'  => esc_html__( 'Thank you for subscribing!', 'fardan-hikmat' ),
			),
		)
	);
}

/* ─────────────────────────────────────────────
   Widgets
───────────────────────────────────────────── */
add_action( 'widgets_init', 'fardan_register_sidebars' );

function fardan_register_sidebars() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Shop Sidebar', 'fardan-hikmat' ),
			'id'            => 'shop-sidebar',
			'description'   => esc_html__( 'Widgets for the shop/product pages.', 'fardan-hikmat' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget__title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Widget Area', 'fardan-hikmat' ),
			'id'            => 'footer-widgets',
			'description'   => esc_html__( 'Widgets displayed in the footer.', 'fardan-hikmat' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget__title">',
			'after_title'   => '</h4>',
		)
	);
}

/* ─────────────────────────────────────────────
   Helper: Render Product Card
   Shared between front-page, related products,
   search results, etc.
───────────────────────────────────────────── */
if ( ! function_exists( 'fardan_render_product_card' ) ) :
	/**
	 * Render a product card.
	 *
	 * @param array $product {
	 *     @type string   $title        Product title.
	 *     @type string   $category     Product category.
	 *     @type string   $price        Current price (e.g. '$24.99').
	 *     @type string   $price_old    Original price (optional).
	 *     @type string   $discount     Discount label (e.g. '15%').
	 *     @type string   $description  Short description.
	 *     @type string   $image        Image URL.
	 *     @type float    $rating       Star rating (e.g. 4.8).
	 *     @type int      $rating_count Number of reviews.
	 *     @type bool     $is_new       Show 'New' badge.
	 *     @type bool     $is_bestseller Show 'Bestseller' badge.
	 *     @type string   $tab_category Tab filter category slug.
	 *     @type string[] $benefits     Array of benefit strings.
	 *     @type string[] $ingredients  Array of ingredient strings.
	 *     @type string   $page_url     Link to PDP page.
	 * }
	 * @return void
	 */
	function fardan_render_product_card( array $product ) {
		$title        = isset( $product['title'] )        ? sanitize_text_field( $product['title'] )        : '';
		$category     = isset( $product['category'] )     ? sanitize_text_field( $product['category'] )     : '';
		$price        = isset( $product['price'] )        ? sanitize_text_field( $product['price'] )        : '';
		$price_old    = isset( $product['price_old'] )    ? sanitize_text_field( $product['price_old'] )    : '';
		$discount     = isset( $product['discount'] )     ? sanitize_text_field( $product['discount'] )     : '';
		$description  = isset( $product['description'] )  ? sanitize_text_field( $product['description'] )  : '';
		$image        = isset( $product['image'] )        ? esc_url( $product['image'] )                    : '';
		$rating       = isset( $product['rating'] )       ? (float) $product['rating']                      : 4.8;
		$rating_count = isset( $product['rating_count'] ) ? (int) $product['rating_count']                  : 0;
		$is_new       = ! empty( $product['is_new'] );
		$is_bestseller = ! empty( $product['is_bestseller'] );
		$tab_category = isset( $product['tab_category'] ) ? sanitize_html_class( $product['tab_category'] ) : 'all';
		$benefits     = isset( $product['benefits'] )     ? (array) $product['benefits']                    : array();
		$ingredients  = isset( $product['ingredients'] )  ? (array) $product['ingredients']                 : array();
		$variations   = isset( $product['variations'] )   ? (array) $product['variations']                  : array();
		$page_url     = isset( $product['page_url'] )     ? esc_url( $product['page_url'] )                 : '#';

		// Build data attributes for drawer
		$data_attrs  = ' data-product-title="' . esc_attr( $title ) . '"';
		$data_attrs .= ' data-product-category="' . esc_attr( $category ) . '"';
		$data_attrs .= ' data-product-price="' . esc_attr( $price ) . '"';
		$data_attrs .= ' data-product-price-old="' . esc_attr( $price_old ) . '"';
		$data_attrs .= ' data-product-discount="' . esc_attr( $discount ) . '"';
		$data_attrs .= ' data-product-description="' . esc_attr( $description ) . '"';
		$data_attrs .= ' data-product-image="' . esc_attr( $image ) . '"';
		$data_attrs .= ' data-product-rating="' . esc_attr( $rating ) . '"';
		$data_attrs .= ' data-product-rating-count="' . esc_attr( $rating_count ) . '"';
		$data_attrs .= ' data-product-new="' . esc_attr( $is_new ? 'true' : 'false' ) . '"';
		$data_attrs .= ' data-product-bestseller="' . esc_attr( $is_bestseller ? 'true' : 'false' ) . '"';
		$data_attrs .= ' data-tab-category="' . esc_attr( $tab_category ) . '"';

		if ( ! empty( $benefits ) ) {
			$data_attrs .= ' data-product-benefits="' . esc_attr( implode( '|', array_map( 'sanitize_text_field', $benefits ) ) ) . '"';
		}

		if ( ! empty( $ingredients ) ) {
			$data_attrs .= ' data-product-ingredients="' . esc_attr( implode( '|', array_map( 'sanitize_text_field', $ingredients ) ) ) . '"';
		}

		if ( ! empty( $variations ) ) {
			$data_attrs .= ' data-product-variations="' . esc_attr( wp_json_encode( $variations ) ) . '"';
		}

		// Build star string
		$full_stars  = floor( $rating );
		$stars_html  = str_repeat( '★', $full_stars ) . str_repeat( '☆', 5 - $full_stars );

		?>
		<div class="product-card-wrapper">
			<article
				class="product-card"
				<?php echo $data_attrs; // Already escaped above. ?>
				role="article"
				aria-label="<?php echo esc_attr( $title ); ?>"
				tabindex="0"
			>
				<!-- Image -->
				<div class="product-card__image-wrap">
					<?php if ( $page_url && '#' !== $page_url ) : ?>
						<a href="<?php echo esc_url( $page_url ); ?>" style="display:block;width:100%;height:100%;">
					<?php endif; ?>
					<?php if ( $image ) : ?>
						<img
							src="<?php echo esc_url( $image ); ?>"
							alt="<?php echo esc_attr( $title ); ?>"
							loading="lazy"
							width="400"
							height="400"
						/>
					<?php endif; ?>
					<?php if ( $page_url && '#' !== $page_url ) : ?>
						</a>
					<?php endif; ?>

					<!-- Badges -->
					<div class="product-card__badges">
						<?php if ( ! empty( $product['is_sale'] ) ) : ?>
							<span class="badge" style="background:#ef4444;color:#ffffff;font-weight:bold;"><?php esc_html_e( 'Sale!', 'fardan-hikmat' ); ?></span>
						<?php endif; ?>
						<?php if ( $is_new ) : ?>
							<span class="badge badge-primary"><?php esc_html_e( 'New', 'fardan-hikmat' ); ?></span>
						<?php endif; ?>
						<?php if ( $is_bestseller ) : ?>
							<span class="badge badge-accent"><?php esc_html_e( 'Bestseller', 'fardan-hikmat' ); ?></span>
						<?php endif; ?>
						<?php if ( $discount ) : ?>
							<span class="badge" style="background:#10b981;color:#ffffff;font-weight:bold;">
								-<?php echo esc_html( $discount ); ?>
							</span>
						<?php endif; ?>
					</div>

					<!-- Direct Details Button -->
					<a
						class="product-card__quick-view"
						href="<?php echo esc_url( $page_url ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'View details for %s', 'fardan-hikmat' ), $title ) ); ?>"
					>
						<?php esc_html_e( 'View Details', 'fardan-hikmat' ); ?>
					</a>
				</div>

				<!-- Body -->
				<div class="product-card__body">
					<p class="product-card__category"><?php echo esc_html( $category ); ?></p>
					<h3 class="product-card__title">
						<?php if ( $page_url && '#' !== $page_url ) : ?>
							<a href="<?php echo esc_url( $page_url ); ?>" style="color:inherit;text-decoration:none;"><?php echo esc_html( $title ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $title ); ?>
						<?php endif; ?>
					</h3>
					<p class="product-card__desc"><?php echo esc_html( $description ); ?></p>

					<div class="product-card__footer">
						<div class="product-card__price">
							<span class="product-card__price-current"><?php echo $price; ?></span>
							<?php if ( ! empty( $price_old ) ) : ?>
								<span class="product-card__price-old"><?php echo $price_old; ?></span>
							<?php endif; ?>
						</div>

						<div class="product-card__rating">
							<span class="product-card__stars" aria-hidden="true"><?php echo esc_html( $stars_html ); ?></span>
							<span class="product-card__rating-count">
								<?php echo esc_html( '(' . $rating_count . ')' ); ?>
							</span>
						</div>
					</div>
				</div>
			</article>
		</div>
		<?php
	}
endif;

/* ─────────────────────────────────────────────
   Helper: Star Rating HTML
───────────────────────────────────────────── */
if ( ! function_exists( 'fardan_star_rating' ) ) :
	function fardan_star_rating( $rating = 5, $max = 5 ) {
		$output = '<span class="star-rating" role="img" aria-label="' . esc_attr( sprintf( __( '%1$s out of %2$s stars', 'fardan-hikmat' ), $rating, $max ) ) . '">';
		for ( $i = 1; $i <= $max; $i++ ) {
			if ( $i <= floor( $rating ) ) {
				$output .= '<svg class="star" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>';
			} else {
				$output .= '<svg class="star empty" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="none" stroke="#D5CEBC" stroke-width="1.5"/></svg>';
			}
		}
		$output .= '</span>';
		return $output;
	}
endif;

/* ─────────────────────────────────────────────
   Dynamic WooCommerce / WP Product Fetcher
───────────────────────────────────────────── */
if ( ! function_exists( 'fardan_get_products' ) ) :
	/**
	 * Get products dynamically from WooCommerce / WordPress DB.
	 * Includes full PKR retail prices & weight variations.
	 */
	function fardan_get_products( $args = array() ) {
		$products = array();

		if ( class_exists( 'WooCommerce' ) ) {
			$defaults = array(
				'limit'   => 12,
				'status'  => 'publish',
				'orderby' => 'date',
				'order'   => 'DESC',
			);
			$query_args  = wp_parse_args( $args, $defaults );
			$wc_products = wc_get_products( $query_args );

			foreach ( $wc_products as $wc_prod ) {
				$image_id  = $wc_prod->get_image_id();
				$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';

				$cats     = wp_get_post_terms( $wc_prod->get_id(), 'product_cat', array( 'fields' => 'names' ) );
				$cat_name = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0] : __( 'Herbs & Raw Materials', 'fardan-hikmat' );

				$cat_slugs = wp_get_post_terms( $wc_prod->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );
				$cat_slug  = ( ! empty( $cat_slugs ) && ! is_wp_error( $cat_slugs ) ) ? $cat_slugs[0] : 'herbs';

				$is_on_sale     = $wc_prod->is_on_sale();
				$sale_p         = floatval( $wc_prod->get_price() );
				$reg_p          = 0;
				$discount_label = '';
				$is_sale_badge  = false;

				if ( $wc_prod->is_type( 'variable' ) ) {
					$reg_p = floatval( $wc_prod->get_variation_regular_price( 'min', true ) );
					if ( $reg_p <= 0 ) {
						$reg_p = floatval( get_post_meta( $wc_prod->get_id(), '_min_variation_regular_price', true ) );
					}
					if ( $reg_p <= 0 ) {
						$reg_p = floatval( get_post_meta( $wc_prod->get_id(), '_regular_price', true ) );
					}
				} else {
					$reg_p = floatval( $wc_prod->get_regular_price() );
				}

				if ( $is_on_sale && $reg_p > 0 && $reg_p > $sale_p ) {
					$p_old  = wc_price( $reg_p );
					$pct    = round( ( ( $reg_p - $sale_p ) / $reg_p ) * 100 );
					if ( $pct > 0 ) {
						$discount_label = $pct . '% OFF';
					}
					$is_sale_badge = true;
				} else {
					$p_old = '';
				}

				$products[] = array(
					'id'           => $wc_prod->get_id(),
					'title'        => $wc_prod->get_name(),
					'category'     => $cat_name,
					'price'        => wc_price( $wc_prod->get_price() ),
					'price_old'    => $p_old,
					'discount'     => $discount_label,
					'is_sale'      => $is_sale_badge,
					'description'  => wp_strip_all_tags( $wc_prod->get_short_description() ? $wc_prod->get_short_description() : $wc_prod->get_description() ),
					'image'        => $image_url,
					'rating'       => (float) $wc_prod->get_average_rating(),
					'rating_count' => (int) $wc_prod->get_rating_count(),
					'is_new'       => false,
					'is_bestseller'=> $wc_prod->is_featured(),
					'tab_category' => $cat_slug,
					'benefits'     => array(),
					'ingredients'  => array(),
					'page_url'     => get_permalink( $wc_prod->get_id() ),
				);
			}
		}

		// Fallback / Featured Products with exact PKR retail pricing & weight variations
		if ( empty( $products ) ) {
			$assets = get_template_directory_uri() . '/assets/images/';

			$products = array(
				array(
					'id'           => 101,
					'title'        => 'Organic Agave Powder (Imported)',
					'category'     => 'Herbs & Raw Materials',
					'price'        => 'Rs. 499',
					'price_old'    => 'Rs. 650',
					'discount'     => '23%',
					'description'  => 'Premium imported organic Agave Powder. A low-glycemic natural sweetener rich in inulin prebiotic fiber, perfect for healthy beverages, herbal concoctions, and diabetic-friendly cooking.',
					'image'        => $assets . 'product-agave.jpg',
					'rating'       => 4.9,
					'rating_count' => 142,
					'is_new'       => true,
					'is_bestseller'=> true,
					'tab_category' => 'herbs',
					'benefits'     => array(
						'Low Glycemic Index natural organic sweetener',
						'Rich in inulin prebiotic fiber supporting digestive & gut health',
						'Dissolves effortlessly in cold and hot herbal drinks',
						'Ideal diabetic-friendly sugar substitute',
						'100% Pure, imported & non-GMO certified',
					),
					'ingredients'  => array( 'Pure Organic Agave Nectar Powder (Agave tequilana)' ),
					'variations'   => array(
						array( 'size' => '50g',   'price' => 'Rs. 499',   'price_old' => 'Rs. 650',   'discount' => '23%' ),
						array( 'size' => '100g',  'price' => 'Rs. 899',   'price_old' => 'Rs. 1,150', 'discount' => '22%' ),
						array( 'size' => '250g',  'price' => 'Rs. 1,950', 'price_old' => 'Rs. 2,400', 'discount' => '19%' ),
						array( 'size' => '500g',  'price' => 'Rs. 3,600', 'price_old' => 'Rs. 4,200', 'discount' => '14%' ),
						array( 'size' => '1000g', 'price' => 'Rs. 6,500', 'price_old' => 'Rs. 7,800', 'discount' => '17%' ),
					),
					'page_url'     => home_url( '/product-detail/' ),
				),
				array(
					'id'           => 102,
					'title'        => 'Dried Ajwain Leaf Powder (Mexican Mint)',
					'category'     => 'Herbs & Raw Materials',
					'price'        => 'Rs. 199',
					'price_old'    => 'Rs. 280',
					'discount'     => '29%',
					'description'  => 'Pure sun-dried Ajwain leaf powder (Mexican Mint / Patharchat). A traditional Unani & Ayurvedic remedy for digestive wellness, instant acidity relief, respiratory health, and immunity.',
					'image'        => $assets . 'product-ajwain.jpg',
					'rating'       => 4.8,
					'rating_count' => 98,
					'is_new'       => true,
					'is_bestseller'=> true,
					'tab_category' => 'herbs',
					'benefits'     => array(
						'Provides quick relief from indigestion, gas, bloating and acidity',
						'Supports chest decongestion and healthy respiratory airflow',
						'Rich in natural thymol essential oils & protective antioxidants',
						'Authentic Unani & Ayurvedic household remedy',
						'Locally harvested, 100% natural & chemical-free',
					),
					'ingredients'  => array( 'Pure Dried Ajwain Leaf Powder (Trachyspermum ammi / Plectranthus amboinicus)' ),
					'variations'   => array(
						array( 'size' => '50g',   'price' => 'Rs. 199',   'price_old' => 'Rs. 280',   'discount' => '29%' ),
						array( 'size' => '100g',  'price' => 'Rs. 349',   'price_old' => 'Rs. 480',   'discount' => '27%' ),
						array( 'size' => '250g',  'price' => 'Rs. 749',   'price_old' => 'Rs. 950',   'discount' => '21%' ),
						array( 'size' => '500g',  'price' => 'Rs. 1,399', 'price_old' => 'Rs. 1,750', 'discount' => '20%' ),
						array( 'size' => '1000g', 'price' => 'Rs. 2,499', 'price_old' => 'Rs. 3,200', 'discount' => '22%' ),
					),
					'page_url'     => home_url( '/product-detail/' ),
				),
			);
		}

		return $products;
	}
endif;

if ( ! function_exists( 'fardan_get_demo_products' ) ) :
	function fardan_get_demo_products() {
		return fardan_get_products();
	}
endif;

/* ─────────────────────────────────────────────
   Dynamic Categories Fetcher
───────────────────────────────────────────── */
if ( ! function_exists( 'fardan_get_categories' ) ) :
	/**
	 * Get product categories dynamically from WooCommerce / WP taxonomy.
	 */
	function fardan_get_categories() {
		$categories = array();
		$taxonomy   = class_exists( 'WooCommerce' ) ? 'product_cat' : 'category';

		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number'     => 8,
		) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$styles = array( 'category-card--herbs', 'category-card--oils', 'category-card--teas', 'category-card--supplements' );
			$icons  = array( '🌿', '🏺', '💧', '🧪', '🌱', '🌸' );

			foreach ( $terms as $index => $term ) {
				$img_url = '';
				if ( class_exists( 'WooCommerce' ) ) {
					$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
					if ( $thumb_id ) {
						$img_url = wp_get_attachment_image_url( $thumb_id, 'medium' );
					}
				}

				$categories[] = array(
					'id'    => 'cat-' . $term->slug,
					'name'  => $term->name,
					'slug'  => $term->slug,
					'desc'  => $term->description ? $term->description : sprintf( __( 'Explore %s', 'fardan-hikmat' ), $term->name ),
					'count' => sprintf( _n( '%d Product', '%d Products', $term->count, 'fardan-hikmat' ), $term->count ),
					'icon'  => $icons[ $index % count( $icons ) ],
					'img'   => $img_url,
					'style' => $styles[ $index % count( $styles ) ],
					'url'   => get_term_link( $term ),
				);
			}
		}

		// Fallback categories matching Fardan Al-Hikmat store
		if ( empty( $categories ) ) {
			$assets = get_template_directory_uri() . '/assets/images/';
			$categories = array(
				array(
					'id'    => 'cat-herbs',
					'name'  => __( 'Herbs & Raw Materials', 'fardan-hikmat' ),
					'slug'  => 'herbs',
					'desc'  => __( 'Jari Bootiyan & Pure Organic Herbs', 'fardan-hikmat' ),
					'count' => __( '2 Products', 'fardan-hikmat' ),
					'icon'  => '🌿',
					'img'   => $assets . 'product-agave.jpg',
					'style' => 'category-card--herbs',
					'url'   => '#shop',
				),
				array(
					'id'    => 'cat-majoon',
					'name'  => __( 'Majoon & Khameere', 'fardan-hikmat' ),
					'slug'  => 'majoon',
					'desc'  => __( 'Traditional Herbal Electuaries', 'fardan-hikmat' ),
					'count' => __( 'Available', 'fardan-hikmat' ),
					'icon'  => '🏺',
					'img'   => $assets . 'product-turmeric.jpg',
					'style' => 'category-card--supplements',
					'url'   => '#shop',
				),
				array(
					'id'    => 'cat-arqiyat',
					'name'  => __( 'Arqiyat (Pure Extracts)', 'fardan-hikmat' ),
					'slug'  => 'arqiyat',
					'desc'  => __( 'Distilled Botanical Extracts', 'fardan-hikmat' ),
					'count' => __( 'Available', 'fardan-hikmat' ),
					'icon'  => '💧',
					'img'   => $assets . 'product-lavender.jpg',
					'style' => 'category-card--oils',
					'url'   => '#shop',
				),
				array(
					'id'    => 'cat-syrups',
					'name'  => __( 'Syrups & Tinctures', 'fardan-hikmat' ),
					'slug'  => 'syrups',
					'desc'  => __( 'Concentrated Elixirs & Oils', 'fardan-hikmat' ),
					'count' => __( 'Available', 'fardan-hikmat' ),
					'icon'  => '🧪',
					'img'   => $assets . 'product-moringa.jpg',
					'style' => 'category-card--teas',
					'url'   => '#shop',
				),
			);
		}

		return $categories;
	}
endif;

/* ─────────────────────────────────────────────
   Document Title
───────────────────────────────────────────── */
add_filter( 'wp_title', 'fardan_wp_title', 10, 2 );

function fardan_wp_title( $title, $sep ) {
	if ( is_feed() ) {
		return $title;
	}
	$site_name = get_bloginfo( 'name', 'display' );
	return $title ? $title . $sep . ' ' . $site_name : $site_name;
}

/* ─────────────────────────────────────────────
   Body Classes
───────────────────────────────────────────── */
add_filter( 'body_class', 'fardan_body_classes' );

function fardan_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'is-front-page';
	}
	if ( is_singular() ) {
		$classes[] = 'is-singular';
	}
	return array_map( 'sanitize_html_class', $classes );
}

/* ─────────────────────────────────────────────
   Excerpt Length
───────────────────────────────────────────── */
add_filter( 'excerpt_length', function() { return 20; } );
add_filter( 'excerpt_more',   function() { return '&hellip;'; } );

/* ─────────────────────────────────────────────
   Security: Remove WordPress version
───────────────────────────────────────────── */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* ─────────────────────────────────────────────
   AJAX: Newsletter Subscription (Demo)
───────────────────────────────────────────── */
add_action( 'wp_ajax_fardan_subscribe',        'fardan_handle_newsletter' );
add_action( 'wp_ajax_nopriv_fardan_subscribe', 'fardan_handle_newsletter' );

function fardan_handle_newsletter() {
	check_ajax_referer( 'fardan_nonce', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Invalid email address.', 'fardan-hikmat' ) ) );
	}

	// In production: integrate with Mailchimp / Klaviyo / etc.
	wp_send_json_success( array( 'message' => esc_html__( 'Thank you for subscribing!', 'fardan-hikmat' ) ) );
}
