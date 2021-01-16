<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();

if ($_REQUEST['activity_id']) {
    $raffleType = $_REQUEST['type'];
    $dataResponse = array();
    if ($raffleType == 'store') {
        $campaignId = $_REQUEST['activity_id'];
        $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : $customer->getId();
        $raffle = Mage::getModel('campaignmanage/raffle')
            ->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId)
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();
        $locator = getLocator($campaign->getId());
        $dataResponse['locator_name'] = $locator->getTitle();
        $productId = $raffle->getProductId();
        $product = Mage::getModel('catalog/product')->load($productId);
        $dataResponse['product_name'] = $product->getName();
        $dataResponse['product_image'] = $product->getImageUrl();
        $dataResponse['product_price'] = (string)$product->getFinalPrice();
        $dataResponse['timeExprire'] = strtotime($campaign->getEndRegisterTime()) . '';
        if ($product->isConfigurable()):
            $option = $raffle->getOption();
            $optionLabel = Mage::helper('campaignmanage')->getOptionLabel($option);
            $dataResponse['option'] = $option;

            $dataResponse['option_name'] = $optionLabel;
        endif;
    }
    if ($raffleType == 'online') {
        $campaignId = $_REQUEST['activity_id'];
        $campaign = Mage::getModel('campaignmanage/campaignonline')->load($campaignId);
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : $customer->getId();
        $raffle = Mage::getModel('campaignmanage/raffleonline')
            ->getCollection()
            ->addFieldToFilter('raffle_id', $campaignId)
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();
        $productId = $raffle->getProductId();
        $product = Mage::getModel('catalog/product')->load($productId);
        $dataResponse['product_name'] = $product->getName();
        $dataResponse['product_image'] = $product->getImageUrl();
        $dataResponse['product_price'] = (string)$product->getFinalPrice();
        $dataResponse['timeExprire'] = '0';
        if ($product->isConfigurable()):
            $option = $raffle->getOption();
            $optionLabel = Mage::helper('campaignmanage')->getOptionLabel($option);
            $dataResponse['option'] = $option;

            $dataResponse['option_name'] = $optionLabel;
        endif;
    }
    dataResponse(200, 'valid', $dataResponse);
} else
    dataResponse(400, 'Missing param activity_id');

