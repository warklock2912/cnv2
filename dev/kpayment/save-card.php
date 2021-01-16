<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
try {
    /** @var Tigren_Kpayment_Helper_Data $kHelper * */
    $kHelper = Mage::helper('kpayment');
    /** @var Tigren_Kpayment_Model_Kpayment $kpayment * */
    $kpayment = Mage::getModel('kpayment/kpayment');
    /** @var Mage_Customer_Model_Session $customerSession */
    $customerSession = Mage::getSingleton('customer/session');
    /** @var Mage_Checkout_Model_Session $checkoutSession */
    $checkoutSession = Mage::getModel('checkout/session');
    $customerUrl = $kHelper->getConfigData('kpayment_credit', 'api_base_url') . '/customer';
    $addNewCardCustomerUrl = $kHelper->getConfigData('kpayment_credit', 'api_base_url') . '/customer/' . $checkoutSession->getCustomerKbankApiId() . '/card';

    $customer = Mage::getModel('customer/customer')->load($customerSession->getCustomer()->getId());
    if ((!$customer->getCustomerKbankApiId() || $customer->getCustomerKbankApiId() != $checkoutSession->getCustomerKbankApiId()) && $checkoutSession->getSaveCard()) {
        $customerParams = array(
            'email' => $customer->getEmail(),
            'name' => $customer->getName(),
            'mode' => 'token',
            'token' => $checkoutSession->getToken()
        );

        $kHelper->logAPI('[Mobile - CREATE CUSTOMER REQUEST Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI($customerUrl, 'credit');

        $customerCreate = $kpayment->create($customerParams, $customerUrl, true);
        $customerCreate = json_decode($customerCreate, true);

        $kHelper->logAPI('[Mobile - CREATE CUSTOMER RESPONSE Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI(array($customerCreate), 'credit');
        $kHelper->logAPI('===== END =====', 'credit');

        if ($customerCreate['id']) {
            $customer->setCustomerKbankApiId($customerCreate['id']);
            $customer->save();
        }
    } elseif ($customer->getCustomerKbankApiId() == $checkoutSession->getCustomerKbankApiId() && $checkoutSession->getSaveCard()) {
        $addNewCardParams = array(
            'mode' => 'token',
            'token' => $checkoutSession->getToken()
        );

        $kHelper->logAPI('[Mobile - ADD NEW CARD TO CUSTOMER REQUEST Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI($addNewCardCustomerUrl, 'credit');

        $addNewCardCreate = $kpayment->create($addNewCardParams, $addNewCardCustomerUrl, true);
        $addNewCardCreate = json_decode($addNewCardCreate, true);

        $kHelper->logAPI('[Mobile - ADD NEW CARD TO CUSTOMER RESPONSE Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI(array($addNewCardCreate), 'credit');
        $kHelper->logAPI('===== END =====', 'credit');
    }
    dataResponse(200, 'Add success!');
} catch (Exception $e) {
    dataResponse(400, $e->getMessage());
}

