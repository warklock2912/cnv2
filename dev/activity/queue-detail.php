<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = Mage::getSingleton('customer/session')->getCustomer();
$customerId = $customer->getId();
if ($_REQUEST['activity_id']):
	$campaignId = $_REQUEST['activity_id'];
	$campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);

	$queueCollection = Mage::getModel('campaignmanage/queue')->getCollection()
		->addFieldToFilter('campaign_id', $campaignId);
	$collectionSize = $queueCollection->getSize();
	$queueCurrent = Mage::getModel('campaignmanage/queue')->getCollection()
		->addFieldToFilter('campaign_id', $campaignId)
		->addFieldToFilter('queue_status', 2)
		->getFirstItem();
	if ($queueCurrent->getId()):
		$data['no_of_waiting'] = $collectionSize - $queueCurrent->getNoOfQueue();
	else:
		$data['no_of_waiting'] = $collectionSize;
	endif;

    // check campaign is full ?
    $queueCollection = Mage::getModel('campaignmanage/queue')
        ->getCollection()
        ->addFieldToFilter('campaign_id', $campaign->getId());
    $noOfParticipants = $campaign->getNoOfPart();
    if (count($queueCollection) >= $noOfParticipants) {
        $data['is_fully_joined'] = true;
    } else
        $data['is_fully_joined'] = false;

	$dataArr = array();

	$data['id'] = $campaign->getId();
	$data['type'] = $campaign->getCampaignType();
	$data['content'] = $campaign->getContent();
	$data['activity_name'] = $campaign->getCampaignName();
	$locator = getLocator($campaign->getId());
	$data['locator_name'] = $locator->getTitle();
	$data['locator_longitude'] = $locator->getLongitude();
	$data['locator_latitude'] = $locator->getLatitude();
	$data['end_register_time'] = $campaign->getEndRegisterTime();
	$data['image'] = $campaign->getImage() ?
		Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'campaignmanage/images/' . $campaign->getImage() : null;
	$dataArr = $data;
	dataResponse(200, 'valid', $dataArr);
else:
	dataResponse(400, 'Missing param activity_id');
endif;