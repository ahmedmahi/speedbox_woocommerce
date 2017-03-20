<?php

/*
 * Plugin Name: Speedbox Maroc
 * Plugin URI: http://www.speedbox.ma/
 * Description: Module de livraison/paiement Speedbox pour WooCommerce 2.x
 * Version: 1.0.0
 * Author: Speedbox
 * Author URI: http://www.speedbox.ma/
 * Developer: Ahmed MAHI <1hmedmahi@gmail.com>
 * Developer URI: http://ahmedmahi.com
 * Text Domain: woocommerce-speedbox
 * Domain Path: /languages
 * @license:     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Exit if accessed directly */
if (!defined('ABSPATH')) {
    exit;
}

function woocommerce_speedbox_relais_init()
{

    if (!class_exists('WC_Speedbox_Shipping_Relais')) {
        class WC_Speedbox_Shipping_Relais extends WC_Shipping_Method
        {
            public $speedbox_api;
            public $speedbox_helper;
            public $log;

            /**
             * Constructor Speedbox  shipping class
             *
             * @access public
             * @return void
             */
            public function __construct()
            {

                $this->init_infos();

                // Set table arrays and last ids
                $this->get_zones();
                $this->get_last_zone_id();

                $this->get_table_rates();
                $this->get_last_table_rate_id();

                $this->init();

            }

            /**
             * Init  settings
             *
             * @access public
             * @return void
             */
            public function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                $this->enabled         = $this->settings['enabled'];
                $this->method_title    = $this->settings['title'];
                $this->speedbox_helper = new WC_Speedbox_Helper();
                $this->speedbox_api    = $this->speedbox_helper->get_api();
                $this->log             = new Logging();

                // Save settings in admin if you have any defined
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_zones'));
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_table_rates'));

            }
            function init_infos()
            {
                $this->id = 'speedbox_relais';

                $this->method_description        = __('SpeedBox delivery plugin for WooCommerce - Morocco vendors only', MD_SPEEDBOX_DOMAIN);
                $this->table_rate_option         = $this->id . '_table_rates';
                $this->last_table_rate_id_option = $this->id . '_last_table_rate_id';
                $this->zones_option              = $this->id . '_zones';
                $this->last_zone_id_option       = $this->id . '_last_zone_id';

            }

            /**
             * calculate_shipping function.
             *
             * @access public
             * @param mixed $package
             * @return void
             */

            public function calculate_shipping($package = array())
            {
                try {

                    if ($this->get_option('gestion_frais_api') == 'interne') {

                        $available_table_rates = $this->get_available_table_rates($package);
                        $table_rate            = $this->pick_cheapest_table_rate($available_table_rates);

                        if ($table_rate != false) {
                            $cost = $table_rate['cost'];
                        }

                    } else {
                        $cost = $this->settings['default_price_api'];
                        if (isset($_COOKIE['speedbox_selected_relais'])) {
                            $point_relai = $_COOKIE['speedbox_selected_relais'];
                            $cout_temps  = $this->speedbox_api->colis->coutTemps($point_relai);
                            if (isset($cout_temps['frais'])) {
                                $cost = $this->settings['supp_api'] + (double) $cout_temps['frais'];
                            }

                        }

                    }

                    if ($this->settings['tax_status'] == 'none') {
                        $tax = false;
                    } else {
                        $tax = '';
                    }
                    /* Coupon override */
                    if ($coupons = WC()->cart->get_coupons()) {
                        foreach ($coupons as $code => $coupon) {
                            if ($coupon->is_valid() && $coupon->enable_free_shipping() && $this->settings['coupon_freeshipping'] == 'enabled') {
                                $cost = 0;
                            }
                        }
                    }
                    /* Register the rate */
                    $rate = array(
                        'id'       => $this->id,
                        'label'    => $this->method_title,
                        'cost'     => $cost,
                        'taxes'    => $tax,
                        'calc_tax' => 'per_order',
                    );
                    $this->add_rate($rate);
                } catch (Exception $e) {
                    $this->log->lwrite_and_lclose(($e->getMessage()));
                }

            }

            /* Add configuration fields */
            public function init_form_fields()
            {
                $states            = get_terms(array('taxonomy' => 'state_city', 'hide_empty' => false, 'parent' => 0));
                $this->form_fields = array(
                    'enabled'                     => array(
                        'title'   => __('Enable/Disable', MD_SPEEDBOX_DOMAIN),
                        'type'    => 'checkbox',
                        'label'   => __('Enable SpeedBox Relais', MD_SPEEDBOX_DOMAIN),
                        'default' => 'yes',
                    ),
                    'title'                       => array(
                        'title'       => __('Title', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'text',
                        'description' => __('The title which the user sees during checkout.', MD_SPEEDBOX_DOMAIN),
                        'default'     => __('Pickup point delivery', MD_SPEEDBOX_DOMAIN),
                    ),

                    'speedbox_relais_MerchantID'  => array(
                        'title'       => __('Merchant ID', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'text',
                        'description' => __('Please enter the merchant ID provided by SpeedBox.', MD_SPEEDBOX_DOMAIN),
                        'default'     => '',
                    ),
                    'api_token'                   => array(
                        'title'       => __('Api token', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'text',
                        'description' => __('Contact your SpeedBox sales representative to obtain this data', MD_SPEEDBOX_DOMAIN),
                    ),
                    'speedbox_relais_SecurityKey' => array(
                        'title'       => __('Security Key', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'password',
                        'description' => __('Please enter the secuity key provided by SpeedBox.', MD_SPEEDBOX_DOMAIN),
                        'default'     => '',
                    ),
                    'speedbox_relais_URL'         => array(
                        'title'       => __('SpeedBox Relais Webservice URL', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'text',
                        'description' => __('Please enter the SpeedBox Relais WebService URL.', MD_SPEEDBOX_DOMAIN),
                        'default'     => 'http://core.speedbox.ma:8001',
                    ),
                    'cash_delivery_active'        => array(
                        'title'       => __('Enable Cash on Delivery', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'checkbox',
                        'description' => __('Link speedbox to "cash at delivery" mode native to woocommerce', MD_SPEEDBOX_DOMAIN),
                        'default'     => 'no',
                    ),

                    'tax_status'                  => array(
                        'title'   => __('Tax Status', MD_SPEEDBOX_DOMAIN),
                        'type'    => 'select',
                        'default' => 'taxable',
                        'options' => array(
                            'taxable' => __('Taxable', MD_SPEEDBOX_DOMAIN),
                            'none'    => __('None', MD_SPEEDBOX_DOMAIN),
                        ),
                    ),
                    'coupon_freeshipping'         => array(
                        'title'       => __('Enable free shipping coupons mangement', MD_SPEEDBOX_DOMAIN),
                        'description' => __('If the customer applies a valid coupon, shipping will be free.', MD_SPEEDBOX_DOMAIN),
                        'desc_tip'    => true,
                        'type'        => 'select',
                        'default'     => 'enabled',
                        'options'     => array(
                            'enabled'  => __('Enabled', MD_SPEEDBOX_DOMAIN),
                            'disabled' => __('Disabled', MD_SPEEDBOX_DOMAIN),
                        ),
                    ),
                    'gestion_frais_api'           => array(
                        'title'       => __('Delivery Fee Management', MD_SPEEDBOX_DOMAIN),

                        'description' => __('Use of Speedbox rates (via API) with possibility of a supplement or specify delivery charges', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'select',
                        'class'       => 'wc-enhanced-select',
                        'default'     => '',
                        'options'     => array(
                            'via_api' => __('Use of Speedbox rates (via API) + supplement', MD_SPEEDBOX_DOMAIN),
                            'interne' => __('Specify shipping costs', MD_SPEEDBOX_DOMAIN),
                        ),
                    ),
                    'supp_api'                    => array(
                        'title'       => __('Overcost.', MD_SPEEDBOX_DOMAIN),
                        'description' => __('Overcost.', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'price',
                        'default'     => '0.00',
                        'desc_tip'    => true,
                    ),
                    'default_price_api'           => array(
                        'title'       => __('default price.', MD_SPEEDBOX_DOMAIN),
                        'description' => __('Default price when the relay point is not yet selected.', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'price',
                        'default'     => '0.00',
                        'desc_tip'    => true,
                    ),
                    'zones_table'                 => array(
                        'type' => 'zones_table',
                    ),
                    'table_rates_table'           => array(
                        'type' => 'table_rates_table',
                    ),
                    'google_api_key'              => array(
                        'title'       => __('Google Maps API Key', MD_SPEEDBOX_DOMAIN),
                        'type'        => 'text',
                        'css'         => 'width: 400px;',
                        'description' => '<a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank">' . __('Click here to retrieve your Google API Key', MD_SPEEDBOX_DOMAIN),

                    ),

                );
            }
            /**
             * Mofifier  HTML for supp_api et default_price_api
             */
            function generate_price_html($key, $data)
            {

                if ($key != 'supp_api' && $key != 'default_price_api') {
                    return parent::generate_text_html($key, $data);
                } else {
                    $field_key = $this->get_field_key($key);
                    $defaults  = array(
                        'title'             => '',
                        'disabled'          => false,
                        'class'             => '',
                        'css'               => '',
                        'placeholder'       => '',
                        'type'              => 'text',
                        'desc_tip'          => false,
                        'description'       => '',
                        'custom_attributes' => array(),
                    );

                    $data = wp_parse_args($data, $defaults);

                    ob_start();

                    include MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-fields-price-change.php';
                    return ob_get_clean();
                }

            }
            /* Generates HTML code for top of configuration page */
            function admin_options()
            {
                include_once MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-options.php';

            }

            /**
             * Generates HTML for zone settings table.
             */
            function generate_zones_table_html()
            {
                ob_start();
                include_once MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-zones-table.php';
                return ob_get_clean();

            }

            /**
             * Generates HTML for table_rate settings table.
             */
            function generate_table_rates_table_html()
            {
                ob_start();
                include_once MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-table-rates.php';
                return ob_get_clean();
            }

            /* Generates dropdown menus */
            function generate_options()
            {
                $option_arr = array();

                /* Zones */
                foreach ($this->zones as $option):
                    $option_arr['table_rate_zone'][esc_attr($option['id'])] = esc_js($option['name']);
                endforeach;

                $option_arr['table_rate_zone']['0'] = __('All of Morocco
', MD_SPEEDBOX_DOMAIN);

                /* Countries */
                foreach (WC()->countries->get_shipping_countries() as $id => $value):
                    if ($id == 'MA') {
                        $option_arr['country'][esc_attr($id)] = esc_js($value);
                        $option_arr['cities'][esc_attr($id)]  = $this->speedbox_helper->get_cities_from_data();
                        break;
                    }

                endforeach;

                /* Table rates */
                $option_arr['rate_basis']['weight'] = sprintf(__('Weight (%s)', MD_SPEEDBOX_DOMAIN), get_option('woocommerce_weight_unit'));
                $option_arr['rate_basis']['price']  = sprintf(__('Total (%s)', MD_SPEEDBOX_DOMAIN), get_woocommerce_currency_symbol());

                return $option_arr;
            }

            /**
             * Process and save submitted zones.
             */
            function process_zones()
            {
                // Save the rates
                $zone_id      = array();
                $zone_name    = array();
                $zone_country = array();
                $zone_include = array();
                $zone_exclude = array();
                $zone_type    = array();
                $zone_enabled = array();

                $zones = array();

                if (isset($_POST[$this->id . '_zone_id'])) {
                    $zone_id = array_map('wc_clean', $_POST[$this->id . '_zone_id']);
                }

                if (isset($_POST[$this->id . '_zone_name'])) {
                    $zone_name = array_map('wc_clean', $_POST[$this->id . '_zone_name']);
                }

                if (isset($_POST[$this->id . '_zone_country'])) {
                    $zone_country = $_POST[$this->id . '_zone_country'];
                }
                if (isset($_POST[$this->id . '_zone_city'])) {
                    $zone_city = $_POST[$this->id . '_zone_city'];
                }
                if (isset($_POST[$this->id . '_zone_include'])) {
                    $zone_include = array_map('wc_clean', $_POST[$this->id . '_zone_include']);
                }

                if (isset($_POST[$this->id . '_zone_exclude'])) {
                    $zone_exclude = array_map('wc_clean', $_POST[$this->id . '_zone_exclude']);
                }

                if (isset($_POST[$this->id . '_zone_type'])) {
                    $zone_type = array_map('wc_clean', $_POST[$this->id . '_zone_type']);
                }

                if (isset($_POST[$this->id . '_zone_enabled'])) {
                    $zone_enabled = array_map('wc_clean', $_POST[$this->id . '_zone_enabled']);
                }

                // Get max key
                $values = $zone_id;
                ksort($values);
                $value = end($values);
                $key   = key($values);

                for ($i = 0; $i <= $key; $i++) {
                    if (isset($zone_id[$i])
                        && !empty($zone_name[$i])
                        && !empty($zone_country[$i])
                        && !empty($zone_city[$i])
                        && isset($zone_include[$i])
                        && isset($zone_exclude[$i])
                        && isset($zone_type[$i])
                        && isset($zone_enabled[$i])) {

                        // Add to flat rates array
                        $zones[] = array(
                            'id'      => $zone_id[$i],
                            'name'    => $zone_name[$i],
                            'country' => $zone_country[$i],
                            'city'    => $zone_city[$i],
                            'include' => $zone_include[$i],
                            'exclude' => $zone_exclude[$i],
                            'type'    => $zone_type[$i],
                            'enabled' => $zone_enabled[$i],
                        );
                    }
                }

                if ((!empty($zone_id[$key]))
                    && ($zone_id[$key] > $this->last_zone_id)
                    && (is_numeric($zone_id[$key]))) {
                    $highest_zone_id = $zone_id[$key];
                    update_option($this->last_zone_id_option, $highest_zone_id);
                }

                update_option($this->zones_option, $zones);
                $this->get_zones();

            }

            /**
             * Process and save submitted table_rates.
             */
            function process_table_rates()
            {
                // Save the rates
                $table_rate_id      = array();
                $table_rate_zone    = array();
                $table_rate_basis   = array();
                $table_rate_min     = array();
                $table_rate_max     = array();
                $table_rate_cost    = array();
                $table_rate_enabled = array();

                $table_rates = array();

                if (isset($_POST[$this->id . '_table_rate_id'])) {
                    $table_rate_id = array_map('wc_clean', $_POST[$this->id . '_table_rate_id']);
                }

                if (isset($_POST[$this->id . '_table_rate_zone'])) {
                    $table_rate_zone = array_map('wc_clean', $_POST[$this->id . '_table_rate_zone']);
                }

                if (isset($_POST[$this->id . '_table_rate_basis'])) {
                    $table_rate_basis = array_map('wc_clean', $_POST[$this->id . '_table_rate_basis']);
                }

                if (isset($_POST[$this->id . '_table_rate_min'])) {
                    $table_rate_min = array_map('stripslashes', $_POST[$this->id . '_table_rate_min']);
                }

                if (isset($_POST[$this->id . '_table_rate_max'])) {
                    $table_rate_max = array_map('stripslashes', $_POST[$this->id . '_table_rate_max']);
                }

                if (isset($_POST[$this->id . '_table_rate_cost'])) {
                    $table_rate_cost = array_map('stripslashes', $_POST[$this->id . '_table_rate_cost']);
                }

                if (isset($_POST[$this->id . '_table_rate_enabled'])) {
                    $table_rate_enabled = array_map('wc_clean', $_POST[$this->id . '_table_rate_enabled']);
                }

                // Get max key
                $values = $table_rate_id;
                ksort($values);
                $value = end($values);
                $key   = key($values);

                for ($i = 0; $i <= $key; $i++) {
                    if (isset($table_rate_id[$i])
                        && isset($table_rate_zone[$i])
                        && isset($table_rate_basis[$i])
                        && isset($table_rate_min[$i])
                        && isset($table_rate_max[$i])
                        && isset($table_rate_cost[$i])
                        && isset($table_rate_enabled[$i])) {

                        $table_rate_cost[$i] = wc_format_decimal($table_rate_cost[$i]);

                        // Add table_rates to array
                        $table_rates[] = array(
                            'id'      => $table_rate_id[$i],
                            'zone'    => $table_rate_zone[$i],
                            'basis'   => $table_rate_basis[$i],
                            'min'     => $table_rate_min[$i],
                            'max'     => $table_rate_max[$i],
                            'cost'    => $table_rate_cost[$i],
                            'enabled' => $table_rate_enabled[$i],
                        );
                    }
                }

                if ((!empty($table_rate_id[$key]))
                    && ($table_rate_id[$key] > $this->last_table_rate_id)
                    && (is_numeric($table_rate_id[$key]))) {
                    $highest_table_rate_id = $table_rate_id[$key];
                    update_option($this->last_table_rate_id_option, $highest_table_rate_id);
                }

                update_option($this->table_rate_option, $table_rates);

                $this->get_table_rates();

            }

            function get_available_zones($package)
            {
                $destination_country = $package['destination']['country'];

                $destination_city = $this->speedbox_helper->get_city_from_data($package['destination']['city']);
                //$cities=$this->speedbox_helper->get_cities_from_data();

                $available_zones = array();

                foreach ($this->zones as $zone):

                    if (!empty($zone['country']) && in_array($destination_country, $zone['country']) && (isset($destination_city['ID']) && in_array($destination_city['ID'], $zone['city']))) {
                        $available_zones[] = $zone['id'];
                    }
                endforeach;

                if (empty($available_zones)) {
                    $found = false;
                    foreach (WC()->countries->get_shipping_countries() as $id => $value):
                        if ($destination_country == $id) {
                            $found = true;
                        }
                    endforeach;
                    if ($found) {
                        $available_zones[] = '0'; // "All of Morocco"
                    }
                }
                return $available_zones;
            }
            /**
             * Retrieves zones array from database.
             */
            function get_zones()
            {
                $this->zones = array_filter((array) get_option($this->zones_option));
            }

            /**
             * Retrieves last zone id from database.
             */
            function get_last_zone_id()
            {
                $this->last_zone_id = (int) get_option($this->last_zone_id_option);
            }

            /* Retrieves available table_rates for cart and supplied shipping addresss */
            function get_available_table_rates($package)
            {
                $available_zones       = $this->get_available_zones($package);
                $available_table_rates = array();

                foreach ($this->table_rates as $table_rate):

                    // Is table_rate for an available zone?
                    $zone_pass = (in_array($table_rate['zone'], $available_zones));

                    // Is table_rate valid for basket weight?
                    if ($table_rate['basis'] == 'weight') {
                        $weight      = WC()->cart->cart_contents_weight;
                        $weight_pass = (($weight >= $table_rate['min']) && ($this->is_less_than($weight, $table_rate['max'])));
                    } else {
                        $weight_pass = true;
                    }

                    // Is table_rate valid for basket total?
                    if ($table_rate['basis'] == 'price') {
                        $total      = WC()->cart->cart_contents_total;
                        $total_pass = (($total >= $table_rate['min']) && ($this->is_less_than($total, $table_rate['max'])));
                    } else {
                        $total_pass = true;
                    }

                    // Accept table_rate if passes all tests
                    if ($zone_pass && $weight_pass && $total_pass) {
                        $available_table_rates[] = $table_rate;
                    }

                endforeach;
                return $available_table_rates;
            }

            /* Return true if value less than max, incl. "*" */
            function is_less_than($value, $max)
            {
                if ($max == '*') {
                    return true;
                } else {
                    return ($value <= $max);
                }

            }

            /* Retrieves an array item by searching for an id value */
            function find_by_id($array, $id)
            {
                foreach ($array as $a):
                    if ($a['id'] == $id) {
                        return $a;
                    }

                endforeach;
                return false;
            }

            /* Retrieves cheapest rate from a list of table_rates. */
            function pick_cheapest_table_rate($table_rates)
            {
                $cheapest = false;
                foreach ($table_rates as $table_rate):
                    if ($cheapest == false) {
                        $cheapest = $table_rate;
                    } else {
                        if ($table_rate['cost'] < $cheapest['cost']) {
                            $cheapest = $table_rate;
                        }

                    }
                endforeach;
                return $cheapest;
            }

            /**
             * Retrieves table_rates array from database.
             */
            function get_table_rates()
            {
                $this->table_rates = array_filter((array) get_option($this->table_rate_option));
            }

            /**
             * Retrieves last table_rate id from database.
             */
            function get_last_table_rate_id()
            {
                $this->last_table_rate_id = (int) get_option($this->last_table_rate_id_option);
            }

        }
    }

}

function add_sppedbox_relais($methods)
{
    $methods['speedbox_shipping_relais'] = 'WC_Speedbox_Shipping_Relais';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_sppedbox_relais');
/* L'ajout du javascript*/
function woocommerce_speedbox_relais_js_script()
{
    $ssr            = new WC_Speedbox_Shipping_Relais();
    $google_api_key = $ssr->settings['google_api_key'];

    wp_enqueue_script('speedbox_relais_js_script', MD_SPEEDBOX_ROOT_URL . '/assets/js/front/speedbox_relais.js', array('jquery'), '0.3');
    wp_enqueue_script('speedbox_relais_map', 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key, '');
    wp_enqueue_script('speedbox_relais_jquery_ui', MD_SPEEDBOX_ROOT_URL . '/assets/js/jquery/plugins/jquery-ui/jquery-ui.min.js', '');

    wp_enqueue_style('speedbox_relais_jquery_ui_css', MD_SPEEDBOX_ROOT_URL . '/assets/js/jquery/plugins/jquery-ui/jquery-ui.min.css');
    wp_enqueue_style('speedbox_relais_css', MD_SPEEDBOX_ROOT_URL . '/assets/css/front/speedbox_front.css');
}
add_action('woocommerce_checkout_shipping', 'woocommerce_speedbox_relais_js_script');

function woocommerce_sppedbox_relais_checkout_controller()
{

    global $woocommerce;

    include MD_SPEEDBOX_FILE_PATH . '/includes/views/frontend/html-front-autocomplete.php';

    $html = sppedbox_relais_front($woocommerce->customer);

    @parse_str($_POST['post_data'], $post_data);

    $postcode = @($post_data['ship_to_different_address'] == 1 ? $post_data['shipping_postcode'] : $post_data['billing_postcode']);

    if ($woocommerce->session->chosen_shipping_methods[0] == 'speedbox_relais') {
        print_r('<tr><td colspan="2">' . $html . '</div></td></tr>');
    } else {
        echo '<script type="text/javascript">
                    if (typeof reset_point_relais_vlues === "function")
                        reset_point_relais_vlues();
                </script>';
    }
}
add_action('woocommerce_review_order_after_shipping', 'woocommerce_sppedbox_relais_checkout_controller');

function sppedbox_relais_front($customer_data)
{
    global $woocommerce, $wpdb;

    $speedbox_relais = new WC_Speedbox_Shipping_Relais();
    $speedbox_helper = $speedbox_relais->speedbox_helper;

    $speedbox_relais_points = $speedbox_helper->
        get_speedbox_points_relais($customer_data, $speedbox_relais);

    ob_start();
    include MD_SPEEDBOX_FILE_PATH . '/includes/views/frontend/html-front-points-relais.php';
    return ob_get_clean();
}

function ajout_ID_point_relais($order_id)
{
    if (isset($_COOKIE['speedbox_selected_relais'])) {
        $point_relai = $_COOKIE['speedbox_selected_relais'];
        update_post_meta($order_id, '_pointrelais', sanitize_text_field($point_relai));
    }

}
add_action('woocommerce_checkout_update_order_meta', 'ajout_ID_point_relais');

function display_admin_order_meta_speedbox_status($order)
{
    $speedbox_helper = new WC_Speedbox_Helper();
    if ($speedbox_helper->get_shipping_method_id($order) == 'speedbox_relais') {
        echo '<p><strong>' . __('Staut du colis Speedbox') . ':</strong> ' . get_post_meta($order->id, '_colis_statut', true) . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_order_details', 'display_admin_order_meta_speedbox_status', 10, 1);

function add_traking_colis_account_content($order_id)
{
    $order               = wc_get_order($order_id);
    $speedbox_management = new WC_Speedbox_Shipping_Relai_Management();
    //echo '<pre>' . print_r($speedbox_management->speedbox_helper->get_postalcodes_from_data(), true) . '</pre>';
    if ($speedbox_management->speedbox_helper->get_shipping_method_id($order) == 'speedbox_relais') {
        echo '<h2><strong>' . __('Staut du colis Speedbox') . '</strong> </h2>';
        $speedbox_management->tracker_colis(array($order->id));

    }

}
add_action('woocommerce_account_view-order_endpoint', 'add_traking_colis_account_content', 10, 1);
