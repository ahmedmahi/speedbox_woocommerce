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

 <tr valign="top" style="display:<?php echo ($this->get_option('gestion_frais_api') == 'interne') ? 'table-row' : 'none'; ?>" >
                        <th scope="row" class="titledesc"><?php _e('Shipping Rates', MD_SPEEDBOX_DOMAIN);?></th>
                        <td class="forminp" id="<?php echo $this->id; ?>_table_rates">
                            <table class="shippingrows widefat" cellspacing="0">
                                <col style="width:0%">
                                <col style="width:0%">
                                <col style="width:0%">
                                <col style="width:0%">
                                <col style="width:0%">
                                <col style="width:100%;">
                                <thead>
                                    <tr>
                                        <th class="check-column"><input type="checkbox"></th>
                                        <th class="debug-col"><?php _e('ID', MD_SPEEDBOX_DOMAIN);?></th>
                                        <th><?php _e('Zone', MD_SPEEDBOX_DOMAIN);?> <a class="tips" data-tip="<?php _e('Shipping zone, as defined in Shipping Zones table.', MD_SPEEDBOX_DOMAIN);?>">[?]</a></th>
                                        <th><?php _e('Condition', MD_SPEEDBOX_DOMAIN);?> <a class="tips" data-tip="<?php _e('Choose which metric to base your table rate on.', MD_SPEEDBOX_DOMAIN);?>">[?]</a></th>
                                        <th><?php _e('Min', MD_SPEEDBOX_DOMAIN);?> <a class="tips" data-tip="<?php _e('Minimum, in decimal format. Inclusive.', MD_SPEEDBOX_DOMAIN);?>">[?]</a></th>
                                        <th><?php _e('Max', MD_SPEEDBOX_DOMAIN);?> <a class="tips" data-tip="<?php _e('Maximum, in decimal format. Inclusive. To impose no upper limit, use *".', MD_SPEEDBOX_DOMAIN);?>">[?]</a></th>
                                        <th><?php _e('Cost', MD_SPEEDBOX_DOMAIN);?> <a class="tips" data-tip="<?php _e('Cost, excluding tax.', MD_SPEEDBOX_DOMAIN);?>">[?]</a></th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="7"><a href="#" class="add button"><?php _e('Add Shipping Rate', MD_SPEEDBOX_DOMAIN);?></a> <a href="#" class="remove button"><?php _e('Delete Selected Rates', MD_SPEEDBOX_DOMAIN);?></a></th>
                                    </tr>
                                </tfoot>
                                <tbody class="table_rates">

                                </tbody>
                            </table>
                            <script type="text/javascript">

                                var lastTableRateId = <?php echo $this->last_table_rate_id; ?>;

                                <?php
foreach ($this->table_rates as $table_rate):
    $js_array = json_encode($table_rate);
    echo "jQuery(addTableRateRowHtml(false, {$js_array})).appendTo('#{$this->id}_table_rates table tbody');\n";
endforeach;
?>

                                function addTableRateRowHtml(isNew, rowArr) {

                                    if (isNew) {
                                        lastTableRateId++;
                                        rowArr = {};
                                        rowArr['id'] = lastTableRateId;
                                        rowArr['zone'] = '<?php echo (!empty($this->zones[0]['id'])) ? $this->zones[0]['id'] : 0; ?>';
                                        rowArr['basis'] = 'weight';
                                        rowArr['min'] = '0';
                                        rowArr['max'] = '*';
                                        rowArr['cost'] = '0';
                                        rowArr['enabled'] = '1';
                                    }

                                    var size = jQuery('#<?php echo $this->id; ?>_table_rates tbody .table_rate').size();
                                    var html = '\
                                            <tr class="table_rate">\
                                                <input type="hidden" name="<?php echo $this->id; ?>_table_rate_id[' + size + ']" value="' + rowArr['id'] + '" />\
                                                <input type="hidden" name="<?php echo $this->id; ?>_table_rate_enabled[' + size + ']" value="' + rowArr['enabled'] + '" />\
                                                <th class="check-column"><input type="checkbox" name="select" /></th>\
                                                <td class="debug-col">\
                                                    ' + rowArr['id'] + '\
                                                </td>\
                                                <td>\
                                                    <select name="<?php echo $this->id; ?>_table_rate_zone[' + size + ']">\
                                                        ' + generateSelectOptionsHtml(options['table_rate_zone'], rowArr['zone']) + '\
                                                    </select>\
                                                </td>\
                                                <td>\
                                                    <select name="<?php echo $this->id; ?>_table_rate_basis[' + size + ']">\
                                                        ' + generateSelectOptionsHtml(options['rate_basis'], rowArr['basis']) + '\
                                                    </select>\
                                                </td>\
                                                <td>\
                                                    <input type="text" name="<?php echo $this->id; ?>_table_rate_min[' + size + ']" value="' + rowArr['min'] + '" placeholder="0" size="4" />\
                                                </td>\
                                                <td>\
                                                    <input type="text" name="<?php echo $this->id; ?>_table_rate_max[' + size + ']" value="' + rowArr['max'] + '" placeholder="*" size="4" />\
                                                </td>\
                                                <td>\
                                                    <input type="text" name="<?php echo $this->id; ?>_table_rate_cost[' + size + ']" value="' + rowArr['cost'] + '" placeholder="<?php echo wc_format_localized_price(0); ?>" size="4" class="wc_input_price" />\
                                                </td>\
                                            </tr>';
                                    return html;
                                }

                                jQuery(function() {

                                    jQuery('#<?php echo $this->id; ?>_table_rates').on( 'click', 'a.add', function(){

                                        jQuery(addTableRateRowHtml(true, false)).appendTo('#<?php echo $this->id; ?>_table_rates table tbody');

                                        return false;
                                    });

                                    // Remove row
                                    jQuery('#<?php echo $this->id; ?>_table_rates').on( 'click', 'a.remove', function(){
                                        var answer = confirm("<?php _e('Delete the selected rates?', MD_SPEEDBOX_DOMAIN);?>");
                                        if (answer) {
                                            jQuery('#<?php echo $this->id; ?>_table_rates table tbody tr th.check-column input:checked').each(function(i, el){
                                                jQuery(el).closest('tr').remove();
                                            });
                                        }
                                        return false;
                                    });

                                });
                            </script>
                        </td>
                    </tr>