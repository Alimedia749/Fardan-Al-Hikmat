<?php
/**
 * Plugin Name: Al-Hikmat Product Manager
 * Plugin URI:  https://example.com/alhikmat-product-manager
 * Description: Dedicated standalone Admin Product Manager dashboard for WooCommerce with Scheduled Sales (Start/End Date & Live Countdown Timer), Dual Descriptions (Short + Long), Dynamic Category Management Engine (Add/Remove + Thumbnail & Banner Gallery), Header Action Bar Integration, Parent Product Meta Price Sync & Database Lookup Rebuild, Front-End Navigation & Custom Theme Loop Override Fix, Dynamic Repeater Limit Guardrail (Max 8 Variations), Variable Price Range Discount Formatting, Store Frontend Discount Badge filter, Global Discount %, Live Preview, Unlimited Gallery, Visual Colors, Quality Grades, and Interactive Variation Table.
 * Version:     2.4.0
 * Author:      DevSuite
 * Text Domain: alhikmat-pm
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// HPOS Compatibility
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

// Override WoodMart / Custom Theme Quick View & Force Direct Product Links
add_filter( 'woodmart_quick_view_btn', '__return_false', 999 );
add_filter( 'woodmart_quick_view_enabled', '__return_false', 999 );
add_filter( 'woodmart_show_quick_view_btn', '__return_false', 999 );

add_action( 'wp_enqueue_scripts', function() {
    wp_register_script( 'ahpm-frontend-fix', false );
    wp_enqueue_script( 'ahpm-frontend-fix' );
    $home_url_json = wp_json_encode( home_url( '/' ) );
    $frontend_fix_js = <<<'JS'
        document.addEventListener('DOMContentLoaded', function() {
            var homeUrl = HOME_URL_PLACEHOLDER;

            // 1. Quick View Modal Bypass & Class Stripper
            function ahpmStripQuickView() {
                var selectors = '.quick-view, .wd-open-qv, .woodmart-open-quick-view, .open-qv, .open-quick-view, .wd-quick-view-btn, a[data-open-quick-view], [data-plugin-quickview], [data-open-drawer], .product-card__quick-view';
                document.querySelectorAll(selectors).forEach(function(el) {
                    el.classList.remove('quick-view', 'wd-open-qv', 'woodmart-open-quick-view', 'open-qv', 'open-quick-view', 'wd-quick-view-btn');
                    el.removeAttribute('data-open-quick-view');
                    el.removeAttribute('data-open-drawer');
                    el.removeAttribute('data-id');
                    el.removeAttribute('data-product_id');
                    el.removeAttribute('data-action');
                    el.removeAttribute('data-target');
                });
            }

            ahpmStripQuickView();
            setTimeout(ahpmStripQuickView, 500);
            setTimeout(ahpmStripQuickView, 1500);
            setInterval(ahpmStripQuickView, 3000);

            // 2. Global Event Interceptor - Force Direct Permalink Redirection
            document.addEventListener('click', function(e) {
                var qvTrigger = e.target.closest('.quick-view, .wd-open-qv, .woodmart-open-quick-view, .open-qv, .open-quick-view, .wd-quick-view-btn, a[data-open-quick-view], [data-open-drawer], .product-card__quick-view');
                var productCard = e.target.closest('.product, .product-grid-item, .wd-product, .product-item, .product-card, .product-card-wrapper');
                var link = e.target.closest('a');

                if (qvTrigger || (productCard && link)) {
                    var href = (link && link.getAttribute('href')) ? link.getAttribute('href') : null;
                    if (!href || href === '#' || href.startsWith('javascript:')) {
                        if (productCard) {
                            var permalinkAnchor = productCard.querySelector('a[href*="/product/"], a.woocommerce-LoopProduct-link, .product-element-top a, .product-title a, .product-card__title a, a[href]');
                            if (permalinkAnchor) {
                                href = permalinkAnchor.getAttribute('href');
                            }
                        }
                    }

                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        window.location.href = href;
                        return false;
                    }
                }
            }, true);

            // 3. Header Navigation, Category Dropdowns & Anchor Target Fix
            function ahpmUnblockHeaderNavigation() {
                var navLinks = document.querySelectorAll('nav a, header a, .navbar__menu a, .site-header a, .footer__links a, .sub-menu a, .wd-dropdown a, .mega-menu-item a, .menu-item a');
                navLinks.forEach(function(anchor) {
                    var href = anchor.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:')) {
                        return;
                    }

                    if (window.getComputedStyle(anchor).pointerEvents === 'none') {
                        anchor.style.pointerEvents = 'auto';
                    }

                    if (href.startsWith('#') && href.length > 1) {
                        anchor.addEventListener('click', function(e) {
                            var targetId = href.substring(1);
                            var targetEl = document.getElementById(targetId);
                            var isHomePage = window.location.pathname === '/' || window.location.pathname === '/index.php' || document.body.classList.contains('home') || document.body.classList.contains('is-front-page');

                            if (!isHomePage || !targetEl) {
                                window.location.href = homeUrl + href;
                            } else {
                                e.preventDefault();
                                var offset = 80;
                                var top = targetEl.getBoundingClientRect().top + window.scrollY - offset;
                                window.scrollTo({ top: top, behavior: 'smooth' });
                            }
                        });
                    } else if (href.indexOf('http') === 0 || href.indexOf('/') === 0) {
                        anchor.addEventListener('click', function(e) {
                            if (e.defaultPrevented && !anchor.classList.contains('has-child')) {
                                window.location.href = href;
                            }
                        });
                    }
                });
            }

            ahpmUnblockHeaderNavigation();
            setTimeout(ahpmUnblockHeaderNavigation, 500);
            setTimeout(ahpmUnblockHeaderNavigation, 1500);

            // 5. Dynamic Category Dropdown & Tab Filter Switcher
            function ahpmHandleCategoryClick(categorySlug) {
                if (!categorySlug) return;
                var cleanSlug = categorySlug.replace('#', '').toLowerCase();

                var tabBtns = document.querySelectorAll('.tab-btn[data-tab]');
                var targetBtn = null;

                tabBtns.forEach(function(btn) {
                    var btnTab = (btn.getAttribute('data-tab') || '').toLowerCase();
                    if (btnTab === cleanSlug || btnTab.indexOf(cleanSlug) !== -1 || cleanSlug.indexOf(btnTab) !== -1) {
                        targetBtn = btn;
                    }
                });

                if (targetBtn) {
                    targetBtn.click();
                    var shopSection = document.getElementById('shop') || document.getElementById('products');
                    if (shopSection) {
                        var offset = 80;
                        var top = shopSection.getBoundingClientRect().top + window.scrollY - offset;
                        window.scrollTo({ top: top, behavior: 'smooth' });
                    }
                }
            }

            document.querySelectorAll('.navbar__dropdown-item, [data-category-slug]').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    var catSlug = this.getAttribute('data-category-slug') || (this.getAttribute('href') ? this.getAttribute('href').replace('#', '') : '');
                    var isHomePage = window.location.pathname === '/' || window.location.pathname === '/index.php' || document.body.classList.contains('home') || document.body.classList.contains('is-front-page');

                    if (isHomePage && catSlug && catSlug !== '#' && !catSlug.startsWith('http') && !catSlug.startsWith('/')) {
                        e.preventDefault();
                        ahpmHandleCategoryClick(catSlug);
                    }
                });
            });

            if (window.location.hash && window.location.hash.length > 1) {
                setTimeout(function() {
                    ahpmHandleCategoryClick(window.location.hash.substring(1));
                }, 300);
            }

            // 4. Live Sale Countdown Timer Handler
            function ahpmUpdateSaleCountdowns() {
                document.querySelectorAll('[data-countdown-end]').forEach(function(el) {
                    var endIso = el.getAttribute('data-countdown-end');
                    if (!endIso) return;
                    var endDate = new Date(endIso).getTime();
                    var now = new Date().getTime();
                    var diff = endDate - now;
                    var textSpan = el.querySelector('.ahpm-timer-text') || el;

                    if (diff <= 0) {
                        el.innerHTML = 'Sale Ended';
                        return;
                    }

                    var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    var secs = Math.floor((diff % (1000 * 60)) / 1000);

                    var str = '';
                    if (days > 0) str += days + 'd ';
                    str += (hours < 10 ? '0' : '') + hours + 'h ';
                    str += (mins < 10 ? '0' : '') + mins + 'm ';
                    str += (secs < 10 ? '0' : '') + secs + 's';

                    if (textSpan) textSpan.textContent = str;
                });
            }

            setInterval(ahpmUpdateSaleCountdowns, 1000);
            ahpmUpdateSaleCountdowns();

            // 6. Live Search Modal Engine
            window.ahpmOpenSearch = function() {
                var searchOverlay = document.getElementById('ahpm-search-overlay');
                var searchInput   = document.getElementById('ahpm-search-input');
                if (searchOverlay) {
                    searchOverlay.style.display = 'flex';
                    if (searchInput) {
                        setTimeout(function() { searchInput.focus(); }, 100);
                    }
                }
            };

            window.ahpmCloseSearch = function() {
                var searchOverlay = document.getElementById('ahpm-search-overlay');
                if (searchOverlay) {
                    searchOverlay.style.display = 'none';
                }
            };

            var searchBtn    = document.getElementById('navbar-search');
            var searchOverlay= document.getElementById('ahpm-search-overlay');
            var searchClose  = document.getElementById('ahpm-search-close');
            var searchInput  = document.getElementById('ahpm-search-input');
            var searchResults= document.getElementById('ahpm-search-results');
            var searchTimer  = null;

            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.ahpmOpenSearch();
                });
            }

            if (searchClose) {
                searchClose.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.ahpmCloseSearch();
                });
            }

            if (searchOverlay) {
                searchOverlay.addEventListener('click', function(e) {
                    if (e.target === searchOverlay) {
                        window.ahpmCloseSearch();
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.ahpmCloseSearch();
                }
            });

            window.ahpmFillSearch = function(term) {
                if (searchInput) {
                    searchInput.value = term;
                    ahpmPerformLiveSearch(term);
                }
            };

            function ahpmPerformLiveSearch(query) {
                if (!searchResults) return;
                if (!query || query.length < 2) {
                    searchResults.innerHTML = '<div style="font-size:11px; color:#C49A1A; font-weight:800; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">🌿 Quick Suggestions</div>' +
                        '<div style="display:flex; flex-wrap:wrap; gap:8px;">' +
                        '<button type="button" onclick="ahpmFillSearch(\'Agave\')" style="background:rgba(45,80,22,0.35); color:#FAFAF7; border:1px solid rgba(196,154,26,0.35); padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600;">🌿 Agave</button>' +
                        '<button type="button" onclick="ahpmFillSearch(\'Ajwain\')" style="background:rgba(45,80,22,0.35); color:#FAFAF7; border:1px solid rgba(196,154,26,0.35); padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600;">🌱 Ajwain</button>' +
                        '<button type="button" onclick="ahpmFillSearch(\'Anise\')" style="background:rgba(45,80,22,0.35); color:#FAFAF7; border:1px solid rgba(196,154,26,0.35); padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600;">🌸 Anise Hyssop</button>' +
                        '<button type="button" onclick="ahpmFillSearch(\'Gooseberry\')" style="background:rgba(45,80,22,0.35); color:#FAFAF7; border:1px solid rgba(196,154,26,0.35); padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600;">🍒 Gooseberry</button>' +
                        '<button type="button" onclick="ahpmFillSearch(\'Angelica\')" style="background:rgba(45,80,22,0.35); color:#FAFAF7; border:1px solid rgba(196,154,26,0.35); padding:6px 14px; border-radius:20px; font-size:12px; cursor:pointer; font-weight:600;">🪵 Angelica Root</button>' +
                        '</div>';
                    return;
                }

                searchResults.innerHTML = '<div style="padding:24px; text-align:center; color:#C49A1A; font-weight:700;">🔍 Searching herbal products for "' + query + '"...</div>';

                var ajaxUrl = homeUrl + 'wp-admin/admin-ajax.php';
                fetch(ajaxUrl + '?action=ahpm_live_search&query=' + encodeURIComponent(query))
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.success && data.data.results && data.data.results.length > 0) {
                            var html = '<div style="font-size:11px; color:#C49A1A; font-weight:800; text-transform:uppercase; margin-bottom:12px; letter-spacing:0.5px;">Found ' + data.data.results.length + ' Herbal Product(s)</div>';
                            data.data.results.forEach(function(item) {
                                html += '<a href="' + item.permalink + '" style="display:flex; align-items:center; gap:14px; padding:12px; border-radius:12px; background:rgba(22,56,43,0.5); border:1px solid rgba(196,154,26,0.25); margin-bottom:10px; text-decoration:none; color:#FAFAF7; transition:all 0.2s ease;">' +
                                    '<img src="' + item.image + '" style="width:52px; height:52px; object-fit:cover; border-radius:10px; border:1px solid rgba(196,154,26,0.3);" />' +
                                    '<div style="flex:1; min-width:0;">' +
                                    '<div style="font-size:11px; color:#C49A1A; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">' + item.category + '</div>' +
                                    '<div style="font-size:14px; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#FAFAF7;">' + item.title + '</div>' +
                                    '</div>' +
                                    '<div style="font-size:13px; font-weight:800; color:#34d399; background:rgba(45,80,22,0.6); padding:4px 10px; border-radius:8px; border:1px solid rgba(52,211,153,0.3);">' + item.price + '</div>' +
                                    '</a>';
                            });
                            searchResults.innerHTML = html;
                        } else {
                            searchResults.innerHTML = '<div style="padding:24px; text-align:center; color:#D5CEBC;">No matching herbal products found for "' + query + '". Try another keyword.</div>';
                        }
                    })
                    .catch(function(err) {
                        searchResults.innerHTML = '<div style="padding:24px; text-align:center; color:#f87171;">Search request failed. Please try again.</div>';
                    });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    var val = this.value.trim();
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        ahpmPerformLiveSearch(val);
                    }, 250);
                });
            // Slide-Out Mini Cart Drawer Engine
            window.ahpmOpenCartDrawer = function() {
                var overlay = document.getElementById('ahpm-mini-cart-overlay');
                var panel   = document.getElementById('ahpm-mini-cart-panel');
                if (overlay && panel) {
                    overlay.style.display = 'block';
                    setTimeout(function() {
                        panel.style.transform = 'translateX(0)';
                    }, 10);
                    window.ahpmFetchCartDrawer();
                }
            };

            window.ahpmCloseCartDrawer = function() {
                var overlay = document.getElementById('ahpm-mini-cart-overlay');
                var panel   = document.getElementById('ahpm-mini-cart-panel');
                if (overlay && panel) {
                    panel.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        overlay.style.display = 'none';
                    }, 300);
                }
            };

            var cartOverlay = document.getElementById('ahpm-mini-cart-overlay');
            if (cartOverlay) {
                cartOverlay.addEventListener('click', function(e) {
                    if (e.target === cartOverlay) {
                        window.ahpmCloseCartDrawer();
                    }
                });
            }

            window.ahpmFetchCartDrawer = function() {
                var itemsContainer = document.getElementById('ahpm-mini-cart-items');
                var subtotalEl     = document.getElementById('ahpm-cart-subtotal');
                var badgeEl        = document.querySelector('#navbar-cart .navbar__badge');

                var ajaxUrl = homeUrl + 'wp-admin/admin-ajax.php';
                fetch(ajaxUrl + '?action=ahpm_get_cart_drawer')
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.success && data.data) {
                            if (itemsContainer) itemsContainer.innerHTML = data.data.html;
                            if (subtotalEl) subtotalEl.innerHTML = data.data.subtotal;
                            if (badgeEl) badgeEl.textContent = data.data.total_count;
                        }
                    });
            };

            window.ahpmRemoveCartItem = function(cartItemKey) {
                var itemsContainer = document.getElementById('ahpm-mini-cart-items');
                if (itemsContainer) itemsContainer.innerHTML = '<div style="text-align:center; padding:30px; color:#64748b;">Updating cart...</div>';

                var ajaxUrl = homeUrl + 'wp-admin/admin-ajax.php';
                var formData = new FormData();
                formData.append('action', 'ahpm_remove_cart_item');
                formData.append('cart_item_key', cartItemKey);

                fetch(ajaxUrl, { method: 'POST', body: formData })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.success && data.data) {
                            var subtotalEl = document.getElementById('ahpm-cart-subtotal');
                            var badgeEl    = document.querySelector('#navbar-cart .navbar__badge');
                            if (itemsContainer) itemsContainer.innerHTML = data.data.html;
                            if (subtotalEl) subtotalEl.innerHTML = data.data.subtotal;
                            if (badgeEl) badgeEl.textContent = data.data.total_count;
                        }
                    });
            };

            // Initial badge sync on load
            setTimeout(function() {
                if (typeof window.ahpmFetchCartDrawer === 'function') {
                    window.ahpmFetchCartDrawer();
                }
            }, 500);

            // Intercept Single Product Add to Cart Form Submissions & Add Buttons
            document.addEventListener('click', function(e) {
                var addBtn = e.target.closest('.single_add_to_cart_button, .add_to_cart_button, #drawer-add-to-cart');
                if (!addBtn) return;

                var form = addBtn.closest('form.cart') || addBtn.closest('form');
                var productIdInput = form ? form.querySelector('[name="add-to-cart"], [name="product_id"]') : null;
                var productId = productIdInput ? productIdInput.value : addBtn.getAttribute('data-product_id');
                var qtyInput = form ? form.querySelector('[name="quantity"]') : null;
                var quantity = qtyInput ? qtyInput.value : 1;
                var varInput = form ? form.querySelector('[name="variation_id"]') : null;
                var variationId = varInput ? varInput.value : 0;

                if (productId) {
                    e.preventDefault();
                    addBtn.disabled = true;
                    var origText = addBtn.innerHTML;
                    addBtn.innerHTML = 'Adding...';

                    var formData = new FormData();
                    formData.append('action', 'ahpm_add_to_cart');
                    formData.append('product_id', productId);
                    formData.append('quantity', quantity);
                    formData.append('variation_id', variationId);

                    var ajaxUrl = homeUrl + 'wp-admin/admin-ajax.php';
                    fetch(ajaxUrl, { method: 'POST', body: formData })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            addBtn.disabled = false;
                            addBtn.innerHTML = origText;
                            if (data && data.success) {
                                window.ahpmOpenCartDrawer();
                            } else {
                                if (form) form.submit();
                            }
                        })
                        .catch(function() {
                            addBtn.disabled = false;
                            addBtn.innerHTML = origText;
                            if (form) form.submit();
                        });
                }
            });
        });
JS;
    $frontend_fix_js = str_replace( 'HOME_URL_PLACEHOLDER', $home_url_json, $frontend_fix_js );
    wp_add_inline_script( 'ahpm-frontend-fix', $frontend_fix_js );
}, 999 );

// ═════════════════════════════════════════════════════════════════
// LIVE MINI CART DRAWER & AJAX ADD TO CART ENGINE
// ═════════════════════════════════════════════════════════════════
add_action( 'wp_ajax_ahpm_get_cart_drawer', 'alhikmat_get_cart_drawer_ajax' );
add_action( 'wp_ajax_nopriv_ahpm_get_cart_drawer', 'alhikmat_get_cart_drawer_ajax' );

add_action( 'wp_ajax_ahpm_add_to_cart', 'alhikmat_add_to_cart_ajax' );
add_action( 'wp_ajax_nopriv_ahpm_add_to_cart', 'alhikmat_add_to_cart_ajax' );

add_action( 'wp_ajax_ahpm_remove_cart_item', 'alhikmat_remove_cart_item_ajax' );
add_action( 'wp_ajax_nopriv_ahpm_remove_cart_item', 'alhikmat_remove_cart_item_ajax' );

function alhikmat_get_cart_drawer_ajax() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_send_json_error( array( 'message' => 'WooCommerce not active' ) );
    }

    $cart       = WC()->cart;
    $items      = $cart->get_cart();
    $total_cnt  = $cart->get_cart_contents_count();
    $subtotal   = $cart->get_cart_subtotal();
    $items_html = '';

    if ( empty( $items ) ) {
        $items_html = '<div style="text-align:center; padding:50px 20px; color:#64748b;">' .
            '<div style="font-size:3.5rem; margin-bottom:12px;">🛒</div>' .
            '<h4 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:6px;">Your cart is empty</h4>' .
            '<p style="font-size:13px; margin-bottom:20px;">Explore our organic herbal remedies and add your favorites to cart.</p>' .
            '<a href="' . esc_url( home_url( '/#shop' ) ) . '" class="btn btn-primary" onclick="ahpmCloseCartDrawer();" style="padding:10px 20px; border-radius:8px; background:#2D5016; color:#fff; text-decoration:none; font-size:13px; font-weight:700; display:inline-block;">Shop All Botanicals</a>' .
            '</div>';
    } else {
        foreach ( $items as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 ) {
                $product_name  = $_product->get_name();
                $thumbnail     = $_product->get_image( array( 60, 60 ), array( 'style' => 'width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0;' ) );
                $product_price = WC()->cart->get_product_price( $_product );
                $quantity      = $cart_item['quantity'];

                $items_html .= '<div style="display:flex; gap:14px; padding:14px 0; border-bottom:1px solid #e2e8f0; align-items:center;">' .
                    '<div>' . $thumbnail . '</div>' .
                    '<div style="flex:1; min-width:0;">' .
                    '<div style="font-size:14px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' . esc_html( $product_name ) . '</div>' .
                    '<div style="font-size:12px; color:#64748b; margin-top:2px;">Qty: <strong>' . esc_html( $quantity ) . '</strong> × ' . $product_price . '</div>' .
                    '</div>' .
                    '<button type="button" onclick="ahpmRemoveCartItem(\'' . esc_attr( $cart_item_key ) . '\')" style="background:transparent; border:none; color:#ef4444; font-size:18px; cursor:pointer; padding:4px 8px; line-height:1;" title="Remove item">🗑️</button>' .
                    '</div>';
            }
        }
    }

    wp_send_json_success( array(
        'html'        => $items_html,
        'subtotal'    => $subtotal,
        'total_count' => $total_cnt,
    ) );
}

function alhikmat_add_to_cart_ajax() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_send_json_error( array( 'message' => 'WooCommerce not active' ) );
    }

    $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
    $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
    $variations   = array();

    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Invalid product' ) );
    }

    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        wp_send_json_error( array( 'message' => 'Product not found' ) );
    }

    // Auto-resolve variation if variable product and no variation selected
    if ( $product->is_type( 'variable' ) && ! $variation_id ) {
        $children = $product->get_children();
        if ( ! empty( $children ) ) {
            $variation_id = $children[0]; // Select default variation e.g. 50g
            $var_obj      = wc_get_product( $variation_id );
            if ( $var_obj ) {
                $variations = $var_obj->get_variation_attributes();
            }
        }
    }

    $added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );

    if ( $added ) {
        alhikmat_get_cart_drawer_ajax();
    } else {
        wp_send_json_error( array( 'message' => 'Failed to add item to cart' ) );
    }
}

function alhikmat_remove_cart_item_ajax() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_send_json_error( array( 'message' => 'WooCommerce not active' ) );
    }

    $cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : '';
    if ( $cart_item_key ) {
        WC()->cart->remove_cart_item( $cart_item_key );
    }

    alhikmat_get_cart_drawer_ajax();
}

// Live AJAX Product Search Handler
add_action( 'wp_ajax_ahpm_live_search', 'alhikmat_live_product_search' );
add_action( 'wp_ajax_nopriv_ahpm_live_search', 'alhikmat_live_product_search' );

function alhikmat_live_product_search() {
    $search = isset( $_GET['query'] ) ? sanitize_text_field( $_GET['query'] ) : '';
    if ( empty( $search ) || strlen( $search ) < 2 ) {
        wp_send_json_success( array( 'results' => array() ) );
    }

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $search,
        'posts_per_page' => 8,
    );

    $query = new WP_Query( $args );
    $results = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $product_id = get_the_ID();
            $product    = wc_get_product( $product_id );

            if ( ! $product ) continue;

            $cats     = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );
            $cat_name = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0] : 'Herbs & Botanicals';

            $results[] = array(
                'id'         => $product_id,
                'title'      => get_the_title(),
                'permalink'  => get_permalink( $product_id ),
                'image'      => get_the_post_thumbnail_url( $product_id, 'thumbnail' ) ?: wc_placeholder_img_src(),
                'price'      => $product->get_price_html(),
                'category'   => $cat_name,
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success( array( 'results' => $results ) );
}

/**
 * Helper: Check if a product's sale is currently active based on schedule
 */
function alhikmat_is_sale_active( $product_id ) {
    $sale_start = get_post_meta( $product_id, '_ahpm_sale_start', true );
    $sale_end   = get_post_meta( $product_id, '_ahpm_sale_end', true );

    $now = current_time( 'timestamp' );

    if ( ! empty( $sale_start ) ) {
        $start_ts = strtotime( $sale_start );
        if ( $start_ts && $now < $start_ts ) {
            return false; // Sale has not started yet
        }
    }

    if ( ! empty( $sale_end ) ) {
        $end_ts = strtotime( $sale_end );
        if ( $end_ts && $now > $end_ts ) {
            return false; // Sale has ended
        }
    }

    return true; // Sale is active!
}

// Frontend Dynamic Sale Price Badge Filter (For Simple Products)
add_filter( 'woocommerce_format_sale_price', function( $price_html, $regular_price, $sale_price ) {
    global $product;
    if ( is_a( $product, 'WC_Product' ) && ! alhikmat_is_sale_active( $product->get_id() ) ) {
        return wc_price( $regular_price );
    }

    $reg  = floatval( $regular_price );
    $sale = floatval( $sale_price );
    if ( $reg > 0 && $sale < $reg ) {
        $pct   = round( ( ( $reg - $sale ) / $reg ) * 100 );
        $badge = ' <span class="price-range-discount-badge">' . $pct . '% OFF</span>';
        return $price_html . $badge;
    }
    return $price_html;
}, 10, 3 );

// Custom Variable Price Range HTML Filter (`woocommerce_variable_price_html`)
add_filter( 'woocommerce_variable_price_html', 'alhikmat_custom_variable_price_html', 10, 2 );
function alhikmat_custom_variable_price_html( $price_html, $product ) {
    if ( ! is_object( $product ) || ! $product->is_type( 'variable' ) ) {
        return $price_html;
    }

    $product_id = $product->get_id();
    $min_regular_price = floatval( $product->get_variation_regular_price( 'min', true ) );
    $max_regular_price = floatval( $product->get_variation_regular_price( 'max', true ) );

    $min_price = floatval( $product->get_variation_price( 'min', true ) );
    $max_price = floatval( $product->get_variation_price( 'max', true ) );

    // Regular Price Range String
    $reg_range_str = ( $min_regular_price === $max_regular_price )
        ? wc_price( $min_regular_price )
        : wc_price( $min_regular_price ) . ' – ' . wc_price( $max_regular_price );

    // Check if Sale Schedule is Active
    if ( ! alhikmat_is_sale_active( $product_id ) || ! $product->is_on_sale() || $min_regular_price <= 0 ) {
        return '<span class="price-range-regular-only">' . $reg_range_str . '</span>';
    }

    // Calculate minimum & maximum discount percentages across active variations
    $max_discount_pct = 0;
    $min_discount_pct = 100;
    $available_variations = $product->get_available_variations();

    if ( ! empty( $available_variations ) ) {
        foreach ( $available_variations as $var_data ) {
            $v_reg  = floatval( isset( $var_data['display_regular_price'] ) ? $var_data['display_regular_price'] : 0 );
            $v_sale = floatval( isset( $var_data['display_price'] ) ? $var_data['display_price'] : 0 );
            if ( $v_reg > 0 && $v_sale < $v_reg ) {
                $pct = round( ( ( $v_reg - $v_sale ) / $v_reg ) * 100 );
                if ( $pct > $max_discount_pct ) {
                    $max_discount_pct = $pct;
                }
                if ( $pct < $min_discount_pct ) {
                    $min_discount_pct = $pct;
                }
            }
        }
    }

    if ( $max_discount_pct > 0 ) {
        // Format active discounted price range
        $sale_str = ( $min_price === $max_price )
            ? wc_price( $min_price )
            : wc_price( $min_price ) . ' – ' . wc_price( $max_price );

        // Format dynamic discount badge label
        $badge_label = ( $min_discount_pct === $max_discount_pct )
            ? $max_discount_pct . '% OFF'
            : 'Up to ' . $max_discount_pct . '% OFF';

        $badge = '<span class="price-range-discount-badge">' . esc_html( $badge_label ) . '</span>';

        // Check for Countdown Timer
        $sale_end = get_post_meta( $product_id, '_ahpm_sale_end', true );
        $timer_html = '';
        if ( ! empty( $sale_end ) ) {
            $end_ts = strtotime( $sale_end );
            if ( $end_ts && $end_ts > current_time( 'timestamp' ) ) {
                $end_iso = date( 'c', $end_ts );
                $timer_html = ' <span class="ahpm-sale-countdown-badge" data-countdown-end="' . esc_attr( $end_iso ) . '">⏰ Ends in: <span class="ahpm-timer-text">--:--:--</span></span>';
            }
        }

        return '<del class="price-range-del">' . $reg_range_str . '</del> <ins class="price-range-ins">' . $sale_str . '</ins> ' . $badge . $timer_html;
    }

    return $price_html;
}

// Frontend Inline CSS Styling for Main Price Badge, Strikethrough & Countdown Timer
add_action( 'wp_head', function() {
    ?>
    <style id="alhikmat-price-range-badge-css">
        .price-range-del,
        .price-range-del .woocommerce-Price-amount,
        del,
        del .woocommerce-Price-amount,
        .product-card__price-old,
        .drawer-price-old {
            color: #4A5568 !important;
            text-decoration: line-through !important;
            font-size: 0.95em !important;
            margin-right: 6px !important;
            font-weight: 600 !important;
            opacity: 1 !important;
        }
        .price-range-ins,
        .price-range-ins .woocommerce-Price-amount,
        ins,
        ins .woocommerce-Price-amount,
        .product-card__price-current,
        .drawer-price-current {
            color: #10b981 !important;
            font-weight: 800 !important;
            text-decoration: none !important;
            margin-right: 6px !important;
        }
        .price-range-discount-badge {
            background: #10B981;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 6px;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(16,185,129,0.3);
            text-transform: uppercase;
            line-height: 1.2;
        }
        .ahpm-sale-countdown-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 12px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 6px;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(245,158,11,0.3);
            line-height: 1.2;
        }
    </style>
    <?php
} );

/**
 * Parent Product Meta Sync & WooCommerce Price Cache/Lookup Rebuild Routine
 *
 * @param int $product_id
 */
function alhikmat_sync_parent_product_prices( $product_id ) {
    $product_id = absint( $product_id );
    if ( ! $product_id ) {
        return;
    }

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        return;
    }

    $child_posts = get_posts( array(
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'numberposts' => -1,
        'post_status' => array( 'publish', 'private' ),
    ) );

    if ( empty( $child_posts ) ) {
        return;
    }

    $min_reg   = null;
    $max_reg   = null;
    $min_sale  = null;
    $max_sale  = null;
    $min_price = null;
    $max_price = null;

    foreach ( $child_posts as $cp ) {
        $variation = wc_get_product( $cp->ID );
        if ( ! $variation ) {
            continue;
        }

        $vr     = floatval( $variation->get_regular_price() );
        $vs_raw = $variation->get_sale_price();
        $vs     = ( '' !== $vs_raw && false !== $vs_raw && null !== $vs_raw ) ? floatval( $vs_raw ) : null;
        $vp     = floatval( $variation->get_price() );

        if ( $vr > 0 ) {
            $min_reg = ( null === $min_reg ) ? $vr : min( $min_reg, $vr );
            $max_reg = ( null === $max_reg ) ? $vr : max( $max_reg, $vr );
        }

        if ( null !== $vs && $vs > 0 ) {
            $min_sale = ( null === $min_sale ) ? $vs : min( $min_sale, $vs );
            $max_sale = ( null === $max_sale ) ? $vs : max( $max_sale, $vs );
        }

        if ( $vp > 0 ) {
            $min_price = ( null === $min_price ) ? $vp : min( $min_price, $vp );
            $max_price = ( null === $max_price ) ? $vp : max( $max_price, $vp );
        }
    }

    // Direct key values saved to main parent product meta
    $final_min_price = ( null !== $min_price && $min_price > 0 ) ? $min_price : ( ( null !== $min_sale && $min_sale > 0 ) ? $min_sale : ( ( null !== $min_reg ) ? $min_reg : 0 ) );
    $final_max_price = ( null !== $max_price && $max_price > 0 ) ? $max_price : ( ( null !== $max_sale && $max_sale > 0 ) ? $max_sale : ( ( null !== $max_reg ) ? $max_reg : 0 ) );
    $final_min_reg   = ( null !== $min_reg && $min_reg > 0 ) ? $min_reg : $final_min_price;
    $final_max_reg   = ( null !== $max_reg && $max_reg > 0 ) ? $max_reg : $final_max_price;
    $final_min_sale  = ( null !== $min_sale && $min_sale > 0 && $min_sale < $final_min_reg ) ? $min_sale : '';
    $final_max_sale  = ( null !== $max_sale && $max_sale > 0 && $max_sale < $final_max_reg ) ? $max_sale : '';

    $is_sale_active = alhikmat_is_sale_active( $product_id );

    if ( ! $is_sale_active ) {
        update_post_meta( $product_id, '_price', $final_min_reg );
        update_post_meta( $product_id, '_regular_price', $final_min_reg );
        update_post_meta( $product_id, '_sale_price', '' );

        update_post_meta( $product_id, '_min_variation_price', $final_min_reg );
        update_post_meta( $product_id, '_max_variation_price', $final_max_reg );
        update_post_meta( $product_id, '_min_variation_regular_price', $final_min_reg );
        update_post_meta( $product_id, '_max_variation_regular_price', $final_max_reg );
        update_post_meta( $product_id, '_min_variation_sale_price', '' );
        update_post_meta( $product_id, '_max_variation_sale_price', '' );
    } else {
        update_post_meta( $product_id, '_price', $final_min_price );
        update_post_meta( $product_id, '_regular_price', $final_min_reg );
        update_post_meta( $product_id, '_sale_price', $final_min_sale );

        update_post_meta( $product_id, '_min_variation_price', $final_min_price );
        update_post_meta( $product_id, '_max_variation_price', $final_max_price );
        update_post_meta( $product_id, '_min_variation_regular_price', $final_min_reg );
        update_post_meta( $product_id, '_max_variation_regular_price', $final_max_reg );
        update_post_meta( $product_id, '_min_variation_sale_price', $final_min_sale );
        update_post_meta( $product_id, '_max_variation_sale_price', $final_max_sale );
    }

    // Clear transients
    wc_delete_product_transients( $product_id );
    delete_transient( 'wc_var_prices_' . $product_id );

    // Rebuild WooCommerce Price Cache & Lookup Tables
    if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
        wc_update_product_lookup_tables( $product_id );
    }

    // Native WooCommerce Variable Sync & Final Transient Purge
    WC_Product_Variable::sync( $product_id );
    wc_delete_product_transients( $product_id );
}

// Hook parent price sync and lookup rebuild routines
add_action( 'woocommerce_process_product_meta', 'alhikmat_sync_parent_product_prices', 20, 1 );
add_action( 'woocommerce_save_product_variation', function( $variation_id ) {
    $parent_id = wp_get_post_parent_id( $variation_id );
    if ( $parent_id ) {
        alhikmat_sync_parent_product_prices( $parent_id );
    }
}, 20, 1 );

class Al_Hikmat_Product_Manager {

    private static $instance = null;
    private $nonce_action = 'ahpm_dashboard_nonce_action';
    private $nonce_field  = 'ahpm_dashboard_nonce';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin Menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'admin_init', array( $this, 'handle_form_actions' ) );

        // AJAX Category Management Handlers
        add_action( 'wp_ajax_ahpm_add_category', array( $this, 'ajax_add_category' ) );
        add_action( 'wp_ajax_ahpm_delete_category', array( $this, 'ajax_delete_category' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Product Manager', 'alhikmat-pm' ),
            __( 'Product Manager', 'alhikmat-pm' ),
            'manage_woocommerce',
            'alhikmat-product-manager',
            array( $this, 'render_dashboard_page' ),
            'dashicons-products',
            25
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_alhikmat-product-manager' !== $hook ) {
            return;
        }

        wp_enqueue_media();

        wp_register_style( 'ahpm-css', false );
        wp_enqueue_style( 'ahpm-css' );

        $custom_css = "
            .ahpm-wrapper, .ahpm-wrapper * { box-sizing: border-box; }
            .ahpm-wrapper { background: #0f172a; border-radius: 16px; color: #f8fafc; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; box-shadow: 0 15px 35px rgba(0,0,0,0.4); border: 1px solid #1e293b; margin: 20px 20px 20px 0; max-width: calc(100% - 20px); min-width: 0; }
            .ahpm-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 16px; margin-bottom: 20px; }
            .ahpm-title { font-size: 22px; font-weight: 800; color: #38bdf8; display: flex; align-items: center; gap: 12px; margin: 0; }
            .ahpm-badge { background: #0c4a6e; color: #38bdf8; font-size: 11px; font-weight: 700; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #0284c7; }

            .ahpm-layout { display: grid; grid-template-columns: 560px minmax(0, 1fr); gap: 24px; min-width: 0; }
            @media (max-width: 1200px) { .ahpm-layout { grid-template-columns: 1fr; } }

            .ahpm-card { background: #1e293b; border-radius: 12px; padding: 20px; border: 1px solid #334155; min-width: 0; }
            .ahpm-card-title { font-size: 15px; font-weight: 700; color: #e2e8f0; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 10px; }

            .ahpm-group { margin-bottom: 16px; }
            .ahpm-group label { display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
            .ahpm-input, .ahpm-select, .ahpm-textarea { width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 12px; border-radius: 8px; font-size: 13px; outline: none; transition: all 0.2s ease; box-sizing: border-box; }
            .ahpm-input:focus, .ahpm-select:focus, .ahpm-textarea:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25); }

            .ahpm-btn-primary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; font-weight: 800; font-size: 14px; padding: 12px 20px; border: none; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35); text-transform: uppercase; letter-spacing: 0.5px; }
            .ahpm-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5); color: #fff; }

            .ahpm-btn-add { background: #0c4a6e; color: #38bdf8; font-weight: 700; font-size: 12px; padding: 8px 14px; border: 1px solid #0284c7; border-radius: 6px; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 6px; }
            .ahpm-btn-add:hover { background: #0284c7; color: #fff; }
            .ahpm-btn-add:disabled { background: #1e293b !important; color: #64748b !important; border-color: #334155 !important; cursor: not-allowed !important; opacity: 0.6; }

            .ahpm-btn-remove { background: #451a03; color: #f97316; font-weight: 700; font-size: 11px; padding: 6px 10px; border: 1px solid #c2410c; border-radius: 6px; cursor: pointer; }
            .ahpm-btn-remove:hover { background: #c2410c; color: #fff; }

            /* Header Action Bar Button */
            .ahpm-header-cat-btn { background: #064e3b; color: #34d399; font-weight: 700; font-size: 12px; padding: 6px 12px; border: 1px solid #059669; border-radius: 6px; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
            .ahpm-header-cat-btn:hover { background: #059669; color: #ffffff; }

            /* Category Manager Drawer / Panel Styling */
            .ahpm-cat-panel { background: #0f172a; border: 1px solid #0284c7; border-radius: 10px; padding: 16px; margin-top: 10px; margin-bottom: 16px; display: none; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
            .ahpm-cat-panel.is-active { display: block; }
            .ahpm-cat-list-item { display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #1e293b; border-radius: 6px; margin-bottom: 6px; font-size: 12px; border: 1px solid #334155; }

            /* Repeater Table Styling */
            .ahpm-repeater-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .ahpm-repeater-table th { background: #0f172a; color: #94a3b8; text-transform: uppercase; font-size: 11px; padding: 8px; text-align: left; border-bottom: 1px solid #334155; }
            .ahpm-repeater-table td { padding: 8px; border-bottom: 1px solid #334155; vertical-align: middle; }

            .ahpm-warning-text { color: #fbbf24; font-size: 11px; font-weight: 600; margin-top: 6px; display: none; }

            /* Product Table Container Overflow Fix */
            .ahpm-table-container { overflow-x: auto; max-width: 100%; }
            .ahpm-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .ahpm-table th { background: #0f172a; color: #94a3b8; text-transform: uppercase; font-size: 11px; padding: 10px 12px; text-align: left; border-bottom: 1px solid #334155; }
            .ahpm-table td { padding: 10px 12px; border-bottom: 1px solid #334155; color: #e2e8f0; font-size: 13px; vertical-align: middle; }
            .ahpm-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; border: 1px solid #334155; background: #0f172a; }

            /* Gallery Grid Styling */
            .ahpm-gallery-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
            .ahpm-gallery-item { position: relative; width: 50px; height: 50px; border-radius: 6px; overflow: hidden; border: 1px solid #334155; }
            .ahpm-gallery-item img { width: 100%; height: 100%; object-fit: cover; }
            .ahpm-gallery-remove { position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.7); color: #fff; border-radius: 50%; width: 16px; height: 16px; line-height: 14px; text-align: center; font-size: 10px; cursor: pointer; }
            
            .ahpm-action-link { display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none; transition: all 0.15s ease; }
            .ahpm-preview-btn { background: #0284c7; color: #ffffff; border: 1px solid #38bdf8; }
            .ahpm-preview-btn:hover { background: #0369a1; color: #fff; }
            .ahpm-edit-btn { background: #0c4a6e; color: #38bdf8; border: 1px solid #0284c7; }
            .ahpm-edit-btn:hover { background: #0284c7; color: #fff; }
            .ahpm-delete-btn { background: #451a03; color: #f97316; border: 1px solid #c2410c; margin-left: 4px; }
            .ahpm-delete-btn:hover { background: #c2410c; color: #fff; }
        ";
        wp_add_inline_style( 'ahpm-css', $custom_css );
    }

    public function ajax_add_category() {
        check_ajax_referer( $this->nonce_action, 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'alhikmat-pm' ) ) );
        }

        $name       = isset( $_POST['cat_name'] ) ? sanitize_text_field( $_POST['cat_name'] ) : '';
        $parent     = isset( $_POST['cat_parent'] ) ? absint( $_POST['cat_parent'] ) : 0;
        $thumb_id   = isset( $_POST['cat_thumb_id'] ) ? absint( $_POST['cat_thumb_id'] ) : 0;
        $gallery_ids= isset( $_POST['cat_gallery_ids'] ) ? sanitize_text_field( $_POST['cat_gallery_ids'] ) : '';

        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'Category name is required.', 'alhikmat-pm' ) ) );
        }

        $args = array();
        if ( $parent > 0 ) {
            $args['parent'] = $parent;
        }

        $result = wp_insert_term( $name, 'product_cat', $args );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $term_id = $result['term_id'];

        if ( $thumb_id > 0 ) {
            update_term_meta( $term_id, 'thumbnail_id', $thumb_id );
        }

        if ( ! empty( $gallery_ids ) ) {
            update_term_meta( $term_id, '_alhikmat_cat_gallery', $gallery_ids );
        }

        $categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
        $cats_html = '<option value="0">-- Select Category --</option>';
        $manage_html = '';
        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
            foreach ( $categories as $cat ) {
                $selected = ( $cat->term_id == $term_id ) ? 'selected' : '';
                $cats_html .= '<option value="' . esc_attr( $cat->term_id ) . '" ' . $selected . '>' . esc_html( $cat->name ) . '</option>';
                $manage_html .= '<div class="ahpm-cat-list-item"><span>' . esc_html( $cat->name ) . '</span><button type="button" class="ahpm-btn-remove" onclick="ahpmDeleteCategoryAjax(' . $cat->term_id . ')">🗑️</button></div>';
            }
        }

        wp_send_json_success( array(
            'term_id'     => $term_id,
            'cats_html'   => $cats_html,
            'manage_html' => $manage_html,
            'message'     => __( 'Category created and selected!', 'alhikmat-pm' ),
        ) );
    }

    public function ajax_delete_category() {
        check_ajax_referer( $this->nonce_action, 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'alhikmat-pm' ) ) );
        }

        $term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
        if ( $term_id <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Invalid Category ID.', 'alhikmat-pm' ) ) );
        }

        $deleted = wp_delete_term( $term_id, 'product_cat' );
        if ( is_wp_error( $deleted ) || ! $deleted ) {
            wp_send_json_error( array( 'message' => __( 'Failed to delete category.', 'alhikmat-pm' ) ) );
        }

        $categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
        $cats_html = '<option value="0">-- Select Category --</option>';
        $manage_html = '';
        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
            foreach ( $categories as $cat ) {
                $cats_html .= '<option value="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . '</option>';
                $manage_html .= '<div class="ahpm-cat-list-item"><span>' . esc_html( $cat->name ) . '</span><button type="button" class="ahpm-btn-remove" onclick="ahpmDeleteCategoryAjax(' . $cat->term_id . ')">🗑️</button></div>';
            }
        }

        wp_send_json_success( array(
            'cats_html'   => $cats_html,
            'manage_html' => $manage_html,
            'message'     => __( 'Category deleted.', 'alhikmat-pm' ),
        ) );
    }

    public function handle_form_actions() {
        if ( ! isset( $_POST[ $this->nonce_field ] ) || ! wp_verify_nonce( $_POST[ $this->nonce_field ], $this->nonce_action ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $action = isset( $_POST['ahpm_action'] ) ? sanitize_text_field( $_POST['ahpm_action'] ) : '';

        if ( 'save_product' === $action ) {
            $product_id        = isset( $_POST['ahpm_product_id'] ) ? absint( $_POST['ahpm_product_id'] ) : 0;
            $title             = isset( $_POST['ahpm_title'] ) ? sanitize_text_field( $_POST['ahpm_title'] ) : '';
            $short_description = isset( $_POST['ahpm_short_description'] ) ? wp_kses_post( $_POST['ahpm_short_description'] ) : '';
            $description       = isset( $_POST['ahpm_description'] ) ? wp_kses_post( $_POST['ahpm_description'] ) : '';
            $category_id       = isset( $_POST['ahpm_category'] ) ? absint( $_POST['ahpm_category'] ) : 0;
            $base_rate         = isset( $_POST['ahpm_base_rate'] ) ? floatval( $_POST['ahpm_base_rate'] ) : 0;
            $global_discount   = isset( $_POST['ahpm_global_discount'] ) ? floatval( $_POST['ahpm_global_discount'] ) : 0;
            $sale_start        = isset( $_POST['ahpm_sale_start'] ) ? sanitize_text_field( $_POST['ahpm_sale_start'] ) : '';
            $sale_end          = isset( $_POST['ahpm_sale_end'] ) ? sanitize_text_field( $_POST['ahpm_sale_end'] ) : '';
            $unit_type         = isset( $_POST['ahpm_unit_type'] ) ? sanitize_text_field( $_POST['ahpm_unit_type'] ) : 'weight';
            $custom_unit       = isset( $_POST['ahpm_custom_unit'] ) ? sanitize_text_field( $_POST['ahpm_custom_unit'] ) : '';
            $colors_str        = isset( $_POST['ahpm_colors'] ) ? sanitize_text_field( $_POST['ahpm_colors'] ) : '';
            $grade             = isset( $_POST['ahpm_grade'] ) ? sanitize_text_field( $_POST['ahpm_grade'] ) : 'none';
            $image_id          = isset( $_POST['ahpm_image_id'] ) ? absint( $_POST['ahpm_image_id'] ) : 0;
            $gallery_ids       = isset( $_POST['ahpm_gallery_ids'] ) ? sanitize_text_field( $_POST['ahpm_gallery_ids'] ) : '';

            $repeater_rows = isset( $_POST['ahpm_rows'] ) && is_array( $_POST['ahpm_rows'] ) ? $_POST['ahpm_rows'] : array();

            if ( empty( $title ) ) return;

            $this->sync_woocommerce_product( $product_id, $title, $short_description, $description, $category_id, $base_rate, $global_discount, $sale_start, $sale_end, $unit_type, $custom_unit, $colors_str, $grade, $repeater_rows, $image_id, $gallery_ids );

            wp_redirect( admin_url( 'admin.php?page=alhikmat-product-manager&message=saved' ) );
            exit;
        }

        if ( 'delete_product' === $action ) {
            $product_id = isset( $_POST['ahpm_delete_id'] ) ? absint( $_POST['ahpm_delete_id'] ) : 0;
            if ( $product_id > 0 ) {
                wp_delete_post( $product_id, true );
            }
            wp_redirect( admin_url( 'admin.php?page=alhikmat-product-manager&message=deleted' ) );
            exit;
        }
    }

    private function sync_woocommerce_product( $product_id, $title, $short_description, $description, $category_id, $base_rate, $global_discount, $sale_start, $sale_end, $unit_type, $custom_unit, $colors_str, $grade, $repeater_rows, $image_id, $gallery_ids_str ) {
        if ( $product_id > 0 ) {
            $product = wc_get_product( $product_id );
        } else {
            $product = new WC_Product_Variable();
        }

        if ( ! $product ) return;

        // Sanitize Short Description to strip unwanted data attributes
        $cleaned_short_desc = preg_replace( '/\s*data-[a-z0-9-]+="[^"]*"/i', '', $short_description );

        $product->set_name( $title );
        $product->set_short_description( $cleaned_short_desc );
        $product->set_description( $description );
        $product->set_status( 'publish' );

        if ( $category_id > 0 ) {
            $product->set_category_ids( array( $category_id ) );
        }

        if ( $image_id > 0 ) {
            $product->set_image_id( $image_id );
        }

        // Product Gallery Images
        $gallery_arr = array_filter( array_map( 'absint', explode( ',', $gallery_ids_str ) ) );
        if ( ! empty( $gallery_arr ) ) {
            $product->set_gallery_image_ids( $gallery_arr );
        } else {
            $product->set_gallery_image_ids( array() );
        }

        $saved_id = $product->save();

        // Ensure variable type
        wp_set_object_terms( $saved_id, 'variable', 'product_type' );

        // HARD CLEANUP: Delete old variations & attributes
        $existing_variations = get_posts( array(
            'post_parent' => $saved_id,
            'post_type'   => 'product_variation',
            'numberposts' => -1,
            'post_status' => array( 'any', 'trash' ),
            'fields'      => 'ids',
        ) );

        if ( ! empty( $existing_variations ) ) {
            foreach ( $existing_variations as $var_id ) {
                wp_delete_post( $var_id, true );
            }
        }

        delete_post_meta( $saved_id, '_product_attributes' );

        // Process Colors Array
        $colors = array_values( array_unique( array_filter( array_map( 'trim', explode( ',', $colors_str ) ) ) ) );

        // Backend Performance Guardrail: Strictly Cap Input Processing at 8 Repeater Rows
        if ( ! empty( $repeater_rows ) && is_array( $repeater_rows ) ) {
            $repeater_rows = array_slice( $repeater_rows, 0, 8 );
        }

        // Process Repeater Rows into Clean Array Terms
        $formatted_options = array();
        $rows_data = array();

        if ( ! empty( $repeater_rows ) ) {
            foreach ( $repeater_rows as $row ) {
                $size        = isset( $row['size'] ) ? sanitize_text_field( $row['size'] ) : '';
                $unit        = isset( $row['unit'] ) ? sanitize_text_field( $row['unit'] ) : '';
                $reg_price   = isset( $row['regular_price'] ) && '' !== trim( $row['regular_price'] ) ? floatval( $row['regular_price'] ) : null;
                $sale_price  = isset( $row['sale_price'] ) && '' !== trim( $row['sale_price'] ) ? floatval( $row['sale_price'] ) : null;

                if ( ! empty( $size ) ) {
                    $term_label = $size . ' ' . $unit;
                    $term_label = trim( $term_label );
                    $formatted_options[] = $term_label;
                    $rows_data[] = array(
                        'term'          => $term_label,
                        'size'          => $size,
                        'unit'          => $unit,
                        'regular_price' => $reg_price,
                        'sale_price'    => $sale_price,
                    );
                }
            }
        }

        if ( empty( $formatted_options ) ) {
            $formatted_options = array( '50g', '100g', '250g', '500g', '1000g' );
            foreach ( $formatted_options as $t ) {
                $rows_data[] = array( 'term' => $t, 'size' => preg_replace( '/[^0-9\.]/', '', $t ), 'unit' => 'g', 'regular_price' => null, 'sale_price' => null );
            }
        }

        // Setup Attributes with set_visible(1) and set_variation(1)
        $wc_attributes = array();

        // Primary Pack Size Attribute
        $attr_size = new WC_Product_Attribute();
        $attr_size->set_id( 0 );
        $attr_size->set_name( 'Pack Size' );
        $attr_size->set_options( array_values( array_unique( $formatted_options ) ) );
        $attr_size->set_position( 0 );
        $attr_size->set_visible( 1 );
        $attr_size->set_variation( 1 );
        $wc_attributes['pack-size'] = $attr_size;

        // Color Attribute
        if ( ! empty( $colors ) ) {
            $attr_color = new WC_Product_Attribute();
            $attr_color->set_id( 0 );
            $attr_color->set_name( 'Color' );
            $attr_color->set_options( $colors );
            $attr_color->set_position( 1 );
            $attr_color->set_visible( 1 );
            $attr_color->set_variation( 1 );
            $wc_attributes['color'] = $attr_color;
        }

        // Grade Attribute
        if ( 'none' !== $grade && ! empty( $grade ) ) {
            $attr_grade = new WC_Product_Attribute();
            $attr_grade->set_id( 0 );
            $attr_grade->set_name( 'Grade / Quality' );
            $attr_grade->set_options( array( $grade ) );
            $attr_grade->set_position( 2 );
            $attr_grade->set_visible( 1 );
            $attr_grade->set_variation( 1 );
            $wc_attributes['grade'] = $attr_grade;
        }

        $product_var = wc_get_product( $saved_id );
        $product_var->set_attributes( $wc_attributes );
        $product_var->save();

        // Save Scheduled Sale Meta
        update_post_meta( $saved_id, '_ahpm_sale_start', $sale_start );
        update_post_meta( $saved_id, '_ahpm_sale_end', $sale_end );

        $start_timestamp = ! empty( $sale_start ) ? strtotime( $sale_start ) : '';
        $end_timestamp   = ! empty( $sale_end ) ? strtotime( $sale_end ) : '';

        if ( $start_timestamp ) {
            update_post_meta( $saved_id, '_sale_price_dates_from', $start_timestamp );
        } else {
            delete_post_meta( $saved_id, '_sale_price_dates_from' );
        }

        if ( $end_timestamp ) {
            update_post_meta( $saved_id, '_sale_price_dates_to', $end_timestamp );
        } else {
            delete_post_meta( $saved_id, '_sale_price_dates_to' );
        }

        // Build Fresh Variations (Cross-combine with Colors & Grade)
        foreach ( $rows_data as $r ) {
            $size_num = floatval( preg_replace( '/[^0-9\.]/', '', $r['size'] ) );
            if ( $size_num <= 0 ) $size_num = 1000;

            if ( null !== $r['regular_price'] && $r['regular_price'] > 0 ) {
                $calc_reg_price = $r['regular_price'];
            } else {
                $calc_reg_price = ( $size_num / 1000.0 ) * $base_rate;
            }

            if ( null !== $r['sale_price'] && $r['sale_price'] > 0 ) {
                $calc_sale_price = $r['sale_price'];
            } elseif ( $global_discount > 0 && $global_discount < 100 ) {
                $calc_sale_price = $calc_reg_price - ( $calc_reg_price * ( $global_discount / 100.0 ) );
            } else {
                $calc_sale_price = '';
            }

            $variation_attrs = array( 'pack-size' => $r['term'] );
            if ( 'none' !== $grade && ! empty( $grade ) ) {
                $variation_attrs['grade'] = $grade;
            }

            if ( ! empty( $colors ) ) {
                foreach ( $colors as $color_val ) {
                    $attr_combo = array_merge( $variation_attrs, array( 'color' => $color_val ) );
                    $this->create_variation_item( $saved_id, $attr_combo, $calc_reg_price, $calc_sale_price, $start_timestamp, $end_timestamp );
                }
            } else {
                $this->create_variation_item( $saved_id, $variation_attrs, $calc_reg_price, $calc_sale_price, $start_timestamp, $end_timestamp );
            }
        }

        // Save Custom Post Meta
        update_post_meta( $saved_id, '_ahpm_base_rate', $base_rate );
        update_post_meta( $saved_id, '_ahpm_global_discount', $global_discount );
        update_post_meta( $saved_id, '_ahpm_unit_type', $unit_type );
        update_post_meta( $saved_id, '_ahpm_custom_unit', $custom_unit );
        update_post_meta( $saved_id, '_ahpm_colors', implode( ', ', $colors ) );
        update_post_meta( $saved_id, '_ahpm_grade', $grade );
        update_post_meta( $saved_id, '_ahpm_repeater_rows', $rows_data );

        // Execute Parent Product Price Meta Sync & Lookup Table Rebuild
        alhikmat_sync_parent_product_prices( $saved_id );
    }

    private function create_variation_item( $parent_id, $attributes, $reg_price, $sale_price, $start_timestamp = '', $end_timestamp = '' ) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id( $parent_id );
        $variation->set_attributes( $attributes );
        
        $variation->set_regular_price( number_format( $reg_price, 2, '.', '' ) );
        
        if ( ! empty( $sale_price ) && floatval( $sale_price ) > 0 && floatval( $sale_price ) < floatval( $reg_price ) ) {
            $variation->set_sale_price( number_format( floatval( $sale_price ), 2, '.', '' ) );
            $variation->set_price( number_format( floatval( $sale_price ), 2, '.', '' ) );
        } else {
            $variation->set_price( number_format( $reg_price, 2, '.', '' ) );
        }

        if ( ! empty( $start_timestamp ) ) {
            $variation->set_date_on_sale_from( $start_timestamp );
        }
        if ( ! empty( $end_timestamp ) ) {
            $variation->set_date_on_sale_to( $end_timestamp );
        }

        $variation->set_manage_stock( false );
        $variation->set_stock_status( 'instock' );
        $variation->set_status( 'publish' );
        $variation->save();
    }

    public function render_dashboard_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $edit_id = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : 0;
        $edit_product = $edit_id > 0 ? wc_get_product( $edit_id ) : null;

        $title             = $edit_product ? $edit_product->get_name() : '';
        $raw_short_desc    = $edit_product ? $edit_product->get_short_description() : '';
        $short_description = preg_replace( '/\s*data-[a-z0-9-]+="[^"]*"/i', '', $raw_short_desc );
        $description       = $edit_product ? $edit_product->get_description() : '';
        $category_ids      = $edit_product ? $edit_product->get_category_ids() : array();
        $cat_id            = ! empty( $category_ids ) ? $category_ids[0] : 0;
        $image_id          = $edit_product ? $edit_product->get_image_id() : 0;
        $image_url         = $image_id > 0 ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';

        $gallery_ids  = $edit_product ? implode( ',', $edit_product->get_gallery_image_ids() ) : '';

        $base_rate   = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_base_rate', true ) : '';
        $discount_pct= $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_global_discount', true ) : '';
        $sale_start  = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_sale_start', true ) : '';
        $sale_end    = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_sale_end', true ) : '';
        $unit_type   = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_unit_type', true ) : 'weight';
        $custom_unit = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_custom_unit', true ) : '';
        $colors_val  = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_colors', true ) : '';
        $grade_val   = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_grade', true ) : 'none';
        $saved_rows  = $edit_id > 0 ? get_post_meta( $edit_id, '_ahpm_repeater_rows', true ) : array();

        if ( ! is_array( $saved_rows ) || empty( $saved_rows ) ) {
            $saved_rows = array(
                array( 'size' => '50', 'unit' => 'g', 'regular_price' => '', 'sale_price' => '' ),
                array( 'size' => '100', 'unit' => 'g', 'regular_price' => '', 'sale_price' => '' ),
                array( 'size' => '250', 'unit' => 'g', 'regular_price' => '', 'sale_price' => '' ),
                array( 'size' => '500', 'unit' => 'g', 'regular_price' => '', 'sale_price' => '' ),
                array( 'size' => '1000', 'unit' => 'g', 'regular_price' => '', 'sale_price' => '' ),
            );
        }

        $categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );

        $all_products = wc_get_products( array(
            'limit'   => -1,
            'type'    => array( 'variable', 'simple' ),
            'orderby' => 'date',
            'order'   => 'DESC',
        ) );
        ?>
        <div class="ahpm-wrapper">
            <div class="ahpm-header">
                <h2 class="ahpm-title">
                    <span>🌿 Al-Hikmat Product Manager</span>
                </h2>
                <span class="ahpm-badge">v2.4 Scheduled Sale & Live Timer Suite</span>
            </div>

            <div class="ahpm-layout">
                <!-- Left Column: Add / Edit Form -->
                <div class="ahpm-card">
                    <div class="ahpm-card-title">
                        <span><?php echo $edit_id > 0 ? '✏️ Edit Product #' . esc_html( $edit_id ) : '➕ Add New Product'; ?></span>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <!-- Header Action Bar Button for Quick Category Manager -->
                            <button type="button" id="toggle-category-modal" class="ahpm-header-cat-btn" onclick="ahpmToggleCatPanel()">📁 Manage / Add Categories</button>
                            
                            <?php if ( $edit_id > 0 ) : 
                                $preview_url = get_permalink( $edit_id );
                            ?>
                                <a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="ahpm-action-link ahpm-preview-btn">👁️ Live Preview</a>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=alhikmat-product-manager' ) ); ?>" style="font-size:12px; color:#38bdf8; text-decoration:none;">+ Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- In-Dashboard Category Controller Overlay / Drawer Panel -->
                    <div id="ahpm-cat-panel" class="ahpm-cat-panel">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                            <div style="font-weight:800; color:#38bdf8; font-size:14px; display:flex; align-items:center; gap:6px;">📁 Quick Category Management Engine</div>
                            <button type="button" onclick="ahpmToggleCatPanel()" style="background:none; border:none; color:#94a3b8; font-size:14px; cursor:pointer;">✕ Close</button>
                        </div>
                        
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                            <div>
                                <label style="font-size:11px; color:#94a3b8; font-weight:700;">Category Name</label>
                                <input type="text" id="ahpm_new_cat_name" placeholder="e.g. Organic Teas" class="ahpm-input" style="padding:6px 10px;" />
                            </div>
                            <div>
                                <label style="font-size:11px; color:#94a3b8; font-weight:700;">Parent Category</label>
                                <select id="ahpm_new_cat_parent" class="ahpm-select" style="padding:6px 10px;">
                                    <option value="0">None (Top Level)</option>
                                    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                                        <?php foreach ( $categories as $cat ) : ?>
                                            <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Category Media (Thumbnail & Banner Gallery) -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div>
                                <label style="font-size:11px; color:#94a3b8; font-weight:700;">Category Thumbnail</label>
                                <input type="hidden" id="ahpm_cat_thumb_id" value="" />
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <img id="ahpm-cat-thumb-preview" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='32' height='32' fill='%23334155'><rect width='32' height='32' rx='4'/></svg>" style="width:32px; height:32px; border-radius:4px; object-fit:cover;" />
                                    <button type="button" id="ahpm-cat-thumb-btn" class="button button-secondary" style="font-size:11px; padding:2px 8px;">📷 Thumb</button>
                                </div>
                            </div>

                            <div>
                                <label style="font-size:11px; color:#94a3b8; font-weight:700;">Category Banner Gallery</label>
                                <input type="hidden" id="ahpm_cat_gallery_ids" value="" />
                                <button type="button" id="ahpm-cat-gallery-btn" class="button button-secondary" style="font-size:11px; padding:2px 8px;">🖼️ Banners</button>
                                <div id="ahpm-cat-gallery-grid" class="ahpm-gallery-grid" style="margin-top:4px;"></div>
                            </div>
                        </div>

                        <button type="button" class="ahpm-btn-primary" onclick="ahpmAddCategoryAjax()" style="padding:8px 12px; font-size:12px; margin-bottom:12px;">➕ Create Category Now</button>

                        <div style="font-weight:700; color:#94a3b8; font-size:11px; text-transform:uppercase; margin-bottom:6px;">Existing Categories List</div>
                        <div id="ahpm-cat-list-container" style="max-height:120px; overflow-y:auto;">
                            <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                                <?php foreach ( $categories as $cat ) : ?>
                                    <div class="ahpm-cat-list-item">
                                        <span><?php echo esc_html( $cat->name ); ?></span>
                                        <button type="button" class="ahpm-btn-remove" onclick="ahpmDeleteCategoryAjax(<?php echo esc_attr( $cat->term_id ); ?>)">🗑️</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form method="post" action="">
                        <?php wp_nonce_field( $this->nonce_action, $this->nonce_field ); ?>
                        <input type="hidden" name="ahpm_action" value="save_product" />
                        <input type="hidden" name="ahpm_product_id" value="<?php echo esc_attr( $edit_id ); ?>" />

                        <div class="ahpm-group">
                            <label for="ahpm_title">Product Title</label>
                            <input type="text" id="ahpm_title" name="ahpm_title" value="<?php echo esc_attr( $title ); ?>" placeholder="e.g. Pure Herb Extract" class="ahpm-input" required />
                        </div>

                        <!-- Category Selector -->
                        <div class="ahpm-group">
                            <label for="ahpm_category">Category</label>
                            <select id="ahpm_category" name="ahpm_category" class="ahpm-select">
                                <option value="0">-- Select Category --</option>
                                <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                                    <?php foreach ( $categories as $cat ) : ?>
                                        <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $cat_id, $cat->term_id ); ?>>
                                            <?php echo esc_html( $cat->name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                            <div class="ahpm-group">
                                <label for="ahpm_unit_type">Measurement</label>
                                <select id="ahpm_unit_type" name="ahpm_unit_type" class="ahpm-select" onchange="ahpmSwitchUnitSystem(this.value)">
                                    <option value="weight" <?php selected( $unit_type, 'weight' ); ?>>Weight (g/kg)</option>
                                    <option value="volume" <?php selected( $unit_type, 'volume' ); ?>>Volume (ml/L)</option>
                                    <option value="length" <?php selected( $unit_type, 'length' ); ?>>Length (m/cm)</option>
                                    <option value="custom" <?php selected( $unit_type, 'custom' ); ?>>Custom Unit</option>
                                </select>
                            </div>

                            <div class="ahpm-group">
                                <label for="ahpm_base_rate">Base Rate (1000g/ml)</label>
                                <input type="number" step="0.01" min="0" id="ahpm_base_rate" name="ahpm_base_rate" value="<?php echo esc_attr( $base_rate ); ?>" placeholder="e.g. 5000" class="ahpm-input" oninput="ahpmRecalculatePrices();" required />
                            </div>

                            <div class="ahpm-group">
                                <label for="ahpm_global_discount">Discount (%)</label>
                                <input type="number" step="0.1" min="0" max="100" id="ahpm_global_discount" name="ahpm_global_discount" value="<?php echo esc_attr( $discount_pct ); ?>" placeholder="e.g. 10" class="ahpm-input" oninput="ahpmRecalculatePrices();" />
                            </div>
                        </div>

                        <!-- Scheduled Sale Start & End Date Time Fields -->
                        <div style="background: #0f172a; border: 1px solid #0284c7; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                            <div style="font-weight:700; color:#38bdf8; font-size:12px; text-transform:uppercase; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                                <span>⏳ Scheduled Sale Schedule (Optional Timer)</span>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label for="ahpm_sale_start" style="font-size:11px; color:#94a3b8; font-weight:700;">Sale Start Date & Time</label>
                                    <input type="datetime-local" id="ahpm_sale_start" name="ahpm_sale_start" value="<?php echo esc_attr( $sale_start ); ?>" class="ahpm-input" style="padding:8px 10px;" />
                                </div>
                                <div>
                                    <label for="ahpm_sale_end" style="font-size:11px; color:#94a3b8; font-weight:700;">Sale End Date & Time</label>
                                    <input type="datetime-local" id="ahpm_sale_end" name="ahpm_sale_end" value="<?php echo esc_attr( $sale_end ); ?>" class="ahpm-input" style="padding:8px 10px;" />
                                </div>
                            </div>
                            <div style="font-size:11px; color:#64748b; margin-top:8px;">
                                💡 Leave blank for continuous sale. If set, discount prices & sale badges will automatically activate on Start date and revert to regular price on End date.
                            </div>
                        </div>

                        <div class="ahpm-group" id="ahpm-custom-unit-group" style="<?php echo ('custom' === $unit_type) ? '' : 'display:none;'; ?>">
                            <label for="ahpm_custom_unit">Custom Unit Label</label>
                            <input type="text" id="ahpm_custom_unit" name="ahpm_custom_unit" value="<?php echo esc_attr( $custom_unit ); ?>" placeholder="e.g. Pills, Sachet, Boxes" class="ahpm-input" />
                        </div>

                        <!-- Visual Color Picker & Quality Grade -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="ahpm-group">
                                <label for="ahpm_colors">Colors / Shades (Comma Separated)</label>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <input type="color" id="ahpm_color_picker" style="width:36px; height:36px; border:none; cursor:pointer; background:none;" onchange="ahpmAppendColorHex(this.value);" />
                                    <input type="text" id="ahpm_colors" name="ahpm_colors" value="<?php echo esc_attr( $colors_val ); ?>" placeholder="e.g. Red, Blue, Dark Amber" class="ahpm-input" />
                                </div>
                            </div>

                            <div class="ahpm-group">
                                <label for="ahpm_grade">Quality Grade (Optional)</label>
                                <select id="ahpm_grade" name="ahpm_grade" class="ahpm-select">
                                    <option value="none" <?php selected( $grade_val, 'none' ); ?>>None / Standard</option>
                                    <option value="Grade A (Premium)" <?php selected( $grade_val, 'Grade A (Premium)' ); ?>>Grade A (Premium)</option>
                                    <option value="Grade B (Standard)" <?php selected( $grade_val, 'Grade B (Standard)' ); ?>>Grade B (Standard)</option>
                                    <option value="Organic Pure" <?php selected( $grade_val, 'Organic Pure' ); ?>>Organic Pure</option>
                                    <option value="Commercial Grade" <?php selected( $grade_val, 'Commercial Grade' ); ?>>Commercial Grade</option>
                                </select>
                            </div>
                        </div>

                        <!-- Interactive Repeater Matrix Table with Max 8 Limit Guardrail -->
                        <div class="ahpm-group">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <label style="margin:0;">Variation Matrix & Pricing (Max 8 Rows)</label>
                                <button type="button" id="ahpm-add-row-btn" class="ahpm-btn-add" onclick="ahpmAddRepeaterRow()">+ Add Row</button>
                            </div>

                            <table class="ahpm-repeater-table">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Unit</th>
                                        <th>Regular Price</th>
                                        <th>Sale Price (% Discounted)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="ahpm-repeater-body">
                                    <?php foreach ( $saved_rows as $idx => $r ) : ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="ahpm_rows[<?php echo $idx; ?>][size]" value="<?php echo esc_attr( isset( $r['size'] ) ? $r['size'] : '' ); ?>" placeholder="e.g. 50" class="ahpm-input ahpm-size-field" oninput="ahpmRowInputChanged(this);" style="padding:6px 8px;" />
                                            </td>
                                            <td>
                                                <input type="text" name="ahpm_rows[<?php echo $idx; ?>][unit]" value="<?php echo esc_attr( isset( $r['unit'] ) ? $r['unit'] : 'g' ); ?>" placeholder="g / ml" class="ahpm-input ahpm-unit-field" style="padding:6px 8px;" />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="ahpm_rows[<?php echo $idx; ?>][regular_price]" value="<?php echo esc_attr( isset( $r['regular_price'] ) && '' !== $r['regular_price'] ? $r['regular_price'] : '' ); ?>" placeholder="Auto Math" class="ahpm-input ahpm-reg-field" oninput="ahpmRowInputChanged(this);" style="padding:6px 8px; color:#10b981; font-weight:bold;" />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="ahpm_rows[<?php echo $idx; ?>][sale_price]" value="<?php echo esc_attr( isset( $r['sale_price'] ) && '' !== $r['sale_price'] ? $r['sale_price'] : '' ); ?>" placeholder="Auto Sale" class="ahpm-input ahpm-sale-field" style="padding:6px 8px; color:#38bdf8; font-weight:bold;" />
                                            </td>
                                            <td>
                                                <button type="button" class="ahpm-btn-remove" onclick="this.closest('tr').remove(); ahpmCheckRepeaterLimit();">🗑️</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div id="ahpm-max-limit-warning" class="ahpm-warning-text">⚠️ Maximum limit of 8 variations reached for optimal server performance.</div>
                        </div>

                        <!-- Featured Image & Gallery Uploader -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="ahpm-group">
                                <label>Featured Main Image</label>
                                <input type="hidden" id="ahpm_image_id" name="ahpm_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img id="ahpm-img-preview" src="<?php echo esc_url( $image_url ? $image_url : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" fill="%23334155"><rect width="42" height="42" rx="6"/></svg>' ); ?>" class="ahpm-thumb" />
                                    <button type="button" id="ahpm-upload-btn" class="button button-secondary" style="font-size:12px;">📷 Upload Main</button>
                                </div>
                            </div>

                            <div class="ahpm-group">
                                <label>Product Gallery Images</label>
                                <input type="hidden" id="ahpm_gallery_ids" name="ahpm_gallery_ids" value="<?php echo esc_attr( $gallery_ids ); ?>" />
                                <button type="button" id="ahpm-gallery-btn" class="button button-secondary" style="font-size:12px; margin-bottom:6px;">🖼️ Add Gallery Images</button>
                                <div id="ahpm-gallery-grid" class="ahpm-gallery-grid">
                                    <?php 
                                    if ( ! empty( $gallery_ids ) ) {
                                        $ids_arr = array_filter( array_map( 'absint', explode( ',', $gallery_ids ) ) );
                                        foreach ( $ids_arr as $g_id ) {
                                            $g_url = wp_get_attachment_image_url( $g_id, 'thumbnail' );
                                            if ( $g_url ) {
                                                echo '<div class="ahpm-gallery-item" data-id="' . $g_id . '"><img src="' . esc_url( $g_url ) . '" /><span class="ahpm-gallery-remove" onclick="ahpmRemoveGalleryImg(this, ' . $g_id . ')">✕</span></div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Short Description Input -->
                        <div class="ahpm-group">
                            <label for="ahpm_short_description">Product Short Summary (Rendered Near Price)</label>
                            <textarea id="ahpm_short_description" name="ahpm_short_description" rows="2" class="ahpm-textarea" placeholder="Brief highlights or key benefits..."><?php echo esc_textarea( $short_description ); ?></textarea>
                        </div>

                        <!-- Rich Text Long Description Editor -->
                        <div class="ahpm-group">
                            <label for="ahpm_description">Product Long Description (Rich Text Editor)</label>
                            <?php 
                            wp_editor( $description, 'ahpm_description', array(
                                'textarea_name' => 'ahpm_description',
                                'textarea_rows' => 5,
                                'teeny'         => true,
                                'media_buttons' => false,
                                'quicktags'     => true,
                            ) );
                            ?>
                        </div>

                        <button type="submit" class="ahpm-btn-primary">
                            <span><?php echo $edit_id > 0 ? '💾 Update Product' : '⚡ Create & Sync Product'; ?></span>
                        </button>
                    </form>
                </div>

                <!-- Right Column: Product Table with Clean Permalinks -->
                <div class="ahpm-card">
                    <div class="ahpm-card-title">
                        <span>📦 All Products List (<?php echo count( $all_products ); ?>)</span>
                    </div>

                    <div class="ahpm-table-container">
                        <table class="ahpm-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Base Rate</th>
                                    <th>Unit</th>
                                    <th>Sale Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( ! empty( $all_products ) ) : ?>
                                    <?php foreach ( $all_products as $p ) : 
                                        $p_id    = $p->get_id();
                                        $p_link  = get_permalink( $p_id );
                                        $p_rate  = get_post_meta( $p_id, '_ahpm_base_rate', true );
                                        $p_unit  = get_post_meta( $p_id, '_ahpm_unit_type', true );
                                        $s_start = get_post_meta( $p_id, '_ahpm_sale_start', true );
                                        $s_end   = get_post_meta( $p_id, '_ahpm_sale_end', true );
                                        $is_active_sale = alhikmat_is_sale_active( $p_id );
                                        $img_src = wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' );
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo esc_url( $p_link ); ?>" target="_blank">
                                                    <img src="<?php echo esc_url( $img_src ? $img_src : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" fill="%23334155"><rect width="42" height="42" rx="6"/></svg>' ); ?>" class="ahpm-thumb" />
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?php echo esc_url( $p_link ); ?>" target="_blank" style="color:#f8fafc; text-decoration:none; font-weight:700;">
                                                    <?php echo esc_html( $p->get_name() ); ?>
                                                </a>
                                            </td>
                                            <td style="color:#10b981; font-weight:bold;">
                                                <?php echo $p_rate ? esc_html( $p_rate ) : '-'; ?>
                                            </td>
                                            <td>
                                                <span style="background:#0f172a; padding:4px 8px; border-radius:4px; font-size:11px; text-transform:uppercase;">
                                                    <?php echo esc_html( $p_unit ? $p_unit : 'weight' ); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ( $is_active_sale && $p->is_on_sale() ) : ?>
                                                    <span style="background:#064e3b; color:#34d399; font-size:10px; font-weight:800; padding:3px 8px; border-radius:10px; border:1px solid #059669;">🔥 ACTIVE SALE</span>
                                                    <?php if ( $s_end ) : ?>
                                                        <div style="font-size:10px; color:#94a3b8; margin-top:2px;">Ends: <?php echo esc_html( date( 'd M, H:i', strtotime( $s_end ) ) ); ?></div>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <span style="background:#1e293b; color:#94a3b8; font-size:10px; font-weight:600; padding:3px 8px; border-radius:10px; border:1px solid #334155;">REGULAR</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo esc_url( $p_link ); ?>" target="_blank" class="ahpm-action-link ahpm-preview-btn">Preview</a>

                                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=alhikmat-product-manager&edit_id=' . $p_id ) ); ?>" class="ahpm-action-link ahpm-edit-btn">Edit</a>
                                                
                                                <form method="post" action="" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                                    <?php wp_nonce_field( $this->nonce_action, $this->nonce_field ); ?>
                                                    <input type="hidden" name="ahpm_action" value="delete_product" />
                                                    <input type="hidden" name="ahpm_delete_id" value="<?php echo esc_attr( $p_id ); ?>" />
                                                    <button type="submit" class="ahpm-action-link ahpm-delete-btn" style="border:none; cursor:pointer;">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; color:#94a3b8; padding:20px;">No products found yet. Add your first product using the form.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            var ahpmNonce = '<?php echo wp_create_nonce( $this->nonce_action ); ?>';
            const MAX_VARIATIONS = 8;

            function ahpmCheckRepeaterLimit() {
                var tbody = document.getElementById('ahpm-repeater-body');
                var addBtn = document.getElementById('ahpm-add-row-btn');
                var warnText = document.getElementById('ahpm-max-limit-warning');
                if (!tbody || !addBtn) return;

                var rowCount = tbody.querySelectorAll('tr').length;

                if (rowCount >= MAX_VARIATIONS) {
                    addBtn.disabled = true;
                    addBtn.textContent = 'Max Limit Reached (' + rowCount + '/' + MAX_VARIATIONS + ')';
                    if (warnText) warnText.style.display = 'block';
                } else {
                    addBtn.disabled = false;
                    addBtn.textContent = '+ Add Row';
                    if (warnText) warnText.style.display = 'none';
                }
            }

            function ahpmToggleCatPanel() {
                var panel = document.getElementById('ahpm-cat-panel');
                if (panel) panel.classList.toggle('is-active');
            }

            function ahpmAddCategoryAjax() {
                var catName    = document.getElementById('ahpm_new_cat_name').value.trim();
                var catParent  = document.getElementById('ahpm_new_cat_parent').value;
                var catThumb   = document.getElementById('ahpm_cat_thumb_id').value;
                var catGallery = document.getElementById('ahpm_cat_gallery_ids').value;

                if (!catName) {
                    alert('Please enter a Category Name.');
                    return;
                }

                var data = new FormData();
                data.append('action', 'ahpm_add_category');
                data.append('nonce', ahpmNonce);
                data.append('cat_name', catName);
                data.append('cat_parent', catParent);
                data.append('cat_thumb_id', catThumb);
                data.append('cat_gallery_ids', catGallery);

                fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert(res.data.message);
                        document.getElementById('ahpm_category').innerHTML = res.data.cats_html;
                        document.getElementById('ahpm_new_cat_parent').innerHTML = res.data.cats_html.replace('-- Select Category --', 'None (Top Level)');
                        document.getElementById('ahpm-cat-list-container').innerHTML = res.data.manage_html;
                        document.getElementById('ahpm_new_cat_name').value = '';
                        document.getElementById('ahpm_cat_thumb_id').value = '';
                        document.getElementById('ahpm_cat_gallery_ids').value = '';
                        document.getElementById('ahpm-cat-thumb-preview').src = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='32' height='32' fill='%23334155'><rect width='32' height='32' rx='4'/></svg>";
                        document.getElementById('ahpm-cat-gallery-grid').innerHTML = '';
                    } else {
                        alert('Error: ' + (res.data.message || 'Could not create category'));
                    }
                })
                .catch(err => alert('Network error: ' + err));
            }

            function ahpmDeleteCategoryAjax(termId) {
                if (!confirm('Are you sure you want to delete this category?')) return;

                var data = new FormData();
                data.append('action', 'ahpm_delete_category');
                data.append('nonce', ahpmNonce);
                data.append('term_id', termId);

                fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert(res.data.message);
                        document.getElementById('ahpm_category').innerHTML = res.data.cats_html;
                        document.getElementById('ahpm_new_cat_parent').innerHTML = res.data.cats_html.replace('-- Select Category --', 'None (Top Level)');
                        document.getElementById('ahpm-cat-list-container').innerHTML = res.data.manage_html;
                    } else {
                        alert('Error: ' + (res.data.message || 'Could not delete category'));
                    }
                })
                .catch(err => alert('Network error: ' + err));
            }

            function ahpmAppendColorHex(hex) {
                var input = document.getElementById('ahpm_colors');
                if (input) {
                    var current = input.value.trim();
                    input.value = current ? current + ', ' + hex : hex;
                }
            }

            function ahpmSwitchUnitSystem(val) {
                var custGrp = document.getElementById('ahpm-custom-unit-group');
                if (custGrp) {
                    custGrp.style.display = (val === 'custom') ? 'block' : 'none';
                }
                var defaultUnit = 'g';
                if (val === 'volume') defaultUnit = 'ml';
                if (val === 'length') defaultUnit = 'm';
                if (val === 'custom') defaultUnit = 'pcs';

                document.querySelectorAll('.ahpm-unit-field').forEach(function(input) {
                    if (!input.value || input.value === 'g' || input.value === 'ml' || input.value === 'm' || input.value === 'pcs') {
                        input.value = defaultUnit;
                    }
                });
            }

            function ahpmRecalculatePrices() {
                var baseRate = parseFloat(document.getElementById('ahpm_base_rate').value) || 0;
                var discountPct = parseFloat(document.getElementById('ahpm_global_discount').value) || 0;

                document.querySelectorAll('#ahpm-repeater-body tr').forEach(function(tr) {
                    var sizeInput = tr.querySelector('.ahpm-size-field');
                    var regInput  = tr.querySelector('.ahpm-reg-field');
                    var saleInput = tr.querySelector('.ahpm-sale-field');

                    var sizeVal = parseFloat(sizeInput.value.replace(/[^0-9\.]/g, '')) || 0;

                    if (baseRate > 0 && sizeVal > 0 && !regInput.getAttribute('data-manual')) {
                        var calcReg = (sizeVal / 1000.0) * baseRate;
                        regInput.value = calcReg.toFixed(2);
                    }

                    var currentReg = parseFloat(regInput.value) || 0;
                    if (currentReg > 0 && discountPct > 0 && discountPct < 100 && !saleInput.getAttribute('data-manual')) {
                        var calcSale = currentReg - (currentReg * (discountPct / 100.0));
                        saleInput.value = calcSale.toFixed(2);
                    }
                });
            }

            function ahpmRowInputChanged(el) {
                if (el.classList.contains('ahpm-reg-field') || el.classList.contains('ahpm-sale-field')) {
                    el.setAttribute('data-manual', 'true');
                }
                ahpmRecalculatePrices();
            }

            function ahpmAddRepeaterRow() {
                var tbody = document.getElementById('ahpm-repeater-body');
                var rowCount = tbody.querySelectorAll('tr').length;

                if (rowCount >= MAX_VARIATIONS) {
                    ahpmCheckRepeaterLimit();
                    return;
                }

                var idx = rowCount;
                var unitType = document.getElementById('ahpm_unit_type').value;
                var defaultUnit = 'g';
                if (unitType === 'volume') defaultUnit = 'ml';
                if (unitType === 'length') defaultUnit = 'm';
                if (unitType === 'custom') defaultUnit = 'pcs';

                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="text" name="ahpm_rows[' + idx + '][size]" placeholder="e.g. 50" class="ahpm-input ahpm-size-field" oninput="ahpmRowInputChanged(this);" style="padding:6px 8px;" /></td>' +
                               '<td><input type="text" name="ahpm_rows[' + idx + '][unit]" value="' + defaultUnit + '" placeholder="g / ml" class="ahpm-input ahpm-unit-field" style="padding:6px 8px;" /></td>' +
                               '<td><input type="number" step="0.01" name="ahpm_rows[' + idx + '][regular_price]" placeholder="Auto Math" class="ahpm-input ahpm-reg-field" oninput="ahpmRowInputChanged(this);" style="padding:6px 8px; color:#10b981; font-weight:bold;" /></td>' +
                               '<td><input type="number" step="0.01" name="ahpm_rows[' + idx + '][sale_price]" placeholder="Auto Sale" class="ahpm-input ahpm-sale-field" style="padding:6px 8px; color:#38bdf8; font-weight:bold;" /></td>' +
                               '<td><button type="button" class="ahpm-btn-remove" onclick="this.closest(\'tr\').remove(); ahpmCheckRepeaterLimit();">🗑️</button></td>';
                tbody.appendChild(tr);

                ahpmRecalculatePrices();
                ahpmCheckRepeaterLimit();
            }

            function ahpmRemoveGalleryImg(el, id) {
                var item = el.closest('.ahpm-gallery-item');
                if (item) item.remove();

                var input = document.getElementById('ahpm_gallery_ids');
                var ids = input.value.split(',').map(x => x.trim()).filter(x => x.length > 0 && parseInt(x) !== id);
                input.value = ids.join(',');
            }

            document.addEventListener('DOMContentLoaded', function() {
                ahpmCheckRepeaterLimit();

                // Main Product Image Uploader
                var uploadBtn = document.getElementById('ahpm-upload-btn');
                var imgInput  = document.getElementById('ahpm_image_id');
                var imgPrev   = document.getElementById('ahpm-img-preview');

                if (uploadBtn) {
                    uploadBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var frame = wp.media({
                            title: 'Select Product Main Image',
                            button: { text: 'Use Main Image' },
                            multiple: false
                        });

                        frame.on('select', function() {
                            var attachment = frame.state().get('selection').first().toJSON();
                            imgInput.value = attachment.id;
                            imgPrev.src = attachment.url;
                        });

                        frame.open();
                    });
                }

                // Category Thumbnail Uploader
                var catThumbBtn = document.getElementById('ahpm-cat-thumb-btn');
                var catThumbId  = document.getElementById('ahpm_cat_thumb_id');
                var catThumbPrev= document.getElementById('ahpm-cat-thumb-preview');
                if (catThumbBtn) {
                    catThumbBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var cFrame = wp.media({ title: 'Select Category Thumbnail', button: { text: 'Use Thumbnail' }, multiple: false });
                        cFrame.on('select', function() {
                            var att = cFrame.state().get('selection').first().toJSON();
                            catThumbId.value = att.id;
                            catThumbPrev.src = att.url;
                        });
                        cFrame.open();
                    });
                }

                // Category Banner Gallery Uploader
                var catGalBtn  = document.getElementById('ahpm-cat-gallery-btn');
                var catGalIds  = document.getElementById('ahpm_cat_gallery_ids');
                var catGalGrid = document.getElementById('ahpm-cat-gallery-grid');
                if (catGalBtn) {
                    catGalBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var cgFrame = wp.media({ title: 'Select Category Banner Images', button: { text: 'Add Banners' }, multiple: true });
                        cgFrame.on('select', function() {
                            var sel = cgFrame.state().get('selection');
                            var ids = catGalIds.value ? catGalIds.value.split(',').map(x=>x.trim()).filter(x=>x.length>0) : [];
                            sel.map(function(att) {
                                var j = att.toJSON();
                                if (!ids.includes(j.id.toString())) {
                                    ids.push(j.id);
                                    var d = document.createElement('div');
                                    d.className = 'ahpm-gallery-item';
                                    d.innerHTML = '<img src="' + j.url + '" />';
                                    catGalGrid.appendChild(d);
                                }
                            });
                            catGalIds.value = ids.join(',');
                        });
                        cgFrame.open();
                    });
                }

                // Product Gallery Images Uploader
                var galleryBtn   = document.getElementById('ahpm-gallery-btn');
                var galleryInput = document.getElementById('ahpm_gallery_ids');
                var galleryGrid  = document.getElementById('ahpm-gallery-grid');

                if (galleryBtn) {
                    galleryBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var galleryFrame = wp.media({
                            title: 'Select Product Gallery Images',
                            button: { text: 'Add to Gallery' },
                            multiple: true
                        });

                        galleryFrame.on('select', function() {
                            var selection = galleryFrame.state().get('selection');
                            var ids = galleryInput.value ? galleryInput.value.split(',').map(x => x.trim()).filter(x => x.length > 0) : [];

                            selection.map(function(attachment) {
                                var json = attachment.toJSON();
                                if (!ids.includes(json.id.toString())) {
                                    ids.push(json.id);

                                    var div = document.createElement('div');
                                    div.className = 'ahpm-gallery-item';
                                    div.setAttribute('data-id', json.id);
                                    div.innerHTML = '<img src="' + json.url + '" /><span class="ahpm-gallery-remove" onclick="ahpmRemoveGalleryImg(this, ' . json.id + ')">✕</span>';
                                    galleryGrid.appendChild(div);
                                }
                            });

                            galleryInput.value = ids.join(',');
                        });

                        galleryFrame.open();
                    });
                }
            });
        </script>
        <?php
    }
}

Al_Hikmat_Product_Manager::get_instance();
