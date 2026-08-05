<?php
/**
 * Plugin Name: Smart Multi-Unit Dynamic Variation Engine
 * Plugin URI:  https://example.com/smart-multi-unit-variation-engine
 * Description: High-performance dynamic variation generator and auto-calculator (v2.4) with Zero-Config Defaults, Explicit Attribute Flags (set_visible 1, set_variation 1), Live Manual Price Override Table, and Packaging Options.
 * Version:     2.4.0
 * Author:      DevSuite
 * Text Domain: smu-variation-engine
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

class Smart_Multi_Unit_Variation_Engine {

    private static $instance = null;
    private $nonce_action = 'smu_v2_nonce_action';
    private $nonce_field  = 'smu_v2_nonce';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Meta Box Hooks
        add_action( 'add_meta_boxes', array( $this, 'add_product_meta_box' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta_and_generate_variations' ), 10, 2 );

        // Quick Edit Hooks
        add_action( 'woocommerce_product_quick_edit_end', array( $this, 'render_quick_edit_fields' ) );
        add_action( 'woocommerce_product_quick_edit_save', array( $this, 'save_quick_edit_fields' ) );

        // Admin Assets
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Admin Menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=product',
            __( 'Multi-Unit Engine v2.4', 'smu-variation-engine' ),
            __( 'Multi-Unit Engine', 'smu-variation-engine' ),
            'manage_woocommerce',
            'smu-variation-engine',
            array( $this, 'render_admin_dashboard' )
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php', 'woocommerce_page_smu-variation-engine' ) ) ) {
            return;
        }

        wp_register_style( 'smu-engine-v2-css', false );
        wp_enqueue_style( 'smu-engine-v2-css' );

        $custom_css = "
            .smu-v2-container { background: #0f172a; border-radius: 14px; color: #f8fafc; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; box-shadow: 0 12px 30px rgba(0,0,0,0.35); border: 1px solid #1e293b; margin-top: 15px; }
            .smu-v2-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 16px; margin-bottom: 20px; }
            .smu-v2-title { font-size: 18px; font-weight: 700; color: #38bdf8; display: flex; align-items: center; gap: 10px; margin: 0; }
            .smu-v2-badge { background: #0c4a6e; color: #38bdf8; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #0284c7; }

            .smu-v2-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            @media (max-width: 900px) { .smu-v2-grid { grid-template-columns: 1fr; } }

            .smu-v2-card { background: #1e293b; border-radius: 12px; padding: 20px; border: 1px solid #334155; }
            .smu-v2-card-title { font-size: 14px; font-weight: 700; color: #e2e8f0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #334155; padding-bottom: 10px; }

            .smu-v2-group { margin-bottom: 18px; }
            .smu-v2-group label { display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
            .smu-v2-input, .smu-v2-select { width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 11px 14px; border-radius: 8px; font-size: 13px; outline: none; transition: all 0.2s ease; box-sizing: border-box; }
            .smu-v2-input:focus, .smu-v2-select:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25); }

            /* Price Override Table */
            .smu-override-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .smu-override-table th { background: #0f172a; color: #94a3b8; text-transform: uppercase; font-size: 11px; padding: 10px 12px; text-align: left; border-bottom: 1px solid #334155; }
            .smu-override-table td { padding: 10px 12px; border-bottom: 1px solid #334155; color: #e2e8f0; font-size: 13px; }
            .smu-override-input { background: #0f172a; border: 1px solid #334155; color: #10b981; font-weight: bold; padding: 6px 10px; border-radius: 6px; width: 100px; font-size: 13px; }

            /* Primary Trigger Action Button */
            .smu-v2-btn-generate { background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%); color: #ffffff; font-weight: 800; font-size: 14px; padding: 14px 24px; border: none; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35); text-transform: uppercase; letter-spacing: 0.5px; }
            .smu-v2-btn-generate:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(2, 132, 199, 0.5); color: #fff; }
        ";
        wp_add_inline_style( 'smu-engine-v2-css', $custom_css );
    }

    public function add_product_meta_box() {
        add_meta_box(
            'smu_variation_engine_meta_box',
            __( '⚡ Smart Multi-Unit Dynamic Variation Engine (v2.4)', 'smu-variation-engine' ),
            array( $this, 'render_meta_box_content' ),
            'product',
            'normal',
            'high'
        );
    }

    public function render_meta_box_content( $post ) {
        wp_nonce_field( $this->nonce_action, $this->nonce_field );

        $base_rate   = get_post_meta( $post->ID, '_smu_base_rate', true );
        $unit_type   = get_post_meta( $post->ID, '_smu_unit_type', true );
        $container   = get_post_meta( $post->ID, '_smu_container_option', true );
        $overrides   = get_post_meta( $post->ID, '_smu_price_overrides', true );

        if ( empty( $unit_type ) )  $unit_type = 'weight';
        if ( ! is_array( $overrides ) ) $overrides = array();

        $default_sizes = ( 'weight' === $unit_type ) 
            ? array( '50g', '100g', '250g', '500g', '1000g (1kg)' )
            : array( '25ml', '50ml', '100ml', '250ml', '500ml', '1000ml (1L)' );
        ?>
        <div class="smu-v2-container">
            <div class="smu-v2-header">
                <h3 class="smu-v2-title">
                    <span>⚡ Smart Multi-Unit Dynamic Variation Engine</span>
                </h3>
                <span class="smu-v2-badge">v2.4 Frontend Dropdown Fix</span>
            </div>

            <div class="smu-v2-grid">
                <!-- Column 1: Base Configuration -->
                <div class="smu-v2-card">
                    <div class="smu-v2-card-title">📐 1. Base Configuration</div>

                    <div class="smu-v2-group">
                        <label for="smu_unit_type">Measurement Base Unit</label>
                        <select id="smu_unit_type" name="smu_unit_type" class="smu-v2-select">
                            <option value="weight" <?php selected( $unit_type, 'weight' ); ?>>Weight Base (1000g / 1kg Rate)</option>
                            <option value="volume" <?php selected( $unit_type, 'volume' ); ?>>Volume Base (1000ml / 1 Liter Rate)</option>
                        </select>
                    </div>

                    <div class="smu-v2-group">
                        <label for="smu_base_rate">Base Price for 1000g or 1000ml (PKR / $)</label>
                        <input type="number" step="0.01" min="0" id="smu_base_rate" name="smu_base_rate" value="<?php echo esc_attr( $base_rate ); ?>" placeholder="e.g. 5000" class="smu-v2-input" />
                    </div>

                    <div class="smu-v2-group">
                        <label for="smu_container_option">Packaging / Bottle Option (Secondary Attribute)</label>
                        <select id="smu_container_option" name="smu_container_option" class="smu-v2-select">
                            <option value="none" <?php selected( $container, 'none' ); ?>>None (Standard Pouch / Standard)</option>
                            <option value="Glass Bottle" <?php selected( $container, 'Glass Bottle' ); ?>>Glass Bottle</option>
                            <option value="Plastic Bottle" <?php selected( $container, 'Plastic Bottle' ); ?>>Plastic Bottle</option>
                            <option value="Glass Jar" <?php selected( $container, 'Glass Jar' ); ?>>Glass Jar</option>
                            <option value="Plastic Jar" <?php selected( $container, 'Plastic Jar' ); ?>>Plastic Jar</option>
                        </select>
                    </div>
                </div>

                <!-- Column 2: Live Price Override Table -->
                <div class="smu-v2-card">
                    <div class="smu-v2-card-title">📊 2. Auto-Calculated Sizes & Price Overrides</div>

                    <table class="smu-override-table">
                        <thead>
                            <tr>
                                <th>Pack Size</th>
                                <th>Math Price</th>
                                <th>Manual Override ($/PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $default_sizes as $size_term ) : 
                                preg_match( '/([0-9\.]+)/', $size_term, $m );
                                $num = ! empty( $m[1] ) ? floatval( $m[1] ) : 1000;
                                $math_price = number_format( ( $num / 1000.0 ) * ( floatval( $base_rate ) > 0 ? floatval( $base_rate ) : 0 ), 2, '.', '' );
                                $override_val = isset( $overrides[ $size_term ] ) ? $overrides[ $size_term ] : '';
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $size_term ); ?></strong></td>
                                    <td style="color:#94a3b8;"><?php echo esc_html( $math_price ); ?></td>
                                    <td>
                                        <input type="number" step="0.01" name="smu_overrides[<?php echo esc_attr( $size_term ); ?>]" value="<?php echo esc_attr( $override_val ); ?>" placeholder="<?php echo esc_attr( $math_price ); ?>" class="smu-override-input" />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <input type="hidden" id="smu_trigger_generate" name="smu_trigger_generate" value="0" />

                    <div style="margin-top:20px;">
                        <button type="button" onclick="smuTriggerGenerateAndSave();" class="smu-v2-btn-generate">
                            <span>⚡ Generate & Save Variations Now</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function smuTriggerGenerateAndSave() {
                document.getElementById('smu_trigger_generate').value = '1';
                var publishBtn = document.getElementById('publish');
                if (publishBtn) {
                    publishBtn.click();
                } else {
                    var form = document.getElementById('post');
                    if (form) form.submit();
                }
            }
        </script>
        <?php
    }

    public function save_product_meta_and_generate_variations( $post_id, $post ) {
        if ( ! isset( $_POST[ $this->nonce_field ] ) || ! wp_verify_nonce( $_POST[ $this->nonce_field ], $this->nonce_action ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $base_rate = isset( $_POST['smu_base_rate'] ) ? floatval( $_POST['smu_base_rate'] ) : 0;
        $unit_type = isset( $_POST['smu_unit_type'] ) ? sanitize_text_field( $_POST['smu_unit_type'] ) : 'weight';
        $container = isset( $_POST['smu_container_option'] ) ? sanitize_text_field( $_POST['smu_container_option'] ) : 'none';
        $trigger   = isset( $_POST['smu_trigger_generate'] ) ? sanitize_text_field( $_POST['smu_trigger_generate'] ) : '0';

        $overrides = array();
        if ( isset( $_POST['smu_overrides'] ) && is_array( $_POST['smu_overrides'] ) ) {
            foreach ( $_POST['smu_overrides'] as $k => $v ) {
                if ( '' !== trim( $v ) ) {
                    $overrides[ sanitize_text_field( $k ) ] = floatval( $v );
                }
            }
        }

        update_post_meta( $post_id, '_smu_base_rate', $base_rate );
        update_post_meta( $post_id, '_smu_unit_type', $unit_type );
        update_post_meta( $post_id, '_smu_container_option', $container );
        update_post_meta( $post_id, '_smu_price_overrides', $overrides );

        if ( '1' === $trigger || $base_rate > 0 ) {
            $this->generate_wc_variations_v2( $post_id, $base_rate, $unit_type, $container, $overrides );
        }
    }

    private function generate_wc_variations_v2( $product_id, $base_rate, $unit_type, $container, $overrides ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) return;

        // 1. Set product type to variable
        wp_set_object_terms( $product_id, 'variable', 'product_type' );

        // 2. HARD CLEANUP: Delete all old existing variations
        $existing_variations = get_posts( array(
            'post_parent' => $product_id,
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

        // 3. HARD CLEANUP: Clear old product attributes meta
        delete_post_meta( $product_id, '_product_attributes' );

        // 4. Zero-Config Default Sizes
        $sizes = ( 'weight' === $unit_type )
            ? array( '50g', '100g', '250g', '500g', '1000g (1kg)' )
            : array( '25ml', '50ml', '100ml', '250ml', '500ml', '1000ml (1L)' );

        // 5. Setup Attributes with Explicit Integer 1 Flags
        $wc_attributes = array();

        $attr_size = new WC_Product_Attribute();
        $attr_size->set_id( 0 );
        $attr_size->set_name( 'Pack Size' );
        $attr_size->set_options( $sizes );
        $attr_size->set_position( 0 );
        $attr_size->set_visible( 1 );   // MUST BE 1 for dropdown to render on frontend
        $attr_size->set_variation( 1 ); // MUST BE 1 for variations to match dropdown
        $wc_attributes['pack-size'] = $attr_size;

        if ( 'none' !== $container && ! empty( $container ) ) {
            $attr_container = new WC_Product_Attribute();
            $attr_container->set_id( 0 );
            $attr_container->set_name( 'Packaging' );
            $attr_container->set_options( array( $container ) );
            $attr_container->set_position( 1 );
            $attr_container->set_visible( 1 );   // MUST BE 1
            $attr_container->set_variation( 1 ); // MUST BE 1
            $wc_attributes['packaging'] = $attr_container;
        }

        $product->set_attributes( $wc_attributes );
        $product->save();

        // 6. Programmatically Build Fresh Variations
        foreach ( $sizes as $size_term ) {
            if ( isset( $overrides[ $size_term ] ) && floatval( $overrides[ $size_term ] ) > 0 ) {
                $calc_price = floatval( $overrides[ $size_term ] );
            } else {
                $calc_price = $this->calculate_variation_price( $size_term, $base_rate );
            }

            $variation = new WC_Product_Variation();
            $variation->set_parent_id( $product_id );
            
            $variation_attrs = array( 'pack-size' => $size_term );
            if ( 'none' !== $container && ! empty( $container ) ) {
                $variation_attrs['packaging'] = $container;
            }

            $variation->set_attributes( $variation_attrs );
            $variation->set_regular_price( number_format( $calc_price, 2, '.', '' ) );
            $variation->set_price( number_format( $calc_price, 2, '.', '' ) );
            $variation->set_manage_stock( false );
            $variation->set_stock_status( 'instock' );
            $variation->set_status( 'publish' );
            $variation->save();
        }

        // 7. Sync Parent Variable Product & Clear Transients
        WC_Product_Variable::sync( $product_id );
        wc_delete_product_transients( $product_id );
    }

    private function calculate_variation_price( $size_str, $base_rate ) {
        preg_match( '/([0-9\.]+)/', $size_str, $matches );
        $numeric_val = ! empty( $matches[1] ) ? floatval( $matches[1] ) : 1000;

        return ( $numeric_val / 1000.0 ) * $base_rate;
    }

    public function render_quick_edit_fields() {
        ?>
        <br class="clear" />
        <div class="smu-quick-edit-wrapper" style="background:#1e293b; padding:12px; border-radius:6px; color:#fff; margin-top:10px; border:1px solid #334155;">
            <strong style="color:#38bdf8;">⚡ Smart Multi-Unit Variation Quick Edit (v2.4)</strong>
            <div style="display:flex; gap:15px; margin-top:8px;">
                <div>
                    <label style="font-size:11px; color:#94a3b8; display:block;">Base Price (1kg/1L)</label>
                    <input type="number" step="0.01" name="_smu_base_rate" class="smu-v2-input" style="width:120px;" />
                </div>
                <div>
                    <label style="font-size:11px; color:#94a3b8; display:block;">Base Unit</label>
                    <select name="_smu_unit_type" class="smu-v2-select" style="width:140px;">
                        <option value="weight">Weight (1000g)</option>
                        <option value="volume">Volume (1000ml)</option>
                    </select>
                </div>
            </div>
        </div>
        <?php
    }

    public function save_quick_edit_fields( $product ) {
        $post_id = $product->get_id();
        if ( isset( $_POST['_smu_base_rate'] ) ) {
            update_post_meta( $post_id, '_smu_base_rate', floatval( $_POST['_smu_base_rate'] ) );
        }
        if ( isset( $_POST['_smu_unit_type'] ) ) {
            update_post_meta( $post_id, '_smu_unit_type', sanitize_text_field( $_POST['_smu_unit_type'] ) );
        }
    }

    public function render_admin_dashboard() {
        ?>
        <div class="wrap">
            <h1>⚡ Smart Multi-Unit Dynamic Variation Engine v2.4</h1>
            <div class="smu-v2-container">
                <h2 style="color:#38bdf8; margin-top:0;">Documentation & v2.4 Updates</h2>
                <ul>
                    <li><strong>Explicit Integer Flags:</strong> <code>set_visible(1)</code> & <code>set_variation(1)</code> explicitly set to integer 1 so WooCommerce renders frontend dropdowns.</li>
                </ul>
            </div>
        </div>
        <?php
    }
}

Smart_Multi_Unit_Variation_Engine::get_instance();
