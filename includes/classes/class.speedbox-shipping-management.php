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

if (!class_exists('WC_Speedbox_Shipping_Relai_Management')) {
    class WC_Speedbox_Shipping_Relai_Management
    {
        public $speedbox_api;
        public $speedbox_helper;
        public $log;
        public function __construct()
        {
            $this->speedbox_helper = new WC_Speedbox_Helper();
            $this->speedbox_api    = $this->speedbox_helper->get_api();
            $this->log             = new Logging();

        }

        public function envoiDemandePriseEnCharge($checkbox)
        {
            $numero_prise_en_charge = $this->speedbox_helper->generate_token();
            foreach ($checkbox as $order_id) {
                $order = new WC_Order($order_id);

                $shipping_method_id = $this->speedbox_helper->get_shipping_method_id($order);
                $pointrelaist       = get_post_meta($order->id, '_pointrelais', true);
                if ($shipping_method_id == 'speedbox_relais' && $pointrelaist && !get_post_meta($order->id, '_numero_colis', true)) {

                    $numero_colis = $this->speedbox_helper->generate_token();

                    $poids = 0;
                    switch (get_option('woocommerce_weight_unit')) {
                        case 'kg':
                            $poids = $_POST['poids'][$order_id];
                            break;
                        case 'g':
                            $poids = number_format($_POST['poids'][$order_id] / 1000, 2, '.', '');
                            break;

                    }
                    $coli = array(
                        'date_de_commande' => date(__('d/m/Y', 'woocommerce'), strtotime($order->order_date)),
                        'numero_colis'     => $numero_colis,
                        'pointrelais'      => $pointrelaist,
                        'nom_du_client'    => $order->shipping_first_name . ' ' . $order->shipping_last_name,
                        'email_du_client'  => $order->billing_email,
                        'numero_du_client' => $this->speedbox_helper->formatTel($order->billing_phone),
                        'cash_due'         => ('cod' === $order->payment_method) ? number_format($order->get_total(), 2, '.', '') : '0',
                        'poids'            => $poids,
                    );

                    $result = $this->speedbox_api->colis->create($coli);

                    if (is_array($result) && $result['result'] == 'ok') {
                        update_post_meta($order_id, '_numero_colis', $numero_colis);
                        update_post_meta($order_id, '_colis_code_barre', $result['code_barre']);
                        update_post_meta($order_id, '_colis_numero_speedbox', $result['numero_speedbox']);
                        update_post_meta($order_id, '_colis_statut', $this->speedbox_helper->getStatus($result['statut']));

                        $this->apiPriseEnCharge($numero_prise_en_charge, $result['numero_speedbox'], $order_id);
                        $this->addNote_changeOrderStatut($order);
                        $this->apiTracker($result['numero_speedbox'], $order_id);

                    } else {
                        wp_die('<div class="warnmsg">' . __('#order ID :' . $order_id . '# ' . $result, MD_SPEEDBOX_DOMAIN) . '</div>');
                    }

                } elseif ($colis_numero_speedbox = get_post_meta($order->id, '_colis_numero_speedbox', true)) {

                    $track = $this->apiTracker($colis_numero_speedbox, $order_id);
                    if (!$track['numero_prise_en_charge']) {

                        $this->apiPriseEnCharge($numero_prise_en_charge, $colis_numero_speedbox, $order_id);
                        $this->addNote_changeOrderStatut($order);
                    }

                }

            }

        }

        public function tracker_colis($checkbox)
        {

            foreach ($checkbox as $order_id) {
                $order              = new WC_Order($order_id);
                $shipping_method_id = $this->speedbox_helper->get_shipping_method_id($order);

                $colis_numero_speedbox = get_post_meta($order->id, '_colis_numero_speedbox', true);
                if ($shipping_method_id == 'speedbox_relais' && $colis_numero_speedbox) {
                    $this->apiTracker($colis_numero_speedbox, $order_id);
                }
            }
        }

        public function cancel_colis($checkbox)
        {

            foreach ($checkbox as $order_id) {
                $order = new WC_Order($order_id);

                $shipping_method_id    = $this->speedbox_helper->get_shipping_method_id($order);
                $colis_numero_speedbox = get_post_meta($order->id, '_colis_numero_speedbox', true);
                if ($shipping_method_id == 'speedbox_relais' && $colis_numero_speedbox) {
                    $result = $this->speedbox_api->colis->cancel($colis_numero_speedbox);

                    $this->valdate_print_message($result, $order_id, __('Package well removed', MD_SPEEDBOX_DOMAIN));

                }
            }

        }

        public function apiPriseEnCharge($numero_prise_en_charge, $numero_speedbox, $order_id)
        {
            $infos_depc = array(
                'numero_prise_en_charge' => $numero_prise_en_charge,
                'numero_speedbox'        => $numero_speedbox,

            );

            $result_depc = $this->speedbox_api->colis->demandePriseEnCharge($infos_depc);

            $post_metas = array('_numero_prise_en_charge' => $numero_prise_en_charge);
            $this->valdate_print_message($result_depc, $order_id, __('Support well sent:', MD_SPEEDBOX_DOMAIN), $post_metas);

        }

        public function apiTracker($colis_numero_speedbox, $order_id, $dajatraite = false)
        {

            $track = $this->speedbox_api->colis->track($colis_numero_speedbox);

            if (isset($track['numero_prise_en_charge']) && $track['numero_prise_en_charge']) {

                $track['Statut']                 = $this->speedbox_helper->getStatus($track['statut']);
                $track['Historique des statuts'] = implode("=>", $this->speedbox_helper->getStatutHistorique($track['statut_historique']));
                $track['Dernière mise à jour'] = date(__('d/m/Y H:i', 'woocommerce'), $track['last_updated_timestamp']);
                unset($track['last_updated_timestamp']);
                unset($track['statut']);
                unset($track['statut_historique']);
            }

            $message    = ($dajatraite ? __('Parcels already treated here is the information:', MD_SPEEDBOX_DOMAIN) : '');
            $post_metas = array('_colis_statut' => $track['Statut']);

            $this->valdate_print_message($track, $order_id, $message, $post_metas);

            return $track;
        }

        public function addNote_changeOrderStatut($order, $html = '')
        {

            $note = __('Dear customer, you can follow your Speedbox parcel delivery by Refresh a page', MD_SPEEDBOX_DOMAIN);

            $order->add_order_note($note . ' ' . $html, 1);

            $order->update_status('processing');
        }
        public function valdate_print_message($result, $order_id, $message, $post_metas = array())
        {
            if (is_array($result) && $result['resultat'] == 'ok') {
                foreach ($post_metas as $key => $value) {
                    $initial = get_post_meta($order_id, $key, true);
                    if ($initial != $value) {
                        update_post_meta($order_id, $key, $value);
                    }
                }
                unset($result['resultat']);
                echo $html = '<div class="okmsg">' . __('#Commande ID :' . $order_id . '# ', MD_SPEEDBOX_DOMAIN) . $message . $this->speedbox_helper->html_show_array($result) . '</div>';
            } else {
                wp_die('<div class="warnmsg">' . __('#Commande ID :' . $order_id . '# ' . $result, MD_SPEEDBOX_DOMAIN) . '</div>');
            }

        }

    }

}

function display_management_page()
{
    global $woocommerce;

    $speedbox_management = new WC_Speedbox_Shipping_Relai_Management();

    include_once MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-management.php';

    if (isset($_POST['envoiDPEC'])) {
        if (!isset($_POST['checkbox'])) {
            echo '<div class="warnmsg">' . __('No order selected', MD_SPEEDBOX_DOMAIN) . '</div>';
        } else {

            $speedbox_management->envoiDemandePriseEnCharge($_POST['checkbox']);

        }
    }
    if (isset($_POST['cancelDPEC'])) {
        if (!isset($_POST['checkbox'])) {
            echo '<div class="warnmsg">' . __('No order selected', MD_SPEEDBOX_DOMAIN) . '</div>';
        } else {

            $speedbox_management->cancel_colis($_POST['checkbox']);

        }
    }
    if (isset($_POST['TrackerDPEC'])) {
        if (!isset($_POST['checkbox'])) {
            echo '<div class="warnmsg">' . __('No order selected', MD_SPEEDBOX_DOMAIN) . '</div>';
        } else {

            $speedbox_management->tracker_colis($_POST['checkbox']);

        }
    }

    if (isset($_POST['deliveredOrders'])) {
        if (!isset($_POST['checkbox'])) {
            echo '<div class="warnmsg">' . __('No order selected', MD_SPEEDBOX_DOMAIN) . '</div>';
        } else {
            foreach ($_POST['checkbox'] as $order_id) {
                $order = new WC_Order($order_id);
                update_post_meta($order->id, '_colis_statut', 'STATUT_RECU');
                // $order->update_status('completed');
            }

            /* Display confirmation message */
            echo '<div class="okmsg">' . __('Delivered orders statuses were updated', MD_SPEEDBOX_DOMAIN) . '</div>';
        }
    }

    $orders_ids = $speedbox_management->speedbox_helper->getOrders();

    if (empty($orders_ids)) {
        wp_die('<div class="warnmsg">' . __('There are no Speedbox orders', MD_SPEEDBOX_DOMAIN) . '</div>');
        exit;
    }

    /* Table header */

    include_once MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-management-tab-heder.php';

    /* Collect order data */
    foreach ($orders_ids as $post_id) {
        $colis_statut = get_post_meta($post_id, '_colis_statut', true);
        if ($colis_statut != 'STATUT_RECU' && $colis_statut != 'STATUT_ANNULE') {
            $order    = wc_get_order($post_id);
            $order_id = $order->get_order_number();

            $shipping_method_id = $speedbox_management->speedbox_helper->get_shipping_method_id($order);
            if ($shipping_method_id == 'speedbox_relais') {
                $order_weight = 0;
                foreach ($order->get_items() as $item_id => $item) {
                    $order_weight = $order_weight + ($order->get_product_from_item($item)->get_weight() * $item['qty']);
                }

                $address = $order->shipping_company . '<br/>' . $order->shipping_postcode . ' ' . $order->shipping_city;

                include MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-management-table-row.php';

            }

        }
    }
    include_once MD_SPEEDBOX_FILE_PATH . '/includes/views/admin/html-admin-management-boutons.php';

}
