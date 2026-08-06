<?php
/**
 * Plugin Name: Custom Product Personalization & Live Preview Tool
 * Plugin URI:  https://example.com/wc-product-personalizer
 * Description: Interactive HTML5 canvas customizer with live text, color picker, image upload, and dynamic print add-on pricing.
 * Version:     1.0.0
 * Author:      DevSuite
 * Text Domain: wc-product-personalizer
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

class WC_Product_Personalizer {

    private static $instance = null;
    private $option_group = 'wcpp_settings_group';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin Settings
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Single Product Canvas Editor
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render_canvas_customizer_widget' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Add Customization Data & Extra Fee to Cart
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_customization_to_cart_item' ), 10, 3 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'display_customization_in_cart' ), 10, 2 );
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_customization_add_on_fee' ), 20, 1 );

        // Order Meta Attachment
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'attach_customization_to_order_item' ), 10, 4 );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Product Customizer', 'wc-product-personalizer' ),
            __( 'Product Customizer', 'wc-product-personalizer' ),
            'manage_options',
            'product-customizer',
            array( $this, 'render_admin_page' ),
            'dashicons-art',
            62
        );
    }

    public function register_settings() {
        register_setting( $this->option_group, 'wcpp_custom_text_fee', array( 'type' => 'number', 'sanitize_callback' => 'floatval', 'default' => 2.0 ) );
        register_setting( $this->option_group, 'wcpp_image_upload_fee', array( 'type' => 'number', 'sanitize_callback' => 'floatval', 'default' => 5.0 ) );
        register_setting( $this->option_group, 'wcpp_enabled_globally', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Product Personalization & Live Canvas Configurator Settings', 'wc-product-personalizer' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( $this->option_group );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Live Canvas Configurator', 'wc-product-personalizer' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wcpp_enabled_globally" value="1" <?php checked( 1, get_option( 'wcpp_enabled_globally', true ) ); ?> />
                                <?php esc_html_e( 'Display live HTML5 canvas mockup customizer on single product pages.', 'wc-product-personalizer' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcpp_custom_text_fee"><?php esc_html_e( 'Custom Text Add-on Fee ($)', 'wc-product-personalizer' ); ?></label></th>
                        <td>
                            <input type="number" step="0.5" id="wcpp_custom_text_fee" name="wcpp_custom_text_fee" value="<?php echo esc_attr( get_option( 'wcpp_custom_text_fee', 2.0 ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcpp_image_upload_fee"><?php esc_html_e( 'Custom Logo/Image Upload Fee ($)', 'wc-product-personalizer' ); ?></label></th>
                        <td>
                            <input type="number" step="0.5" id="wcpp_image_upload_fee" name="wcpp_image_upload_fee" value="<?php echo esc_attr( get_option( 'wcpp_image_upload_fee', 5.0 ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render Canvas Configurator Widget on Single Product Page
     */
    public function render_canvas_customizer_widget() {
        if ( ! get_option( 'wcpp_enabled_globally', true ) ) {
            return;
        }

        $text_fee  = (float) get_option( 'wcpp_custom_text_fee', 2.0 );
        $image_fee = (float) get_option( 'wcpp_image_upload_fee', 5.0 );
        ?>
        <div id="wcpp-customizer-container" style="margin-bottom: 25px; border-radius: 12px; border: 1px solid #cbd5e1; background: #ffffff; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <!-- ON / OFF Toggle Header -->
            <div id="wcpp-toggle-header" style="padding: 14px 18px; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;">
                <label for="wcpp-enable-toggle" style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; color: #1e293b; font-size: 14px; margin: 0;">
                    <input type="checkbox" id="wcpp-enable-toggle" name="wcpp_enable_customization" value="1" style="width: 18px; height: 18px; accent-color: #2D5016; cursor: pointer;" />
                    <span>🎨 <?php esc_html_e( 'Want to customize this product? (+Custom Label/Logo)', 'wc-product-personalizer' ); ?></span>
                </label>
                <span id="wcpp-toggle-badge" style="font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 20px; background: #e2e8f0; color: #64748b; transition: all 0.2s ease;">OFF</span>
            </div>

            <!-- Customizer Body (Hidden by default, shown when ON) -->
            <div id="wcpp-customizer-body" style="display: none; padding: 18px; border-top: 1px solid #cbd5e1; background: #fafafa;">
                <div style="display:flex; gap:20px; flex-wrap:wrap; align-items:flex-start;">
                    <!-- HTML5 Canvas Preview Area -->
                    <div style="position:relative; background:#fff; border:1px solid #ccc; border-radius:8px; padding:5px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <canvas id="wcpp-canvas" width="260" height="260" style="border:1px dashed #94a3b8; border-radius:6px; display:block; cursor:crosshair;"></canvas>
                    </div>

                    <!-- Controls Panel -->
                    <div style="flex:1; min-width:220px;">
                        <div style="margin-bottom:12px;">
                            <label style="font-weight:bold; font-size:13px; display:block; margin-bottom:4px; color:#334155;"><?php esc_html_e( 'Custom Text:', 'wc-product-personalizer' ); ?> (+<?php echo wc_price( $text_fee ); ?>)</label>
                            <input type="text" id="wcpp-text-input" placeholder="Type name or custom text..." class="input-text" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #cbd5e1;" />
                        </div>

                        <div style="margin-bottom:12px; display:flex; gap:10px; align-items:center;">
                            <label style="font-weight:bold; font-size:13px; color:#334155;"><?php esc_html_e( 'Text Color:', 'wc-product-personalizer' ); ?></label>
                            <input type="color" id="wcpp-color-picker" value="#000000" style="cursor:pointer; border:1px solid #cbd5e1; border-radius:4px; width:36px; height:32px; padding:2px;" />
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="font-weight:bold; font-size:13px; display:block; margin-bottom:4px; color:#334155;"><?php esc_html_e( 'Upload Logo/Design:', 'wc-product-personalizer' ); ?> (+<?php echo wc_price( $image_fee ); ?>)</label>
                            <input type="file" id="wcpp-image-file" accept="image/*" style="font-size:12px;" />
                        </div>

                        <button type="button" id="wcpp-reset-canvas" class="button" style="font-size:11px; font-weight:600;"><?php esc_html_e( 'Reset Customization', 'wc-product-personalizer' ); ?></button>
                    </div>
                </div>

                <!-- Hidden Inputs for Cart Transmission -->
                <input type="hidden" name="wcpp_custom_text" id="wcpp-hidden-text" value="" />
                <input type="hidden" name="wcpp_canvas_preview" id="wcpp-hidden-preview" value="" />
                <input type="hidden" name="wcpp_has_image" id="wcpp-hidden-has-image" value="0" />
            </div>
        </div>
        <?php
    }

    public function enqueue_frontend_assets() {
        if ( ! is_product() ) {
            return;
        }

        wp_register_script( 'wcpp-canvas-js', false );
        wp_enqueue_script( 'wcpp-canvas-js' );

        // Pure HTML5 Canvas rendering engine for real-time text & image preview
        $inline_js = "
            document.addEventListener('DOMContentLoaded', function() {
                var toggleInput = document.getElementById('wcpp-enable-toggle');
                var customBody  = document.getElementById('wcpp-customizer-body');
                var toggleBadge = document.getElementById('wcpp-toggle-badge');

                var canvas = document.getElementById('wcpp-canvas');
                if (!canvas) return;
                var ctx = canvas.getContext('2d');

                var textInput  = document.getElementById('wcpp-text-input');
                var colorPicker= document.getElementById('wcpp-color-picker');
                var imageFile  = document.getElementById('wcpp-image-file');
                var resetBtn   = document.getElementById('wcpp-reset-canvas');

                var hiddenText = document.getElementById('wcpp-hidden-text');
                var hiddenPreview = document.getElementById('wcpp-hidden-preview');
                var hiddenImage = document.getElementById('wcpp-hidden-has-image');

                var uploadedImg = null;

                function updateToggleState() {
                    if (toggleInput && toggleInput.checked) {
                        customBody.style.display = 'block';
                        toggleBadge.textContent = 'ON';
                        toggleBadge.style.background = '#2D5016';
                        toggleBadge.style.color = '#ffffff';
                        drawCanvas();
                    } else {
                        customBody.style.display = 'none';
                        toggleBadge.textContent = 'OFF';
                        toggleBadge.style.background = '#e2e8f0';
                        toggleBadge.style.color = '#64748b';
                        if (hiddenText) hiddenText.value = '';
                        if (hiddenPreview) hiddenPreview.value = '';
                        if (hiddenImage) hiddenImage.value = '0';
                    }
                }

                if (toggleInput) {
                    toggleInput.addEventListener('change', updateToggleState);
                }

                function drawCanvas() {
                    // Clear background
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    // Draw Mockup Guide Box
                    ctx.strokeStyle = '#e0e0e0';
                    ctx.setLineDash([4, 4]);
                    ctx.strokeRect(20, 20, 220, 220);
                    ctx.setLineDash([]);

                    // Draw Uploaded Image if available
                    if (uploadedImg) {
                        ctx.drawImage(uploadedImg, 50, 50, 160, 160);
                    }

                    // Draw Text
                    var val = textInput.value;
                    if (val) {
                        ctx.font = 'bold 20px sans-serif';
                        ctx.fillStyle = colorPicker.value;
                        ctx.textAlign = 'center';
                        ctx.fillText(val, canvas.width / 2, 220);
                    }

                    // Save state
                    hiddenText.value = val;
                    hiddenPreview.value = canvas.toDataURL('image/png');
                }

                textInput.addEventListener('input', drawCanvas);
                colorPicker.addEventListener('input', drawCanvas);

                imageFile.addEventListener('change', function(e) {
                    var file = e.target.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function(evt) {
                            var img = new Image();
                            img.onload = function() {
                                uploadedImg = img;
                                hiddenImage.value = '1';
                                drawCanvas();
                            };
                            img.src = evt.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });

                resetBtn.addEventListener('click', function() {
                    textInput.value = '';
                    uploadedImg = null;
                    imageFile.value = '';
                    hiddenImage.value = '0';
                    drawCanvas();
                });
            });
        ";

        wp_add_inline_script( 'wcpp-canvas-js', $inline_js );
    }

    public function add_customization_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
        if ( isset( $_POST['wcpp_enable_customization'] ) && '1' === $_POST['wcpp_enable_customization'] ) {
            if ( isset( $_POST['wcpp_custom_text'] ) && ! empty( $_POST['wcpp_custom_text'] ) ) {
                $cart_item_data['wcpp_custom_text'] = sanitize_text_field( $_POST['wcpp_custom_text'] );
            }
            if ( isset( $_POST['wcpp_has_image'] ) && '1' === $_POST['wcpp_has_image'] ) {
                $cart_item_data['wcpp_has_image'] = true;
            }
            if ( isset( $_POST['wcpp_canvas_preview'] ) && ! empty( $_POST['wcpp_canvas_preview'] ) ) {
                $cart_item_data['wcpp_canvas_preview'] = sanitize_text_field( $_POST['wcpp_canvas_preview'] );
            }
        }
        return $cart_item_data;
    }

    public function display_customization_in_cart( $item_data, $cart_item ) {
        if ( isset( $cart_item['wcpp_custom_text'] ) ) {
            $item_data[] = array(
                'key'   => __( 'Custom Print Text', 'wc-product-personalizer' ),
                'value' => esc_html( $cart_item['wcpp_custom_text'] )
            );
        }
        if ( isset( $cart_item['wcpp_has_image'] ) && $cart_item['wcpp_has_image'] ) {
            $item_data[] = array(
                'key'   => __( 'Custom Logo Upload', 'wc-product-personalizer' ),
                'value' => __( 'Yes (Image Attached)', 'wc-product-personalizer' )
            );
        }
        return $item_data;
    }

    public function apply_customization_add_on_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        $text_fee  = (float) get_option( 'wcpp_custom_text_fee', 2.0 );
        $image_fee = (float) get_option( 'wcpp_image_upload_fee', 5.0 );

        foreach ( $cart->get_cart() as $cart_item ) {
            $extra = 0;
            if ( ! empty( $cart_item['wcpp_custom_text'] ) ) {
                $extra += $text_fee;
            }
            if ( ! empty( $cart_item['wcpp_has_image'] ) ) {
                $extra += $image_fee;
            }

            if ( $extra > 0 ) {
                $orig_price = $cart_item['data']->get_price();
                $cart_item['data']->set_price( $orig_price + $extra );
            }
        }
    }

    public function attach_customization_to_order_item( $item, $cart_item_key, $values, $order ) {
        if ( isset( $values['wcpp_custom_text'] ) ) {
            $item->add_meta_data( __( 'Custom Text', 'wc-product-personalizer' ), $values['wcpp_custom_text'] );
        }
        if ( isset( $values['wcpp_has_image'] ) && $values['wcpp_has_image'] ) {
            $item->add_meta_data( __( 'Custom Image Uploaded', 'wc-product-personalizer' ), 'Yes' );
        }
    }
}

WC_Product_Personalizer::get_instance();
