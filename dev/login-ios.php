<?php

require_once '../app/Mage.php';
require_once 'functions.php';
require_once '../lib/nusoap/nusoap.php';

Mage::getSingleton("core/session", array("name" => "frontend"));
Mage::getSingleton('customer/session')->clear();
$session = Mage::getSingleton('customer/session');
$customer = false;

$dataReturn = array(
    'status_code' => 400,
    'message' => 'valid',
    'accountInfomation' => array()
);
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$email = $data['email'];
$token = isset($data['token']) ? $data['token'] : '';
$firstname = isset($data['first_name']) ? $data['first_name'] : '';
$lastname = isset($data['last_name']) ? $data['last_name'] : '';
if (!isset($token) || $token == '') {
    $dataReturn['status_code'] = 404;
    $dataReturn['message'] = 'invalidPost';
    http_response_code($dataReturn['status_code']);
    echo json_encode($dataReturn);
    return;
}

$customer = Mage::getModel('customer/customer')->getCollection()
    ->addAttributeToSelect('*')
    ->addAttributeToFilter('mobileapp_ios_token', $token)
    ->addAttributeToSort('position', 'ASC')
    ->getFirstItem();

if (!$customer || !$customer->getId()) {
    if (!isset($email) || $email == '' || !isset($firstname) || $firstname == '' || !isset($lastname) || $lastname == '') {
        $dataReturn['status_code'] = 404;
        $dataReturn['message'] = 'invalidPost';
        http_response_code($dataReturn['status_code']);
        echo json_encode($dataReturn);
        return;
    }
    $pwd_length = 7;
    $customer = Mage::getModel("customer/customer");
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

    $customer->loadByEmail($email);

    if (!$customer->getId()) {
        $customer->setEmail($email);
        $customer->setFirstname($firstname);
        $customer->setLastname($lastname);
        $customer->setPassword($customer->generatePassword($pwd_length));
        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->setMobileappIosToken($token);
            $customer->save();
            $customer->sendNewAccountEmail();
        } catch (Exception $e) {
            Mage::log($e->__toString());
            $dataReturn['message'] = 'invalid';
            $dataReturn['accountInfomation'] = 'Your are get something error.';
            http_response_code($dataReturn['status_code']);
            echo json_encode($dataReturn);
            return;
        }
    } else {
        try {
            $customer->setMobileappIosToken($token);
            $customer->save();
        } catch (Exception $e) {
            Mage::log($e->__toString());
            $dataReturn['message'] = 'invalid';
            $dataReturn['accountInfomation'] = 'Your are get something error.';
            http_response_code($dataReturn['status_code']);
            echo json_encode($dataReturn);
            return;
        }
    }

    $dataReturn['status_code'] = 200;
    // } elseif (isset($email) && $email != '' && $customer->getEmail() != $email) {
    //     $dataReturn['message'] = 'invalid';
    //     $dataReturn['accountInfomation'] = 'Your are get something error.[Email not compared]';
} elseif ($customer->getMobileappIosToken() == $token) {
    $dataReturn['status_code'] = 200;
} else if ($customer->getId()) {
    // $dataReturn['message'] = 'invalid';
    // $dataReturn['accountInfomation'] = 'Token not matched.';    
    $customer = Mage::getModel("customer/customer")->load($customer->getId());
    try {
        $customer->setMobileappIosToken($token);
        $customer->save();
    } catch (Exception $e) {
        Mage::log($e->__toString());
        $dataReturn['message'] = 'invalid';
        $dataReturn['accountInfomation'] = 'Your are get something error.';
        http_response_code($dataReturn['status_code']);
        echo json_encode($dataReturn);
        return;
    }
    $dataReturn['status_code'] = 200;
}

$session->logout()->renewSession();
if ($dataReturn['status_code'] == 200) {
    try {
        $session->loginById($customer->getId());
        $dataReturn['message'] = 'valid';
        $dataReturn['accountInfomation'] = getCustomerData($customer);
        addToken($customer);
    } catch (Mage_Core_Exception $e) {
        Mage::log($e->__toString());
        $dataReturn['status_code'] = 400;
        $dataReturn['message'] = 'invalid';
        $dataReturn['accountInfomation'] = 'Login failed.';
    } catch (Exception $e) {
        Mage::log($e->__toString());
        $dataReturn['status_code'] = 400;
        $dataReturn['message'] = 'invalid';
        $dataReturn['accountInfomation'] = 'Login failed.';
    }
}
http_response_code($dataReturn['status_code']);
echo json_encode($dataReturn);
