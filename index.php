<?php
require 'oauth.php';
require 'codenvy.php';

session_start();

$callback = 'jhb-php.codenvy.purplemagma.com';
initCodEnvy($callback);

$oauthObject = new OAuthSimple();

$output = 'Authorizing...';

$signatures = array( 'consumer_key'     => 'qyprd07iM1J7HVZPrsuB6KjKaktPYf',
                     'shared_secret'    => 'PEfu7CX1PK1OY8510ie2jqRFZcgJ89HLaGIPf9tq');

if (!isset($_GET['oauth_verifier'])) {
    $result = $oauthObject->sign(array(
        'path'      =>'https://oauth.intuit.com/oauth/v1/get_request_token',
        'parameters'=> array(
            'oauth_callback'=> 'https://' . $callback),
        'signatures'=> $signatures));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
    $r = curl_exec($ch);
    curl_close($ch);

    parse_str($r, $returned_items);
    $request_token = $returned_items['oauth_token'];
    $request_token_secret = $returned_items['oauth_token_secret'];
    
    $_SESSION['oauth_token_secret'] = $request_token_secret;
    
    $result = $oauthObject->sign(array(
        'path'      =>'https://appcenter.intuit.com/Connect/Begin',
        'parameters'=> array(
            'oauth_token' => $request_token),
        'signatures'=> $signatures));

    header("Location:$result[signed_url]");
    exit;
}
else {
    $signatures['oauth_secret'] = $_SESSION['oauth_token_secret'];
    $signatures['oauth_token'] = $_GET['oauth_token'];
    $realmId = $_GET['realmId'];
    
    $result = $oauthObject->sign(array(
        'path'      => 'https://oauth.intuit.com/oauth/v1/get_access_token',
        'parameters'=> array(
            'oauth_verifier' => $_GET['oauth_verifier'],
            'oauth_token'    => $_GET['oauth_token']),
        'signatures'=> $signatures));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $r = curl_exec($ch);

    parse_str($r, $returned_items);        
    $access_token = $returned_items['oauth_token'];
    $access_token_secret = $returned_items['oauth_token_secret'];
    
    $signatures['oauth_token'] = $access_token;
    $signatures['oauth_secret'] = $access_token_secret;

    $oauthObject->reset();
    $url =  'https://qb.sbfinance.intuit.com/v3/company/' . $realmId . '/companyinfo/' . $realmId;
    $result = $oauthObject->sign(array(
        'path'      => $url,
        'action'    => 'GET',
        'signatures'=> $signatures));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Authorization: ' . $result['header'], 'Content-Type: text/plain'));
    curl_setopt($ch, CURLOPT_URL, $url);
    $companyInfo = curl_exec($ch);
    
    $oauthObject->reset();
    $url =  'https://qb.sbfinance.intuit.com/v3/company/' . $realmId . '/query';
    $params = 'query=' . OAuthSimple::_oauthEscape('select * from Item');

    $result = $oauthObject->sign(array(
        'path'      => $url,
        'action'    => 'GET',
        'parameters' => $params,
        'signatures'=> $signatures));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Authorization: ' . $result['header'], 'Content-Type: text/plain'));
    curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
    $itemList = curl_exec($ch);

    $output = "<p>Access Token: $access_token<BR>
                  Token Secret: $access_token_secret</p>
                  <p>CompanyInfo: ".$companyInfo . "</p>
                  <p>Item List: "  . $itemList . "</p>";
    curl_close($ch);
}        
?>
<HTML>
<BODY>
<?php echo $output;?>
</BODY>
</HTML>
