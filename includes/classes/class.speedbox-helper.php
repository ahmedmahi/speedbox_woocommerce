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

if (!class_exists('WC_Speedbox_Helper')) {
    class WC_Speedbox_Helper
    {
        public function get_api()
        {
            $options = array(
                'validate_url' => false,
                'timeout'      => 30,
                'ssl_verify'   => false,
            );
            $settings             = get_option('woocommerce_speedbox_relais_settings');
            $speedbox_URL         = $settings['speedbox_relais_URL'];
            $speedbox_SecurityKey = $settings['speedbox_relais_SecurityKey'];
            $api_token            = $settings['api_token'];

            return new SPEEDBOX_API($speedbox_URL, $api_token, $speedbox_SecurityKey);

        }

        public function get_speedbox_points_relais($customer_data, $speedbox_relais)
        {

            $speedbox_relais_points = array();
            $ville_proche           = array();
            $country                = $this->stripAccents($customer_data->country);
            $city                   = $this->stripAccents($customer_data->city/*shipping_city*/);
            $zipcode                = $customer_data->postcode/*shipping_postcode*/;
            $points_relais          = $speedbox_relais->speedbox_api->points_relais->get_by_city($city);
            if ($country != 'MA') {
                $speedbox_relais_points = array('error' => __('This shipping method is only available in Morocco .', MD_SPEEDBOX_DOMAIN));
            } else if (is_array($points_relais) && !empty($points_relais) && !isset($points_relais['error'])) {
                $speedbox_relais_points           = $points_relais;
                $ville_proche['min_city']['city'] = $city;
                $ville_proche['distance']         = 1000;

            } else if (!empty($city_data = $this->get_city_from_data($city/*, $zipcode*/))) {
                // desactivation du recherche basé sur les codes postales
                $cities                 = $speedbox_relais->speedbox_api->villes->get();
                $ville_proche           = $this->min_circle_distance($city_data, $cities);
                $speedbox_relais_points = $speedbox_relais->speedbox_api->points_relais->get_by_city($ville_proche['min_city']['city']);

            } else {
                $speedbox_relais_points = array('error' => __('There are no Pickup points near this address, please modify it.', MD_SPEEDBOX_DOMAIN));
            }

            try {

                if (!isset($speedbox_relais_points['error'])) {
                    foreach ($speedbox_relais_points as $pr => $item) {
                        $point = array();
                        $item  = (array) $item;

                        $point['relay_id']  = $item['id'];
                        $point['shop_name'] = $this->stripAccents($item['nom']);
                        $point['address1']  = $this->stripAccents($item['adresse']);

                        $point['city']     = $this->stripAccents($ville_proche['min_city']['city']);
                        $point['distance'] = number_format($ville_proche['distance'] / 1000, 2);
                        if ($point['distance'] == 0) {
                            $point['distance'] = 1;
                        }

                        $point['coord_lat']  = (float) strtr($item['gps_lat'], ',', '.');
                        $point['coord_long'] = (float) strtr($item['gps_lng'], ',', '.');
                        $point['images']     = $item['images'];
                        $point['postcode']   = $this->get_postalcode($point['city']);

                        if (isset($city_data['region'])) {

                            $point['state'] = $city_data['region'];
                        } else {
                            $p_city         = $this->get_city_from_data($point['city']);
                            $point['state'] = $p_city['region'];
                        }
                        $point['state'] = $this->speedbox_get_state($point['state']);

                        $days = array(0 => 'monday', 1 => 'tuesday', 2 => 'wednesday', 3 => 'thursday', 4 => 'friday', 5 => 'saturday', 6 => 'sunday');
                        if (count($item['horaires']) > 0) {
                            foreach ($item['horaires'] as $k => $oh_item) {
                                $point[$days[$k]][] = gmdate("H:i", $oh_item[0]['ouverture']) . ' - ' . gmdate("H:i", $oh_item[0]['fermeture']);
                            }
                        }

                        if (empty($point['monday'])) {$h1 = __('Closed', MD_SPEEDBOX_DOMAIN);} else {
                            if (empty($point['monday'][1])) {$h1 = $point['monday'][0];} else { $h1 = $point['monday'][0] . ' & ' . $point['monday'][1];}}

                        if (empty($point['tuesday'])) {$h2 = __('Closed', MD_SPEEDBOX_DOMAIN);} else {
                            if (empty($point['tuesday'][1])) {$h2 = $point['tuesday'][0];} else { $h2 = $point['tuesday'][0] . ' & ' . $point['tuesday'][1];}}

                        if (empty($point['wednesday'])) {$h3 = __('Closed', MD_SPEEDBOX_DOMAIN);} else {
                            if (empty($point['wednesday'][1])) {$h3 = $point['wednesday'][0];} else { $h3 = $point['wednesday'][0] . ' & ' . $point['wednesday'][1];}}

                        if (empty($point['thursday'])) {$h4 = __('Closed', MD_SPEEDBOX_DOMAIN);} else {
                            if (empty($point['thursday'][1])) {$h4 = $point['thursday'][0];} else { $h4 = $point['thursday'][0] . ' & ' . $point['thursday'][1];}}

                        if (empty($point['friday'])) {$h5 = __('Closed', MD_SPEEDBOX_DOMAIN);} else {
                            if (empty($point['friday'][1])) {$h5 = $point['friday'][0];} else { $h5 = $point['friday'][0] . ' & ' . $point['friday'][1];}}

                        if (empty($point['saturday'])) {$h6 = __('Closed', MD_SPEEDBOX_DOMAIN);} else {
                            if (empty($point['saturday'][1])) {$h6 = $point['saturday'][0];} else { $h6 = $point['saturday'][0] . ' & ' . $point['saturday'][1];}}

                        if (empty($point['sunday'])) {$h7 = __('Closed', MD_SPEEDBOX_DOMAIN);} else {
                            if (empty($point['sunday'][1])) {$h7 = $point['sunday'][0];} else { $h7 = $point['sunday'][0] . ' & ' . $point['sunday'][1];}}

                        $point['opening_hours'] = array('monday' => $h1, 'tuesday' => $h2, 'wednesday' => $h3, 'thursday' => $h4, 'friday' => $h5, 'saturday' => $h6, 'sunday' => $h7);
                        unset($speedbox_relais_points[$pr]);
                        $speedbox_relais_points[] = $point;

                    }
                }

            } catch (Exception $e) {
                $speedbox_relais_points['error'] = __('Speedbox Relais is not available at the moment, please try again shortly.', MD_SPEEDBOX_DOMAIN);
            }
            return $speedbox_relais_points;

        }
        public function get_city_from_data($city, $zipcode = 0)
        {
            $all_cities = $this->get_all_cities_from_data();
            foreach ($all_cities['cities'] as $key => $val) {
                if (strcasecmp($city, $this->stripAccents($val['city'])) == 0) {
                    $all_cities['cities'][$key]['ID'] = $key;
                    return ($all_cities['cities'][$key]);
                }
            }
            if ($zipcode) {
                $all_postalcodes = $this->get_all_postalcodes_from_data();
                foreach ($all_postalcodes['postalcodes'] as $pkey => $pval) {
                    foreach ($pval as $key => $val) {
                        if ($val == $zipcode) {
                            return $this->get_city_from_data($this->stripAccents($key));
                        }
                    }

                }
            }

            return array();
        }
        public function get_all_cities_from_data()
        {
            $city_data        = MD_SPEEDBOX_FILE_PATH . '/includes/data/city.json';
            $cities_json_data = file_get_contents($city_data);
            return json_decode($cities_json_data, true);

        }
        public function get_all_postalcodes_from_data()
        {
            $postalcodes           = MD_SPEEDBOX_FILE_PATH . '/includes/data/postalcodes.json';
            $postalcodes_json_data = file_get_contents($postalcodes);
            return json_decode($postalcodes_json_data, true);

        }
        public function get_cities_from_data()
        {
            $cities     = array();
            $all_cities = $this->get_all_cities_from_data();
            foreach ($all_cities['cities'] as $key => $val) {

                $cities[$key] = $val['city'];

            }
            return $cities;

        }
        public function get_cities_values_from_data()
        {
            $cities = array();
            foreach ($this->get_cities_from_data() as $key => $val) {

                $cities[$val] = $val;

            }
            return $cities;

        }
        public function get_postalcode($city)
        {

            $all_postalcodes = $this->get_all_postalcodes_from_data();

            foreach ($all_postalcodes['postalcodes'] as $pkey => $pval) {
                foreach ($pval as $key => $val) {
                    if ($city == $key) {
                        return $val;
                    }
                }

            }

            return '';

        }

        public function circleDistance(
            $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
            // convert from degrees to radians
            $latFrom = deg2rad($latitudeFrom);
            $lonFrom = deg2rad($longitudeFrom);
            $latTo   = deg2rad($latitudeTo);
            $lonTo   = deg2rad($longitudeTo);

            $lonDelta = $lonTo - $lonFrom;
            $a        = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
            $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

            $angle = atan2(sqrt($a), $b);
            return $angle * $earthRadius;
        }

        public function min_circle_distance($city, $cities)
        {
            $min = array('distance' => 999999999999999999999999, 'min_city' => array());
            foreach ($cities as $speedbox_city) {
                if (!empty($city_data = $this->get_city_from_data($speedbox_city))) {

                    $distance = $this->circleDistance(
                        $city_data['latitude'], $city_data['longitude'], $city['latitude'], $city['longitude']);
                    if ($distance < $min['distance']) {
                        $min['distance'] = $distance;
                        $min['min_city'] = $city_data;
                    }

                }
            }

            return $min;

        }
        public function stripAccents($str)
        {
            return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),

                'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        }

        public function generate_token()
        {
            $id_length = 9;

            $alfa  = "abcdefghijklmnopqrstuvwxyz1234567890";
            $token = "";
            for ($i = 1; $i < $id_length; $i++) {

                @$token .= $alfa[rand(1, strlen($alfa))];

            }
            return $token;
        }

        public function getOrders()
        {
            global $wpdb;
            return $wpdb->get_col("SELECT ID FROM {$wpdb->posts}
                            WHERE post_type = 'shop_order'
                            AND post_status IN ('publish','wc-pending','wc-processing','wc-on-hold')
                            ORDER BY id DESC");

        }

        public static function formatTel($gsm_dest)
        {

            $gsm_dest = str_replace(array(' ', '.', '-', ',', ';', '/', '\\', '(', ')'), '', $gsm_dest);

            if (substr($gsm_dest, 0, 1) == 0) {
                // Chrome autofill fix
                $gsm_dest = substr_replace($gsm_dest, '+212', 0, 1);
            } else {
                $gsm_dest = '+212' . $gsm_dest;
            }
            if ((substr($gsm_dest, 4, 1) == 6 || substr($gsm_dest, 4, 1) == 5) && strlen($gsm_dest) == 13) {
                return $gsm_dest;
            } else {
                return '+212600000000';
            }

        }
        public function get_shipping_method_id($order)
        {
            $shipping_method_id = '';
            foreach ($order->get_shipping_methods() as $shipping_method) {
                $shipping_method_id = $shipping_method['method_id'];
            }

            return $shipping_method_id;
        }

        public function getStatus($code)
        {

            $status = array(
                '100' => 'STATUT_DEMANDE_DE_PRISE_EN_CHARGE',
                '110' => 'STATUT_PRISE_EN_CHARGE_ID',
                '120' => 'STATUT_RAMASSAGE',
                '130' => 'STATUT_PRISE_EN_CHARGE_HUB',
                '140' => 'STATUT_CENTRE_DE_TRI',
                '150' => 'STATUT_TRIE',
                '160' => 'STATUT_MIS_EN_SAC',
                '170' => 'STATUT_EN_COURS_DE_LIVRAISON',
                '1'   => 'STATUT_EN_ATTENTE',
                '2'   => 'STATUT_RECU',
                '14'  => 'STATUT_DEVOYE',
                '3'   => 'STATUT_RECU_NON_CONFORME',
                '12'  => 'STATUT_REFUS_CLIENT',
                '4'   => 'STATUT_ENCAISSE',
                '8'   => 'STATUT_TRANSFERE',
                '13'  => 'STATUT_DELAIS_DE_GARDE_DEPASSE',
                '9'   => 'STATUT_ANNULE',

            );

            return $status[$code];
        }
        public function speedbox_get_state($code)
        {
            $states = array(
                '45' => 'Grand Casablanca',
                '50' => 'Chaouia-Ouardigha',
                '51' => 'Doukkala-Abda',
                '46' => 'Fès-Boulemane',
                '52' => 'Gharb-Chrarda-Beni Hssen',
                '53' => 'Guelmim-Es Semara',
                '47' => 'Marrakech-Tensift-Al Haouz',
                '48' => 'Meknès-Tafilalet',
                '54' => 'l\'Oriental',
                '49' => 'Rabat-Salé-Zemmour-Zaër',
                '55' => 'Souss-Massa-Draâ',
                '56' => 'Tadla-Azilal',
                '57' => 'Tanger-Tétouan',
                '58' => 'Taza-Al Hoceïma-Taounate',
                '59' => 'Laayoune-Boujdour-Sakia-Hamra',
                '60' => 'Oued-Eddahab-Lagouira',

            );
            return isset($states[$code]) ? $states[$code] : '';
        }
        public function getStatutHistorique($statut_historique)
        {
            foreach ($statut_historique as $key => $value) {
                $statut_historique[$key] = $this->getStatus($value);
            }
            return $statut_historique;

        }

        public function do_offset($level)
        {
            $offset = "";
            for ($i = 1; $i < $level; $i++) {
                $offset = $offset . "<td></td>";
            }
            return $offset;
        }

        public function show_array($array, $level, $sub)
        {
            $html = '';
            if (is_array($array) == 1) {
                // check if input is an array
                foreach ($array as $key_val => $value) {
                    $offset = "";
                    if (is_array($value) == 1) {
                        // array is multidimensional
                        $html .= "<tr>";
                        $offset = $this->do_offset($level);
                        $html .= $offset . "<td>" . $key_val . "</td>";
                        $html .= $this->show_array($value, $level + 1, 1);
                    } else {
                        // (sub)array is not multidim
                        if ($sub != 1) {
                            // first entry for subarray
                            $html .= "<tr nosub>";
                            $offset = $this->do_offset($level);
                        }
                        $sub = 0;
                        $html .= $offset . "<td main " . $sub . " >" . $key_val .
                            "</td><td>" . $value . "</td>";
                        $html .= "</tr>\n";
                    }
                } //foreach $array
            } else {
                // argument $array is not an array
                return;
            }
            return $html;
        }

        public function html_show_array($array)
        {
            $html = "<table>\n";
            $html .= $this->show_array($array, 1, 0);
            $html .= "</table>\n";
            return $html;
        }

    }

}
