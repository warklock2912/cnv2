<?php

class Crystal_NewsNotification_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('NewsNotificationGrid');
		$this->setDefaultSort('category_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(TRUE);
	}


	public function getMainButtonsHtml() {
		$html = parent::getMainButtonsHtml();
		$add_button = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setData(array(
				'label'     => Mage::helper('adminhtml')->__('Add'),
				'onclick'   => 'setLocation(\'' . $this->getUrl('*/newsnotification') .'\')',
			));
		$html .= $add_button->toHtml();
		return $html;
	}


	protected function _prepareColumns()
	{
		$this->addColumn('category_id', array(
			'header' => Mage::helper('newsnotification')->__('Category ID'),
			'align' => 'left',
			'width' => '50px',
			'index' => 'category_id',
		));
		$this->addColumn('name', array(
			'header' => Mage::helper('newsnotification')->__('Category Name'),
			'align' => 'left',
			'index' => 'name',
		));


		return parent::_prepareColumns();
	}

}