<?php
/**
 * Plugin Name: Back in Stock & Price Drop Instant WhatsApp Alerts
 * Plugin URI:  https://example.com/wa-back-in-stock-alerts
 * Description: Automated WhatsApp notifications for back-in-stock items and price drops with batch processing queue and admin analytics.
 * Version:     1.0.0
 * Author:      DevSuite
 * Text Domain: wa-back-in-stock-alerts
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

class WA_Back_In_Stock_Alerts {

    private static $instance = null;
    private $option_group = 'wabis_settings_group';

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

        // Frontend Display Hooks
        add_action( 'woocommerce_single_product_summary', array( $this, 'render_subscription_widget' ), 35 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

        // AJAX Handler for Subscriptions
        add_action( 'wp_ajax_wabis_subscribe', array( $this, 'ajax_subscribe' ) );
        add_action( 'wp_ajax_nopriv_wabis_subscribe', array( $this, 'ajax_subscribe' ) );

        // Product Update Hooks (Triggers)
        add_action( 'woocommerce_product_set_stock_status', array( $this, 'handle_stock_status_change' ), 10, 3 );
        add_action( 'woocommerce_process_product_meta', array( $this, 'handle_price_and_stock_meta_save' ), 20, 1 );

        // Cron Batch Queue Handler
        add_action( 'wabis_process_notification_queue_cron', array( $this, 'process_notification_queue' ) );
        if ( ! wp_next_scheduled( 'wabis_process_notification_queue_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'wabis_process_notification_queue_cron' );
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'WhatsApp Alerts', 'wa-back-in-stock-alerts' ),
            __( 'WhatsApp Alerts', 'wa-back-in-stock-alerts' ),
            'manage_options',
            'wa-alerts',
            array( $this, 'render_admin_page' ),
            'dashicons-whatsapp',
            57
        );
    }

    public function register_settings() {
        register_setting( $this->option_group, 'wabis_api_url', array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '' ) );
        register_setting( $this->option_group, 'wabis_api_token', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
        register_setting( $this->option_group, 'wabis_stock_msg', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => "Good news! {product_name} is back in stock! Order here: {product_url}" ) );
        register_setting( $this->option_group, 'wabis_price_msg', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => "Price Drop Alert! {product_name} is now on sale for {price}! Order here: {product_url}" ) );
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $subscribers = get_option( 'wabis_subscribers_list', array() );
        $stats_sent  = get_option( 'wabis_total_sent_count', 0 );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Back in Stock & Price Drop WhatsApp Alerts', 'wa-back-in-stock-alerts' ); ?></h1>

            <div style="display:flex; gap:20px; margin-bottom:20px;">
                <div style="background:#fff; padding:15px 25px; border-left:4px solid #25D366; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin:0; font-size:24px; color:#333;"><?php echo count( $subscribers ); ?></h3>
                    <p style="margin:0; color:#666;"><?php esc_html_e( 'Total Subscribers', 'wa-back-in-stock-alerts' ); ?></p>
                </div>
                <div style="background:#fff; padding:15px 25px; border-left:4px solid #2271b1; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin:0; font-size:24px; color:#333;"><?php echo absint( $stats_sent ); ?></h3>
                    <p style="margin:0; color:#666;"><?php esc_html_e( 'Notifications Sent', 'wa-back-in-stock-alerts' ); ?></p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( $this->option_group );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="wabis_api_url"><?php esc_html_e( 'WhatsApp Gateway Webhook URL', 'wa-back-in-stock-alerts' ); ?></label></th>
                        <td>
                            <input type="text" id="wabis_api_url" name="wabis_api_url" value="<?php echo esc_attr( get_option( 'wabis_api_url' ) ); ?>" class="large-text" placeholder="https://api.ultramsg.com/INSTANCE/messages/chat" />
                            <p class="description"><?php esc_html_e( 'Supports Twilio, UltraMsg, Wati, or any custom REST API endpoint.', 'wa-back-in-stock-alerts' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wabis_api_token"><?php esc_html_e( 'API Token / Secret Key', 'wa-back-in-stock-alerts' ); ?></label></th>
                        <td>
                            <input type="password" id="wabis_api_token" name="wabis_api_token" value="<?php echo esc_attr( get_option( 'wabis_api_token' ) ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wabis_stock_msg"><?php esc_html_e( 'Back in Stock Message Template', 'wa-back-in-stock-alerts' ); ?></label></th>
                        <td>
                            <textarea id="wabis_stock_msg" name="wabis_stock_msg" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'wabis_stock_msg' ) ); ?></textarea>
                            <p class="description">Placeholders: <code>{product_name}</code>, <code>{product_url}</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wabis_price_msg"><?php esc_html_e( 'Price Drop Message Template', 'wa-back-in-stock-alerts' ); ?></label></th>
                        <td>
                            <textarea id="wabis_price_msg" name="wabis_price_msg" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'wabis_price_msg' ) ); ?></textarea>
                            <p class="description">Placeholders: <code>{product_name}</code>, <code>{price}</code>, <code>{product_url}</code></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <h2><?php esc_html_e( 'Active Subscriptions Queue', 'wa-back-in-stock-alerts' ); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'wa-back-in-stock-alerts' ); ?></th>
                        <th><?php esc_html_e( 'Product ID / Title', 'wa-back-in-stock-alerts' ); ?></th>
                        <th><?php esc_html_e( 'Phone Number', 'wa-back-in-stock-alerts' ); ?></th>
                        <th><?php esc_html_e( 'Alert Type', 'wa-back-in-stock-alerts' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'wa-back-in-stock-alerts' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $subscribers ) ) : foreach ( array_reverse( array_slice( $subscribers, -50 ) ) as $sub ) : ?>
                        <tr>
                            <td><?php echo esc_html( $sub['date'] ); ?></td>
                            <td>#<?php echo esc_html( $sub['product_id'] ); ?> - <?php echo esc_html( get_the_title( $sub['product_id'] ) ); ?></td>
                            <td><?php echo esc_html( $sub['phone'] ); ?></td>
                            <td><span class="button-secondary" style="font-size:11px;"><?php echo esc_html( strtoupper( $sub['type'] ) ); ?></span></td>
                            <td><strong style="color: <?php echo 'sent' === $sub['status'] ? 'green' : 'orange'; ?>;"><?php echo esc_html( strtoupper( $sub['status'] ) ); ?></strong></td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="5"><?php esc_html_e( 'No subscribers yet.', 'wa-back-in-stock-alerts' ); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render Subscription Form on Product Page
     */
    public function render_subscription_widget() {
        global $product;

        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        if ( ! is_a( $product, 'WC_Product' ) ) {
            return;
        }

        $is_out_of_stock = ! $product->is_in_stock();
        $product_id      = $product->get_id();
        $nonce           = wp_create_nonce( 'wabis_subscribe_nonce' );

        echo '<div class="wabis-widget-wrapper" style="margin-top: 15px; margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #25D366; border-radius: 6px;">';
        
        if ( $is_out_of_stock ) {
            echo '<h4 style="margin-top:0; color:#25D366; display:flex; align-items:center; gap:6px;">📲 ' . esc_html__( 'Get WhatsApp Alert When Back In Stock', 'wa-back-in-stock-alerts' ) . '</h4>';
            echo '<p style="font-size:13px; color:#666;">' . esc_html__( 'Enter your WhatsApp number to receive an instant alert as soon as this product is restocked.', 'wa-back-in-stock-alerts' ) . '</p>';
        } else {
            echo '<h4 style="margin-top:0; color:#2271b1; display:flex; align-items:center; gap:6px;">📉 ' . esc_html__( 'Watch For Price Drop', 'wa-back-in-stock-alerts' ) . '</h4>';
            echo '<p style="font-size:13px; color:#666;">' . esc_html__( 'Get notified on WhatsApp immediately if price drops for this product!', 'wa-back-in-stock-alerts' ) . '</p>';
        }

        echo '<div style="display:flex; gap:8px;">';
        echo '<input type="text" id="wabis-phone-input" placeholder="Phone with country code (e.g. 923001234567)" class="input-text" style="padding:8px; flex:1;" />';
        echo '<button type="button" id="wabis-submit-btn" class="button alt" style="background:#25D366; border-color:#25D366; color:#fff;">' . esc_html__( 'Notify Me', 'wa-back-in-stock-alerts' ) . '</button>';
        echo '</div>';

        echo '<div id="wabis-msg-box" style="margin-top:8px; font-weight:bold; font-size:13px;"></div>';
        echo '<input type="hidden" id="wabis-product-id" value="' . esc_attr( $product_id ) . '" />';
        echo '<input type="hidden" id="wabis-type" value="' . ( $is_out_of_stock ? 'stock' : 'price' ) . '" />';
        echo '<input type="hidden" id="wabis-current-price" value="' . esc_attr( $product->get_price() ) . '" />';
        echo '<input type="hidden" id="wabis-nonce" value="' . esc_attr( $nonce ) . '" />';
        echo '</div>';
    }

    public function enqueue_frontend_scripts() {
        if ( ! is_product() ) {
            return;
        }

        wp_register_script( 'wabis-frontend-js', false );
        wp_enqueue_script( 'wabis-frontend-js' );

        $inline_script = "
            jQuery(document).ready(function($) {
                $('#wabis-submit-btn').on('click', function(e) {
                    e.preventDefault();
                    var phone = $('#wabis-phone-input').val();
                    var pid   = $('#wabis-product-id').val();
                    var type  = $('#wabis-type').val();
                    var price = $('#wabis-current-price').val();
                    var nonce = $('#wabis-nonce').val();

                    if (!phone || phone.length < 8) {
                        $('#wabis-msg-box').css('color', 'red').text('Please enter a valid phone number with country code.');
                        return;
                    }

                    $('#wabis-msg-box').css('color', '#333').text('Subscribing...');
                    $.post('" . esc_url( admin_url( 'admin-ajax.php' ) ) . "', {
                        action: 'wabis_subscribe',
                        phone: phone,
                        product_id: pid,
                        type: type,
                        target_price: price,
                        nonce: nonce
                    }, function(res) {
                        if (res.success) {
                            $('#wabis-msg-box').css('color', 'green').text('✓ Subscribed! We will alert you on WhatsApp.');
                            $('#wabis-phone-input').val('');
                        } else {
                            $('#wabis-msg-box').css('color', 'red').text(res.data.message || 'Subscription failed.');
                        }
                    });
                });
            });
        ";

        wp_add_inline_script( 'wabis-frontend-js', $inline_script );
    }

    public function ajax_subscribe() {
        check_ajax_referer( 'wabis_subscribe_nonce', 'nonce' );

        $phone      = isset( $_POST['phone'] ) ? preg_replace( '/[^0-9]/', '', $_POST['phone'] ) : '';
        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $type       = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'stock';
        $price      = isset( $_POST['target_price'] ) ? (float) $_POST['target_price'] : 0.0;

        if ( empty( $phone ) || ! $product_id ) {
            wp_send_json_error( array( 'message' => 'Invalid product or phone number.' ) );
        }

        $subscribers   = get_option( 'wabis_subscribers_list', array() );
        $subscribers[] = array(
            'id'           => uniqid( 'sub_' ),
            'product_id'   => $product_id,
            'phone'        => $phone,
            'type'         => $type,
            'target_price' => $price,
            'status'       => 'pending',
            'date'         => current_time( 'mysql' )
        );

        update_option( 'wabis_subscribers_list', $subscribers );
        wp_send_json_success();
    }

    /**
     * Listen for stock status change trigger
     */
    public function handle_stock_status_change( $product_id, $stock_status, $product ) {
        if ( 'instock' === $stock_status ) {
            $this->queue_notifications_for_product( $product_id, 'stock' );
        }
    }

    /**
     * Listen for meta save price drop trigger
     */
    public function handle_price_and_stock_meta_save( $product_id ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return;
        }

        if ( $product->is_in_stock() ) {
            $this->queue_notifications_for_product( $product_id, 'stock' );
        }

        $current_price = (float) $product->get_price();
        $subscribers   = get_option( 'wabis_subscribers_list', array() );

        foreach ( $subscribers as &$sub ) {
            if ( $sub['product_id'] === $product_id && 'price' === $sub['type'] && 'pending' === $sub['status'] ) {
                if ( $current_price < (float) $sub['target_price'] ) {
                    $sub['ready_to_send'] = true;
                }
            }
        }
        update_option( 'wabis_subscribers_list', $subscribers );
    }

    private function queue_notifications_for_product( $product_id, $type ) {
        $subscribers = get_option( 'wabis_subscribers_list', array() );
        foreach ( $subscribers as &$sub ) {
            if ( $sub['product_id'] === $product_id && $sub['type'] === $type && 'pending' === $sub['status'] ) {
                $sub['ready_to_send'] = true;
            }
        }
        update_option( 'wabis_subscribers_list', $subscribers );
    }

    /**
     * Batch Processing Queue (50 messages per run)
     */
    public function process_notification_queue() {
        $subscribers = get_option( 'wabis_subscribers_list', array() );
        if ( empty( $subscribers ) ) {
            return;
        }

        $count = 0;
        $batch_limit = 50;
        $total_sent  = get_option( 'wabis_total_sent_count', 0 );

        foreach ( $subscribers as &$sub ) {
            if ( $count >= $batch_limit ) {
                break;
            }

            if ( isset( $sub['ready_to_send'] ) && true === $sub['ready_to_send'] && 'pending' === $sub['status'] ) {
                $product = wc_get_product( $sub['product_id'] );
                if ( $product ) {
                    $sent = $this->send_whatsapp_message( $sub['phone'], $product, $sub['type'] );
                    if ( $sent ) {
                        $sub['status']        = 'sent';
                        $sub['ready_to_send'] = false;
                        $count++;
                        $total_sent++;
                    }
                }
            }
        }

        update_option( 'wabis_subscribers_list', $subscribers );
        update_option( 'wabis_total_sent_count', $total_sent );
    }

    private function send_whatsapp_message( $phone, $product, $type ) {
        $api_url   = get_option( 'wabis_api_url' );
        $api_token = get_option( 'wabis_api_token' );

        $template = ( 'price' === $type ) ? get_option( 'wabis_price_msg' ) : get_option( 'wabis_stock_msg' );
        $message  = str_replace(
            array( '{product_name}', '{price}', '{product_url}' ),
            array( $product->get_name(), wp_strip_all_tags( wc_price( $product->get_price() ) ), get_permalink( $product->get_id() ) ),
            $template
        );

        if ( empty( $api_url ) ) {
            return true; // Demo fallback mode
        }

        $response = wp_remote_post( $api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode( array(
                'to'      => $phone,
                'message' => $message,
            ) ),
            'timeout' => 10,
        ) );

        return ! is_wp_error( $response );
    }
}

WA_Back_In_Stock_Alerts::get_instance();
