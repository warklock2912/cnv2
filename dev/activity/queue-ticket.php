<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();

$data = array();

$customer = Mage::getSingleton('customer/session')->getCustomer();
$customerId = $customer->getId();
if ($_REQUEST['activity_id']):

    $campaignId = $_REQUEST['activity_id'];
    $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);

    if ($campaign->getCampaignType() == 2 && $campaign->getis_waiting() == true) {
        //dataResponse(200, 'Waiting');
        //die();
    }

    $queueOfCustomer = Mage::getModel('campaignmanage/queue')->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->addFieldToFilter('customer_id', $customerId)
        ->getFirstItem();

    $data['activity_name'] = $campaign->getCampaignName();
    $data['type'] = $campaign->getCampaignType();
    $data['no_of_id'] = $queueOfCustomer->getNoOfId();
    $data['prefix'] = $campaign->getQueuePrefix();
    $data['joined_at'] = (string)strtotime($queueOfCustomer->getCreatedAt());
    $isEnd = $campaign->getIsEnd() == 1 ? true : false;
    $data['is_end'] = $isEnd;
    $data['is_passed'] = false;
    if ($queueOfCustomer->getQueueStatus() == 1):
        $data['no_of_queue'] = $queueOfCustomer->getNoOfQueue();
        $queueCollection = Mage::getModel('campaignmanage/queue')->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId);
        $collectionSize = $queueCollection->getSize();
        $queueCurrent = Mage::getModel('campaignmanage/queue')->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId)
            ->addFieldToFilter('queue_status', 2)
            ->getFirstItem();


        if ($queueCurrent->getId()):
            $data['no_of_waiting'] = $queueOfCustomer->getNoOfQueue() - $queueCurrent->getNoOfQueue();
        else:
            $data['no_of_waiting'] = $queueOfCustomer->getNoOfQueue();
        endif;
        $data['no_of_waiting'] = (string)$data['no_of_waiting'];
        $data['is_waiting'] = (bool)$campaign->getis_waiting();
        dataResponse(200, 'valid', $data);
    elseif ($queueOfCustomer->getQueueStatus() == 2):
        $data['no_of_queue'] = $queueOfCustomer->getNoOfQueue();
        $data['no_of_waiting'] = "0";
        $data['is_waiting'] = (bool)$campaign->getis_waiting();
        dataResponse(200, 'You\'re in queue', $data);
    else:
        $data['is_passed'] = true;
        dataResponse(200, 'Passed', $data);
    endif;

    // $cacheId = 'queue_ticket_'.$campaignId;
    // $cacheTag = 'block_html';
    // $cacheStatus = 'MISS';
    // $loadNewQueueOfCustomer = false;
    // $queueOfCustomerList = [];
    // $queueCurrent = [];
    //
    // if ($campaign = Mage::app()->getCache()->load($cacheId)) {
    //     $campaign = unserialize($campaign);
    //     if($campaign['next_refresh'] < time()){
    //         $loadNewQueueOfCustomer = true;
    //     }else{
    //         $queueOfCustomerList = $campaign['queue_of_customer'];
    //         $queueCurrent = $campaign['queue_current'];
    //         $cacheStatus = 'HIT';
    //     }
    // } else {
    //     $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
    //     $campaign = $campaign->getData();
    //     /*
    //     if ($campaign['campaign_type'] == 2 && $campaign['is_waiting'] == true) {
    //         //dataResponse(200, 'Waiting');
    //         //die();
    //     }
    //     */
    //     $loadNewQueueOfCustomer = true;
    // }
    //
    // if($loadNewQueueOfCustomer){
    //     $queueOfCustomerListCollection = Mage::getModel('campaignmanage/queue')->getCollection()
    //         ->addFieldToFilter('campaign_id', $campaignId)
    //         ;
    //     foreach($queueOfCustomerListCollection as $qc){
    //         $qc = $qc->getData();
    //         $queueOfCustomerList[$qc['customer_id']] = $qc;
    //         if($qc['queue_status'] == 2){
    //             $queueCurrent = $qc;
    //         }
    //     }
    //     $campaign['next_refresh'] = strtotime('+1 minute');
    //     $campaign['queue_of_customer'] = $queueOfCustomerList;
    //     $campaign['queue_current'] = $queueCurrent;
    //     Mage::app()->getCache()->save(serialize($campaign), $cacheId, array($cacheTag), 60*60);
    // }
    //
    // if(!isset($queueOfCustomerList[$customerId])){
    //     dataResponse(400, 'Missing param customer_id');
    //     die;
    // }
    //
    // $queueOfCustomer = $queueOfCustomerList[$customerId];
    //
    // $data['activity_name'] = $campaign['campaign_name'];
    // $data['type'] = $campaign['campaign_type'];
    // $data['no_of_id'] = $queueOfCustomer['no_of_id'];
    // $data['prefix'] = $campaign['queue_prefix'];
    // $data['joined_at'] = (string)strtotime($queueOfCustomer['created_at']);
    // $isEnd = $campaign->getIsEnd() == 1 ? true : false;
    // $data['is_end'] = $isEnd;
    // $data['is_passed'] = false;
    // $data['cache_status'] = $cacheStatus;
    // if ($queueOfCustomer['queue_status'] == 1):
    //     $data['no_of_queue'] = $queueOfCustomer['no_of_queue'];
    //     if (!empty($queueCurrent)):
    //         $data['no_of_waiting'] = $queueOfCustomer['no_of_queue'] - $queueCurrent['no_of_queue'];
    //     else:
    //         $data['no_of_waiting'] = $queueOfCustomer['no_of_queue'];
    //     endif;
    //     $data['no_of_waiting'] = (string)$data['no_of_waiting'] ?: '';
    //     $data['is_waiting'] = (bool)($campaign['is_waiting'] ?: 'false');
    //     dataResponse(200, 'valid', $data);
    // elseif ($queueOfCustomer['queue_status'] == 2):
    //     $data['no_of_queue'] = $queueOfCustomer['no_of_queue'];
    //     $data['no_of_waiting'] = "0";
    //     $data['is_waiting'] = (bool)($campaign['is_waiting'] ?: 'false');
    //     dataResponse(200, 'You\'re in queue', $data);
    // else:
    //     $data['is_passed'] = true;
    //     dataResponse(200, 'Passed', $data);
    // endif;



else:
    dataResponse(400, 'Missing param activity_id');
endif;
