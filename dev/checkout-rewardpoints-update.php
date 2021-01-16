<?php

require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$customer_id = Mage::getSingleton('customer/session')->getCustomerId();

if ($customer_id) {
    $result = array();
    $pointsBlock = Mage::app()->getLayout()->createBlock('Magestore_RewardPoints_Block_Checkout_Cart_Point');
    $sliderRules = $pointsBlock->getSliderRules();
    $session = Mage::getSingleton('checkout/session');

    if ($sliderRules && count($sliderRules) == 1) {
        $rule = current($sliderRules);
        $result['reward_sales_rule'] = $rule->getId();
    }
//    $entityBody = file_get_contents('php://input');
//    $data = json_decode($entityBody, true);
    $pointUse = $_REQUEST['reward_sales_point'] ? (int)$_REQUEST['reward_sales_point'] : 0;
    if ($pointUse >= 0) {
        $points = $_REQUEST['reward_sales_point'];;
        $session = Mage::getSingleton('checkout/session');
        $session->setData('use_point', true);
        $session->setRewardSalesRules(array(
            'rule_id' => array_key_exists('reward_sales_rule', $result) ? $result['reward_sales_rule'] : 'rate',
            'use_point' => $points,
        ));


        $cart = Mage::getSingleton('checkout/cart');
        $result = array();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            $rewardSalesRules = $session->getRewardSalesRules();
            $arrayRules = Mage::helper('rewardpoints/block_spend')->getRulesArray();
            if (Mage::helper('rewardpoints/calculation_spending')->isUseMaxPointsDefault()) {
                if (isset($rewardSalesRules['use_point']) && isset($rewardSalesRules['rule_id']) && isset($arrayRules[$rewardSalesRules['rule_id']]) && isset($arrayRules[$rewardSalesRules['rule_id']]['sliderOption']) && isset($arrayRules[$rewardSalesRules['rule_id']]['sliderOption']['maxPoints']) && ($rewardSalesRules['use_point'] < $arrayRules[$rewardSalesRules['rule_id']]['sliderOption']['maxPoints'])) {
                    $session->setData('use_max', 0);
                } else {
                    $session->setData('use_max', 1);
                }
            }
        } else {
            $result['refresh'] = true;
        }
        $quote = getQuote();
        $cartData = getCartDetails($quote, $customer_id);
        dataResponse(200, 'Points be updated', $cartData, 'cartData');
    } else {
        dataResponse(400, 'Please add Points to update');
    }

} else {
    dataResponse(400, 'Invaliad');
}