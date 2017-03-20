
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

$dir = dirname(__FILE__);

// base class
require_once $dir . '/class-api.php';

// plumbing
require_once $dir . '/class-api-authentication.php';
require_once $dir . '/class-api-http-request.php';

// exceptions
require_once $dir . '/exceptions/class-api-exception.php';
require_once $dir . '/exceptions/class-api-http-exception.php';

// resources
require_once $dir . '/resources/abstract-api-resource.php';
require_once $dir . '/resources/class-api-resource-colis.php';
require_once $dir . '/resources/class-api-resource-points-relais.php';
require_once $dir . '/resources/class-api-resource-villes.php';
