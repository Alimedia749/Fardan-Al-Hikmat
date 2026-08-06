<?php
/**
 * Plugin Name: Dynamic Bundle & Quantity Discount Engine
 * Plugin URI:  https://example.com/dynamic-bundle-discount
 * Description: Enables quantity-based tiered discounts for WooCommerce products with custom pricing tables and cart calculation hooks.
 * Version:     1.0.0
 * Author:      DevSuite
 * Text Domain: bundle-discount-engine
 * WC requires at least: 5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Bundle_Discount_Engine {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin Product Meta Box
        add_action( 'add_meta_boxes', array( $this, 'add_discount_meta_box' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_discount_meta_box' ) );

        // Frontend Displays
        add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'render_discount_table' ) );

        // Cart Discounts Hook
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_tiered_discounts' ), 20, 1 );
    }

    // 1. Add Meta Box to Product Screen
    public function add_discount_meta_box() {
        add_meta_box(
            'bde_discount_rules',
            __( 'Quantity Tier Discounts', 'bundle-discount-engine' ),
            array( $this, 'render_meta_box_content' ),
            'product',
            'normal',
            'default'
        );
    }

    public function render_meta_box_content( $post ) {
        wp_nonce_field( 'bde_save_meta_box', 'bde_meta_box_nonce' );

        $tier1_qty     = get_post_meta( $post->ID, '_bde_tier1_qty', true );
        $tier1_discount= get_post_meta( $post->ID, '_bde_tier1_discount', true );
        $tier2_qty     = get_post_meta( $post->ID, '_bde_tier2_qty', true );
        $tier2_discount= get_post_meta( $post->ID, '_bde_tier2_discount', true );
        ?>
        <p>Set quantity thresholds and percentage discount rates for this product.</p>
        <table class="form-table">
            <tr>
                <th><label>Tier 1 (Min Qty / Discount %)</label></th>
                <td>
                    <input type="number" min="1" name="bde_tier1_qty" value="<?php echo esc_attr( $tier1_qty ); ?>" placeholder="e.g. 2" style="width: 80px;" />
                    <span>Qty &rarr;</span>
                    <input type="number" step="0.1" min="0" max="100" name="bde_tier1_discount" value="<?php echo esc_attr( $tier1_discount ); ?>" placeholder="e.g. 10" style="width: 80px;" />
                    <span>% Off</span>
                </td>
            </tr>
            <tr>
                <th><label>Tier 2 (Min Qty / Discount %)</label></th>
                <td>
                    <input type="number" min="1" name="bde_tier2_qty" value="<?php echo esc_attr( $tier2_qty ); ?>" placeholder="e.g. 5" style="width: 80px;" />
                    <span>Qty &rarr;</span>
                    <input type="number" step="0.1" min="0" max="100" name="bde_tier2_discount" value="<?php echo esc_attr( $tier2_discount ); ?>" placeholder="e.g. 20" style="width: 80px;" />
                    <span>% Off</span>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_discount_meta_box( $post_id ) {
        if ( ! isset( $_POST['bde_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['bde_meta_box_nonce'], 'bde_save_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = array( 'bde_tier1_qty', 'bde_tier1_discount', 'bde_tier2_qty', 'bde_tier2_discount' );

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = sanitize_text_field( $_POST[ $field ] );
                update_post_meta( $post_id, '_' . $field, $value );
            }
        }
    }

    // 2. Display Discount Table on Single Product Page
    public function render_discount_table() {
        global $product;

        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        if ( ! is_a( $product, 'WC_Product' ) ) {
            return;
        }

        $tier1_qty      = get_post_meta( $product->get_id(), '_bde_tier1_qty', true );
        $tier1_discount = get_post_meta( $product->get_id(), '_bde_tier1_discount', true );
        $tier2_qty      = get_post_meta( $product->get_id(), '_bde_tier2_qty', true );
        $tier2_discount = get_post_meta( $product->get_id(), '_bde_tier2_discount', true );

        if ( empty( $tier1_qty ) && empty( $tier2_qty ) ) {
            return;
        }

        echo '<div class="bde-discount-table" style="margin-bottom: 20px; padding: 15px; border: 1px dashed #2271b1; background-color: #f0f6fc; border-radius: 5px;">';
        echo '<h4 style="margin-top:0;">' . esc_html__( 'Buy More, Save More!', 'bundle-discount-engine' ) . '</h4>';
        echo '<ul style="list-style: none; margin: 0; padding: 0;">';
        
        if ( ! empty( $tier1_qty ) && ! empty( $tier1_discount ) ) {
            echo '<li>' . sprintf( esc_html__( 'Buy %d+ units: Get %s%% OFF each', 'bundle-discount-engine' ), esc_html( $tier1_qty ), esc_html( $tier1_discount ) ) . '</li>';
        }

        if ( ! empty( $tier2_qty ) && ! empty( $tier2_discount ) ) {
            echo '<li>' . sprintf( esc_html__( 'Buy %d+ units: Get %s%% OFF each', 'bundle-discount-engine' ), esc_html( $tier2_qty ), esc_html( $tier2_discount ) ) . '</li>';
        }

        echo '</ul>';
        echo '</div>';
    }

    // 3. Apply Discount Dynamically in WooCommerce Cart
    public function apply_tiered_discounts( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
            return;
        }

        foreach ( $cart->get_cart() as $cart_item ) {
            $product_id = $cart_item['product_id'];
            $quantity   = $cart_item['quantity'];

            $tier1_qty      = (int) get_post_meta( $product_id, '_bde_tier1_qty', true );
            $tier1_discount = (float) get_post_meta( $product_id, '_bde_tier1_discount', true );
            $tier2_qty      = (int) get_post_meta( $product_id, '_bde_tier2_qty', true );
            $tier2_discount = (float) get_post_meta( $product_id, '_bde_tier2_discount', true );

            $applied_discount = 0;

            // Determine highest eligible discount tier
            if ( ! empty( $tier2_qty ) && $quantity >= $tier2_qty && ! empty( $tier2_discount ) ) {
                $applied_discount = $tier2_discount;
            } elseif ( ! empty( $tier1_qty ) && $quantity >= $tier1_qty && ! empty( $tier1_discount ) ) {
                $applied_discount = $tier1_discount;
            }

            if ( $applied_discount > 0 ) {
                $original_price = $cart_item['data']->get_regular_price();
                if ( empty( $original_price ) ) {
                    $original_price = $cart_item['data']->get_price();
                }
                
                $discounted_price = $original_price - ( $original_price * ( $applied_discount / 100 ) );
                $cart_item['data']->set_price( $discounted_price );
            }
        }
    }
}

Bundle_Discount_Engine::get_instance();
