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
 <script type="text/javascript">

     var options = <?php echo json_encode($this->generate_options()); ?>;

     function generateSelectOptionsHtml(options, selected) {
        var html;
        var selectedHtml;

        for (var key in options) {
            var value = options[key];

            if (selected instanceof Array) {
                if (selected.indexOf(key) != -1) {
                                        selectedHtml = ' selected="selected"';
                } else {
                    selectedHtml = '';
                   }
            } else {
                if (key == selected) {
                    selectedHtml = ' selected="selected"';
                } else {
                     selectedHtml = '';
                        }
                    }
             html += '<option value="' + key +'"' + selectedHtml + '>' + value + '</option>';
        }
         return html;
    }
</script>
<h3><?php echo $this->method_title ?></h3>
<table class = "form-table">
            <?php $this->generate_settings_html();?>
</table>
<style>
             .debug-col {
                            display: none;
                        }
                        table.shippingrows tr th {
                            padding-left: 10px;
                        }
                        .zone td {
                            vertical-align: top;
                        }
                        .zone textarea {
                            width: 100%;
                        }

                    </style>