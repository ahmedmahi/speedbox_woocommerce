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

class SPEEDBOX_API_Authentication
{

    protected $url;

    protected $consumer_key;

    protected $consumer_secret;

    const HASH_ALGORITHM = 'SHA256';

    protected $au_request;

    protected $au_response;

    protected $seed = '';

    protected $ntoken = '';

    protected $options = array();

    public function __construct($url, $consumer_key, $consumer_secret, $options)
    {

        $this->url             = $url;
        $this->consumer_key    = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->options         = $options;
    }

    public function get_auth_params($params)
    {

        $this->setNtoken();

        $params = array_merge((array) $params, array(
            'ntoken'                => $this->ntoken,
            'auth_timestamp'        => time(),
            'auth_nonce'            => sha1(microtime()),
            'auth_signature_method' => 'HMAC-' . self::HASH_ALGORITHM,

        ));

        return $params;
    }

    public function generate_auth_signature($string_to_sign)
    {

        return base64_encode(hash_hmac(self::HASH_ALGORITHM, $string_to_sign, $this->consumer_secret, true));
    }

    public function is_ssl()
    {

        return substr($this->url, 0, 5) === 'https';
    }

    public function get_consumer_key()
    {
        return $this->consumer_key;
    }

    public function get_consumer_secret()
    {
        return $this->consumer_secret;
    }

    public function setNtoken()
    {
        if ($this->ntoken) {
            return;
        }

        $this->au_request = new stdClass();

        $this->au_request->headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: SpeedBox API Client-PHP/' . SPEEDBOX_API::VERSION,
        );

        $this->au_request->method = 'POST';

        // trailing slashes tend to cause OAuth authentication issues, so strip them
        $this->au_request->url = rtrim($this->url, '/');

        $this->au_request->params = array();
        $this->au_request->data   = array('token' => $this->consumer_key);

        if ($this->seed) {
            $this->au_request->data = array_merge($this->au_request->data, array(
                'seed'   => $this->seed,
                'secret' => $this->generate_auth_signature($this->seed),
            ));
        }

        // optional cURL opts
        $timeout    = (int) $this->options['timeout'];
        $ssl_verify = (bool) $this->options['ssl_verify'];

        $ch = curl_init();

        // default cURL opts
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verify);
        //  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verify);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // set request headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->au_request->headers);

        $this->au_request->body = json_encode($this->au_request->data);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->au_request->body);

        $this->au_request->url = $this->get_auth_url($this->url);

        // set request url
        curl_setopt($ch, CURLOPT_URL, $this->au_request->url);

        $this->au_response = new stdClass();

        // blank headers
        $this->curl_headers = '';

        $start_time = microtime(true);

        // send request + save raw response body
        $this->au_response->body = curl_exec($ch);

        // request duration
        $this->au_request->duration = round(microtime(true) - $start_time, 5);

        // response code
        $this->au_response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        unset($ch);
        $body = json_decode($this->au_response->body, true);

        if (isset($body['error'])) {

            throw new SPEEDBOX_API_HTTP_Exception(sprintf($body['error'] . ' Erreur code:  %s.', $body['errorcode']), $this->au_response->code, $this->au_request, $this->au_response);
        }
        if (isset($body['seed'])) {
            $this->seed = $body['seed'];

        }if (isset($body['ntoken'])) {
            $this->ntoken = $body['ntoken'];
        }

        if (!isset($body['error']) && $this->seed && !$this->ntoken) {
            $this->setNtoken();

        }

    }

    public function get_auth_url($url)
    {

        return $url .= 'cust/auth/';
    }

}
