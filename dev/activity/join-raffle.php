<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
require_once '../../lib/nusoap/nusoap.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$campaignId = $data['activity_id'];
$productId = $data['product_id'];
$size = $data['size'];
$customer = $data['customer_id'] ?
	Mage::getModel('customer/customer')->load($data['customer_id']) : Mage::getSingleton('customer/session')->getCustomer();
$customerId = $customer->getId();

$raffleFilterByCustomer = Mage::getModel('campaignmanage/raffle')->getCollection()
	->addFieldToFilter('campaign_id', $campaignId)
	->addFieldToFilter('customer_id', $customerId);

if (count($raffleFilterByCustomer)) {
	dataResponse(400, 'You\'re Joined');
	return;
}

$raffleCollection = Mage::getModel('campaignmanage/raffle')
	->getCollection()
	->addFieldToFilter('campaign_id', $campaignId);

$campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);

$noOfParticipants = $campaign->getNoOfPart();
if (count($raffleCollection) >= $noOfParticipants) {
	dataResponse(400, 'Sorry, Activity is limited');
	return;
}

$name = $customer->getName();
$email = $customer->getEmail();
$phone = $customer->getTelephone();
$cardFilterByCustomerId = Mage::getModel('activity/activity')
	->getCollection()
	->addFieldToFilter('customer_id', $customerId);
$cardId = $cardFilterByCustomerId->getFirstItem($customerId)->getCardId();

try {
    $points_cost = $campaign->getData('points_cost');

    if($points_cost && $points_cost > 0){
        $spent_point  = spendPointsActivity($customerId,$points_cost,$campaign->getData('campaign_name'));
        if($spent_point != 1){
            dataResponse('400',$spent_point);
            die();
        }else{
            Mage::dispatchEvent('raffle_use_point',array( 'customer' => $customer, 'reward_point_spent' => $points_cost));
        }
    }

	$raffle = Mage::getModel('campaignmanage/raffle');
	$raffle->setCampaignId($campaignId)
		->setCustomerId($customerId)
		->setCustomerName($name)
		->setEmail($email)
		->setPhone($phone)
		->setCreatedAt(now())
		->setCardId($cardId)
		->setProductId($productId)
		->setOption($size);
	$raffle->save();
	dataResponse(200, 'successfully');
} catch (Exception $e) {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
}
