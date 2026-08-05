/**
 * Product Drawer — Fardan Al-Hikmat
 *
 * Handles slide-over product detail drawer:
 * - Opens on product card click
 * - Populates with product data from data attributes
 * - Manages gallery thumbnails
 * - Quantity selector
 * - Accordion panels
 * - Add to Cart with toast notification
 * - Keyboard & accessibility support
 */

(function () {
    'use strict';

    /* ─── Element References ─── */
    const overlay   = document.getElementById('drawer-overlay');
    const drawer    = document.getElementById('product-drawer');
    const closeBtn  = document.getElementById('drawer-close');
    const body      = document.body;

    if (!overlay || !drawer || !closeBtn) return;

    /* ─── State ─── */
    let currentProduct  = null;
    let isOpen          = false;

    /* ─── Open Drawer ─── */
    function openDrawer(productData) {
        currentProduct = productData;
        populateDrawer(productData);

        overlay.classList.add('is-open');
        drawer.classList.add('is-open');
        body.style.overflow = 'hidden';
        isOpen = true;

        // Focus close button for accessibility
        setTimeout(function () {
            closeBtn.focus();
        }, 500);

        // Track open in drawer body
        drawer.querySelector('.drawer-body').scrollTop = 0;
    }

    /* ─── Close Drawer ─── */
    function closeDrawer() {
        overlay.classList.remove('is-open');
        drawer.classList.remove('is-open');
        body.style.overflow = '';
        isOpen = false;

        // Return focus to triggering element
        if (currentProduct && currentProduct.triggerEl) {
            currentProduct.triggerEl.focus();
        }
    }

    /* ─── Populate Drawer with Product Data ─── */
    function populateDrawer(data) {
        // Category
        var catEl = drawer.querySelector('.drawer-product__category');
        if (catEl) catEl.textContent = data.category || '';

        // Title
        var titleEl = drawer.querySelector('.drawer-product__title');
        if (titleEl) titleEl.textContent = data.title || '';

        // Price
        var priceEl = drawer.querySelector('.drawer-price-current');
        if (priceEl) priceEl.textContent = data.price || '';

        var priceOldEl = drawer.querySelector('.drawer-price-old');
        if (priceOldEl) {
            priceOldEl.textContent = data.priceOld || '';
            priceOldEl.style.display = data.priceOld ? '' : 'none';
        }

        var saveBadge = drawer.querySelector('.drawer-product__save-badge');
        if (saveBadge) {
            if (data.priceOld && data.discount) {
                saveBadge.textContent = 'Save ' + data.discount;
                saveBadge.style.display = '';
            } else {
                saveBadge.style.display = 'none';
            }
        }

        // Short Description
        var descEl = drawer.querySelector('.drawer-product__desc');
        if (descEl) descEl.textContent = data.description || '';

        // Main image
        var mainImg = drawer.querySelector('.drawer-gallery__main img');
        if (mainImg) {
            mainImg.src = data.image || '';
            mainImg.alt = data.title || '';
        }

        // Benefits
        var benefitsList = drawer.querySelector('.drawer-benefits-list');
        if (benefitsList && data.benefits && data.benefits.length) {
            benefitsList.innerHTML = '';
            data.benefits.forEach(function (benefit) {
                var li = document.createElement('div');
                li.className = 'drawer-benefit-item';
                li.innerHTML = '<span class="drawer-benefit-check">✓</span><span>' + escapeHTML(benefit) + '</span>';
                benefitsList.appendChild(li);
            });
        }

        // Ingredients
        var ingredientsEl = drawer.querySelector('.drawer-ingredients-chips');
        if (ingredientsEl && data.ingredients && data.ingredients.length) {
            ingredientsEl.innerHTML = '';
            data.ingredients.forEach(function (ing) {
                var chip = document.createElement('span');
                chip.className = 'drawer-ingredient-chip';
                chip.textContent = ing;
                ingredientsEl.appendChild(chip);
            });
        }

        // Star Rating
        var starsEl = drawer.querySelector('.drawer-product__stars');
        if (starsEl) starsEl.textContent = buildStars(data.rating || 5);

        var ratingCountEl = drawer.querySelector('.drawer-product__rating-count');
        if (ratingCountEl) ratingCountEl.textContent = '(' + (data.ratingCount || 0) + ' reviews)';

        // Badges
        var badgesEl = drawer.querySelector('.drawer-gallery__badge');
        if (badgesEl) {
            badgesEl.innerHTML = '';
            if (data.isNew) {
                badgesEl.innerHTML += '<span class="badge badge-primary">New</span>';
            }
            if (data.isBestseller) {
                badgesEl.innerHTML += '<span class="badge badge-accent">Bestseller</span>';
            }
            if (data.discount) {
                badgesEl.innerHTML += '<span class="badge" style="background:#dcfce7;color:#15803d;">-' + data.discount + '</span>';
            }
        }

        // Dynamic Size/Weight Variations
        var variantOptions = drawer.querySelector('.drawer-variant-options');
        if (variantOptions) {
            if (data.variations && data.variations.length) {
                variantOptions.innerHTML = '';
                data.variations.forEach(function (v, idx) {
                    var btn = document.createElement('button');
                    btn.className = 'variant-card' + (idx === 0 ? ' is-selected' : '');
                    btn.type = 'button';
                    btn.dataset.price = v.price;
                    btn.dataset.priceOld = v.price_old || '';
                    btn.dataset.discount = v.discount || '';

                    var oldPriceHTML = v.price_old ? '<span class="variant-card__old">' + escapeHTML(v.price_old) + '</span>' : '';
                    var discountHTML = v.discount ? '<span class="variant-card__badge">-' + escapeHTML(v.discount) + '</span>' : '';

                    btn.innerHTML = '<span class="variant-card__weight">' + escapeHTML(v.size) + '</span>' +
                                    '<div class="variant-card__pricing">' +
                                        '<span class="variant-card__price">' + escapeHTML(v.price) + '</span>' +
                                        oldPriceHTML +
                                        discountHTML +
                                    '</div>';

                    btn.addEventListener('click', function () {
                        variantOptions.querySelectorAll('.variant-card').forEach(function (b) {
                            b.classList.remove('is-selected');
                        });
                        this.classList.add('is-selected');

                        if (priceEl) priceEl.textContent = this.dataset.price;
                        if (priceOldEl) {
                            priceOldEl.textContent = this.dataset.priceOld;
                            priceOldEl.style.display = this.dataset.priceOld ? '' : 'none';
                        }
                        if (saveBadge) {
                            if (this.dataset.priceOld && this.dataset.discount) {
                                saveBadge.textContent = 'Save ' + this.dataset.discount;
                                saveBadge.style.display = '';
                            } else {
                                saveBadge.style.display = 'none';
                            }
                        }

                        var label = drawer.querySelector('.drawer-variant-label span');
                        if (label) label.textContent = '— ' + v.size + ' (' + v.price + ')';
                    });

                    variantOptions.appendChild(btn);
                });

                var label = drawer.querySelector('.drawer-variant-label span');
                if (label) label.textContent = '— ' + data.variations[0].size + ' (' + data.variations[0].price + ')';
            }
        }

        // Reset quantity
        var qtyInput = drawer.querySelector('.qty-input');
        if (qtyInput) qtyInput.value = 1;
    }

    /* ─── Utility: Build Star String ─── */
    function buildStars(rating) {
        var full  = Math.floor(rating);
        var stars = '';
        for (var i = 0; i < 5; i++) {
            stars += i < full ? '★' : '☆';
        }
        return stars;
    }

    /* ─── Utility: Escape HTML ─── */
    function escapeHTML(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /* ─── Event: Open on Product Card Click ─── */
    document.querySelectorAll('[data-open-drawer]').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();

            var card = trigger.closest('.product-card') || trigger;

            var variations = [];
            try {
                if (card.dataset.productVariations) {
                    variations = JSON.parse(card.dataset.productVariations);
                }
            } catch (err) {}

            var data = {
                triggerEl:   trigger,
                title:       card.dataset.productTitle       || card.querySelector('.product-card__title')?.textContent || '',
                category:    card.dataset.productCategory    || card.querySelector('.product-card__category')?.textContent || '',
                price:       card.dataset.productPrice       || card.querySelector('.product-card__price-current')?.textContent || '',
                priceOld:    card.dataset.productPriceOld    || '',
                discount:    card.dataset.productDiscount    || '',
                description: card.dataset.productDescription || card.querySelector('.product-card__desc')?.textContent || '',
                image:       card.dataset.productImage       || card.querySelector('.product-card__image-wrap img')?.src || '',
                rating:      parseFloat(card.dataset.productRating || '4.8'),
                ratingCount: parseInt(card.dataset.productRatingCount || '0', 10),
                isNew:       card.dataset.productNew === 'true',
                isBestseller: card.dataset.productBestseller === 'true',
                benefits:    (card.dataset.productBenefits || '').split('|').filter(Boolean),
                ingredients: (card.dataset.productIngredients || '').split('|').filter(Boolean),
                variations:  variations,
            };

            openDrawer(data);
        });
    });

    /* ─── Event: Close ─── */
    closeBtn.addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);

    /* ─── Event: Keyboard ─── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            closeDrawer();
        }
    });

    /* ─── Trap Focus inside Drawer ─── */
    drawer.addEventListener('keydown', function (e) {
        if (!isOpen || e.key !== 'Tab') return;

        var focusable = drawer.querySelectorAll(
            'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
        );

        if (!focusable.length) return;

        var first = focusable[0];
        var last  = focusable[focusable.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });

    /* ─── Quantity Selector ─── */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.qty-btn');
        if (!btn) return;

        var input = btn.closest('.qty-selector')?.querySelector('.qty-input');
        if (!input) return;

        var val = parseInt(input.value, 10) || 1;

        if (btn.dataset.action === 'minus') {
            input.value = Math.max(1, val - 1);
        } else if (btn.dataset.action === 'plus') {
            input.value = val + 1;
        }
    });

    /* ─── Add to Cart — Drawer ─── */
    var drawerCartBtn = document.getElementById('drawer-add-to-cart');
    if (drawerCartBtn) {
        drawerCartBtn.addEventListener('click', function () {
            var qty = drawer.querySelector('.qty-input')?.value || 1;
            showToast('🌿 ' + (currentProduct?.title || 'Product') + ' ×' + qty + ' added to cart!');
        });
    }

    /* ─── Buy Now — Drawer ─── */
    var drawerBuyBtn = document.getElementById('drawer-buy-now');
    if (drawerBuyBtn) {
        drawerBuyBtn.addEventListener('click', function () {
            showToast('Redirecting to checkout…');
        });
    }

    /* ─── Accordion in Drawer ─── */
    document.querySelectorAll('.accordion-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var expanded  = this.getAttribute('aria-expanded') === 'true';
            var targetId  = this.getAttribute('aria-controls');
            var content   = document.getElementById(targetId);

            if (!content) return;

            // Close siblings in same accordion group
            var group = this.closest('[data-accordion-group]');
            if (group) {
                group.querySelectorAll('.accordion-trigger').forEach(function (sib) {
                    if (sib !== trigger) {
                        sib.setAttribute('aria-expanded', 'false');
                        var sibId = sib.getAttribute('aria-controls');
                        var sibContent = document.getElementById(sibId);
                        if (sibContent) sibContent.classList.remove('is-open');
                    }
                });
            }

            // Toggle current
            this.setAttribute('aria-expanded', String(!expanded));
            content.classList.toggle('is-open', !expanded);
        });
    });

    /* ─── Toast Notification ─── */
    var toastEl = document.getElementById('aura-toast');

    function showToast(message) {
        if (!toastEl) return;

        toastEl.querySelector('.toast-message').textContent = message;
        toastEl.classList.add('is-visible');

        clearTimeout(toastEl._timer);
        toastEl._timer = setTimeout(function () {
            toastEl.classList.remove('is-visible');
        }, 3500);
    }

    /* ─── Expose globally for PDP page ─── */
    window.FardanDrawer = {
        open:  openDrawer,
        close: closeDrawer,
        toast: showToast,
    };

}());
