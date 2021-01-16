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
$activityType = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'online'; // type = store or online
$campaigns = Mage::getModel('campaignmanage/campaign')->getCollection()
    ->addFieldToFilter('app_display', 1)
    ->addFieldToFilter('start_register_time', array('lt' => Varien_Date::now()))
    ->setOrder('start_register_time', 'DESC');
if ($activityType == 'online') {
    $campaigns->addFieldToFilter('campaign_type', 4);
} else {
    $campaigns->addFieldToFilter('campaign_type', array('neq' => 4));
}
if (count($campaigns)) {
    foreach ($campaigns as $campaign) {
        $data['id'] = $campaign->getId();

        if ($campaign->getCampaignType() == 1 || $campaign->getCampaignType() == 2) :

            $isJoined = checkJoinedQueue($campaign->getid(), $customerId);
            // check campaign is full ?
            $queueCollection = Mage::getModel('campaignmanage/queue')
                ->getCollection()
                ->addFieldToFilter('campaign_id', $campaign->getId());
            $noOfParticipants = $campaign->getNoOfPart();
            if (count($queueCollection) >= $noOfParticipants) {
                $data['is_fully_joined'] = true;
            } else
                $data['is_fully_joined'] = false;

            //  Is customer pass queue ?
            $queueOfCustomer = Mage::getModel('campaignmanage/queue')->getCollection()
                ->addFieldToFilter('campaign_id', $campaign->getId())
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();
            if ($queueOfCustomer->getId() && $queueOfCustomer->getQueueStatus() == 3) {
                $data['is_passed'] = true;
            } else
                $data['is_passed'] = false;
            $isEnd = $campaign->getIsEnd() == 1 ? true : false;
            $data['is_end'] = $isEnd;
        else:
            if ($campaign->getData('end_register_time') <= Varien_Date::now()) {
                continue;
            }
            //$data['points_cost'] = $campaign->getData('points_cost');
            $data['is_started_queue'] = $campaign->getData('status') == 2 ? true : false;
            $isJoined = checkJoinedRaffle($campaign->getid(), $customerId);
            // check campaign is full ?
            $queueCollection = Mage::getModel('campaignmanage/raffle')
                ->getCollection()
                ->addFieldToFilter('campaign_id', $campaign->getId());
            $noOfParticipants = $campaign->getNoOfPart();
            if (count($queueCollection) >= $noOfParticipants) {
                $data['is_fully_joined'] = true;
            } else
                $data['is_fully_joined'] = false;
        endif;
        $data['is_joined'] = $isJoined;


        $data['type'] = $campaign->getCampaignType();
        $data['content'] = $campaign->getContent();
        //$data['point_spent'] = $campaign->getPointSpent();
        $data['point_spent'] = $campaign->getData('points_cost');
        $data['activity_name'] = $campaign->getCampaignName();
        $locator = getLocator($campaign->getId());
        $data['locator_name'] = $locator->getTitle();
        $data['locator_longitude'] = $locator->getLongitude();
        $data['locator_latitude'] = $locator->getLatitude();
        $data['end_register_time'] = strtotime($campaign->getEndRegisterTime()) . '';
        $data['start_register_time'] = strtotime($campaign->getStartRegisterTime()) . '';
        $data['image'] = $campaign->getImage() ?
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'campaignmanage/images/' . $campaign->getImage() : null;

        if ($campaign->getCampaignType() == 3 && $campaign->getData('points_cost') < 0) {
            continue;
        }
        $dataArr[] = $data;
    }
}


dataResponse($statusCode, $message, $dataArr);