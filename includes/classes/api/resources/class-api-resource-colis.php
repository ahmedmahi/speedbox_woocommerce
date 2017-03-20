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

class SPEEDBOX_API_Resource_Colis extends SPEEDBOX_API_Resource
{
    const ISAUTH = 1;

    public function __construct($api)
    {

        parent::__construct('cust', $api);
    }

    public function create($data)
    {

        $this->set_request_args(array(
            'method' => 'POST',
            'path'   => 'new',
            'body'   => $data,
        ));

        return $this->do_request(self::ISAUTH);
    }

    public function demandePriseEnCharge($data)
    {

        $this->set_request_args(array(
            'method' => 'POST',
            'path'   => 'dpc',
            'body'   => $data,
        ));

        return $this->do_request(self::ISAUTH);
    }

    public function search($numero_colis)
    {

        $this->set_request_args(array(
            'method' => 'POST',
            'path'   => 'search',
            'body'   => array('numero_colis' => urlencode($numero_colis)),
        ));

        return $this->do_request(self::ISAUTH);
    }

    public function coutTemps($pointrelais)
    {

        $this->set_request_args(array(
            'method' => 'POST',
            'path'   => 'ctd',
            'body'   => array(
                'pointrelais' => urlencode($pointrelais),
            ),
        ));

        return $this->do_request(self::ISAUTH);
    }
    public function track($numero_speedbox)
    {

        $this->set_request_args(array(
            'method' => 'POST',
            'path'   => 'track',
            'body'   => array(
                'numero_speedbox' => urlencode($numero_speedbox),
            )));

        return $this->do_request(self::ISAUTH);
    }
    public function cancel($numero_speedbox)
    {

        $this->set_request_args(array(
            'method' => 'POST',
            'path'   => 'cancel',
            'body'   => array(
                'numero_speedbox' => urlencode($numero_speedbox),
            )));

        return $this->do_request(self::ISAUTH);
    }

}
