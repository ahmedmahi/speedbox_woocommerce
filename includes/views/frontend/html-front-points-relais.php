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

<?php if (!empty($speedbox_relais_points['error'])): ?>
    <div class="sb_relais_error"><?php echo $speedbox_relais_points['error'] ?></div>
     <?php else: ?>
    <script type="text/javascript">
                        if (typeof relais_data != "undefined")
                            $("#"+relais_data.relay_id).click();
                        </script>
   <div id="sb_relais_filter" onclick="
                            $('#sb_relais_filter').hide();
                            $('.sb_relaisbox').hide();"></div>
                        <table align="center" id="sb_relais_point_table" class="sb_relaistable">
                         <tr>
                            <td colspan="2" style="padding:0px;" class="sb_headertd">
                                <div id="sb_div_relais_header"><p><?php echo __('Please select your Pickup point among this list', MD_SPEEDBOX_DOMAIN) ?></p></div>

                            </td>
                        </tr>
                        <?php if (!empty($speedbox_relais_points['empty'])): ?>



        <tr><td colspan="2" style="padding:0px;" class="sb_headertd"><div class="sb_relais_error"><?php echo $speedbox_relais_points['empty']; ?></div></td></tr>
               <?php else: ?>
    <?php foreach ($speedbox_relais_points as $key => $item): ?>

                <tr><td colspan="2" class="sb_tdpr">
                    <div class="sb_lignepr">
                        <div align="left" class="sb_logorelais"></div>
                        <div align="left" class="sb_adressepr"><b>
                          <a href="#!" onClick="popup_speedbox_view('<?php echo MD_SPEEDBOX_ROOT_URL; ?>','sb_relaydetail<?php echo $key; ?>','map_canvas<?php echo $key; ?>','<?php echo $item['coord_lat']; ?>','<?php echo $item['coord_long']; ?>')"><?php echo $item['shop_name']; ?></a>
                        </b><br/><?php echo $item['address1']; ?><br/><?php echo $item['postal_code'] . ' ' . $item['city']; ?><br/></div>
                        <div align="right" class="sb_distancepr"><a href="#!" onClick="popup_speedbox_view('<?php echo MD_SPEEDBOX_ROOT_URL; ?>','sb_relaydetail<?php echo $key; ?>','map_canvas<?php echo $key; ?>','<?php echo $item['coord_lat']; ?>','<?php echo $item['coord_long']; ?>')"><?php echo $item['distance']; ?> km</a></div>
                        <div align="center" class="sb_radiopr">
                            <input onclick="write_point_relais_vlues(document.getElementById('<?php echo $item['relay_id']; ?>'), '<?php echo ($ki == 0) ? 'first' : '' ?>')" type="radio" name="sb_relay_id" id="<?php echo $item['relay_id']; ?>" value='<?php echo json_encode($item); ?>'></input>
                            <label for="<?php echo $item['relay_id']; ?>"><span><span></span></span><b><?php echo __('', MD_SPEEDBOX_DOMAIN); ?></b></label>
                        </div>
                    </div>
                </td></tr>

            <div class="sb_relaisbox" id="sb_relaydetail<?php echo $key; ?>" style="display:none;">
                        <div class="sb_relaisboxclose" onclick="
                            document.getElementById('sb_relaydetail<?php echo $key; ?>').style.display='none';
                            document.getElementById('sb_relais_filter').style.display='none'">
                            <img src="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/img/front/relais/box-close.png"/>
                        </div>
                                <div class="boxcarto" id="map_canvas<?php echo $key; ?>" style="width:100%;"></div>
                                <div id="boxbottom" class="boxbottom">
                                <div id="boxadresse" class="boxadresse">
                                    <div class="boxadresseheader"><img src="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/img/front/relais/logo-speedbox.png" alt="" /></div>
                                    <strong><?php echo $item['shop_name']; ?></strong></br><?php echo $item['address1']; ?></br>


            <?php if (!empty($item['address2'])): ?>
                 <?php echo $item['address2']; ?></br>
            <?php endif;?>
             <?php echo $item['postal_code'] . '  ' . $item['city']; ?><br/>
           <?php if (!empty($item['local_hint'])): ?>
                <p><?php echo __('Landmark', MD_SPEEDBOX_DOMAIN) . ' : ' . $item['local_hint']; ?></p>
           <?php endif;?>

           </div>

           <div class="boxhoraires">
                                <div class="boxhorairesheader"><img src="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/img/front/relais/horaires.png" alt="-" width="32" height="32"/><br/><?php echo __('Opening Hours', MD_SPEEDBOX_DOMAIN); ?></div>
                                <p><span><?php echo __('Monday', MD_SPEEDBOX_DOMAIN); ?> : </span><?php echo $item['opening_hours']['monday']; ?></p>
                                <p><span><?php echo __('Tuesday', MD_SPEEDBOX_DOMAIN); ?> : </span><?php echo $item['opening_hours']['tuesday']; ?></p>
                                <p><span><?php echo __('Wednesday', MD_SPEEDBOX_DOMAIN); ?> : </span><?php echo $item['opening_hours']['wednesday']; ?></p>
                                <p><span><?php echo __('Thursday', MD_SPEEDBOX_DOMAIN); ?> : </span><?php echo $item['opening_hours']['thursday']; ?></p>
                                <p><span><?php echo __('Friday', MD_SPEEDBOX_DOMAIN); ?> : </span><?php echo $item['opening_hours']['friday']; ?></p>
                                <p><span><?php echo __('Saturday', MD_SPEEDBOX_DOMAIN); ?> : </span><?php echo $item['opening_hours']['saturday']; ?></p>
                                <p><span><?php echo __('Sunday', MD_SPEEDBOX_DOMAIN); ?> : </span><?php echo $item['opening_hours']['sunday']; ?></p>
                            </div>

            <div class="boxinfos">
                                <div><h5><?php echo __('Distance in KM', MD_SPEEDBOX_DOMAIN); ?> : </h5><strong><?php echo $item['distance']; ?> km </strong></div>
                                <div><h5><?php echo __('Pickup ID', MD_SPEEDBOX_DOMAIN); ?> : </h5><strong><?php echo (string) $item['relay_id']; ?> </strong></div>
                                <div><img src="<?php echo $item['images']; ?>" alt="-"  height="162" /> </div>

            <?php if (!empty($item['closing_period']) && count($item['closing_period']) > 0): ?>
           <?php foreach ($item['closing_period'] as $holiday_item): ?>
                   <?php $holiday_item = (array) $holiday_item;?>
                    <div><img id="boxinfoswarning" src="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/img/front/relais/warning.png" alt="-" width="16" height="16"/> <h4><?php echo __('Closing period', MD_SPEEDBOX_DOMAIN); ?> : </h4> <?php echo $holiday_item[0]; ?></div>
                <?php endforeach;?>
            <?php endif;?>
            </div>

            </div>
            </div>
             <?php $ki++;?>
         <?php endforeach;?>
    <?php endif;?>
    </table>
    <script type="text/javascript">
                        if (typeof $ === "function" && $("input[name=sb_relay_id]:checked").length == 0)
                            $("input[name=sb_relay_id]").first().click();
                    </script>
  <?php endif;?>
