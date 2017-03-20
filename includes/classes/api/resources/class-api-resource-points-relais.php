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

class SPEEDBOX_API_Resource_Points_Relais extends SPEEDBOX_API_Resource
{

    const ISAUTH = 0;
    public function __construct($api)
    {

        parent::__construct('listeprville', $api);
    }

    public function get_by_city($ville, $args = array())
    {

        $this->set_request_args(array(
            'method' => 'POST',
            'body'   => array('ville' => $ville),
        ));

        return $this->do_request(self::ISAUTH);
    }

}
