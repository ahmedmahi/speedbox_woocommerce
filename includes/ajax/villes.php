<?php

if (isset($_GET['sous_city'])) {
    echo speedbox_get_city(trim($_GET['sous_city']));
}
function speedbox_get_city($sous_city, $city_data)
{
    $city_data        = dirname(dirname(dirname(__FILE__))) . '/includes/data/city.json';
    $cities_json_data = file_get_contents($city_data);
    $all_cities       = json_decode($cities_json_data, true);
    $cities           = $all_cities['cities'];
    foreach ($all_cities['cities'] as $key => $val) {
        if (!preg_match('/' . $sous_city . '/i', $val['city'])) {
            unset($all_cities['cities'][$key]);
        }
    }
    return json_encode($all_cities);
}
