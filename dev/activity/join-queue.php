<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
require_once '../../lib/nusoap/nusoap.php';
checkIsLoggedIn();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['activity_id']) {
    $campaignId = $data['activity_id'];
    $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
    $statusWait = 1;
    $customer = $data['customer_id'] ?
        Mage::getModel('customer/customer')->load($data['customer_id']) : Mage::getSingleton('customer/session')->getCustomer();
    $customerId = $customer->getId();

    $queueFilterByCustomer = Mage::getModel('campaignmanage/queue')->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->addFieldToFilter('customer_id', $customerId);
    if (count($queueFilterByCustomer)) {
        dataResponse(400, 'You\'re Joined');
        return;
    }

    $queueCollection = Mage::getModel('campaignmanage/queue')
        ->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId);

    $noOfParticipants = $campaign->getNoOfPart();
    if (count($queueCollection) >= $noOfParticipants) {
        dataResponse(400, 'Sorry, Queue is limited');
        return;
    }


    $name = $customer->getName();
    $email = $customer->getEmail();
    $phone = $customer->getTelephone();
    $cardFilterByCustomerId = Mage::getModel('activity/activity')
        ->getCollection()
        ->addFieldToFilter('customer_id', $customerId);
    $cardId = $cardFilterByCustomerId->getFirstItem($customerId)->getCardId();


    $currentCustomer = Mage::getModel('campaignmanage/queue')
        ->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->addFieldToFilter('queue_status', 2)
        ->getFirstItem();
    if ($currentCustomer->getId()) {
        $currentNo = $currentCustomer->getNoOfQueue();
    }

    $noOfQueue = count($queueCollection) ? count($queueCollection) + 1 : 1;
    $queueWaitingCollection = Mage::getModel('campaignmanage/queue')->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->addFieldToFilter('queue_status', $statusWait);
    $queueLastCustomer = Mage::getModel('campaignmanage/queue')->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->setOrder('created_at', 'DESC')
        ->getFirstItem();

    if ($campaign->getCampaignType() == Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE) {
        $pointsCost = $campaign->getData('points_cost');
        $dataArr['pointCost'] = $pointsCost;
        if ($pointsCost && $pointsCost > 0) {
            $spent_point = spendPointsActivity($customerId, $pointsCost, $campaign->getData('campaign_name'));
            $dataArr['point_spent'] = $spent_point;
            if ($spent_point != 1) {
                dataResponse('400', $spent_point);
                die();
            }else{
                Mage::dispatchEvent('raffle_use_point',array( 'customer' => $customer, 'reward_point_spent' => $points_cost));
            }
        }
    }

    $noOfId = 1;
    if (!empty($queueLastCustomer) && $queueLastCustomer->getNoOfId()) {
        $noOfId = $queueLastCustomer->getNoOfId() + 1;
    }
    try {
        $queue = Mage::getModel('campaignmanage/queue');
        $queue->setCampaignId($campaignId)
            ->setCustomerId($customerId)
            ->setCustomerName($name)
            ->setEmail($email)
            ->setPhone($phone)
            ->setCreatedAt(now())
            ->setCardId($cardId)
            ->setNoOfQueue($noOfQueue)
            ->setNoOfId($noOfId)
            ->setQueueStatus($statusWait)
            ->setIsShowing(true);
        $queue->save();
        $dataArr['id'] = $campaign->getId();
        $locator = getLocator($campaign->getId());
        $dataArr['locator_title'] = $locator->getTitle();
        $dataArr['activity_name'] = $campaign->getCampaignName();
        $dataArr['no_of_queue'] = (string)$queue->getNoOfQueue();
        $dataArr['prefix'] = $campaign->getQueuePrefix();
        $dataArr['type'] = $campaign->getCampaignType();
        $dataArr['no_of_id'] = null;
        $dataArr['is_waiting'] = false;
        if ($campaign->getCampaignType() == Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE) {
            $dataArr['no_of_id'] = (string)$queue->getNoOfId();
            $dataArr['is_waiting'] = $campaign->getIsWaiting() ? true : false;
        } else {
            if ($currentNo) {
                $dataArr['no_of_waiting'] = ($queue->getNoOfQueue() - $currentNo);
            } else {
                $dataArr['no_of_waiting'] = $queue->getNoOfQueue();
            }
        }
        $dataArr['no_of_waiting'] = (string)$dataArr['no_of_waiting'];

        dataResponse(200, 'successfully', $dataArr);
    } catch (Exception $e) {
        dataResponse(400, $e->getMessage());
    }
} else
    dataResponse(400, 'Invalid Post');
