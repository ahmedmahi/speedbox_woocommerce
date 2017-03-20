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

abstract class SPEEDBOX_API_Resource
{

    protected $endpoint;

    protected $api;

    protected $request_method;

    protected $request_path;

    protected $request_params;

    protected $request_body;

    public function __construct($endpoint, $api)
    {

        $this->endpoint = $endpoint;
        $this->api      = $api;
    }

    protected function set_request_args($args)
    {

        $this->request_method = $args['method'];
        $this->request_path   = isset($args['path']) ? $args['path'] : null;
        $this->request_params = isset($args['params']) ? $args['params'] : null;
        $this->request_body   = isset($args['body']) ? $args['body'] : null;

    }

    protected function get_endpoint_path()
    {

        return empty($this->request_path) ? $this->endpoint : $this->endpoint . '/' . implode('/', (array) $this->request_path);
    }

    protected function get_request_data()
    {

        return ('GET' === $this->request_method || 'DELETE' === $this->request_method) ? $this->request_params : $this->request_body;
    }

    public function do_request($is_auth = 0)
    {

        try {
            $response = $this->api->make_api_call($this->request_method, $this->get_endpoint_path(), $this->get_request_data(), $is_auth);

            if (isset($response['errorcode'])) {
                return $response['error'] . ' ( errorcode:' . $response['errorcode'] . ' )';
            }

        } catch (Exception $e) {
            return ($e->getMessage());
        }
        return $response;

    }

}
