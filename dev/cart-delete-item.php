<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['item_id']) {
	$customerId = $data['customer_id'];
	$itemId = $data['item_id'];
	try {

        $itemObject = Mage::getModel('sales/quote_item')->load($itemId);
        $product_id = $itemObject->getProductId();

        // edit by xanka
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();
        $cart->removeItem($itemId)
            ->save();
        // end

        Mage::helper('cartreservation/product')->cleanCache($product_id);

        $quote = getQuote();
		$quote->removeItem($itemId);
		$quote->collectTotals()->save();
		$productsResult = getCartDetails($quote,$customerId);

		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'cartData' => $productsResult));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}
