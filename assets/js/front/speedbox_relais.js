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
    if ($("#ship-to-different-address-checkbox").attr('checked') && $('input[name=sb_relay_id]:checked', document).val()) {
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
    if (!$("#ship-to-different-address-checkbox").is(':checked')) $("#ship-to-different-address-checkbox").click();
    value = $(item).attr('value');
    relais_data = $.parseJSON(value);
    $("#shipping_first_name").val($("#billing_first_name").val());
    $("#shipping_last_name").val($("#billing_last_name").val());
    /*  $("#shipping_company").val(relais_data.shop_name + ' (' + relais_data.relay_id + ')');*/
    $("#shipping_address_1").val(relais_data.address1);
    $("#shipping_address_2").val(relais_data.address2);
    $("#shipping_city").val(relais_data.city);
    $("#shipping_postcode").val(relais_data.postcode);
    $("#billing_state").val(relais_data.state);
    $("#shipping_state").val(relais_data.state);
    $(".shipping_address").show();
    valideRelais();
    $curcheck = getCookie('speedbox_selected_relais');
    var expire = new Date();
    expire.setDate(expire.getDate() + 1);
    document.cookie = 'speedbox_selected_relais' + '=' + $(item).attr('id') + ';expires=' + expire.toGMTString();
    document.cookie = 'speedbox_selected_relais_name' + '=' + relais_data.shop_name + ';expires=' + expire.toGMTString();
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

function reset_point_relais_vlues() {
    var regex = new RegExp(/\(P\d{5}\)/);
    var company = document.getElementById('shipping_company');
    var PickupID = company.value.substr(-7, 6);
    if (regex.test(company.value) && $("#ship-to-different-address-checkbox").attr('checked')) {
        $("#shipping_first_name").val($("#billing_first_name").val());
        $("#shipping_last_name").val($("#billing_last_name").val());
        $("#shipping_company").val($("#billing_company").val());
        $("#shipping_address_1").val($("#billing_address_1").val());
        $("#shipping_address_2").val($("#billing_address_2").val());
        $("#shipping_postcode").val($("#billing_postcode").val());
        $("#shipping_city").val($("#billing_city").val());
        $(".shipping_address").hide();
        document.getElementById('ship-to-different-address-checkbox').checked = false;
        $('#shipping_postcode').trigger({
            type: 'keydown',
            which: 13,
            keyCode: 13
        });
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