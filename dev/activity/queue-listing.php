<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = Mage::getSingleton('customer/session')->getCustomer();
$customerId = $_REQUEST['customer_id'] ? $_REQUEST['customer_id'] : $customer->getId();
$queueCollection = Mage::getModel('campaignmanage/queue')->getCollection()
    ->addFieldToFilter('customer_id', $customerId)
    ->addFieldToFilter('is_showing', true);
$dataArr = array();
foreach ($queueCollection as $queue) {
    $campaignId = $queue->getCampaignId();
    $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
    if($campaign->getCampaignId()){
        if ($queue->getQueueStatus() != 3) {
            $data['activity_id'] = $campaign->getId();
            $locator = getLocator($campaign->getId());
            $data['locator_title'] = $locator->getTitle();
            $data['activity_name'] = $campaign->getCampaignName();
            $data['no_of_queue'] = $queue->getNoOfQueue();
            $data['prefix'] = $campaign->getQueuePrefix();
            $data['type'] = $campaign->getCampaignType();
            $currentCustomer = Mage::getModel('campaignmanage/queue')
                ->getCollection()
                ->addFieldToFilter('campaign_id', $campaignId)
                ->addFieldToFilter('queue_status', 2)
                ->getFirstItem();
            if ($currentCustomer->getId()) {
                $currentNo = $currentCustomer->getNoOfQueue();
                $noOfWaiting = $queue->getNoOfQueue() - $currentNo;
            } else {
                $noOfWaiting = $queue->getNoOfQueue();
            }
            $data['no_of_waiting'] = null;
            $data['no_of_id'] = null;
            $data['is_waiting'] = false;
            if ($campaign->getCampaignType() == Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE) {
                $data['no_of_id'] = $queue->getNoOfId();
                if ($campaign->getIsWaiting() == false) {
                    $data['no_of_waiting'] = $noOfWaiting . '';
                } else
                    $data['is_waiting'] = true;

            } else {
                $data['no_of_waiting'] = $noOfWaiting . '';
            }
            $dataArr[] = $data;
        }
    }
}
dataResponse(200, 'valid', $dataArr);