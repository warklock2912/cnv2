<?php

class Crystal_NewsNotification_Block_Adminhtml_Customer_Brand_Tab
	extends Crystal_NewsNotification_Block_Adminhtml_Customer_Grid
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	/**
	 * Set the template for the block
	 *
	 */
	/**
	 * Disable filters and paging
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('customer_edit_tab_newsnotification_brand');
	}

	/**
	 * Return Tab label
	 *
	 * @return string
	 */
	public function getTabLabel()
	{
		return $this->__('News Notification Brands');
	}

	/**
	 * Return Tab title
	 *
	 * @return string
	 */
	public function getTabTitle()
	{
		return $this->__('News Notification Brands');
	}

	/**
	 * Can show tab in tabs
	 *
	 * @return boolean
	 */
	public function canShowTab()
	{
		$customer = Mage::registry('current_customer');
		return (bool)$customer->getId();
	}

	/**
	 * Tab is hidden
	 *
	 * @return boolean
	 */
	public function isHidden()
	{
		return false;
	}

	/**
	 * Prepare collection for grid
	 *
	 * @return Mage_Sales_Block_Adminhtml_Customer_Edit_Tab_Recurring_Profile
	 */
	protected function _prepareCollection()
	{
		$data = Mage::getModel('newsnotification/newsnotification')->getCollection()
			->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
		if (count($data))
			$category_array = array();
		else
			$category_array = null;
		foreach ($data as $item) {
			array_push($category_array, $item['category_id']);
		}
		$collection = Mage::getModel('mpblog/category')->getCollection()
			->addFieldToFilter('category_id', $category_array)
			->addFieldToFilter('category_for_app', 2);

		$this->setCollection($collection);
//		return parent::_prepareCollection();
		return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
	}

	/**
	 * Defines after which tab, this tab should be rendered
	 *
	 * @return string
	 */
	public function getAfter()
	{
		return 'tags';
	}

	/**
	 * Return grid url
	 *
	 * @return string
	 */
//	public function getGridUrl()
//	{
//		return $this->getUrl('*/sales_recurring_profile/customerGrid', array('_current' => true));
//	}
}