<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 13/08/2018
 * Time: 16:58
 */

require_once '../app/Mage.php';
require_once 'functions.php';
try {
    Mage::getSingleton("core/session", array("name" => "frontend"));
} catch(Exception $e){
    sessionExpiredResult();
    die();
}
if ($_REQUEST['id']) {
	$customerId = $_REQUEST['customer_id'];
    $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
    try {
//	$product_id = 83461; //Suppose
		$product_id = $_REQUEST['id'];
		if(isset($_REQUEST['activity_id'])  && isset($_REQUEST['activity_type'])){
		    $campaignId = $_REQUEST['activity_id'];
            $campaignType = $_REQUEST['activity_type'];
            if ($campaignType == 'store'){
                $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
                $queueCollection = Mage::getModel('campaignmanage/raffle')
                    ->getCollection()
                    ->addFieldToFilter('campaign_id', $campaign->getId());
                $noOfParticipants = $campaign->getNoOfPart();
                if (count($queueCollection) >= $noOfParticipants && strtotime($campaign->getEndRegisterTime()) >= strtotime(Varien_Date::now() . ' -1 day')) {
                    $product_data['is_fully_joined'] = true;
                } else
                    $product_data['is_fully_joined'] = false;
            }
            if($campaignType == 'online'){
                $campaigns_online = Mage::getModel('campaignmanage/campaignonline')->load($campaignId);
                $raffleCollection = Mage::getModel('campaignmanage/raffleonline')
                    ->getCollection()
                    ->addFieldToFilter('raffle_id', $campaigns_online->getId());
                $noOfParticipants = $campaigns_online->getNoOfPart();
                if (count($raffleCollection) >= $noOfParticipants && strtotime($campaigns_online->getEndRegisterTime()) >= strtotime(Varien_Date::now() . ' -1 day')) {
                    $product_data['is_fully_joined'] = true;
                } else
                    $product_data['is_fully_joined'] = false;
            }
        }
		$_product = Mage::getSingleton('catalog/product')->load($product_id);
		//

        $_product->setCustomerGroupId($groupId);

        $labels = array();
        $label_collection = Mage::getModel('amlabel/label')->getCollection()
            ->addFieldToFilter('include_type', array('neq'=>1));
        if (0 < $label_collection->getSize()) {
            foreach ($label_collection as $label) {
                $name = 'amlabel_' . $label->getId();
                if ($_product->hasData($name)) {
                    $labels[] = $label->getId();
                 }
                elseif ($_product->getData('sku')) {
                    $skus = explode(',', $label->getIncludeSku());
                    if (in_array($_product->getData('sku'), $skus)){
                        $labels[] = $label->getId();
                    }
                }
            }
        }

        //var_dump($_product->getFinalPrice());
        //var_dump($_product->getPrice());
        //var_dump($_product->getData());

        $final_price = $_product->getFinalPrice();

        $store_id = getStoreId();

        $promo_price = Mage::getResourceModel('catalogrule/rule')->getRulePrice(
            Mage::app()->getLocale()->storeTimeStamp($store_id),
            Mage::app()->getStore($store_id)->getWebsiteId(),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            $_product->getId());

        if($promo_price){
            $final_price = $promo_price;
        }

        $product_data['label'] = $labels;
        //
		$product_data["id"] = $_product->getId();
		$product_data["name"] = $_product->getName();
		$product_data["short_description"] = $_product->getShortDescription();
		$product_data["description"] = $_product->getDescription();
		$product_data["price"] = (string)$_product->getPrice();
		$product_data["special_price"] = (string)$final_price;
		$product_data["url"] = (string)$_product->getProductUrl();
		$product_data['brand'] = $_product->getResource()->getAttribute('carnival_brand')->getFrontend()->getValue($_product);
		if ($_product->isConfigurable()) {
			$allProducts = $_product->getTypeInstance(true)->getUsedProducts(null, $_product);
			foreach ($allProducts as $subproduct) {
				if ($subproduct->getIsInStock() == 1)
					$sizes[] = array(
						'label' => $subproduct->getAttributeText('size_products'),
						'value_index' => $subproduct->getData('size_products')
					);
			}
			$product_data["size"] = $sizes;
		} else
			$product_data["size"] = null;

		if (Mage::helper('catalog/output')->productAttribute($_product, $_product->getSizing(), 'sizing')) {
			$html = Mage::helper('catalog/output')->productAttribute($_product, $_product->getSizing(), 'sizing');
			preg_match_all('/src="([^"]*)"/', $html, $images);
			$product_data['sizing_image'] = $images[1][0];
		} else
			$product_data['sizing_image'] = null;

		$websiteId = Mage::app()->getWebsite()->getId();
		$point = Mage::helper('rewardpoints/calculation_earning')->getRateEarningPoints($_product->getFinalPrice());
		$product_data['point'] = $point;
		$wishList = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId);
		$wishListItemCollection = $wishList->getItemCollection();
		$isWishList = false;
		foreach ($wishListItemCollection as $item) {
			if ($item->getProductId() == $product_id)
				$isWishList = true;
		}
		$product_data["is_wishlist"] = $isWishList;

		//check product is upcomming product?
        $upcommingCategories = array(
          Mage::getStoreConfig('mobile_configuration/block6/category'),
          Mage::getStoreConfig('mobile_configuration/block18/category'),
          Mage::getStoreConfig('mobile_configuration/block19/category')
        );
        $categoryIds = $_product->getCategoryIds();
        $product_data["is_upcomming"] = false;

        foreach ($upcommingCategories as $upcommingCategory) {
          if (in_array($upcommingCategory,$categoryIds)){
            $product_data["is_upcomming"] = true;
            $category = Mage::getModel('catalog/category')->load($upcommingCategory);
            $fromDate = $category->getData('counting_downs');
            $timezone =  Mage::getStoreConfig('general/locale/timezone');
            $fromDate = new DateTime($fromDate, new DateTimeZone($timezone));
            /* Converts to UTC/GMT time zone */
            $fromDate = $fromDate->format('U');
            /* Formats datetime in UTC/GMT timezone to string */
            //$fromDate = date("Y-m-d H:i:s",$fromDate);
            $product_data['countdownTime'] = $fromDate;
            break;
          }
        }

		$imagesData = array();
		$dataMediaArr = array();
		foreach ($_product->getMediaGalleryImages() as $image) { //will load all gallery images in loop
			$imagesData['title'] = $image->getTitle();
			$imagesData['position'] = $image->getPosition();
			$imagesData['url'] = $image->getUrl();
			$dataMediaArr[] = $imagesData;
		}
		$product_data["image"] = $_product->getImageUrl();
		$product_data["images"] = $dataMediaArr;
		$product_data["sku"] = $_product->getSku();

		$reviews = Mage::getModel('review/review')->getCollection()
			->addStoreFilter(Mage::app()->getStore()->getId())
			->addEntityFilter('product', $_product->getId())
			->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
			->setDateOrder()
			->addRateVotes();

		/** @var Mage_Review_Model_Review $review */
		$reviewData = array();
		$reviewAllData = array();
		foreach ($reviews AS $review) {
			/** @var Mage_Rating_Model_Resource_Rating_Option_Vote_Collection $votes */
			$reviewData['review_nickname'] = $review->getNickname();
			$reviewData['review_title'] = $review->getTitle();
			$reviewData['review_detail'] = $review->getDetail();
			$reviewData['review_created_at'] = $review->getCreatedAt();

			$vote = Mage::getModel('rating/rating_option_vote')
				->getResourceCollection()
				->setReviewFilter($review->getReviewId())
				->setStoreFilter(getStoreId())
				->getFirstItem();

			$reviewData['review_vote'] = $vote->getPercent();
			$reviewAllData[] = $reviewData;
		}
		$product_data["reviews"] = $reviewAllData;

		$_rating = Mage::getModel('review/review_summary')->load($_product->getId());
		$product_data["rating"] = $_rating['rating_summary'];

		$product_data["shipping"] = Mage::getStoreConfig('carriers/flatrate/price');
		$product_data["formkey"] = Mage::getSingleton('core/session')->getFormKey();

		if ($_product->isSalable() == 1)
			$product_data["in_stock"] = true;
		else
			$product_data["in_stock"] = false;

        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
        if($stock->getData('use_config_min_sale_qty')){
            $min_qty = 1;
        }else{
            $min_qty = $stock->getData('min_sale_qty');
        }

        if($stock->getData('use_config_max_sale_qty')){
            $max_qty = Mage::getStoreConfig('cataloginventory/item_options/max_sale_qty', Mage::app()->getStore());
        }else{
            $max_qty = $stock->getData('max_sale_qty');
        }

        $product_data["min_sale_qty"] = (int)$min_qty;
        $product_data["max_sale_qty"] = (int)$max_qty;

        $product_data['buy_with_point'] = false;
        if ($_product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($_product)){
          $product_data['buy_with_point'] = true;
          $product_data['points_spend'] = (int)$_product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($_product);
        }
		//var_dump($product_data);
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'postData' => $product_data));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}
