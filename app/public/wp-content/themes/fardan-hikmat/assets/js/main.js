/**
 * Main JavaScript — Fardan Al-Hikmat
 *
 * Handles:
 * - Navbar scroll behavior (transparent → frosted glass)
 * - Mobile menu toggle
 * - Scroll reveal animations (IntersectionObserver)
 * - Product tab filtering
 * - Hero counter animation
 * - Navbar active link
 * - Smooth scroll for anchor links
 * - Newsletter form submission
 */

(function () {
    'use strict';

    /* ─── Navbar Scroll Behavior ─── */
    var header = document.getElementById('site-header');

    if (header) {
        var scrollThreshold = 60;

        function updateNavbar() {
            var isFrontPage = document.body.classList.contains('is-front-page');
            if (!isFrontPage || window.scrollY > scrollThreshold) {
                header.classList.add('is-scrolled');
                header.classList.remove('is-transparent');
            } else {
                header.classList.remove('is-scrolled');
                header.classList.add('is-transparent');
            }
        }

        window.addEventListener('scroll', updateNavbar, { passive: true });
        updateNavbar();
    }

    /* ─── Mobile Menu Toggle ─── */
    var mobileToggle = document.getElementById('navbar-toggle');
    var navMenu      = document.getElementById('navbar-menu');

    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function () {
            var expanded = mobileToggle.getAttribute('aria-expanded') === 'true';
            mobileToggle.setAttribute('aria-expanded', String(!expanded));
            navMenu.classList.toggle('is-open');
            document.body.classList.toggle('menu-open');
        });

        // Close on link click
        navMenu.querySelectorAll('.navbar__link').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileToggle.setAttribute('aria-expanded', 'false');
                navMenu.classList.remove('is-open');
                document.body.classList.remove('menu-open');
            });
        });
    }

    /* ─── Scroll Reveal with IntersectionObserver ─── */
    var revealTargets = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

    if (revealTargets.length && 'IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15, rootMargin: '0px 0px -50px 0px' }
        );

        revealTargets.forEach(function (el) {
            revealObserver.observe(el);
        });
    } else {
        // Fallback: show all
        revealTargets.forEach(function (el) {
            el.classList.add('is-visible');
        });
    }

    /* ─── Product Tab Filtering ─── */
    var tabBtns     = document.querySelectorAll('[data-tab]');
    var tabPanels   = document.querySelectorAll('[data-tab-panel]');

    if (tabBtns.length) {
        tabBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = this.dataset.tab;

                // Update active tab button
                tabBtns.forEach(function (b) { b.classList.remove('is-active'); });
                this.classList.add('is-active');

                // Show/hide products
                var productCards = document.querySelectorAll('.product-card[data-tab-category]');

                productCards.forEach(function (card) {
                    if (target === 'all' || card.dataset.tabCategory === target) {
                        card.closest('.product-card-wrapper').style.display = '';
                        card.closest('.product-card-wrapper').classList.add('reveal');
                        setTimeout(function () {
                            card.closest('.product-card-wrapper').classList.add('is-visible');
                        }, 50);
                    } else {
                        card.closest('.product-card-wrapper').style.display = 'none';
                    }
                });
            });
        });
    }

    /* ─── Animated Counter (Hero Stats) ─── */
    function animateCounter(el, target, suffix, duration) {
        var start     = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            var current  = Math.floor(eased * target);

            el.textContent = current.toLocaleString() + suffix;

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);
    }

    var counterEls = document.querySelectorAll('[data-counter]');

    if (counterEls.length && 'IntersectionObserver' in window) {
        var counterObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var el      = entry.target;
                        var target  = parseInt(el.dataset.counter, 10);
                        var suffix  = el.dataset.counterSuffix || '';
                        var dur     = parseInt(el.dataset.counterDuration || '2000', 10);

                        animateCounter(el, target, suffix, dur);
                        counterObserver.unobserve(el);
                    }
                });
            },
            { threshold: 0.5 }
        );

        counterEls.forEach(function (el) { counterObserver.observe(el); });
    }

    /* ─── Category Card Hover Parallax ─── */
    document.querySelectorAll('.category-card').forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var rect    = card.getBoundingClientRect();
            var x       = (e.clientX - rect.left) / rect.width  - 0.5;
            var y       = (e.clientY - rect.top)  / rect.height - 0.5;
            var bg      = card.querySelector('.category-card__bg, .category-card__img');

            if (bg) {
                bg.style.transform = 'scale(1.08) translate(' + (x * 8) + 'px, ' + (y * 8) + 'px)';
            }
        });

        card.addEventListener('mouseleave', function () {
            var bg = card.querySelector('.category-card__bg, .category-card__img');
            if (bg) bg.style.transform = '';
        });
    });

    /* ─── Newsletter Form ─── */
    var newsletterForm = document.getElementById('newsletter-form');

    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var emailInput = this.querySelector('.newsletter-input');
            var email      = emailInput ? emailInput.value.trim() : '';

            if (!email || !isValidEmail(email)) {
                showFormError(emailInput, 'Please enter a valid email address.');
                return;
            }

            var btn = this.querySelector('[type="submit"]');
            if (btn) {
                btn.textContent = 'Subscribed!';
                btn.disabled    = true;
                btn.classList.add('btn-accent');
            }

            if (window.FardanDrawer) {
                window.FardanDrawer.toast('🌿 Welcome! Check your inbox for 10% off.');
            }

            if (emailInput) emailInput.value = '';
        });
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showFormError(input, msg) {
        if (!input) return;
        input.style.borderColor = 'rgba(255,100,100,0.8)';
        setTimeout(function () { input.style.borderColor = ''; }, 3000);
    }

    /* ─── Smooth Scroll ─── */
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var targetId = this.getAttribute('href').slice(1);
            var target   = document.getElementById(targetId);

            if (target) {
                e.preventDefault();
                var offset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--navbar-height') || '80');
                var top    = target.getBoundingClientRect().top + window.scrollY - offset - 16;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });

    /* ─── PDP Gallery Interaction ─── */
    var pdpThumbs = document.querySelectorAll('.pdp-gallery__thumb');
    var pdpMain   = document.querySelector('.pdp-gallery__main img');

    if (pdpThumbs.length && pdpMain) {
        pdpThumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                pdpThumbs.forEach(function (t) { t.classList.remove('is-active'); });
                this.classList.add('is-active');

                var newSrc = this.querySelector('img').src;
                pdpMain.style.opacity = '0';
                pdpMain.style.transform = 'scale(0.97)';

                setTimeout(function () {
                    pdpMain.src = newSrc;
                    pdpMain.style.opacity = '1';
                    pdpMain.style.transform = '';
                }, 200);
            });
        });

        pdpMain.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
    }

    /* ─── PDP Variant Selector ─── */
    document.querySelectorAll('.pdp-variant__btn, .variant-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = this.closest('.pdp-variant__options, .drawer-variant-options');
            if (group) {
                group.querySelectorAll('button').forEach(function (b) { b.classList.remove('is-selected'); });
            }
            this.classList.add('is-selected');

            // Update label
            var label = this.closest('.pdp-variant, .drawer-variant-section')?.querySelector('.pdp-variant__selected');
            if (label) label.textContent = '— ' + this.textContent;
        });
    });

    /* ─── PDP Add to Cart ─── */
    var pdpCartBtn = document.getElementById('pdp-add-to-cart');
    if (pdpCartBtn) {
        pdpCartBtn.addEventListener('click', function () {
            var qty    = document.getElementById('pdp-qty-input')?.value || 1;
            var title  = document.querySelector('.pdp-info__title')?.textContent || 'Product';

            if (window.FardanDrawer) {
                window.FardanDrawer.toast('🌿 ' + title + ' ×' + qty + ' added to cart!');
            }

            // Animate button
            this.textContent = '✓ Added!';
            this.classList.add('btn-accent');

            var self = this;
            setTimeout(function () {
                self.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Add to Cart';
                self.classList.remove('btn-accent');
            }, 2500);
        });
    }

    /* ─── Marquee pause on hover ─── */
    var trustTrack = document.querySelector('.trust-bar__track');
    if (trustTrack) {
        var trustBar = trustTrack.closest('.trust-bar');
        if (trustBar) {
            trustBar.addEventListener('mouseenter', function () {
                trustTrack.style.animationPlayState = 'paused';
            });
            trustBar.addEventListener('mouseleave', function () {
                trustTrack.style.animationPlayState = 'running';
            });
        }
    }

}());
