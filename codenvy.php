<?php
  function initCodEnvy($sslUrl) {
    $url = json_decode(getenv('VCAP_APPLICATION'))->{'uris'}[0];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $sslUrl . '/update_url/' . $url);
    $r = curl_exec($ch);
    curl_close($ch);
}