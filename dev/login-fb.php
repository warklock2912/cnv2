<?php
require_once '../app/Mage.php';
require_once 'functions.php';
require_once '../lib/nusoap/nusoap.php';
Mage::getSingleton("core/session", array("name" => "frontend"));
Mage::getSingleton('customer/session')->clear();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$_helper = Mage::helper('netgo_customerpic');
$root_path = $_helper->getBaseDir();
$email = $data['email'];
$token = isset($data['token']) ? $data['token'] : null;
$profilePhoto = isset($data['avatar_url']) ? substr($data['avatar_url'], 0, -1) : null;
$profilePhotoName = md5($email . date('m/d/Y h:i:s')) . ".png";
$profilePhotoPath = $root_path . "/media/profile/";
if (!is_dir($profilePhotoPath)) {
    mkdir($profilePhotoPath, 0777, TRUE);
}
$firstName = isset($data['first_name']) ? $data['first_name'] : null;
$lastName = isset($data['last_name']) ? $data['last_name'] : null;
$id = isset($data['id']) ? $data['id'] : null;
$type = isset($data['type']) ? $data['type'] : null;
$title = 'amajaxlogin_' . $type . '_id';
$store = getStoreId();
$deviceId = isset($data['deviceId']) ? $data['deviceId'] : null;

$customerBySocialId = Mage::helper('amajaxlogin')->getCustomerBySocialId($title, $id);
// Existing connected user - login
if ($customerBySocialId) {
    Mage::helper('amajaxlogin')->loginByCustomer($customerBySocialId);
    $customer = Mage::getSingleton('customer/session')->getCustomer();
    $customerId = $customer->getId();
    $customer = Mage::getModel("customer/customer")->load($customerId);
    $userInfo = loginAction($customer, $profilePhoto, $profilePhotoPath, $profilePhotoName, $deviceId);

    Mage::dispatchEvent('customer_login',
        array('customer' => $customer)
    );

    // add mobile token;
    addToken($customer);
    http_response_code(200);
    echo json_encode(array('status_code' => 200, 'message' => 'login successfully', 'accountInfomation' => $userInfo));
} else {
    if (isset($data['email'])) {
        $customerByEmail = Mage::helper('amajaxlogin')
            ->getCustomerByEmail($email);
        if (empty($email) || empty($firstName) || empty($lastName)) {
            http_response_code(400);
            echo json_encode(array('status_code' => 400, 'message' => 'Not enough infomation'));
            return;
        }

        if ($customerByEmail) {
            Mage::helper('amajaxlogin')->connectByEmail(
                $customerByEmail,
                $id,
                $token,
                $type
            );
            $customer = Mage::getModel("customer/customer");
            $customer->setStore($store);
            $customer->loadByEmail($email);

            $userInfo = loginAction($customer, $profilePhoto, $profilePhotoPath, $profilePhotoName, $deviceId);

            Mage::dispatchEvent('customer_login',
                array('customer' => $customer)
            );

            // add mobile token;
            addToken($customer);
            http_response_code(200);
            echo json_encode(array('status_code' => 200, 'message' => 'login successfully', 'accountInfomation' => $userInfo));
            return;
        }


        Mage::helper('amajaxlogin')->connectByCreatingAccount(
            $email,
            $firstName,
            $lastName,
            $id,
            $token,
            $type
        );

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $userInfo = loginAction($customer, $profilePhoto, $profilePhotoPath, $profilePhotoName, $deviceId);

        Mage::dispatchEvent('customer_login',
            array('customer' => $customer)
        );

        // add mobile token;
        addToken($customer);
        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'login successfully', 'accountInfomation' => $userInfo));
    } else {
        http_response_code(200);
        echo json_encode(array('status_code' => 408, 'message' => 'Please Enter Email'));
    }
}
