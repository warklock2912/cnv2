<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Raffle_Raffle extends Mage_Adminhtml_Block_Widget
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('campaign_raffle_raffle');
		$this->setTemplate('campaignmanage/raffle.phtml');
	}

	protected function _prepareLayout()
	{
		$this->setChild('raffle_random_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label' => Mage::helper('campaignmanage')->__('Random Winner(s)'),
					'name' => 'raffle_random',
					'element_name' => 'raffle_random',
					'onclick' => 'setLocation(\' '  . $this->getUrl('*/*/randomAllWinner', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
					'class' => 'raffle_random',
				))
		);
		$this->setChild('raffle_send_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label' => Mage::helper('campaignmanage')->__('Send Notification To All Winner(s)'),
					'name' => 'raffle_send',
					'element_name' => 'raffle_send',
					'class' => 'raffle_send',
				))
		);
		return parent::_prepareLayout();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getCampaignId()
	{
		return $this->getRequest()->getParam('id');
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getCampaignStatus()
	{
		return Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'))->getStatus();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getProducts()
	{
		$collection = Mage::getModel('campaignmanage/products')->getCollection()
			->addFieldToFilter('campaign_id', $this->getRequest()->getParam('id'));
		return $collection;
	}

	public function getProductIds()
	{
		$productIds = array();
		$products = $this->getProducts();
		foreach ($products as $product) {
			$productIds[] = $product->getProductId();
		}
		return $productIds;
	}

	public function getOptions($productId)
	{
		$options = array();
		$optionArr = array();
		$product = Mage::getModel('campaignmanage/products')->getCollection()
			->addFieldToFilter('campaign_id', $this->getRequest()->getParam('id'))
			->addFieldToFilter('product_id', $productId)
			->getFirstItem();
		$optionArr = explode(";", $product->getOption());
		foreach ($optionArr as $option) {
			$option = explode(",", $option);
			$options[] = $option;
		}
		return $options;
	}



	public function getListWinnerByOption($optionValue,$productId)
	{
		$collection = Mage::getModel('campaignmanage/raffle')->getCollection()
			->addFieldToFilter('campaign_id', $this->getCampaignId())
			->addFieldToFilter('product_id',$productId)
			->addFieldToFilter('option', $optionValue)
			->addFieldToFilter('is_winner', true);
		return $collection;
	}

	public function getListWinnerByProduct($productId){
		$collection = Mage::getModel('campaignmanage/raffle')->getCollection()
			->addFieldToFilter('campaign_id', $this->getCampaignId())
			->addFieldToFilter('product_id', $productId)
			->addFieldToFilter('is_winner', true);
		return $collection;
	}

	public function getCategory()
	{
		$campaign = Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'));
		return Mage::getModel('catalog/category')->load($campaign->getCategoryId());
	}

	public function getProductCollection()
	{
		$collection = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*');
		$productIds = $this->getProductIds();
		$collection->addFieldToFilter('entity_id', array('in' => $productIds));
		return $collection;
	}

	/**
	 * @return string
	 */
	public function getSendNotificationButton()
	{
		return $this->getChildHtml('raffle_send_button');
	}

	/**
	 * @return string
	 */
	public function getRaffleRandomButtonHtml()
	{
		return $this->getChildHtml('raffle_random_button');
	}

	/**
	 * @return string
	 */
	public function getTabLabel()
	{
		return Mage::helper('campaignmanage')->__('Raffle');
	}

	/**
	 * @return string
	 */
	public function getTabTitle()
	{
		return Mage::helper('campaignmanage')->__('Raffle');
	}

	/**
	 * @return bool
	 */
	public function canShowTab()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function isHidden()
	{
		return false;
	}
}