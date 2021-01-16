<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
$customerID = $customer->getId();

$paymentDetail = new stdClass();

$desc = "generate payment token";
$invoice_no = time();
$currency_code = "THB";
$amount = "000000000100";

//Construct payment token request
$paymentDetail->invoiceNo = $invoice_no;
$paymentDetail->desc = $desc;
$paymentDetail->amount = $amount;
$paymentDetail->currencyCode = $currency_code;
$paymentDetail->userDefined1 = $customerID;
$paymentDetail->userDefined2 = "add_card";
$paymentDetail->userDefined3 = "";
$paymentDetail->userDefined4 = "";
$paymentDetail->userDefined5 = "";


//Important: Verify response signature

$paymentToken = Mage::helper('twoctwop')->getPaymentToken($paymentDetail);
if ($paymentToken['status']) {
    dataResponse(200, $paymentToken['message'], $paymentToken['payment_response']);
} else {
    dataResponse(400, $paymentToken['message']);
};

