<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));
if ($_REQUEST['id']) {
	$order_id = $_REQUEST['id'];
	$order = Mage::getModel('sales/order')
		->load($order_id);
	$data = array();
	if ($order->getId()) {
		$data['id'] = $order->getId();
		$data['created_at'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$data['created_at'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$data['real_order_id'] = $order->getRealOrderId();
		$data['status'] = $order->getStatusLabel();
		$order->getShippingAddress()->format();
		$address = $order->getShippingAddress();
		$city = Mage::getModel('customaddress/city')->load($address->getCityId());
		$region = Mage::getModel('directory/region')->load($address->getRegionId());
		$subdistrict = Mage::getModel('customaddress/subdistrict')->load($address->getSubdistrictId());
		$shipingAddress = array(
			'first_name' => $address->getFirstname(),
			'last_name' => $address->getLastname(),
			'telephone' => $address->getTelephone(),
			'street' => $address->getData('street'),
			'city' => array(
				'city_id' => $city->getId(),
				'code' => $city->getCode(),
				'name' => $city->getName(),
			),
			'region' => array(
				'region_id' => $region->getId(),
				'code' => $region->getCode(),
				'name' => $region->getName(),
			),
			'subdistrict' => array(
				'subdistrict_id' => $subdistrict->getId(),
				'code' => $subdistrict->getCode(),
				'name' => $subdistrict->getName(),
			),
			'country' => array(
				'code' => $address->getCountryId(),
				'label' => Mage::app()->getLocale()->getCountryTranslation($address->getCountry()),
			),
			'post_code' => $address->getPostcode(),
		);
		$data['shipping_address'] = $shipingAddress;
		$data['shipping_price'] = $order->getData('base_shipping_amount');
		$order->getBillingAddress()->format();
		$address = $order->getBillingAddress();
		$city = Mage::getModel('customaddress/city')->load($address->getCityId());
		$region = Mage::getModel('directory/region')->load($address->getRegionId());
		$subdistrict = Mage::getModel('customaddress/subdistrict')->load($address->getSubdistrictId());
		$billingAddress = array(
			'first_name' => $address->getFirstname(),
			'last_name' => $address->getLastname(),
			'telephone' => $address->getTelephone(),
			'street' => $address->getData('street'),
			'city' => array(
				'city_id' => $city->getId(),
				'code' => $city->getCode(),
				'name' => $city->getName(),
			),
			'region' => array(
				'region_id' => $region->getId(),
				'code' => $region->getCode(),
				'name' => $region->getName(),
			),
			'subdistrict' => array(
				'subdistrict_id' => $subdistrict->getId(),
				'code' => $subdistrict->getCode(),
				'name' => $subdistrict->getName(),
			),
			'country' => array(
				'code' => $address->getCountryId(),
				'label' => Mage::app()->getLocale()->getCountryTranslation($address->getCountry()),
			),
			'post_code' => $address->getPostcode(),
		);
		$data['billing_address'] = $billingAddress;
		$data['shipping_method'] = $order->getShippingDescription();
		$data['payment_method'] =trim(strip_tags($order->getPayment()->getMethodInstance()->getTitle()));
		$data['sub_total'] = $order->getSubtotal();
		$data['reward_point_earn'] = $order->getRewardpointsEarn();
		$data['reward_point_spent'] = $order->getRewardpointsSpent();
		$data['use_point'] = $order->getRewardpointsDiscount();
		$data['discount'] = (int)$order->getDiscountAmount() == 0 ? $order->getDiscountAmount() : substr($order->getDiscountAmount(),1);
		$data['grand_total'] = $order->getGrandTotal();

		foreach ($order->getShipmentsCollection() as $shipment) {
			$tracks = $shipment->getAllTracks();
			if(count($tracks) > 0){
				$track_title = $tracks[0]->getTitle();
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

			if ($item->getProductType() == 'configurable') {
				$product = $item->getProduct();
			} else {
				$product = Mage::getModel('catalog/product')->load($productId);
			}
			$itemData['price'] = (string)$item->getPrice();
			$productMediaConfig = Mage::getModel('catalog/product_media_config');
			$thumbnailUrl = $productMediaConfig->getMediaUrl($product->getThumbnail());
			$itemData['image'] = $thumbnailUrl;
			$optionList = array();
			$optionListData = array();
			$options = $item->getProductOptions();
			$options = $options['attributes_info'];
			foreach ($options as $op):
				$optionListData['label'] = $op['label'];
				$optionListData['value'] = $op['value'];
				$optionList[] = $optionListData;
			endforeach;
			$itemData['options'] = $optionList;
			array_push($itemList, $itemData);
		endforeach;
		$data['items'] = $itemList;
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'orderDetail' => $data));
	} else {
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'orderDetail' => $data));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}
