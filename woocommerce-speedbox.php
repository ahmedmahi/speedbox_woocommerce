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
ini_set('display_errors', 1);
define('MD_SPEEDBOX_FILE_PATH', plugin_dir_path(__FILE__));
define('MD_SPEEDBOX_ROOT_URL', plugins_url('', __FILE__));
define('MD_SPEEDBOX_DOMAIN', 'woocommerce-speedbox');
if (!class_exists('Speedbox_Shipping')) {

    class Speedbox_Shipping
    {

        public $speedbox_shipping_relais;
        public function __construct()
        {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            if (is_plugin_active('woocommerce/woocommerce.php')) {
                add_action('plugins_loaded', array($this, 'init'), 8);

            }

        }
        public function init()
        {

            //shiping class
            require_once MD_SPEEDBOX_FILE_PATH . '/includes/classes/class.speedbox-shipping.php';
            // Api classes
            require_once MD_SPEEDBOX_FILE_PATH . '/includes/classes/api/speedbox-api.php';
            // Helper classe
            require_once MD_SPEEDBOX_FILE_PATH . '/includes/classes/class.speedbox-helper.php';
            // management shiping class
            require_once MD_SPEEDBOX_FILE_PATH . '/includes/classes/class.speedbox-shipping-management.php';
            // Log classe
            require_once MD_SPEEDBOX_FILE_PATH . '/includes/log/class.logging.php';

            add_action('woocommerce_shipping_init', 'woocommerce_speedbox_relais_init');
            add_action('admin_menu', array($this, 'add_speedbox_tab'));
            //desactivation du state sous forme de liste
            // add_filter('woocommerce_states', array($this, 'addStatesMaroc'));
            add_filter('woocommerce_checkout_fields', array($this, 'edit_checkout_cities_field'), 20, 1);
            add_filter('woocommerce_form_field_city', array($this, 'change_woocommerce_form_field_city'), 10, 4);

            // Set up localisation.
            $this->load_plugin_textdomain();

        }

        public function addStatesMaroc($states)
        {

            include MD_SPEEDBOX_FILE_PATH . '/includes/data/states_city.php';
            $states['MA'] = speedbox_get_states();
            return $states;
        }

        public function load_plugin_textdomain()
        {
            $locale = apply_filters('plugin_locale', get_locale(), MD_SPEEDBOX_FILE_PATH);
            load_textdomain(MD_SPEEDBOX_DOMAIN, MD_SPEEDBOX_FILE_PATH .
                'languages/woocommerce-speedbox-' . $locale . '.mo');
            load_plugin_textdomain(MD_SPEEDBOX_DOMAIN, false, MD_SPEEDBOX_FILE_PATH . 'languages/');

        }

        public function add_speedbox_tab()
        {
            add_submenu_page('woocommerce', __('Speedbox management', MD_SPEEDBOX_DOMAIN), __('Speedbox management', MD_SPEEDBOX_DOMAIN), 'manage_woocommerce', 'woocommerce-speedbox', 'display_management_page', 8);
        }public function edit_checkout_cities_field($fields)
        {
            $settings = get_option('woocommerce_speedbox_relais_settings');
            if ($settings['city_list'] == 'yes') {
                $types = array('billing', 'shipping');
                foreach ($types as $type) {

                    $fields[$type][$type . '_city'] = array(
                        'label'        => __('City', 'woocommerce'),
                        'description'  => '',
                        'class'        => explode(',', 'form-row-wide,address-field'),
                        'type'         => 'city',
                        'required'     => true,
                        'id'           => $type . '_city',
                        'autocomplete' => 'city',

                    );

                }
            }

            return $fields;
        }

        public function change_woocommerce_form_field_city($field, $key, $args, $value)
        {

            $speedbox_helper = new WC_Speedbox_Helper();

            $cities = $speedbox_helper->get_cities_values_from_data();

            if (1 === sizeof($cities)) {

                $field .= '<strong>' . current(array_values($cities)) . '</strong>';

                $field .= '<input type="hidden" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" value="' . current(array_keys($cities)) . '"  class="country_to_state" />';

            } else {

                $field = '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" ' . $args['autocomplete'] . ' class="city_select ' . esc_attr(implode(' ', $args['input_class'])) . '" >'
                . '<option value="">' . __('Select a city&hellip;', MD_SPEEDBOX_DOMAIN) . '</option>';

                foreach ($cities as $ckey => $cvalue) {
                    $field .= '<option value="' . esc_attr($ckey) . '" ' . selected($value, $ckey, false) . '>' . $cvalue . '</option>';
                }

                $field .= '</select>';

            }
            $label_id        = $args['id'];
            $field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>';
            if (!empty($field)) {
                $field_html = '';

                if ($args['label']) {
                    $field_html .= '<label for="' . esc_attr($label_id) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . $args['label'] . $required . '</label>';
                }

                $field_html .= $field;

                if ($args['description']) {
                    $field_html .= '<span class="description">' . esc_html($args['description']) . '</span>';
                }

                $container_class = 'form-row ' . esc_attr(implode(' ', $args['class']));
                $container_id    = esc_attr($args['id']) . '_field';

                $after = !empty($args['clear']) ? '<div class="clear"></div>' : '';

                $field = sprintf($field_container, $container_class, $container_id, $field_html) . $after;
            }
            return $field;

        }

    }
}

$ss = new Speedbox_Shipping();
