<?php
require_once '../app/Mage.php';
require_once 'functions.php';
$bankStr = Mage::getStoreConfig('confirmpayment/info/bank');
$bankArr = array();
if ($bankStr) {
	$bankArr = explode(',', $bankStr);
}
http_response_code(200);
echo json_encode(array('status_code' => 200, 'message' => 'valid', 'bankArr' => $bankArr));