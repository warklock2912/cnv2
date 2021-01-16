<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Initialization
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_shipment_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_shipment_grid_collection';
    }

    /**
     * Prepare and set collection of grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $collection **/
        $collection = Mage::getResourceModel('sales/order_shipment_collection');
        $collection->join(
            'shipment_grid',
            'main_table.increment_id = shipment_grid.increment_id',
            array(
                'shipment_grid.order_increment_id',
                'shipment_grid.order_created_at',
                'shipment_grid.shipping_name'
            )
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare and add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Shipment #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Date Shipped'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('sales')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ));

        $this->addColumnAfter(
            'consignment_no',
            array(
                'header' => Mage::helper('sales')->__('Consignment No'),
                'index' => 'consignment_no',
                'column_css_class' => 'consignment-no'
            ),
            'total_qty'
        );
        $this->addColumnAfter(
            'booking_created_at',
            array(
                'header' => Mage::helper('sales')->__('Booking At'),
                'index' => 'booking_created_at',
                'type'      => 'datetime',
            ),
            'consignment_no'
        );
        $this->addColumnAfter(
            'cod_amount',
            array(
                'header' => Mage::helper('sales')->__('COD'),
                'index' => 'cod_amount',
                'type'  => 'currency',
                'currency' => 'base_currency_code',
            ),
            'booking_created_at'
        );
        $this->addColumnAfter(
            'box_sum',
            array(
                'header' => Mage::helper('sales')->__('BOX'),
                'index' => 'box_sum',
                'type'  => 'number',
                'align' => 'center',
            ),
            'cod_amount'
        );
        $this->addColumnAfter(
            'print_awb',
            array(
                'header'    => Mage::helper('sales')->__('Sticker'),
                'width'     => '50px',
                'type'      => 'action',
                'column_css_class'     => 'print-awb',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('Print'),
                        'url'     => array('base' => 'kerry/adminhtml_bill/print'),
                        'field'   => 'shipment_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ),
            'box_sum'
        );

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url'     => array('base'=>'*/sales_shipment/view'),
                        'field'   => 'shipment_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ));
        $this->sortColumnsByOrder();
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Get url for row
     *
     * @param string $row
     * @return string
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/shipment')) {
            return false;
        }

        return $this->getUrl('*/sales_shipment/view',
            array(
                'shipment_id'=> $row->getId(),
            )
        );
    }

    /**
     * Prepare and set options for massaction
     *
     * @return Mage_Adminhtml_Block_Sales_Shipment_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('shipment_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
            'label'=> Mage::helper('sales')->__('PDF Packingslips'),
            'url'  => $this->getUrl('*/sales_shipment/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
            'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
            'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }

    /**
     * Get url of grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', array('_current' => true));
    }

}
