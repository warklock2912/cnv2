<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$customer_id = Mage::getSingleton('customer/session')->getCustomerId();
if ($data['product_id']) {
    $productId = $data['product_id'];
    $customerId = $data['customer_id'];
    $qty = $data['qty'];
    $store = Mage::app()->getStore();
    $size = $data['size'] ? $data['size'] : null;
    try {
        $quote = getQuote();
        $product = Mage::getModel('catalog/product')->load($productId);
        $checkLimitMinMaxQty = limitMinMaxQty($quote, $qty, $product);
        if (!$checkLimitMinMaxQty['success']){
            dataResponse(400, $checkLimitMinMaxQty['message']);
            die;
        }

        $upcommingCategories = array(
            Mage::getStoreConfig('mobile_configuration/block6/category'),
            Mage::getStoreConfig('mobile_configuration/block18/category'),
            Mage::getStoreConfig('mobile_configuration/block19/category')
        );
        $categoryIds = $product->getCategoryIds();

        foreach ($upcommingCategories as $upcommingCategory) {
            if (in_array($upcommingCategory,$categoryIds)){
                $category = Mage::getModel('catalog/category')->load($upcommingCategory);
                $fromDate = $category->getData('counting_downs');
                $timezone =  Mage::getStoreConfig('general/locale/timezone');
                $fromDate = new DateTime($fromDate, new DateTimeZone($timezone));
                /* Converts to UTC/GMT time zone */
                $fromDate = $fromDate->format('U');
                /* Formats datetime in UTC/GMT timezone to string */
                //$fromDate = date("Y-m-d H:i:s",$fromDate);
                $dt = new DateTime();
                $dt->setTimezone( new DateTimeZone($timezone));
                $dt->getTimestamp();

                if ($fromDate > $dt->getTimestamp()){
                    dataResponse(400, 'สินค้านี้ยังไม่สามารถสั่งซื้อได้');
                    die;
                }
                break;
            }
        }

        if ($product->isConfigurable()) {
            $params = new Varien_Object(array(
                'product' => $productId,
                'super_attribute' => array('255' => $size),
                'qty' => $qty
            ));

            $superAttributes = array(
                '255' => $size
            );

            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($superAttributes, $product);

            if($childProduct){
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
                $stock_qty = $stock->getData('qty');

                check_product_sale_qty($stock,$qty);

                $reservedQty = Mage::helper('cartreservation/product')->getReservedCount($childProduct->getId(), $stock_qty);
                $current_qty = $stock_qty - $reservedQty;

                if($qty > $current_qty){
                    http_response_code(400);
                    echo json_encode(array('status_code' => 400, 'message' => 'The requested quantity for ' . $product->getName() . ' is not available.'));
                    die;
                }
            }


        } else {
            $params = new Varien_Object(array(
                'product' => $productId,
                'qty' => $qty
            ));

            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $stock_qty = $stock->getData('qty');

            check_product_sale_qty($stock,$qty);

            $reservedQty = Mage::helper('cartreservation/product')->getReservedCount($productId, $stock_qty);
            $current_qty = $stock_qty - $reservedQty;

            if($qty > $current_qty){
                http_response_code(400);
                echo json_encode(array('status_code' => 400, 'message' => 'The requested quantity for ' . $product->getName() . ' is not available.'));
                die;
            }
        }

        $quote->addProduct($product, $params);
        $quote->collectTotals();

        $quote->save();
        $dataArr = array();
        $dataArr['qty'] = $quote->getItemsQty();
        $dataArr['sub_total'] = $quote->getSubtotal();

        if ($customer_id) {
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $quote = getQuote();
            $productsResult = getCartDetails($quote, $customer_id);
            $dataArr['cart_expired_time'] = $productsResult['cart_expired_time'];
        }

        //$dataArr['cart_expired_time'] = Mage::helper('cartreservation')->getConfigTime();

        dataResponse(200, 'successfully', $dataArr);
    } catch (Exception $e) {
        dataResponse(400, $e->getMessage());
    }
} else {
    dataResponse(400, 'Invalid');
}
