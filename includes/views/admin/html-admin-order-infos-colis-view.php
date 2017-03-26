<h3><?php echo __('Speedbox Information', MD_SPEEDBOX_DOMAIN) ?></h3>
<p><strong> <?php echo __('Package status', MD_SPEEDBOX_DOMAIN) ?>:</strong> <br /><?php echo $_colis_statut ?> </p>
<p><strong><?php echo __('Number', MD_SPEEDBOX_DOMAIN) ?>:</strong> <br /><?php echo get_post_meta($order->id, '_colis_numero_speedbox', true) ?> </p>
<p><strong><?php echo __('Barcode') ?>:</strong><br /><?php echo get_post_meta($order->id, '_colis_code_barre', true) ?></p>