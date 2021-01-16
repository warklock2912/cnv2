<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
Mage::getSingleton('core/date')->gmtDate();
$timezone =  Mage::getStoreConfig('general/locale/timezone');
$fromDate  = date('Y-m-d H:i:s', strtotime('-30day'));
$endDate  = date('Y-m-d H:i:s', strtotime(Varien_Date::now()));
if (isset($_REQUEST['startDate'])){
    $fromDate = $_REQUEST['startDate'];
    $fromDate = date("Y-m-d H:i:s",strtotime($fromDate));
}
if (isset($_REQUEST['endDate'])){
    $endDate = $_REQUEST['endDate'];
    $endDate = date("Y-m-d H:i:s",strtotime($endDate.' +1day'));
}
$notificationCollection = Mage::getModel('pushnotification/notification')->getCollection();
$notificationCollection->addFieldToFilter('created_at', array(
    'from' => $fromDate,
    'to' => $endDate,
    'date' => true,
));
$notificationCollection->addFieldToFilter('type', array(
    'neq' => 'crop',
));
$notificationCollection->setOrder('created_at', 'DESC')
    ->setPageSize(20);
$dataBroadCastArr = array();
foreach ($notificationCollection as $notification):;
    $type = '2';// type broad cast
    $data['type'] = $type;
    $data['notification_id'] = $notification->getId();
    $data['title'] = $notification->getTitle();
    $data['content'] = $notification->getMessage();
    $data['url'] = $notification->getUrl();
    $data['created_at'] = strtotime($notification->getCreatedAt()).'';
    $dataBroadCastArr[] = $data;
endforeach;

///// Crop And Drop Data
$cropAndDropCollection =  Mage::getModel('campaignmanage/cropanddrop')->getCollection();
$cropAndDropCollection->addFieldToFilter('created_at', array(
    'from' => $fromDate,
    'to' => $endDate,
    'date' => true,
));

$dataCropAndDropArr = array();

if(count($cropAndDropCollection)){
    foreach ($cropAndDropCollection as $item):;
       if($item->getSize() != null){
//           $type = '7';// type broad cast

           $size_name = '';

           $_product = Mage::getModel('catalog/product')->load($item->getProductId());

           if($_product->getTypeId() != 'configurable'){
               continue;
           }

           $allProducts = $_product->getTypeInstance(true)->getUsedProducts(null, $_product);

           foreach ($allProducts as $subproduct) {
               if($subproduct->getData('size_products') == $item->getSize()){
                   $size_name = $subproduct->getAttributeText('size_products');
               }
           }

           $data['type'] = '7';
           $data['id'] = $item->getId();
           $data['title'] = $item->getTitle();
           $data['content'] = $item->getContent();
           $data['product_id'] = $item->getProductId();
           $data['selected_size'] = $item->getSize();
           $data['size_name'] = $size_name;
           $data['content_agrs'] = array($_product->getName());
           $data['notification_id'] = $item->getNotificationId();
           $data['created_at'] = strtotime($item->getCreatedAt()).'';
           $dataCropAndDropArr[] = $data;
       }
    endforeach;
}
http_response_code(200);

$dataResponse = array(
    'status_code' => 200,
    'message' => 'Valid',
    'broadCastData' => $dataBroadCastArr,
    'cropAndDropData' => $dataCropAndDropArr
);

echo json_encode($dataResponse);
