<?php

require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();


$block = Mage::app()->getLayout()->getBlockSingleton('Mage_Customer_Block_Form_Edit');
$pointsUse = Mage::getStoreConfig('rewardpoints/display/point_use_to_upgrade');
$pointsRenewUse = Mage::getStoreConfig('rewardpoints/display/point_use_to_renew');
$expiredDate = $block->getCustomer()->getVipMemberExpireDate();
$expiredDate = date("d / m / Y", strtotime($expiredDate));
$filter = Mage::getModel('core/email_template_filter');
$variables = array('points' => $pointsUse, 'renew_points' => $pointsRenewUse);
if ($expiredDate) {
    $variables['expired_date'] = $expiredDate;
}
$filter->setVariables($variables);
$blockUpgrade = $block->getLayout()->createBlock('cms/block')->setBlockId('carnival-upgrade-customer-info');
$blockRenew = $block->getLayout()->createBlock('cms/block')->setBlockId('carnival-upgrade-customer-renew');
$blockPopup = $block->getLayout()->createBlock('cms/block')->setBlockId('carnival-upgrade-customer-info-popup');
$blockNotEnough = $block->getLayout()->createBlock('cms/block')->setBlockId('carnival-upgrade-customer-info-popup-not-enough');
$htmlUpgrade = $filter->filter($blockUpgrade->toHtml());
$htmlRenew = $filter->filter($blockRenew->toHtml());
$expireDay = Mage::getStoreConfig('rewardpoints/display/expired_vip_after');
$date = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
$dateExpired = new DateTime($block->getCustomer()->getVipMemberExpireDate());
$expiredAfter = intval((strtotime($dateExpired->format('Y-m-d')) - strtotime($date->format('Y-m-d')))/86400);


$dataArr = [];
$dataArr['is_vip'] = true;
if(!$expiredAfter || $expiredAfter <= 0){
    $dataArr['is_vip'] = false;
}


if ($block->getCustomer()->getGroupId() == 4 && (0 < $expiredAfter && $expiredAfter <= intval($expireDay))) {
    $dataArr['expired_date'] = $expiredDate;
    $dataArr['points'] = $pointsRenewUse;
}

if (!$expiredAfter || $expiredAfter <= 0) {
    $dataArr['points'] = $pointsUse;
}


dataResponse(200, 'valid', $dataArr);
