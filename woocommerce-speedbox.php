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

            //le chargement automatique des states fait une boucle infini je le desactive pour l'instnt
            //add_filter('woocommerce_states', array($this, 'addStatesMaroc'));

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
        }

    }
}

$z = new Speedbox_Shipping();
