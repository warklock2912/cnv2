<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));

if (isset($_REQUEST['id'])) {
    $customerId = $_REQUEST['id'];
    $collection = Mage::getResourceModel('sales/order_collection')
        ->addFieldToFilter('customer_id', $customerId)
        ->setOrder('created_at', 'desc');
    if (isset($_REQUEST['status'])) {
        $collection->addFieldToFilter('status', $_REQUEST['status']);
    }

    $data = array();
    $dataArr = array();
    if (count($collection)) {
        foreach ($collection as $order):

            $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();

            if (isset($_REQUEST['status']) && $_REQUEST['status'] == 'pending' && ($payment_method_code !== 'banktransfer') && $payment_method_code == 'crystal_braintree') {
                continue;
            }
            $data['order_id'] = $order->getId();
            $confirm = Mage::getModel('confirmorder/confirm')->getCollection()
                ->addFieldToFilter('order_increment_id', $order->getRealOrderId())
                ->getFirstItem();
            $data['is_confirmed'] = $confirm->getIsConfirmed() ? (int)$confirm->getIsConfirmed() : 0;
            $data['created_at'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $data['real_order_id'] = $order->getRealOrderId();
            $data['status'] = $order->getStatusLabel();
            $data['total'] = $order->getGrandTotal();
            $data['reward_point_earn'] = $order->getRewardpointsEarn();
            $data['reward_point_spent'] = $order->getRewardpointsSpent();
            $data['use_point'] = $order->getRewardpointsDiscount();
            $data['tracking_number'] = '';
            $data['method'] = $order->getPayment()->getMethodInstance()->getCode();
            foreach ($order->getShipmentsCollection() as $shipment) {
    			$tracks = $shipment->getAllTracks();
    			if(count($tracks) > 0){
    				$track_title = $tracks[0]->getTitle();
                    $data['tracking_title'] = $track_title;
    				if (stristr($track_title ,'kerry')){
    					$active_track = Mage::getStoreConfig('kerry/general/tracking_url', Mage::app()->getStore());
    					$getNumber = $tracks[0]->getTrackNumber();
    					$data['tracking_number'] = $active_track . $getNumber;
    				}else{
    					$data['tracking_number'] = $tracks[0]->getTrackNumber();
    				}
    			}else{
    				$data['tracking_number'] = '';
    			}
    		}

            $items = $order->getAllVisibleItems();
            $itemList = array();
            $itemData = null;
            foreach ($items as $item):
                $itemData['id'] = $item->getId();
                $productId = $item->getProductId();
                $itemData['product_id'] = $productId;
                $itemData['name'] = $item->getName();
                $itemData['sku'] = $item->getSku();
                $itemData['qty'] = $item->getQtyOrdered();
                /* @var $product Mage_Catalog_Model_Product */
                if ($item->getProductType() == 'configurable') {
                    $product = $item->getProduct();
                } else {
                    $product = Mage::getModel('catalog/product')->load($productId);
                }
                $itemData['price'] = (string)$item->getPrice();
                $productMediaConfig = Mage::getModel('catalog/product_media_config');
                $thumbnailUrl = $productMediaConfig->getMediaUrl($product->getThumbnail());
                $itemData['image'] = $thumbnailUrl;
                $itemData['weight'] = $product->getWeight();
                $optionList = array();
                $optionListData = array();
                $options = $item->getProductOptions();
                $options = $options['attributes_info'];
                if (count($options)) {
                    foreach ($options as $op):
                        $optionListData['label'] = $op['label'];
                        $optionListData['value'] = $op['value'];
                        $optionList[] = $optionListData;
                    endforeach;
                }
                $itemData['options'] = $optionList;
                array_push($itemList, $itemData);
            endforeach;
            $data['items'] = $itemList;
            $dataArr[] = $data;
        endforeach;
        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'valid', 'orderData' => $dataArr));
    } else {
        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'No Order', 'orderData' => $dataArr));
    }
} else {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}
