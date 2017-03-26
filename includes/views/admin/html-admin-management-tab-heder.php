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
        <form id="exportform" action="admin.php?page=woocommerce-speedbox" method="POST" enctype="multipart/form-data">
        <table class="wp-list-table widefat fixed posts">
            <thead>
                <tr>
                    <th scope="col" id="checkbox" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-1"><?php echo __('', MD_SPEEDBOX_DOMAIN); ?></label><input onchange="checkallboxes(this)" id="cb-select-all-1" type="checkbox"/></th>
                    <th scope="col" id="order_id" class="manage-column column-order_id" style=""><?php echo __('Order ID', MD_SPEEDBOX_DOMAIN); ?></th>
                    <th scope="col" id="order_date" class="manage-column column-order_date" style=""><?php echo __('Date', MD_SPEEDBOX_DOMAIN); ?></th>
                    <th scope="col" id="order_customer" class="manage-column column-order_customer" style=""><?php echo __('Customer', MD_SPEEDBOX_DOMAIN); ?></th>
                    <th scope="col" id="order_address" class="manage-column column-order_address" style=""><?php echo __('Destination', MD_SPEEDBOX_DOMAIN); ?></th>
                    <th scope="col" id="order_weight" class="manage-column column-order_weight" style=""><?php echo __('Weight', MD_SPEEDBOX_DOMAIN); ?></th>
                    <th scope="col" id="order_amount" class="manage-column column-order_amount"  style=""><?php echo __('Amount', MD_SPEEDBOX_DOMAIN); ?></th>
                    <th scope="col" id="order_status" class="manage-column column-order_status" style=""><?php echo __('Order Status', MD_SPEEDBOX_DOMAIN); ?></th>
                    <th scope="col" id="colis_status" class="manage-column column-colis_status" style=""><?php echo __('Package status', MD_SPEEDBOX_DOMAIN); ?></th>
                </tr>
            </thead>
            <tbody id="the-list">