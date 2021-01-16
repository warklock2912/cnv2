<?php

require_once '../../app/Mage.php';
require_once '../functions.php';

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$email = $data['email'];
$session = Mage::getSingleton('customer/session');

if ($email) {
	if (!Zend_Validate::is($email, 'EmailAddress')) {
		$session->setForgottenEmail($email);
		dataResponse(400, 'Invalid Email Address');
		return;
	}

	/** @var $customer Mage_Customer_Model_Customer */
	$customer = Mage::getModel('customer/customer')
		->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
		->loadByEmail($email);

	if ($customer->getId()) {
		try {
			$newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
			$customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
			$customer->sendPasswordResetConfirmationEmail();
		} catch (Exception $exception) {
			dataResponse(400, $exception->getMessage());
			return;
		}
	}
	dataResponse(200, 'If there is an account associated with this email, you will receive an email with a link to reset your password');
} else {
	dataResponse(400, 'Pleaser Enter Email');
}