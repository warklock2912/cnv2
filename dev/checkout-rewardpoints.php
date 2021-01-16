<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$customer_id = Mage::getSingleton('customer/session')->getCustomerId();
if ($customer_id) {
    $result = array();
    $session = Mage::getSingleton('checkout/session');
    $quote = $session->getQuote();
    if ($quote->getItemsQty()) {
        $_blockRewardpointsAccount = Mage::app()->getLayout()->getBlockSingleton('rewardpoints/account_dashboard');
        $pointsAvailable = $_blockRewardpointsAccount->getBalanceTextPoints();
        $result['balance_points'] = ['label' => __('You have ' . $pointsAvailable . ' Available'), 'value' => $pointsAvailable];
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $cart = Mage::getSingleton('checkout/cart');
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
        }
        $helperEarning = Mage::helper('rewardpoints/calculation_earning');
        $helperSpending = Mage::helper('rewardpoints/calculation_spending');

        $result['earning_points'] = [
            'label' => __('Points Earning'),
            'value' => '+' . $helperEarning->getTotalPointsEarning($cart->getQuote())
        ];
        if ($helperSpending->getTotalPointSpent()) {
            $result['spending_points'] = ['label' => __('Points Spending'), 'value' => '-' . $helperSpending->getTotalPointSpent()];
        }
        if ($address->getRewardpointsDiscount()) {
            $result['use_points'] = ['label' => __('Use Point (' . $address->getRewardpointsDiscount() . ') Points'), 'value' => '-' . $address->getRewardpointsDiscount()];
        }

        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'valid', 'data' => $result));
    } else {
        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'valid', 'data' => __('Shopping cart is empty ')));
    }
} else {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}