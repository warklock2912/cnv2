<?php

require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$password = $data['password'];


if ($password && $password && strlen($password) > 5) {
	$customer = Mage::getModel("customer/session")->getCustomer();
	$currPass   = $data['current_password'];
	$newPass    = $data['password'];

	$oldPass = $customer->getPasswordHash();
	if ( Mage::helper('core/string')->strpos($oldPass, ':')) {
		list($_salt, $salt) = explode(':', $oldPass);
	} else {
		$salt = false;
	}

	if ($customer->hashPassword($currPass, $salt) == $oldPass) {
		if (strlen($newPass)) {
			/**
			 * Set entered password and its confirmation - they
			 * will be validated later to match each other and be of right length
			 */
			$customer->setPassword($newPass);
			$customer->save();
			dataResponse(200, 'change password successfully');
		} else {
			dataResponse(400, 'New password field cannot be empty.');
			return;
		}
	} else {
		dataResponse(400, 'Invalid current password');
	}

} else {
	dataResponse(400, 'Password Invalid');
}