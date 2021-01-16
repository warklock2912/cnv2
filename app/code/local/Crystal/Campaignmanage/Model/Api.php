<?php
class Crystal_Campaignmanage_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	/**
	 * @return array
	 */
	public function items(){
		$campaigns =Mage::getModel("campaignmanage/campaign")
			->getCollection()
			->addFieldToSelect('*')
			->setOrder('campaign_id', 'DESC')
			->setPageSize(20);

		foreach ($campaigns as $campaign) {
			$arr_campaigns[] = $campaign->toArray();
		}

		return $arr_campaigns;
	}

	/**
	 * @param $campaignId
	 * @return array
	 * @throws Mage_Api_Exception
	 */
	public function getInfo($campaignId){
		$campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
		if(!$campaign->getId()){
			$this->_fault('not exists');
		}
		return $campaign->toArray();
	}

	/**
	 * @param $campaignData
	 * @return mixed
	 * @throws Mage_Api_Exception
	 */
	public function create($campaignData){
		try {
			$campaign = Mage::getModel('campaignmanage/campaign')
				->setData($campaignData)
				->save();
		} catch (Mage_Core_Exception $e){
			$this->_fault('data_invalid',$e->getMessages());
		} catch (Exception $e){
			$this->_fault('data_invalid',$e->getMessage());
		}

		return $campaign->getId();
	}

	/**
	 * @param $campaignId
	 * @param $campaignData
	 * @return bool
	 * @throws Mage_Api_Exception
	 */
	public function update($campaignId, $campaignData)
	{
		$campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);

		if (!$campaign->getId()) {
			$this->_fault('not_exists');
			// No customer found
		}

		$campaign->addData($campaignData)->save();
		return true;
	}
	/**
	 * @param $productId
	 * @return bool
	 * @throws Mage_Api_Exception
	 */
	public function delete($campaignId){
		$campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
		if(!$campaign->getId()){
			return $this->_fault('not exists');
		}
		try{
			$campaign->delete();
		} catch (exception $e){
			$this->_fault('not_deleted',$e->getMessage());
		}
		return true;
	}
}