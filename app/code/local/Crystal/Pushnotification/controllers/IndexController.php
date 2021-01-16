<?php

class Crystal_Pushnotification_IndexController extends Mage_Core_Controller_Front_Action
{

	public function pushNotificationAction()
	{
		if ($this->getRequest()->isPost()) {
			$data_receive = json_decode($this->getRequest()->getRawBody());
			$msg = $data_receive->message;
			$deviceCollection = Mage::getModel('pushnotification/device')->getCollection();
			$data = array();
			$dataArr = array();
			foreach ($deviceCollection as $device) {
    			Mage::helper('pushnotification')->pushBlogNotification('Notification For IOS', $msg,array($device->getDeviceToken()) , 22);
				$this->saveNotificationList($device->getUserId(), 22, 0);
			}
			$res = json_encode(
				array('status' => 200, 'message' => 'successfully')
			);
		} else {
			$res = json_encode(
				array('status' => 400, 'message' => 'Invalid')
			);
		}
		$this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
		$this->getResponse()->setBody($res);
	}

	public function saveNotificationList($customerId, $blogId, $notificationStatus)
	{
		$notificationList = Mage::getModel('pushnotification/notificationlist');
		$currentTime = new Zend_Date();
		$type = 1;
		$notificationList->setCustomerId($customerId)
			->setCreatedAt($currentTime)
			->setType($type)
			->setContentId($blogId)
			->setNotificationStatus($notificationStatus);

		try {
			$notificationList->save();
		} catch (Exception $e) {

		}
	}
}