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
$pointsUse = Mage::getStoreConfig('rewardpoints/display/point_use_to_upgrade');


$session = Mage::getSingleton('customer/session');
$customer = Mage::getModel('customer/customer')->load($session->getCustomer()->getId());
$member_code = $controller->getnewcustomercode();

$response['upgrade_success'] = 0;
if (!$customer->getData('vip_member_id') && !$customer->getData('vip_member_expire_date')) {
    $customerToPOS = $memberHelper->addCustomerToPos($customer, $member_code, $apiclient, $NuSOAPClient);
    $customer_id = $customerToPOS->getId();
    $vip_member_code = $customerToPOS->getVipMemberId();
    $web_point = $memberHelper->getWebPoint($customer_id);
    /*
    for first time update web point to POS
     */
    $sync_pos_point = @$customer->getSyncPosPoint() ?: false;
    if ($web_point > 0) {
        $memberHelper->api_addpoint($vip_member_code, $web_point, $apiclient, $NuSOAPClient);
        $customer->setSyncPosPoint(true)->save();
        sleep(5);
    }
}
$upgradeVip = $memberHelper->upgradeToVip($customer, $apiclient, $NuSOAPClient);
if ($upgradeVip[0]['Success'] == '1') {
    Mage::helper('member')->api_removepoint($customer->getVipMemberId(), $pointUse, $apiclient, $NuSOAPClient);
    $pos_point = $memberHelper->apigetpoint($customer->getVipMemberId(), $apiclient, $NuSOAPClient);
    $web_point = $memberHelper->getWebPoint($customer->getId());
    if ($pos_point && ($pos_point > $pointUse)) {
        $diff_point = ($pos_point ?: 0) - $web_point;
        if ($diff_point != 0) {
            $title = $controller->__("Use Point for upgrade VIP");
            $extra_content = $controller->__("burn_point");
            $point_balance = $pos_point;
            $query = "INSERT INTO rewardpoints_customer (customer_id, point_balance) VALUES (:customer_id, :point_balance) ON DUPLICATE KEY UPDATE point_balance = values(point_balance);";
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $binds = array(
                'customer_id' => $customer->getId(),
                'point_balance' => $point_balance
            );
            $writeConnection->query($query, $binds);
            $memberHelper->log_reward($customer->getId(), $memberHelper->getDataWebPoint($customer->getId()), $diff_point, $title, $extra_content);
            if ($customer->getGroupId() != 4) {
                $customer->setGroupId(4)
                    ->setVipMemberExpireDate($upgradeVip['VipExpireDateAfterUpgrading'])
                    ->save();
                $response['upgrade_success'] = 1;
                Mage::getSingleton('customer/session')->addSuccess('Upgraded to Vip Member');
            }
        }
    } else {
        $response['upgrade_success'] = 2;
    }
}

Mage::helper('member')->logAPI('|Upgrade Vip Member|');
Mage::helper('member')->logAPI('|[TRANS]|-------- Upgrade Status --------|');
Mage::helper('member')->logAPI($response['upgrade_success'] == 1 ? 'success' : 'failed');
Mage::helper('member')->logAPI('POS point: ' . $memberHelper->apigetpoint($customer->getVipMemberId(), $apiclient, $NuSOAPClient));
Mage::helper('member')->logAPI('Web point: ' . $memberHelper->getWebPoint($customer->getId()));
if ($response['upgrade_success'] == 2) {
    Mage::helper('member')->logAPI('Reason: Not enough points');
}

if ($response['upgrade_success'] == 1) {
    dataResponse(200, 'success', ['status' => true]);
    die;
}
dataResponse(200, 'failed', ['status' => false]);