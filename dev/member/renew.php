<?php

require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
require_once '../../lib/nusoap/nusoap.php';

$NuSOAPClientPath = Mage::getStoreConfig('mgfapisetting/mgfapi/apiurl');
// require you file
if (!class_exists('Tigren_Member_VipController')) //in case the class already exists
{
    require_once('../../app/code/local/Tigren/Member/controllers/VipController.php');
}

// instantiate your controller, using the `Mage:app()` object to grab the required request and response
$controller = new Tigren_Member_VipController(
    Mage::app()->getRequest(),
    Mage::app()->getResponse()
);

$memberHelper = Mage::helper('member');
$apiclient = $controller->include_nusoap();
$NuSOAPClient = $apiclient['NuSOAPClient'];

$pointRenew = Mage::getStoreConfig('rewardpoints/display/point_use_to_renew');
$session = Mage::getSingleton('customer/session');
$customer = Mage::getModel('customer/customer')->load($session->getCustomer()->getId());
$renewDays =  Mage::getStoreConfig('rewardpoints/display/renew_date_expired_vip');
$renew = $memberHelper->renewVip($customer, $renewDays, $apiclient, $NuSOAPClient);
if($renew[0]['Success'] == '1') {
    $memberHelper->api_removepoint($customer->getVipMemberId(), $pointRenew, $apiclient, $NuSOAPClient);
    $pos_point = $memberHelper->apigetpoint($customer->getVipMemberId(), $apiclient, $NuSOAPClient);
    $web_point = $memberHelper->getWebPoint($customer->getId());
    if($pos_point) {
        $diff_point = ($pos_point ?: 0) - $web_point;
        if($diff_point != 0) {
            $title = $controller->__("Use Point for Renewal");
            $extra_content = $controller->__("burn_point");
            $point_balance   = $pos_point;

            $query           = "INSERT INTO rewardpoints_customer (customer_id, point_balance) VALUES (:customer_id, :point_balance) ON DUPLICATE KEY UPDATE point_balance = values(point_balance);";
            $resource        = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $binds           = array(
                'customer_id' => $customer->getId(),
                'point_balance' => $point_balance
            );
            $writeConnection->query($query, $binds);
            $memberHelper->log_reward($customer->getId(), $memberHelper->getDataWebPoint($customer->getId()), $diff_point, $title, $extra_content);
            $customer->setVipMemberExpireDate($renew['VipExpireDateAfterRenew'])
                ->save();
            Mage::getSingleton('customer/session')->addSuccess('Renewed Vip Member Expire Date');
            Mage::helper('member')->logAPI('|Renew Vip Member|');
            Mage::helper('member')->logAPI('|[TRANS]|-------- Renew Status --------|');
            Mage::helper('member')->logAPI('success');
        }
    }
}
dataResponse(200, 'success', ['status' => true]);