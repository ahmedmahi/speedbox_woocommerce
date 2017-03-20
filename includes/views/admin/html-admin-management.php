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
<link rel="stylesheet" type="text/css" href="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/css/admin/Speedbox_admin.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/js/jquery/plugins/fancybox/jquery.fancybox.css"/>
        <script type="text/javascript" src="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/js/jquery/plugins/fancybox/jquery.fancybox.js"></script>
        <script type="text/javascript">
            var $ = jQuery.noConflict();
            $(document).ready(function(){

                $('a.popup').fancybox({
                    'hideOnContentClick': true,
                    'padding'           : 0,
                    'overlayColor'      :'#D3D3D3',
                    'overlayOpacity'    : 0.7,
                    'width'             : 1024,
                    'height'            : 640,
                    'type'              :'iframe'
                });
                $.expr[':'].contains = function(a, i, m) {
                    return $(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
                };

                $("#tableFilter").keyup(function () {
                    //split the current value of tableFilter
                    var data = this.value.split(";");
                    //create a jquery object of the rows
                    var jo = $("#the-list").find("tr");
                    if (this.value == "") {
                        jo.show();
                        return;
                    }
                    //hide all the rows
                    jo.hide();

                    //Recusively filter the jquery object to get results.
                    jo.filter(function (i, v) {
                        var t = $(this);
                        for (var d = 0; d < data.length; ++d) {
                            if (t.is(":contains('" + data[d] + "')")) {
                                return true;
                            }
                        }
                        return false;
                    })
                    //show the rows that match.
                    .show();
                    }).focus(function () {
                        this.value = "";
                        $(this).css({
                            "color": "black"
                        });
                        $(this).unbind('focus');
                    }).css({
                        "color": "#C0C0C0"
                    });
            });
            function checkallboxes(ele) {
                 var checkboxes = document.getElementsByName('checkbox[]');
                 if (ele.checked) {
                     for (var i = 0; i < checkboxes.length; i++) {
                         if (checkboxes[i].type == 'checkbox') {
                             checkboxes[i].checked = true;
                         }
                     }
                 } else {
                     for (var i = 0; i < checkboxes.length; i++) {
                         if (checkboxes[i].type == 'checkbox') {
                             checkboxes[i].checked = false;
                         }
                     }
                 }
             }
        </script>


        <h2><img src="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/img/admin/carrier_speedbox_logo.png"/> Gestion des expéditions Speedbox</h2>
<input id="tableFilter" value="<?php echo __('Search something, separate values by semicolons ;', MD_SPEEDBOX_DOMAIN); ?>"/><img id="filtericon" src="<?php echo MD_SPEEDBOX_ROOT_URL; ?>/assets/img/admin/search.png"/><br/><br/>