<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['item_id']) {
    $customerId = $data['customer_id'];
    $qty = $data['qty'];
    $itemId = $data['item_id'];
    $cartProductQty = array();
    try {
        $cart = Mage::getModel('checkout/cart')->getQuote();
        $quote = getQuote();
        $checkLimitMinMaxQty = limitMinMaxQty($quote, $qty, null, $itemId );
        if (!$checkLimitMinMaxQty['success']){
            dataResponse(400, $checkLimitMinMaxQty['message']);
            die;
        }


        foreach ($cart->getAllItems() as $item) {

            $item_id = $item->getItemId();
            if($itemId == $item_id){

                $product = $item->getProduct();
                $productId = $item->getProduct()->getId();
                $productPrice = $item->getProduct()->getPrice();
                $cartProductQty[$item->getId()] = $item->getQty();

                if($product->getTypeId() == 'configurable'){
                    $allOptions = $item->getProduct()
                        ->getTypeInstance(true)
                        ->getOrderOptions($item->getProduct());

                    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $allOptions['simple_sku']);
                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                    $stock_qty = $stock->getData('qty');

                    check_product_sale_qty($stock,$qty);

                    $reservedQty = Mage::helper('cartreservation/product')->getReservedCount($productId, $stock_qty);
                    $current_qty = $stock_qty - $reservedQty + $item->getQty();

                    if($current_qty < $qty){
                        http_response_code(400);
                        echo json_encode(array('status_code' => 400, 'message' => 'The requested quantity for ' . $_product->getName() . ' is not available.'));
                        die;
                    }
                }else{
                    $_product = Mage::getModel('catalog/product')->load($productId);
                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                    $stock_qty = $stock->getData('qty');

                    check_product_sale_qty($stock,$qty);

                    $reservedQty = Mage::helper('cartreservation/product')->getReservedCount($productId, $stock_qty);
                    $current_qty = $stock_qty - $reservedQty + $item->getQty();

                    if($current_qty < $qty){
                        http_response_code(400);
                        echo json_encode(array('status_code' => 400, 'message' => 'The requested quantity for ' . $_product->getName() . ' is not available.'));
                        die;
                    }
                }
            }

        }

        $cartData = array(
            $itemId => array(
                'qty' =>   $qty
            )

        );
        $filter = new Zend_Filter_LocalizedToNormalized(
            array('locale' => Mage::app()->getLocale()->getLocaleCode())
        );
        foreach ($cartData as $index => $data) {
            if (isset($data['qty'])) {
                $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
            }
        }
        $cart = Mage::getSingleton('checkout/cart');
        if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
            $cart->getQuote()->setCustomerId(null);
        }

        $cartData = $cart->suggestItemsQty($cartData);

        foreach ($cartData as $index => $data) {
            if (isset($data['qty'])) {
                $cartData[$index]['qty'] = (float)$filter->filter(trim($data['qty']));
                $cartData[$index]['before_suggest_qty'] = (float)3;
            }
        }

        $cart->updateItems($cartData)
            ->save();
        $productsResult = getCartDetails($cart->getQuote(), $customerId);
        $maxItems = (int) Mage::getStoreConfig('cartitems_options/Item/maxitems');

        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'maxItems' => $maxItems,'cartData' => $productsResult));
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}
