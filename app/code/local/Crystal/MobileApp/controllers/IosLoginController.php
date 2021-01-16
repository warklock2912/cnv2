<?php
class Crystal_MobileApp_IosLoginController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		Mage::getSingleton("core/session", array("name" => "frontend"));
		Mage::getSingleton('customer/session')->clear();
		$this->getResponse()->clearHeaders()->setHeader(
			'Content-type',
			'application/json'
		);
		$dataReturn = array(
			'status_code' => 400,
			'message' => 'valid',
			'accountInfomation' => array()
		);

		if (Mage::app()->getRequest()->isPost()) {
			$email = Mage::app()->getRequest()->getPost('email');
			$token = Mage::app()->getRequest()->getPost('token');
			if (!isset($email) || $email == '' || !isset($token) || $token == '') {
				$dataReturn['status_code'] = 404;
				$dataReturn['message'] = 'invalidPost';
				$this->getResponse()->setBody(json_encode($dataReturn));
				return;
			}

			$customer = Mage::getModel('customer/customer');
			$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
			$customer->loadByEmail($email);
			if (!$customer->getId()) {
				$firstname = substr($email, 0, strpos($email, "@"));
				$lastname = 'ios';
				$pwd_length = 7;
				//We're good to go with customer registration process
				$customer->setEmail($email);
				$customer->setFirstname($firstname);
				$customer->setLastname($lastname);
				$customer->setPassword($customer->generatePassword($pwd_length));
				//if process fails, we don't want to break the page
				try {
					$customer->save();
					$customer->setConfirmation(null);
					$customer->setMobileappIosToken($token);
					$customer->save();
					$customer->sendNewAccountEmail();
				} catch (Exception $e) {
					Mage::log($e->__toString());
					$dataReturn['message'] = 'invalid';
					$dataReturn['accountInfomation'] = 'Your are get something error.';
					$this->getResponse()->setBody(json_encode($dataReturn));
					return;
				}
				$dataReturn['status_code'] = 200;
			} elseif ($customer->getMobileappIosToken() == $token) {
				$dataReturn['status_code'] = 200;
			} else {
				$dataReturn['message'] = 'invalid';
				$dataReturn['accountInfomation'] = 'Token not matched.';
			}
		}
		$session = $this->_getSession();
        $session->logout()->renewSession();
		if ($dataReturn['status_code'] == 200) {
			try {
				$session->setCustomerAsLoggedIn($customer);
				$dataReturn['message'] = 'valid';
				$dataReturn['accountInfomation'] = $this->_getCustomerData($customer);
			} catch (Mage_Core_Exception $e) {
				Mage::log($e->__toString());
				$dataReturn['status_code'] = 400;
				$dataReturn['message'] = 'invalid';
				$dataReturn['accountInfomation'] = 'Login failed.';
			} catch (Exception $e) {
				Mage::log($e->__toString());
				$dataReturn['status_code'] = 400;
				$dataReturn['message'] = 'invalid';
				$dataReturn['accountInfomation'] = 'Login failed.';
			}
		}
		$this->getResponse()->setBody(json_encode($dataReturn));
	}

	protected function _getCustomerData($customer)
	{
		$data = array();
		$rewardPoint = Mage::getResourceModel('rewardpoints/customer_collection')->addFieldToFilter('customer_id', $customer->getId())->getFirstItem();
		$data['user_id'] = $customer->getId();
		$data['first_name'] = $customer->getFirstname();
		$data['last_name'] = $customer->getLastname();
		$data['email'] = $customer->getEmail();
		$data['gender'] = $customer->getGender();
		$data['telephone'] = $customer->getTelephone();
		$data['point'] = $rewardPoint->getPointBalance();
		$data['birth_day'] = $customer->getDob() ? date("Y-m-d", strtotime($customer->getDob())) : null;
		$data['vip_member'] = $customer->getVipMemberId();
		$data['is_vip_member'] = $customer->getGroupId() == 4 ? true : false;
		$data['profile_photo'] = $customer->getProfilePhoto() ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $customer->getProfilePhoto() : null;
		$customerCard = Mage::getModel('activity/activity')->getCollection()->addFieldToFilter('customer_id', $customer->getId())->getFirstItem();
		$data['cart_id'] = $customerCard->getCardId() ? $customerCard->getCardId() : null;
		$data['session'] = $this->_getSession()->getEncryptedSessionId();
		return $data;
	}

	protected function _addToken($customer)
	{
		$SID = Mage::getSingleton('core/session')->getEncryptedSessionId(); //current session id

		$resource = Mage::getSingleton('core/resource');
		$write = $resource->getConnection('core_write');
		$table = $resource->getTableName('customer_entity');
		$write->update(
			$table,
			['m_token' => $SID],
			['entity_id = ?' => $customer->getId()]
		);
	}

	/**
	 * Retrieve customer session model object
	 *
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}
}
