<?php

define("SESSION",               "GROWFASTSESSION");
define("LOKASI_PROFILE",        "assets/img/profile/");

define("MENUNGGU",              1);
define("PERLU_VERIF",           2);
define("SUDAH_VERIF",           3);
define("BATAL",                 4);
define("TOLAK",                 5);

define("DIJEMPUT",              2);
define("SELESAI",               3);

if (!function_exists("getApiKey")) {
    function getApiKey()
    {
        $CI = &get_instance();
        $api_key_variable = $CI->config->item('rest_key_name');
        $key_name   = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));
        $apiKey     = $CI->input->server($key_name);
        return $apiKey;
    }
}
