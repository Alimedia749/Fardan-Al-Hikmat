<?php
/**
 * Plugin Name: WhatsApp Order Engine for WooCommerce
 * Plugin URI:  https://example.com/whatsapp-order-engine
 * Description: Enables direct product ordering via WhatsApp with custom messaging and admin styling controls.
 * Version:     1.0.0
 * Author:      DevSuite
 * Text Domain: wa-order-engine
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WA_Order_Engine {

    private static $instance = null;
    private $option_group = 'wa_order_engine_settings';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        // Frontend Hooks
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_whatsapp_button_single' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'wp_head', array( $this, 'inject_button_styles' ) );

        // Hide Add to Cart if enabled
        add_action( 'wp', array( $this, 'conditional_hide_add_to_cart' ) );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'WhatsApp Order Settings', 'wa-order-engine' ),
            __( 'WhatsApp Order', 'wa-order-engine' ),
            'manage_options',
            'wa-order-engine',
            array( $this, 'render_admin_page' )
        );
    }

    public function register_settings() {
        register_setting( $this->option_group, 'wa_order_phone', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_phone_number' ),
            'default'           => ''
        ) );

        register_setting( $this->option_group, 'wa_order_message_template', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => "Hello, I want to buy {product_name}.\nPrice: {price}\nQuantity: {quantity}\nURL: {product_url}"
        ) );

        register_setting( $this->option_group, 'wa_order_btn_text', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'Order via WhatsApp'
        ) );

        register_setting( $this->option_group, 'wa_order_btn_bg', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#25D366'
        ) );

        register_setting( $this->option_group, 'wa_order_btn_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#FFFFFF'
        ) );

        register_setting( $this->option_group, 'wa_order_hide_add_to_cart', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false
        ) );
    }

    public function sanitize_phone_number( $phone ) {
        return preg_replace( '/[^0-9]/', '', $phone );
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WhatsApp Order Engine Settings', 'wa-order-engine' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( $this->option_group );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="wa_order_phone"><?php esc_html_e( 'WhatsApp Phone Number', 'wa-order-engine' ); ?></label></th>
                        <td>
                            <input type="text" id="wa_order_phone" name="wa_order_phone" value="<?php echo esc_attr( get_option( 'wa_order_phone' ) ); ?>" class="regular-text" placeholder="923001234567" required />
                            <p class="description"><?php esc_html_e( 'Include country code without + or zeros. Example: 923001234567', 'wa-order-engine' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wa_order_btn_text"><?php esc_html_e( 'Button Text', 'wa-order-engine' ); ?></label></th>
                        <td>
                            <input type="text" id="wa_order_btn_text" name="wa_order_btn_text" value="<?php echo esc_attr( get_option( 'wa_order_btn_text', 'Order via WhatsApp' ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wa_order_message_template"><?php esc_html_e( 'Message Template', 'wa-order-engine' ); ?></label></th>
                        <td>
                            <textarea id="wa_order_message_template" name="wa_order_message_template" rows="5" class="large-text"><?php echo esc_textarea( get_option( 'wa_order_message_template' ) ); ?></textarea>
                            <p class="description">Available Placeholders: <code>{product_name}</code>, <code>{price}</code>, <code>{quantity}</code>, <code>{product_url}</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wa_order_btn_bg"><?php esc_html_e( 'Button Background Color', 'wa-order-engine' ); ?></label></th>
                        <td>
                            <input type="color" id="wa_order_btn_bg" name="wa_order_btn_bg" value="<?php echo esc_attr( get_option( 'wa_order_btn_bg', '#25D366' ) ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wa_order_btn_color"><?php esc_html_e( 'Button Text Color', 'wa-order-engine' ); ?></label></th>
                        <td>
                            <input type="color" id="wa_order_btn_color" name="wa_order_btn_color" value="<?php echo esc_attr( get_option( 'wa_order_btn_color', '#FFFFFF' ) ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Hide Standard Add to Cart', 'wa-order-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wa_order_hide_add_to_cart" value="1" <?php checked( 1, get_option( 'wa_order_hide_add_to_cart' ), true ); ?> />
                                <?php esc_html_e( 'Hide default "Add to Cart" button on single product pages.', 'wa-order-engine' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_frontend_assets() {
        if ( ! is_product() ) {
            return;
        }

        wp_register_script( 'wa-order-js', false );
        wp_enqueue_script( 'wa-order-js' );

        $phone            = get_option( 'wa_order_phone' );
        $template         = get_option( 'wa_order_message_template' );
        global $product;

        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        if ( ! is_a( $product, 'WC_Product' ) || empty( $phone ) ) {
            return;
        }

        $js_data = array(
            'phone'        => esc_js( $phone ),
            'template'     => esc_js( $template ),
            'product_name' => esc_js( $product->get_name() ),
            'price'        => esc_js( wp_strip_all_tags( wc_price( $product->get_price() ) ) ),
            'product_url'  => esc_url( get_permalink( $product->get_id() ) )
        );

        $inline_script = "
            jQuery(document).ready(function($) {
                $('.wa-order-btn').on('click', function(e) {
                    e.preventDefault();
                    var data = " . wp_json_encode( $js_data ) . ";
                    var qty = $('input.qty').val() || 1;
                    
                    var message = data.template
                        .replace('{product_name}', data.product_name)
                        .replace('{price}', data.price)
                        .replace('{quantity}', qty)
                        .replace('{product_url}', data.product_url);

                    var url = 'https://api.whatsapp.com/send?phone=' + data.phone + '&text=' + encodeURIComponent(message);
                    window.open(url, '_blank');
                });
            });
        ";

        wp_add_inline_script( 'wa-order-js', $inline_script );
    }

    public function inject_button_styles() {
        if ( ! is_product() ) {
            return;
        }

        $bg    = get_option( 'wa_order_btn_bg', '#25D366' );
        $color = get_option( 'wa_order_btn_color', '#FFFFFF' );

        echo "<style>
            .wa-order-btn {
                background-color: " . esc_attr( $bg ) . " !important;
                color: " . esc_attr( $color ) . " !important;
                display: inline-block;
                padding: 12px 24px;
                font-weight: bold;
                border-radius: 4px;
                text-decoration: none;
                margin-top: 10px;
                cursor: pointer;
                border: none;
                transition: opacity 0.2s ease;
            }
            .wa-order-btn:hover {
                opacity: 0.9;
            }
        </style>";
    }

    public function render_whatsapp_button_single() {
        $phone = get_option( 'wa_order_phone' );
        if ( empty( $phone ) ) {
            return;
        }

        $btn_text = get_option( 'wa_order_btn_text', 'Order via WhatsApp' );
        echo '<a href="#" class="button wa-order-btn">' . esc_html( $btn_text ) . '</a>';
    }

    public function conditional_hide_add_to_cart() {
        // Preserved Add to Cart button & variation dropdown options (50g, 100g)
    }
}

WA_Order_Engine::get_instance();
