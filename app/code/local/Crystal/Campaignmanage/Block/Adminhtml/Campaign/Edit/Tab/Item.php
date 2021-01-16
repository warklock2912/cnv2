<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Tab_Item extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('ruffle_items_grid');
		$this->setDefaultSort('entity_id');
		$this->setUseAjax(true);

	}

	protected function _addColumnFilterToCollection($column)
	{
		// Set custom filter for in product flag
		if ($column->getId() == 'selected_items') {
			$productIds = $this->_getSelectedProducts();
			if (empty($productIds)) {
				$productIds = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
			} else {
				if ($productIds) {
					$this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
				}
			}
		} else {
			parent::_addColumnFilterToCollection($column);
		}
		return $this;
	}

	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$this->setChild('add_item_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label' => Mage::helper('adminhtml')->__('Add Selected Item(s)'),
					'onclick' => 'addSelectedItems()'

				))
		);
	}

	public function getAddSelectedButton()
	{
		return $this->getChildHtml('add_item_button');
	}

	public function getMainButtonsHtml()
	{
		$html = '';
		if (!count($this->getProducts()))
			$html = $this->getAddSelectedButton();
		$html .= parent::getMainButtonsHtml();
		return $html;
	}

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

	public function getCategory()
	{
		$campaign = Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'));
		return Mage::getModel('catalog/category')->load($campaign->getCategoryId());
	}

	protected function _prepareCollection()
	{
		$categoryId = $this->getCategory()->getId();
		$collection = Mage::getModel('catalog/category')
			->load($categoryId)
			->getProductCollection()
			->addAttributeToSelect('*');
		if (count($this->getProducts())) {
			$productIds = $this->getProductIds();
			$collection->addFieldToFilter('entity_id', array('in' => $productIds));
		} else {
			Mage::getSingleton('cataloginventory/stock')
				->addInStockFilterToCollection($collection);
		}

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		if (!count($this->getProducts()))
			$this->addColumn('selected_items', array(
				'header_css_class' => 'a-center',
				'type' => 'checkbox',
				'name' => 'selected_items',
				'values' => $this->_getSelectedProducts(),
				'hidden' => true,
				'align' => 'center',
				'index' => 'price'
			));

		$this->addColumn('entity_id', array(
			'header' => Mage::helper('catalog')->__('ID'),
			'sortable' => true,
			'width' => 60,
			'index' => 'entity_id'
		));

		$this->addColumn('name', array(
			'header' => Mage::helper('catalog')->__('Name'),
			'index' => 'name'
		));

		$this->addColumn('type', array(
			'header' => Mage::helper('catalog')->__('Type'),
			'width' => 100,
			'index' => 'type_id',
			'type' => 'options',
			'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
		));

		$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
			->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
			->load()
			->toOptionHash();

		$this->addColumn('set_name', array(
			'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
			'width' => 130,
			'index' => 'attribute_set_id',
			'type' => 'options',
			'options' => $sets,
		));

		$this->addColumn('status', array(
			'header' => Mage::helper('catalog')->__('Status'),
			'width' => 90,
			'index' => 'status',
			'type' => 'options',
			'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
		));

		$this->addColumn('visibility', array(
			'header' => Mage::helper('catalog')->__('Visibility'),
			'width' => 90,
			'index' => 'visibility',
			'type' => 'options',
			'options' => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
		));

		$this->addColumn('sku', array(
			'header' => Mage::helper('catalog')->__('SKU'),
			'width' => 80,
			'index' => 'sku'
		));

		$this->addColumn('price', array(
			'header' => Mage::helper('catalog')->__('Price'),
			'type' => 'currency',
			'currency_code' => (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
			'index' => 'price'
		));
		return parent::_prepareColumns();
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current' => true));
	}

	protected function _getSelectedProducts()
	{
		return array();
	}

	public function getSelectedRuffleItems()
	{
		$products = array();
		return $products;
	}
}