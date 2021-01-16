<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 08/08/2018
 * Time: 09:31
 */

require_once '../app/Mage.php';
require_once 'functions.php';
require_once '../lib/nusoap/nusoap.php';
Mage::getSingleton("core/session", array("name" => "frontend"));

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
Mage::getSingleton('customer/session')->clear();
if (isset($data['email'])) {
    $email = $data['email'];
    $password = $data['password'];
    $websiteId = Mage::app()->getWebsite()->getId();
    $store = Mage::app()->getStore();
    $deviceId = $data['deviceId'] ? $data['deviceId'] : null;
    $customer = Mage::getModel("customer/customer");
    $customer->website_id = $websiteId;
    $customer->setStore($store);

    try {
        $customer->loadByEmail($email);

        // add message
        if($customer->getId()){
            // check password
            try{
                $customer->authenticate($email, $password);
            }catch (Exception $e){
                $message = "Your password is incorrect";
                http_response_code(200);
                echo json_encode(array('status_code' => 402, 'message' => $message));
                return;
            }
        }else{
            $message = "Your email is incorrect";
            http_response_code(200);
            echo json_encode(array('status_code' => 401, 'message' => $message));
            return;
        }

        $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
        $session->login($email, $password);

        Mage::dispatchEvent('customer_login',
            array( 'customer' => $customer)
        );

        $userData = getCustomerData($customer);
        $quote = Mage::getModel('sales/quote')->setSharedStoreIds(getStoreId())->loadByCustomer($customer);
        if (!$quote->getId()) {
            $quoteObj = Mage::getModel('sales/quote');
            $quoteObj->assignCustomer($customer);
            $quoteObj->setStoreId(getStoreId());
            $quoteObj->collectTotals();
            $quoteObj->setIsActive(true);
            $quoteObj->save();
        }

        //add notification device
        if ($deviceId != null) {
            $notificationDevice = Mage::getModel('pushnotification/device')
                ->getCollection()
                ->addFieldToFilter('device_id', $deviceId)->getFirstItem();
            $notificationDevice->setData('user_id', $customer->getId())->save();
        }

        // add mobile token;
        addToken($customer);

        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'valid', 'accountInfomation' => $userData));
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => 'invalid', 'accountInfomation' => $e->getMessage()));
    }
} else {
    http_response_code(404);
    echo json_encode(array('status_code' => 404, 'message' => 'invalidPost'));
}
