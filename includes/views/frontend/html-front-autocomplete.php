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

<script type="text/javascript"> jQuery(function() {


    jQuery("#billing_city").autocomplete({
        source: function(requete, reponse){ // les deux arguments représentent les données nécessaires au plugin
    $.ajax({
            url : "<?php echo MD_SPEEDBOX_ROOT_URL; ?>/includes/ajax/villes.php", // on appelle le script JSON
            dataType : "json", // on spécifie bien que le type de données est en JSON
             data : {
                sous_city: $("#billing_city").val() // on donne la chaîne de caractère tapée dans le champ de recherche
            },
            success : function(donnee){
                reponse($.map(donnee.cities, function(objet){
                    return objet.city /*+ ', ' + objet.region*/;
                }));
            }
        });
    },
          select: function(event, ui) {
        jQuery("#billing_state").change();
   }
    });



$("#shipping_city").autocomplete({
    source : function(requete, reponse){ // les deux arguments représentent les données nécessaires au plugin
    $.ajax({
            url : "<?php echo MD_SPEEDBOX_ROOT_URL; ?>/includes/ajax/villes.php", // on appelle le script JSON
            dataType : "json", // on spécifie bien que le type de données est en JSON
             data : {
                sous_city: $("#shipping_city").val() // on donne la chaîne de caractère tapée dans le champ de recherche
            },
            success : function(donnee){
                reponse($.map(donnee.cities, function(objet){
                    return objet.city /*+ ', ' + objet.region*/;
                }));
            }
        });
    },
       select: function(event, ui) {
        jQuery("#shipping_state").change();
   }
});





});

</script>