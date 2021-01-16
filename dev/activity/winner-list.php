<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();

if ($_REQUEST['activity_id']) {
	$campaignId = $_REQUEST['activity_id'];
	$campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);

	$dataResponse = array();
	$customer = Mage::getSingleton('customer/session')->getCustomer();
	$customerId = isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : $customer->getId();
    $raffle = Mage::getModel('campaignmanage/raffle')
        ->getCollection()
        ->addFieldToFilter('campaign_id',$campaignId)
        ->addFieldToFilter('customer_id',$customerId)
        ->getFirstItem()
    ;
    $locator = getLocator($campaign->getId());
    $dataResponse['locator_name'] = $locator->getTitle();
		$productId = $raffle->getProductId();
		$product = Mage::getModel('catalog/product')->load($productId);
    $dataResponse['product_name'] = $product->getName();
		if ($product->isConfigurable()):
			$optionList = Mage::helper('campaignmanage')->getOptionValues($productId, $campaignId);
			foreach ($optionList as $option):
				$data = array();
				$optionLabel = Mage::helper('campaignmanage')->getOptionLabel($option);
				$data['option'] =  $optionLabel;
				$winners = Mage::getModel('campaignmanage/raffle')->getCollection()
					->addFieldToFilter('campaign_id', $campaignId)
					->addFieldToFilter('product_id', $productId)
					->addFieldToFilter('option', $option)
					->addFieldToFilter('is_winner', true);
				$data['winner'] = array();
				foreach ($winners as $winner):
					$data['winner'][] = $winner->getCustomerName();
				endforeach;
				$dataResponse['data'][] = $data;
			endforeach;
		else:
			$data = array();
			$data['option'] = $product->getName();
            $data['winner'] = array();
            $winners = Mage::getModel('campaignmanage/raffle')->getCollection()
                ->addFieldToFilter('campaign_id', $campaignId)
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('is_winner', true);
            foreach ($winners as $winner):
                $data['winner'][] = $winner->getCustomerName();
            endforeach;
            $dataResponse['data'][] = $data;
		endif;
	dataResponse(200, 'valid', $dataResponse);
} else
	dataResponse(400, 'Missing param activity_id');

