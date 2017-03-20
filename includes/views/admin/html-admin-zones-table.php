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
<tr valign="top"       style="display:<?php echo ($this->get_option('gestion_frais_api') == 'interne') ? 'table-row' : 'none'; ?>" >
                        <th scope="row" class="titledesc"><?php _e('Shipping Zones', MD_SPEEDBOX_DOMAIN);?></th>
                        <td class="forminp" id="<?php echo $this->id; ?>_zones">
                            <p style="padding-bottom: 10px;"><?php _e('After adding a shipping zone, hit "Save changes" so that it appears as an option in the table rate section.', MD_SPEEDBOX_DOMAIN);?></p>
                            <table class="shippingrows widefat" cellspacing="0">
                                <col style="width:0%">
                                <col style="width:0%">
                                <col style="width:100%;">
                                <thead>
                                    <tr>
                                        <th class="check-column"><input type="checkbox"></th>
                                        <!--<th class="debug-col"><?php _e('ID', MD_SPEEDBOX_DOMAIN);?></th>-->
                                        <th><?php _e('Name', MD_SPEEDBOX_DOMAIN);?> <a class="tips" data-tip="<?php _e('Shipping zone name, will appear in table rates table.', MD_SPEEDBOX_DOMAIN);?>">[?]</a></th>
                                        <th><?php _e('Cities', MD_SPEEDBOX_DOMAIN);?> <a class="tips" data-tip="<?php _e('Add one or more Cities that are part of this shipping zone.', MD_SPEEDBOX_DOMAIN);?>">[?]</a></th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="3"><a href="#" class="add button"><?php _e('Add Shipping Zone', MD_SPEEDBOX_DOMAIN);?></a> <a href="#" class="remove button"><?php _e('Delete Selected Zones', MD_SPEEDBOX_DOMAIN);?></a></th>
                                    </tr>
                                </tfoot>
                                <tbody class="zones">
                                    <tr class="zone">
                                        <th></th>
                                        <!--<td class="debug-col">0</td>-->
                                        <td><div style="width: 200px;"><?php _e('Default Zone (All of Morocco)', MD_SPEEDBOX_DOMAIN);?></div></td>
                                        <td><em><?php _e('All of Morocco', MD_SPEEDBOX_DOMAIN);?></em></td>
                                    </tr>
                                </tbody>
                            </table>


                            <script type="text/javascript">
                               var lastZoneId = <?php echo $this->last_zone_id; ?>;

                                <?php
foreach ($this->zones as $zone):
    $js_array = json_encode($zone);
    echo "jQuery('#{$this->id}_zones table tbody tr:last').before(addZoneRowHtml(false, {$js_array}));\n";
endforeach;
?>

    function addZoneRowHtml(isNew, rowArr) {

       if (isNew) {
             lastZoneId++;
              rowArr = {};
              rowArr['id'] = lastZoneId;
              rowArr['name'] = '';
              rowArr['country'] = 'MA';
              rowArr['type'] = 'country';
              rowArr['include'] = '';
              rowArr['exclude'] = '';
              rowArr['enabled'] = '1';
         }

        var size = jQuery('#<?php echo $this->id; ?>_zones tbody .zone').size();
        var html = '\
            <tr class="zone">\
             <input type="hidden" name="<?php echo $this->id; ?>_zone_id[' + size + ']" value="' + rowArr['id'] + '" />\
             <input type="hidden" name="<?php echo $this->id; ?>_zone_type[' + size + ']" value="' + rowArr['type'] + '" />\
             <input type="hidden" name="<?php echo $this->id; ?>_zone_include[' + size + ']" value="' + rowArr['include'] + '" />\
             <input type="hidden" name="<?php echo $this->id; ?>_zone_exclude[' + size + ']" value="' + rowArr['exclude'] + '" />\
             <input type="hidden" name="<?php echo $this->id; ?>_zone_enabled[' + size + ']" value="' + rowArr['enabled'] + '" />\
             <th class="check-column"><input type="checkbox" name="select" /></th>\
             <td class="debug-col">\
               ' + rowArr['id'] + '\
              </td>\
              <td>\
              <input type="text" name="<?php echo $this->id; ?>_zone_name[' + size + ']" value="' + rowArr['name'] + '" size="30" placeholder="" />\
               </td>\
                <input type="hidden" name="<?php echo $this->id; ?>_zone_country[' + size + '][]"  value="'+rowArr['country']+'" >\
                <td style="overflow:visible;">\
                <select multiple="multiple" name="<?php echo $this->id; ?>_zone_city[' + size + '][]" class="multiselect chosen_select">\
                       ' + generateSelectOptionsHtml(options['cities']['MA'], rowArr['city']) + '\
                 </select>\
                 </td>\
                 </tr>';

               return html;
                                }

                                jQuery(function() {

                                    jQuery('#<?php echo $this->id; ?>_zones').on( 'click', 'a.add', function(){

                                        jQuery('#<?php echo $this->id; ?>_zones table tbody tr:last').before(addZoneRowHtml(true, false));

                                        if (jQuery().chosen) {
                                            jQuery("select.chosen_select").chosen({
                                                width: '350px',
                                                disable_search_threshold: 5
                                            });
                                        } else {
                                            jQuery("select.chosen_select").select2();
                                        }

                                        return false;
                                    });

                                    // Remove row
                                    jQuery('#<?php echo $this->id; ?>_zones').on( 'click', 'a.remove', function(){

                                        var answer = confirm("<?php _e('Delete the selected zones?', MD_SPEEDBOX_DOMAIN);?>");
                                        if (answer) {
                                            jQuery('#<?php echo $this->id; ?>_zones table tbody tr th.check-column input:checked').each(function(i, el){
                                                jQuery(el).closest('tr').remove();
                                            });
                                        }
                                        return false;
                                    });

                                });


    jQuery("#<?php echo $this->get_field_key('gestion_frais_api'); ?>").change(function() {
           var value = jQuery("#<?php echo $this->get_field_key('gestion_frais_api'); ?> option:selected").val();
           if ( value=='interne' ) {
          jQuery( '#<?php echo $this->get_field_key('supp_api'); ?>' ).closest( 'tr' ).hide();
          jQuery( '#<?php echo $this->get_field_key('default_price_api'); ?>' ).closest( 'tr' ).hide();
          jQuery( '#<?php echo $this->id; ?>_zones').closest( 'tr' ).show();
          jQuery( '#<?php echo $this->id; ?>_table_rates').closest( 'tr' ).show();
        } else if ( value=='via_api' ){
          jQuery( '#<?php echo $this->id; ?>_zones').closest( 'tr' ).hide();
          jQuery( '#<?php echo $this->id; ?>_table_rates').closest( 'tr' ).hide();
          jQuery( '#<?php echo $this->get_field_key('supp_api'); ?>' ).closest( 'tr' ).show();
          jQuery( '#<?php echo $this->get_field_key('default_price_api'); ?>' ).closest( 'tr' ).show();
    }


});
                            </script>
                        </td>
                    </tr>



















