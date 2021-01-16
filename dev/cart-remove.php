<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['customer_id']) {
    $customerId = $data['customer_id'];
	$quote = getQuote();
    try {
        $quote->removeAllItems();
        $quote->collectTotals()->save();
        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'successfully'));
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}