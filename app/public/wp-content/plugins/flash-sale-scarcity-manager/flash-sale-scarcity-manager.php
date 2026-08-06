<?php
/**
 * Plugin Name: Flash Sale & Scarcity Countdown Manager for WooCommerce
 * Plugin URI:  https://example.com/flash-sale-scarcity-manager
 * Description: Boost conversions with dynamic countdown timers, stock scarcity bars, and order cutoff dispatch indicators.
 * Version:     1.0.0
 * Author:      DevSuite
 * Text Domain: flash-sale-scarcity
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

class Flash_Sale_Scarcity_Manager {

    private static $instance = null;
    private $option_group = 'fssm_settings_group';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Single Product Page Displays
        add_action( 'woocommerce_single_product_summary', array( $this, 'render_scarcity_widget' ), 15 );

        // Shop Loop Catalog Badges
        add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'render_loop_timer_badge' ), 15 );

        // Enqueue Assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'wp_head', array( $this, 'inject_custom_styles' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Scarcity Manager', 'flash-sale-scarcity' ),
            __( 'Scarcity Manager', 'flash-sale-scarcity' ),
            'manage_options',
            'scarcity-manager',
            array( $this, 'render_admin_page' ),
            'dashicons-clock',
            58
        );
    }

    public function register_settings() {
        register_setting( $this->option_group, 'fssm_timer_type', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'evergreen' ) );
        register_setting( $this->option_group, 'fssm_evergreen_minutes', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 15 ) );
        register_setting( $this->option_group, 'fssm_fixed_endtime', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '23:59:59' ) );
        register_setting( $this->option_group, 'fssm_enable_stock_bar', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
        register_setting( $this->option_group, 'fssm_enable_faux_scarcity', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
        register_setting( $this->option_group, 'fssm_faux_min', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 3 ) );
        register_setting( $this->option_group, 'fssm_faux_max', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 7 ) );
        register_setting( $this->option_group, 'fssm_cutoff_hour', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 17 ) );
        register_setting( $this->option_group, 'fssm_bg_color', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#fff5f5' ) );
        register_setting( $this->option_group, 'fssm_text_color', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#c53030' ) );
        register_setting( $this->option_group, 'fssm_bar_color', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#e53e3e' ) );
        register_setting( $this->option_group, 'fssm_animation', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'pulse' ) );
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Flash Sale & Scarcity Countdown Settings', 'flash-sale-scarcity' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( $this->option_group );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="fssm_timer_type"><?php esc_html_e( 'Countdown Timer Mode', 'flash-sale-scarcity' ); ?></label></th>
                        <td>
                            <select id="fssm_timer_type" name="fssm_timer_type">
                                <option value="evergreen" <?php selected( get_option( 'fssm_timer_type', 'evergreen' ), 'evergreen' ); ?>><?php esc_html_e( 'Evergreen Personal Timer (Per user session)', 'flash-sale-scarcity' ); ?></option>
                                <option value="midnight" <?php selected( get_option( 'fssm_timer_type' ), 'midnight' ); ?>><?php esc_html_e( 'Midnight Reset Timer (Resets daily at 23:59)', 'flash-sale-scarcity' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fssm_evergreen_minutes"><?php esc_html_e( 'Evergreen Timer Duration (Minutes)', 'flash-sale-scarcity' ); ?></label></th>
                        <td>
                            <input type="number" id="fssm_evergreen_minutes" name="fssm_evergreen_minutes" value="<?php echo esc_attr( get_option( 'fssm_evergreen_minutes', 15 ) ); ?>" min="1" max="1440" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Stock Scarcity Counter', 'flash-sale-scarcity' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="fssm_enable_stock_bar" value="1" <?php checked( 1, get_option( 'fssm_enable_stock_bar', true ) ); ?> />
                                <?php esc_html_e( 'Display stock scarcity text and progress bar.', 'flash-sale-scarcity' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Faux Scarcity Fallback', 'flash-sale-scarcity' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="fssm_enable_faux_scarcity" value="1" <?php checked( 1, get_option( 'fssm_enable_faux_scarcity', true ) ); ?> />
                                <?php esc_html_e( 'Show randomized low stock range (e.g. 3-7 left) for unmanaged inventory.', 'flash-sale-scarcity' ); ?>
                            </label>
                            <br/><br/>
                            <label>Min: <input type="number" name="fssm_faux_min" value="<?php echo esc_attr( get_option( 'fssm_faux_min', 3 ) ); ?>" style="width:60px;" /></label>
                            <label style="margin-left:15px;">Max: <input type="number" name="fssm_faux_max" value="<?php echo esc_attr( get_option( 'fssm_faux_max', 7 ) ); ?>" style="width:60px;" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fssm_cutoff_hour"><?php esc_html_e( 'Same-Day Dispatch Cutoff Hour (24h format)', 'flash-sale-scarcity' ); ?></label></th>
                        <td>
                            <input type="number" id="fssm_cutoff_hour" name="fssm_cutoff_hour" value="<?php echo esc_attr( get_option( 'fssm_cutoff_hour', 17 ) ); ?>" min="1" max="23" />
                            <p class="description"><?php esc_html_e( 'Default 17 = 5:00 PM. Displays "Order within X hrs to get dispatched today!".', 'flash-sale-scarcity' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fssm_bg_color"><?php esc_html_e( 'Widget Background Color', 'flash-sale-scarcity' ); ?></label></th>
                        <td>
                            <input type="color" id="fssm_bg_color" name="fssm_bg_color" value="<?php echo esc_attr( get_option( 'fssm_bg_color', '#fff5f5' ) ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fssm_text_color"><?php esc_html_e( 'Text Color', 'flash-sale-scarcity' ); ?></label></th>
                        <td>
                            <input type="color" id="fssm_text_color" name="fssm_text_color" value="<?php echo esc_attr( get_option( 'fssm_text_color', '#c53030' ) ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fssm_bar_color"><?php esc_html_e( 'Progress Bar Color', 'flash-sale-scarcity' ); ?></label></th>
                        <td>
                            <input type="color" id="fssm_bar_color" name="fssm_bar_color" value="<?php echo esc_attr( get_option( 'fssm_bar_color', '#e53e3e' ) ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fssm_animation"><?php esc_html_e( 'Urgency Animation Effect', 'flash-sale-scarcity' ); ?></label></th>
                        <td>
                            <select id="fssm_animation" name="fssm_animation">
                                <option value="pulse" <?php selected( get_option( 'fssm_animation', 'pulse' ), 'pulse' ); ?>>Pulse Glow</option>
                                <option value="shake" <?php selected( get_option( 'fssm_animation' ), 'shake' ); ?>>Subtle Shake</option>
                                <option value="none" <?php selected( get_option( 'fssm_animation' ), 'none' ); ?>>Static (None)</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function inject_custom_styles() {
        $bg        = get_option( 'fssm_bg_color', '#fff5f5' );
        $text      = get_option( 'fssm_text_color', '#c53030' );
        $bar       = get_option( 'fssm_bar_color', '#e53e3e' );
        $animation = get_option( 'fssm_animation', 'pulse' );

        echo "<style>
            .fssm-scarcity-box {
                background-color: {$bg};
                color: {$text};
                padding: 15px;
                border-radius: 8px;
                border: 1px solid {$text};
                margin-bottom: 20px;
                font-family: inherit;
            }
            .fssm-timer-display {
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 1px;
                display: inline-block;
                padding: 4px 8px;
                background: #ffffff;
                border-radius: 4px;
                margin-left: 5px;
            }
            .fssm-progress-bar-bg {
                background: #e2e8f0;
                height: 10px;
                border-radius: 5px;
                overflow: hidden;
                margin-top: 8px;
            }
            .fssm-progress-bar-fill {
                background: {$bar};
                height: 100%;
                width: 75%;
                transition: width 0.5s ease;
            }
            @keyframes fssmPulse {
                0% { box-shadow: 0 0 0 0 rgba(229, 62, 62, 0.4); }
                70% { box-shadow: 0 0 0 10px rgba(229, 62, 62, 0); }
                100% { box-shadow: 0 0 0 0 rgba(229, 62, 62, 0); }
            }
            @keyframes fssmShake {
                0%, 100% { transform: translateX(0); }
                20%, 60% { transform: translateX(-3px); }
                40%, 80% { transform: translateX(3px); }
            }
            .fssm-anim-pulse { animation: fssmPulse 2s infinite; }
            .fssm-anim-shake { animation: fssmShake 3s infinite; }
        </style>";
    }

    /**
     * Render Single Product Scarcity Widget
     */
    public function render_scarcity_widget() {
        global $product;

        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        if ( ! is_a( $product, 'WC_Product' ) || ! $product->is_in_stock() ) {
            return;
        }

        $stock_count = $product->get_stock_quantity();
        if ( null === $stock_count && get_option( 'fssm_enable_faux_scarcity', true ) ) {
            $min = get_option( 'fssm_faux_min', 3 );
            $max = get_option( 'fssm_faux_max', 7 );
            $stock_count = rand( $min, $max );
        }

        $timer_type = get_option( 'fssm_timer_type', 'evergreen' );
        $duration   = get_option( 'fssm_evergreen_minutes', 15 );
        $anim_class = 'pulse' === get_option( 'fssm_animation' ) ? 'fssm-anim-pulse' : ( 'shake' === get_option( 'fssm_animation' ) ? 'fssm-anim-shake' : '' );
        ?>
        <div class="fssm-scarcity-box <?php echo esc_attr( $anim_class ); ?>" data-timer-type="<?php echo esc_attr( $timer_type ); ?>" data-duration="<?php echo esc_attr( $duration ); ?>">
            <div style="font-weight: bold; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                <span>🔥 <?php esc_html_e( 'Special Offer Ends In:', 'flash-sale-scarcity' ); ?></span>
                <span class="fssm-timer-display" id="fssm-countdown">--:--</span>
            </div>

            <?php if ( get_option( 'fssm_enable_stock_bar', true ) && $stock_count ) : ?>
                <div style="font-size: 13px; margin-top: 8px;">
                    <strong>⚠️ <?php printf( esc_html__( 'Hurry! Only %d items remaining in stock!', 'flash-sale-scarcity' ), esc_html( $stock_count ) ); ?></strong>
                </div>
                <div class="fssm-progress-bar-bg">
                    <div class="fssm-progress-bar-fill" style="width: <?php echo esc_attr( min( 100, max( 15, $stock_count * 10 ) ) ); ?>%;"></div>
                </div>
            <?php endif; ?>

            <div id="fssm-cutoff-notice" style="font-size: 12px; margin-top: 8px; font-style: italic;">
                🚚 <span id="fssm-cutoff-text"><?php esc_html_e( 'Order soon for Same-Day Dispatch!', 'flash-sale-scarcity' ); ?></span>
            </div>
        </div>
        <?php
    }

    public function render_loop_timer_badge() {
        global $product;

        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        if ( ! is_a( $product, 'WC_Product' ) || ! $product->is_on_sale() ) {
            return;
        }

        echo '<div style="font-size: 11px; font-weight: bold; color: #c53030; margin-top: 4px;">⚡ ' . esc_html__( 'Limited Time Offer', 'flash-sale-scarcity' ) . '</div>';
    }

    public function enqueue_frontend_assets() {
        if ( ! is_product() ) {
            return;
        }

        wp_register_script( 'fssm-vanilla-js', false );
        wp_enqueue_script( 'fssm-vanilla-js' );

        $cutoff_hour = get_option( 'fssm_cutoff_hour', 17 );

        // Pure Vanilla JavaScript implementation for zero latency & zero dependencies
        $vanilla_js = "
            document.addEventListener('DOMContentLoaded', function() {
                var widget = document.querySelector('.fssm-scarcity-box');
                if (!widget) return;

                var timerType = widget.getAttribute('data-timer-type') || 'evergreen';
                var durationMinutes = parseInt(widget.getAttribute('data-duration')) || 15;
                var display = document.getElementById('fssm-countdown');

                var endTimeKey = 'fssm_end_time_' + window.location.pathname;
                var targetTime;

                if (timerType === 'evergreen') {
                    var stored = localStorage.getItem(endTimeKey);
                    if (!stored || parseInt(stored) < Date.now()) {
                        targetTime = Date.now() + (durationMinutes * 60 * 1000);
                        localStorage.setItem(endTimeKey, targetTime);
                    } else {
                        targetTime = parseInt(stored);
                    }
                } else {
                    var now = new Date();
                    var midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    targetTime = midnight.getTime();
                }

                function updateTimer() {
                    var now = Date.now();
                    var diff = targetTime - now;

                    if (diff <= 0) {
                        if (timerType === 'evergreen') {
                            targetTime = Date.now() + (durationMinutes * 60 * 1000);
                            localStorage.setItem(endTimeKey, targetTime);
                        } else {
                            display.textContent = '00:00:00';
                            return;
                        }
                    }

                    var totalSec = Math.floor(diff / 1000);
                    var hrs = Math.floor(totalSec / 3600);
                    var mins = Math.floor((totalSec % 3600) / 60);
                    var secs = totalSec % 60;

                    var hrsStr = hrs < 10 ? '0' + hrs : hrs;
                    var minsStr = mins < 10 ? '0' + mins : mins;
                    var secsStr = secs < 10 ? '0' + secs : secs;

                    display.textContent = (hrs > 0 ? hrsStr + ':' : '') + minsStr + ':' + secsStr;
                }

                updateTimer();
                setInterval(updateTimer, 1000);

                // Cutoff Dispatch Calculation
                var cutoffText = document.getElementById('fssm-cutoff-text');
                if (cutoffText) {
                    var now = new Date();
                    var cutoffHour = " . absint( $cutoff_hour ) . ";
                    var cutoffTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), cutoffHour, 0, 0);

                    if (now < cutoffTime) {
                        var diffMs = cutoffTime - now;
                        var diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                        var diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                        cutoffText.textContent = 'Order within ' + diffHrs + ' hrs ' + diffMins + ' mins to get it dispatched Today!';
                    } else {
                        cutoffText.textContent = 'Order now for Dispatch Tomorrow Morning!';
                    }
                }
            });
        ";

        wp_add_inline_script( 'fssm-vanilla-js', $vanilla_js );
    }
}

Flash_Sale_Scarcity_Manager::get_instance();
