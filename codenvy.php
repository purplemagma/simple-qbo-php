<?php
  function initCodEnvy($sslUrl) {
    $vcap = getenv('VCAP_APPLICATION');
    if (!empty($vcap)) {
      $url = json_decode($vcap)->{'uris'}[0];
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $sslUrl . '/update_url/' . $url);
      $r = curl_exec($ch);
      curl_close($ch);
    }
}
?>