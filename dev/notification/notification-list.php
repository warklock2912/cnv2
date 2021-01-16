<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();

if ($_REQUEST['id']) {
    $customerId = $_REQUEST['id'];
    $notificationCollection = Mage::getModel('pushnotification/notificationlist')->getCollection()
        ->addFieldToFilter('customer_id', $customerId)
        ->setOrder('created_at', 'DESC');
    $dataArr = array();
    foreach ($notificationCollection as $notification):;
        $data['notification_id'] = $notification->getId();
        $data['type'] = $notification->getType();
        $data['content_id'] = $notification->getContentId();
        $data['title'] = $notification->getTitle();
        $data['short_content'] = $notification->getShortContent();
        $data['content_agrs'] = explode(',', $notification->getContentArgs());
        $data['is_read'] = $notification->getNotificationStatus() == 1 ? true : false;
        $data['created_at'] = strtotime($notification->getCreatedAt()) . '';
        $data['product_id'] = '';
        $data['selected_size'] = '';
        $data['size_name'] = '';
        $data['is_card_payment'] = $notification->getIsCardPayment() ? true : false;
        if ($notification->getProductId() != null) {
            $data['product_id'] = $notification->getProductId();
        }
        if ($notification->getSelectedSize() != null) {
            $data['selected_size'] = $notification->getSelectedSize();
            $_product = Mage::getModel('catalog/product')->load($notification->getProductId());
            $size_name = $_product->getAttributeText('size_products');
            $data['size_name'] = $size_name;
        }
        $dataArr[] = $data;
    endforeach;
    dataResponse(200, 'valid', $dataArr);
} else {
    dataResponse(400, 'Invalid');
}
