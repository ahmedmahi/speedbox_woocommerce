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

class SPEEDBOX_API_HTTP_Exception extends SPEEDBOX_API_Exception
{

    protected $request;

    protected $response;

    public function __construct($message, $code = 0, $request, $response)
    {

        parent::__construct($message, $code);

        $this->request  = $request;
        $this->response = $response;
    }

    public function get_request()
    {

        return $this->request;
    }

    public function get_response()
    {

        return $this->response;
    }

}
