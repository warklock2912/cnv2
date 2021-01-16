<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('amseogooglesitemap');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('amseogooglesitemap/sitemap')->getCollection();
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header' => Mage::helper('amseogooglesitemap')->__('ID'),
			'align'  => 'left',
			'index'  => 'id',
			'width'  => '50px'
		));

		if (! Mage::app()->isSingleStoreMode()) {
			$this->addColumn('stores', array(
				'header'     => Mage::helper('amseogooglesitemap')->__('Store'),
				'index'      => 'stores',
				'type'       => 'store',
				'store_view' => true,
			));
		}

		$this->addColumn('title', array(
			'header' => Mage::helper('amseogooglesitemap')->__('Title'),
			'align'  => 'left',
			'index'  => 'title',
		));

		$this->addColumn('folder_name', array(
			'header'   => Mage::helper('amseogooglesitemap')->__('Path'),
			'align'    => 'left',
			'width'    => '80px',
			'index'    => 'folder_name'
		));

		$this->addColumn('result_link', array(
			'header'   => Mage::helper('amseogooglesitemap')->__('Url'),
			'align'    => 'left',
			'width'    => '80px',
			'index'    => 'result_link',
			'renderer' => 'Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Grid_Renderer_File'
		));

		$this->addColumn('last_run', array(
			'header' => Mage::helper('amseogooglesitemap')->__('Generated At'),
			'align'  => 'left',
			'index'  => 'last_run',
		));


		/*$this->addColumn('run_link', array(
			'header'   => Mage::helper('amseogooglesitemap')->__('Run now'),
			'align'    => 'left',
			'width'    => '80px',
			'index'    => 'result_link',
			'renderer' => 'Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Grid_Renderer_Run'
		));*/

		$this->addColumn('action',
			array(
				'header'    => Mage::helper('amseogooglesitemap')->__('Action'),
				'width'     => '150',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption' => Mage::helper('amseogooglesitemap')->__('Generate'),
						'url'     => array('base' => 'adminhtml/amseogooglesitemap_sitemap/run'),
						'field'   => 'id'
					),
					array(
						'caption' => Mage::helper('amseogooglesitemap')->__('Duplicate'),
						'url'     => array('base' => '*/*/massDuplicate'),
						'field'   => 'id'
					),
					array(
						'caption' => Mage::helper('amseogooglesitemap')->__('Edit'),
						'url'     => array('base' => '*/*/edit'),
						'field'   => 'id'
					),
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
			));

		$this->addExportType('*/*/exportCsv', Mage::helper('amseogooglesitemap')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('amseogooglesitemap')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('sitemaps');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'   => Mage::helper('amseogooglesitemap')->__('Delete'),
			'url'     => $this->getUrl('*/*/massDelete'),
			'confirm' => Mage::helper('amseogooglesitemap')->__('Are you sure?')
		));

		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}