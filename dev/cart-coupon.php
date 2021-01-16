<?php
/**
 * Created by PhpStorm.
 * User: bach95
 * Date: 14/09/2018
 * Time: 15:10
 */
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['customer_id']) {
	$customerId = $data['customer_id'];
	$quote = getQuote();
	if ($data['cancel'] && $data['cancel'] == true) {
		try {
			$quote->getShippingAddress()
				->setCollectShippingRates(true);
			$quote->setData('coupon_code', '')
				->collectTotals()
				->save();
			$productsResult = getCartDetails($quote, $customerId);
			http_response_code(200);
			echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'cartData' => $productsResult));
			return;
		} catch (Exception $e) {
			http_response_code(400);
			echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
			return;
		}
	}
	$couponCode = (string)$data['coupon_code'];

	if (!$quote->getAllVisibleItems()) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => 'No Item(s) In Cart'));
		return;
	}

	$oldCouponCode = $quote->getCouponCode();
	if (!strlen($couponCode) && !strlen($oldCouponCode)) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => 'No Coupon'));
		return;
	}
	$oCoupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
	if (!$oCoupon->getCode()) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => 'Coupon doesn\'t exist'));
		return;
	}
	try {

		$codeLength = strlen($couponCode);
		$isCodeLengthValid = $codeLength && $codeLength <= 255;
		$quote->getShippingAddress()
			->setCollectShippingRates(true);
		$quote->setData('coupon_code', $isCodeLengthValid ? $couponCode : '')
			->collectTotals()
			->save();
        $productsResult = getCartDetails($quote, $customerId);

        $discount = $quote->getTotals();
        if (!$discount['discount']) {
            echo json_encode(array('status_code' => 400, 'message' => 'Coupon doesn\'t active'));
            return;
        }
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => $quote->getCouponCode(),'cartData' => $productsResult));
	} catch (Exception $e) {
		echo 'Cannot apply the coupon code.';
		Mage::logException($e);
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}