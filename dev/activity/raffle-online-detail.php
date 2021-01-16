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

if (isset($_REQUEST['id'])) {
    $campaign = Mage::getModel('campaignmanage/campaignonline')->load($_REQUEST['id']);
    if ($campaign->getId()) {

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
        $data['allow_pickup'] = $campaign->getAllowPickup() ? true : false;
        $data['allow_shipping'] = $campaign->getAllowShipping() ? true : false;
        $data['is_card_payment'] = $campaign->getIsCardPayment() ? true : false;
        $time = strtotime($campaign->getEndRegisterTime());
        $data['end_register_time'] = $time . '';
        $data['image'] = $campaign->getImage() ?
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'campaignmanage/images/' . $campaign->getImage() : null;

        dataResponse($statusCode, $message, $data);
    } else {
        $statusCode = 400;
        $message = 'Invalid request';
        dataResponse($statusCode, $message);
    }
} else {
    $statusCode = 400;
    $message = 'Invalid request';
    dataResponse($statusCode, $message);
}
