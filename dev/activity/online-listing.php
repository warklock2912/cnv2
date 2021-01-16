<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = Mage::getSingleton('customer/session')->getCustomer();
$customerId = $customer->getId();
$dataArr = array();
$statusCode = 200;
$message = 'Valid';
$data = array();
$campaigns_online = Mage::getModel('campaignmanage/campaignonline')->getCollection()
    ->addFieldToFilter('app_display', 1)
    ->addFieldToFilter('start_register_time', array('lt' => Varien_Date::now()))
    ->addFieldToFilter('end_register_time', array('gt' => Varien_Date::now()))
    ->setOrder('start_register_time', 'DESC');

if (count($campaigns_online)) {
    foreach ($campaigns_online as $campaign) {
        $data['id'] = $campaign->getId();
        $isJoined = checkJoinedRaffleOnline($campaign->getid(), $customerId);

        // check campaign is full ?

        $raffleCollection = Mage::getModel('campaignmanage/raffleonline')
            ->getCollection()
            ->addFieldToFilter('raffle_id', $campaign->getId());
        $noOfParticipants = $campaign->getNoOfPart();
        if (count($raffleCollection) >= $noOfParticipants) {
            $data['is_fully_joined'] = true;
        } else
            $data['is_fully_joined'] = false;

        $data['is_joined'] = $isJoined;
        $data['is_started_queue'] = $campaign->getData('status') == 2 ? true : false;
        $data['content'] = $campaign->getContent();
        $data['point_spent'] = $campaign->getPointSpent();
        $data['activity_name'] = $campaign->getCampaignName();
        $data['allow_pickup'] = $campaign->getAllowPickup() ? true :false;
        $data['allow_shipping'] = $campaign->getAllowShipping() ? true :false;
        $data['is_card_payment'] = $campaign->getIsCardPayment() ? true :false;
        $time = strtotime($campaign->getEndRegisterTime());
        $data['end_register_time'] = $time.'';
        $data['image'] = $campaign->getImage() ?
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'campaignmanage/images/' . $campaign->getImage() : null;
        $dataArr[] = $data;
    }
}

dataResponse($statusCode, $message, $dataArr);
