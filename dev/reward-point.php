<?php
/**
 * Created by PhpStorm.
 * User: bach95
 * Date: 31/08/2018
 * Time: 11:19
 */
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));

if ($_REQUEST['id']) {
	$customerId = $_REQUEST['id'];

	$transactionCollection = Mage::getResourceModel('rewardpoints/transaction_collection')
		->addFieldToFilter('customer_id', $customerId)
		->setOrder('created_time', 'DESC');
	$rules = Mage::getModel('rewardpoints/rate')->getCollection();

	$rewardPoint = Mage::getResourceModel('rewardpoints/customer_collection')->addFieldToFilter('customer_id', $customerId)->getFirstItem();
	$data = array();
	$data['point'] = $rewardPoint->getPointBalance();
	$ruleData = null;
	foreach ($rules as $rule):
		if ($rule->getId() == 1) {
			$ruleData['type'] = 'earn';
			$ruleData['point'] = $rule->getPoints();
			$ruleData['money'] = $rule->getMoney();
		}
		if ($rule->getId() == 2) {
			$ruleData['type'] = 'spent';
			$ruleData['point'] = $rule->getPoints();
			$ruleData['money'] = $rule->getMoney();
		}
		$data['rule'][] = $ruleData;
	endforeach;
	if (count($transactionCollection)) {
		$earnPointArr = array();
		$spentPointArr = array();
		foreach ($transactionCollection as $transaction):
			$action_type = $transaction->getActionType();
			$dataPointArr = array();
			if ($transaction->getPointAmount() > 0) {

				$earnPointData['type'] = 'earn';
				$earnPointData['id'] = $transaction->getId();
				if ($transaction->getOrderIncrementId())
					$earnPointData['title'] = $transaction->getOrderIncrementId();
				else
					$earnPointData['title'] = $transaction->getTitle();
				$earnPointData['create_at'] = $transaction->getCreatedTime();
				$earnPointData['point'] = $transaction->getPointAmount();
				$dataPointArr = $earnPointData;
			}
			if ($transaction->getPointAmount() < 0) {
				$spentPointData['type'] = 'spent';
				$spentPointData['id'] = $transaction->getId();
				if ($transaction->getOrderIncrementId())
					$spentPointData['title'] = $transaction->getOrderIncrementId();
				else
					$spentPointData['title'] = $transaction->getTitle();
				$spentPointData['create_at'] = $transaction->getCreatedTime();
				$spentPointData['point'] = $transaction->getPointAmount();
				$dataPointArr = $spentPointData;
			}
			$data['pointArr'][] = $dataPointArr;
		endforeach;
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'rewardPointData' => $data));
	} else {

		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'No Data', 'rewardPointData' => $data));
	}

} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid '));
}
