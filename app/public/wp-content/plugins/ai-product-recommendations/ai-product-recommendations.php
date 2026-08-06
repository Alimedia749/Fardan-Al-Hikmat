<?php
/**
 * Plugin Name: AI Product Recommendation Engine for WooCommerce
 * Plugin URI:  https://example.com/ai-product-recommendations
 * Description: Uses OpenAI / External REST APIs to fetch and display dynamic AI product recommendations cached via WP Transients.
 * Version:     1.0.0
 * Author:      DevSuite
 * Text Domain: ai-product-recommendations
 * WC requires at least: 5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AI_Recommendation_Engine {

    private static $instance = null;
    private $option_group = 'airec_settings_group';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin Menu & Settings
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Frontend Displays & AJAX
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'render_recommendation_container' ), 25 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

        // AJAX Action for Async AI Fetch
        add_action( 'wp_ajax_airec_fetch_recommendations', array( $this, 'ajax_fetch_recommendations' ) );
        add_action( 'wp_ajax_nopriv_airec_fetch_recommendations', array( $this, 'ajax_fetch_recommendations' ) );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'AI Recommendations', 'ai-product-recommendations' ),
            __( 'AI Recommendations', 'ai-product-recommendations' ),
            'manage_options',
            'ai-recommendations',
            array( $this, 'render_admin_page' )
        );
    }

    public function register_settings() {
        register_setting( $this->option_group, 'airec_openai_api_key', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ) );

        register_setting( $this->option_group, 'airec_max_products', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 3
        ) );
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'AI Recommendation Engine Settings', 'ai-product-recommendations' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( $this->option_group );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="airec_openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'ai-product-recommendations' ); ?></label></th>
                        <td>
                            <input type="password" id="airec_openai_api_key" name="airec_openai_api_key" value="<?php echo esc_attr( get_option( 'airec_openai_api_key' ) ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'Provide your OpenAI secret key to generate smart semantic product recommendations.', 'ai-product-recommendations' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="airec_max_products"><?php esc_html_e( 'Max Recommended Products', 'ai-product-recommendations' ); ?></label></th>
                        <td>
                            <input type="number" id="airec_max_products" name="airec_max_products" min="1" max="6" value="<?php echo esc_attr( get_option( 'airec_max_products', 3 ) ); ?>" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function render_recommendation_container() {
        echo '<div id="airec-recommendations-wrapper" style="margin-top: 40px;">';
        echo '<h3>' . esc_html__( 'AI-Powered Recommendations for You', 'ai-product-recommendations' ) . '</h3>';
        echo '<div id="airec-recommendations-grid" class="products columns-3" style="min-height: 100px;">' . esc_html__( 'Loading smart recommendations...', 'ai-product-recommendations' ) . '</div>';
        echo '</div>';
    }

    public function enqueue_frontend_scripts() {
        if ( ! is_product() ) {
            return;
        }

        global $product;

        $product_id = 0;
        if ( is_a( $product, 'WC_Product' ) ) {
            $product_id = $product->get_id();
        } else {
            $product_id = get_the_ID();
        }

        if ( ! $product_id ) {
            return;
        }

        wp_register_script( 'airec-frontend-js', false );
        wp_enqueue_script( 'airec-frontend-js' );

        $js_data = array(
            'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
            'product_id' => absint( $product_id ),
            'nonce'      => wp_create_nonce( 'airec_fetch_nonce' )
        );

        $inline_script = "
            jQuery(document).ready(function($) {
                var container = $('#airec-recommendations-grid');
                if (!container.length) return;

                $.ajax({
                    url: '" . esc_url( admin_url( 'admin-ajax.php' ) ) . "',
                    type: 'POST',
                    data: {
                        action: 'airec_fetch_recommendations',
                        product_id: " . absint( $product_id ) . ",
                        nonce: '" . wp_create_nonce( 'airec_fetch_nonce' ) . "'
                    },
                    success: function(response) {
                        if (response.success && response.data.html) {
                            container.html(response.data.html);
                        } else {
                            $('#airec-recommendations-wrapper').hide();
                        }
                    },
                    error: function() {
                        $('#airec-recommendations-wrapper').hide();
                    }
                });
            });
        ";

        wp_add_inline_script( 'airec-frontend-js', $inline_script );
    }

    public function ajax_fetch_recommendations() {
        check_ajax_referer( 'airec_fetch_nonce', 'nonce' );

        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        if ( ! $product_id ) {
            wp_send_json_error( array( 'message' => 'Invalid Product ID' ) );
        }

        $transient_key = 'airec_p_' . $product_id;
        $cached_html   = get_transient( $transient_key );

        if ( false !== $cached_html ) {
            wp_send_json_success( array( 'html' => $cached_html ) );
        }

        $recommended_ids = $this->get_ai_recommended_product_ids( $product_id );

        if ( empty( $recommended_ids ) ) {
            wp_send_json_error( array( 'message' => 'No recommendations found' ) );
        }

        ob_start();
        $args = array(
            'post_type'      => 'product',
            'post__in'       => $recommended_ids,
            'posts_per_page' => count( $recommended_ids )
        );
        $loop = new WP_Query( $args );

        if ( $loop->have_posts() ) {
            woocommerce_product_loop_start();
            while ( $loop->have_posts() ) {
                $loop->the_post();
                wc_get_template_part( 'content', 'product' );
            }
            woocommerce_product_loop_end();
        }
        wp_reset_postdata();

        $rendered_html = ob_get_clean();

        // Cache output for 24 Hours to optimize performance & API costs
        set_transient( $transient_key, $rendered_html, DAY_IN_SECONDS );

        wp_send_json_success( array( 'html' => $rendered_html ) );
    }

    private function get_ai_recommended_product_ids( $product_id ) {
        $api_key = get_option( 'airec_openai_api_key' );
        $limit   = get_option( 'airec_max_products', 3 );

        // Fallback to Category-based products if API Key is not configured
        if ( empty( $api_key ) ) {
            $terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
            if ( empty( $terms ) ) {
                return array();
            }

            return get_posts( array(
                'post_type'      => 'product',
                'posts_per_page' => $limit,
                'post__not_in'   => array( $product_id ),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $terms,
                    ),
                ),
                'fields'         => 'ids'
            ) );
        }

        // Fetch catalog titles for context
        $catalog = get_posts( array(
            'post_type'      => 'product',
            'posts_per_page' => 30,
            'post__not_in'   => array( $product_id ),
            'fields'         => 'ids'
        ) );

        $catalog_items = array();
        foreach ( $catalog as $p_id ) {
            $catalog_items[] = array( 'id' => $p_id, 'title' => get_the_title( $p_id ) );
        }

        $current_title = get_the_title( $product_id );
        $prompt        = "Current Product: '{$current_title}'. Catalog: " . wp_json_encode( $catalog_items ) . ". Return ONLY a JSON array of the top {$limit} product IDs that best complement or match the current product. Example output: [12, 15, 99]";

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode( array(
                'model'       => 'gpt-4o-mini',
                'messages'    => array(
                    array( 'role' => 'user', 'content' => $prompt )
                ),
                'temperature' => 0.3
            ) ),
            'timeout' => 10
        ) );

        if ( is_wp_error( $response ) ) {
            return array_slice( wp_list_pluck( $catalog_items, 'id' ), 0, $limit );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['choices'][0]['message']['content'] ) ) {
            $ids = json_decode( trim( $data['choices'][0]['message']['content'] ), true );
            if ( is_array( $ids ) ) {
                return array_map( 'absint', $ids );
            }
        }

        return array_slice( wp_list_pluck( $catalog_items, 'id' ), 0, $limit );
    }
}

AI_Recommendation_Engine::get_instance();
