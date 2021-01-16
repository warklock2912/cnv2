<?php
require_once '../app/Mage.php';
//
header('Content-Type: application/json');
Mage::app();
define('STATUS_SESSION_EXPIRED', 406);
define('STATUS_TOKEN_EXPIRED', 405);
Mage::app()->setCurrentStore(getStoreId());

$iosData = array(
    'url' => Mage::getStoreConfig('appupdate_options/Ios/url'),
    'lastest' => Mage::getStoreConfig('appupdate_options/Ios/lastest'),
    'minimum' => Mage::getStoreConfig('appupdate_options/Ios/minimum'),
    'enable' => Mage::getStoreConfig('appupdate_options/Ios/status') == 1 ? true : false
);
$androidData = array(
    'url' => Mage::getStoreConfig('appupdate_options/Android/url'),
    'lastest' => Mage::getStoreConfig('appupdate_options/Android/lastest'),
    'minimum' => Mage::getStoreConfig('appupdate_options/Android/minimum'),
    'enable' => Mage::getStoreConfig('appupdate_options/Android/status') == 1 ? true : false
);

$dataArr = array(
    'ios' => $iosData,
    'android' => $androidData
);
dataResponse(200, 'valid', $dataArr);


function dataResponse($statusCode, $message, $data = '', $label = 'data', $total = null)
{
  http_response_code($statusCode);

  $dataResponse = array(
    'status_code' => $statusCode,
    'message' => $message,
    $label => $data
  );
  if ($total != null) {
    $dataResponse['total'] = $total;
  }
  echo json_encode($dataResponse);
}

function getStoreId()
{

  if (!function_exists('getallheaders')) {
    function getallheaders()
    {
      $headers = array();
      foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
          $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
      }
      return $headers;
    }
  }

  $headers = getallheaders();

  $store_code = $headers['Store'];
  switch ($store_code) {
    case 'en':
    $store_id = Mage::getModel('core/store')->load('en', 'code')->getId();
    break;
    case 'th':
    $store_id = Mage::getModel('core/store')->load('th', 'code')->getId();
    break;
    case 'app_en':
    $store_id = Mage::getModel('core/store')->load('app_en', 'code')->getId();
    break;
    case 'app_th':
    $store_id = Mage::getModel('core/store')->load('app_th', 'code')->getId();
    break;
    default:
    $store_id = Mage::app()->getStore()->getId();
    break;
  }
  return $store_id;
}
