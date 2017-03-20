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

if (!defined('ABSPATH')) {
    exit;
}

?>
<tr>
   <td><input class="checkbox" type="checkbox" name="checkbox[]" value="<?php echo $order->get_order_number(); ?>"></td>
   <td class="id"><?php echo $order->get_order_number(); ?></td>
    <td class="date"><?php echo $order->order_date; ?></td>
    <td class="nom"><?php echo $order->shipping_first_name . ' ' . $order->shipping_last_name; ?></td>
    <td class="pr"><?php echo $address; ?></td>
    <td class="poids"><input name="poids[<?php echo $order->get_order_number(); ?>]" class="poids" value="<?php echo number_format($order_weight, (get_option('woocommerce_weight_unit') == 'g' ? 0 : 2), '.', ''); ?>"></input><?php echo get_option('woocommerce_weight_unit'); ?></td>
    <td class="prix" align="right"><?php echo number_format($order->get_total(), 2, '.', '') . ' ' . get_option('woocommerce_currency'); ?> </td>

    <td class="statutcommande" align="center"><?php echo wc_get_order_status_name($order->get_status()); ?></td>

                        </tr>
