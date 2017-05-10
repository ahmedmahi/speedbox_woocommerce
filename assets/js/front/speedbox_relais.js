var $ = jQuery.noConflict();

function init_google_maps(baseurl, mapid, lat, longti) {
    var latlng = new google.maps.LatLng(lat, longti);
    var myOptions = {
        zoom: 16,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
    };
    var map = new google.maps.Map(document.getElementById(mapid), myOptions);
    var marker = new google.maps.Marker({
        icon: baseurl + "/assets/img/front/relais/logo-max-png.png",
        position: latlng,
        animation: google.maps.Animation.DROP,
        map: map
    });
}

function popup_speedbox_view(baseurl, id, mapid, lat, longti) {
    $("#sb_relais_filter").fadeIn(150, function() {
        $("#" + id).fadeIn(150);
    });
    window.setTimeout(function() {
        init_google_maps(baseurl, mapid, lat, longti)
    }, 200);
}

function valideRelais() {
    if ($('input[name=sb_relay_id]:checked', document).val()) {
        $("#place_order").removeAttr('disabled');
        $('#place_order').removeClass('button').addClass('button alt');
        return true;
    } else {
        $("#place_order").attr('disabled', 'disabled');
        $('#place_order').removeClass('button alt').addClass('button');
        return false;
    }
    return false;
}

function write_point_relais_vlues(item, $position) {
    value = $(item).attr('value');
    relais_data = $.parseJSON(value);
    if ($("#ship-to-different-address-checkbox").is(':checked')) {
        $("#shipping_state").val(relais_data.state);
    } else {
        $("#billing_state").val(relais_data.state);
    }
    valideRelais();
    $curcheck = getCookie('speedbox_selected_relais');
    var expire = new Date();
    expire.setDate(expire.getDate() + 1);
    document.cookie = 'speedbox_selected_relais' + '=' + $(item).attr('id') + ';expires=' + expire.toGMTString();
    document.cookie = 'speedbox_selected_relais_name' + '=' + relais_data.shop_name + ';expires=' + expire.toGMTString();
    document.cookie = 'speedbox_selected_relais_address' + '=' + relais_data.address1 + ';expires=' + expire.toGMTString();
    document.cookie = 'speedbox_selected_relais_city' + '=' + relais_data.city + ';expires=' + expire.toGMTString();
    if (!$curcheck || ( /*$position != 'first' &&*/ $curcheck != $(item).attr('id'))) jQuery("#shipping_state").change();
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function on_change_city_billing() {
    if ($('#shipping_method_0_speedbox_relais').is(':checked') && !$("#ship-to-different-address-checkbox").attr('checked')) {
        if (!$("#billing_address_1").val()) $("#billing_address_1").val(".");
        if (!$("#billing_postcode").val()) $("#billing_postcode").val("0");
        if (!$("#billing_state").val()) $("#billing_state").val(".");
    }
}

function on_change_city_shipping() {
    if ($('#shipping_method_0_speedbox_relais').is(':checked')) {
        if (!$("#shipping_address_1").val()) $("#shipping_address_1").val(".");
        if (!$("#shipping_postcode").val()) $("#shipping_postcode").val("0");
        if (!$("#shipping_state").val()) $("#shipping_state").val(".");
    }
}
$(document).ajaxComplete(function() {
    if ($('.shipping_method:checked').val() == 'speedbox_relais' || $('.shipping_method option:selected').val() == 'speedbox_relais' || $('.shipping_method:hidden').val() == 'speedbox_relais') {
        valideRelais();
    }
    if ($('#shipping_method_0_speedbox_relais').is(':checked')) valideRelais();
    $('#shipping_method_0_speedbox_relais').change(function() {
        valideRelais();
    });
});
$(document).ready(function() {
    $("body").keyup(function() {
        if ($('.shipping_method:checked').val() == 'speedbox_relais' || $('.shipping_method option:selected').val() == 'speedbox_relais' || $('.shipping_method:hidden').val() == 'speedbox_relais') valideRelais();
        if ($('#shipping_method_0_speedbox_relais').is(':checked')) valideRelais();
    });
});