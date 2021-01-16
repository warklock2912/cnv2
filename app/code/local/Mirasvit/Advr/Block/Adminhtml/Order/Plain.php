<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Reports
 * @version   1.0.27
 * @build     822
 * @copyright Copyright (C) 2017 Mirasvit (http://mirasvit.com/)
 */



/**
 * Class Mirasvit_Advr_Block_Adminhtml_Order_Plain.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Mirasvit_Advr_Block_Adminhtml_Order_Plain extends Mirasvit_Advr_Block_Adminhtml_Block_Container
{
    public function getTotals()
    {
        $fields = array();
        $totals = new Varien_Object();
        $columns = $this->getColumns();
        foreach ($columns as $code => $column) {
            if (isset($column['type']) && in_array($column['type'], array('currency', 'number'))) {
                $fields[$code] = '0';
            }
        }

        foreach ($this->getCollection() as $order) {
            foreach($fields as $field => $value){
                $fields[$field] += $order->getData($field);
            }
        }

        $totals->setData($fields);
        $this->setTotals($totals);

        return $totals;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setHeaderText(Mage::helper('advr')->__('Orders'));

        return $this;
    }

    protected function prepareChart()
    {
        $this->setChartType('column');

        $this->initChart()
            ->setXAxisType('order')
            ->setXAxisField('increment_id');

        return $this;
    }

    protected function prepareGrid()
    {
        $this->initGrid()
            ->setDefaultSort('base_grand_total')
            ->setDefaultDir('desc')
            ->setPagerVisibility(true)
            ->setRowUrlCallback(array($this, 'rowUrlCallback'));

        return $this;
    }

    public function _prepareCollection()
    {
        $columns = $this->getColumns();
        $filterData = clone $this->getFilterData();
        $collection = Mage::getModel('advr/report_sales')->setBaseTable('sales/order', true);
        $tableDescription = $collection->getConnection()->describeTable($collection->getTable('sales/order'));

        // Add every report column to collection
        foreach (array_merge($this->getVisibleColumns(),array('entity_id')) as $column) {
            $data = $columns[$column];
            if (isset($tableDescription[$column])) {
                $data['expression'] = 'sales_order_table.'.$column;
                $data['table'] = 'sales/order';
                if(isset($data['type']) &&
                    $data['type'] == 'currency' &&
                    strpos($column, 'base') !== false) {
                    // please look at Mirasvit_Advr_Model_Report_Abstract::getExpression()
                    $data['expression'] = '(' . $data['expression'] . ')';
                }
            }
            $data['label'] = $data['header'];
            $collection->addColumn($column, $data);
        }

        $collection->setFilterData($filterData->unsOrders(), false, true) // Unset extra data from filters
            ->selectColumns(array_merge($this->getVisibleColumns(),array('entity_id')))
            ->groupByColumn('entity_id');

        $this->applyFilter($collection);

        $this->setCollection($collection);

        return $collection;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function getColumns()
    {
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gteq' => 0))
            ->load()
            ->toOptionHash();

        $paymentMethods = Mage::getSingleton('payment/config')->getActiveMethods();
        $paymentMethodOptions = array();

        foreach (array_keys($paymentMethods) as $paymentCode) {
            $paymentMethodOptions[$paymentCode] = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
        }

        $columns = array(
            'increment_id' => array(
                'header' => Mage::helper('advr')->__('Order #'),
                'totals_label' => Mage::helper('advr')->__('Totals'),
            ),

            'entity_id' => array(
                'header' => Mage::helper('advr')->__('Order ID'),
                'totals_label' => Mage::helper('advr')->__(''),
                'hidden' => true,
            ),

            'invoice_increment_id' => array(
                'header' => Mage::helper('advr')->__('Invoice #'),
                'expression' => 'sales_invoice_table.increment_id',
                'table' => 'sales/invoice',
                'hidden' => true,
                'column_css_class' => 'nobr',
            ),

            'customer_firstname' => array(
                'header' => Mage::helper('advr')->__('Firstname'),
                'column_css_class' => 'nobr',
            ),

            'customer_lastname' => array(
                'header' => Mage::helper('advr')->__('Lastname'),
                'column_css_class' => 'nobr',
            ),

            'customer_email' => array(
                'header' => Mage::helper('advr')->__('Email'),
                'column_css_class' => 'nobr',
            ),

            'customer_group_id' => array(
                'header' => Mage::helper('advr')->__('Customer Group'),
                'type' => 'options',
                'options' => $groups,
                'column_css_class' => 'nobr',
            ),

            'customer_taxvat' => array(
                'header' => Mage::helper('advr')->__('Tax/VAT number'),
                'hidden' => true,
            ),

            'created_at' => array(
                'header' => Mage::helper('advr')->__('Purchased On'),
                'type' => 'datetime',
                'column_css_class' => 'nobr',
                'export_callback' => array($this, 'createdAt'),
                'totals_label' => '',
            ),

            'state' => array(
                'header' => Mage::helper('advr')->__('State'),
                'type' => 'options',
                'options' => Mage::getSingleton('sales/order_config')->getStates(),
                'hidden' => true,
            ),

            'status' => array(
                'header' => Mage::helper('advr')->__('Status'),
                'type' => 'options',
                'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            ),

            'products' => array(
                'header' => Mage::helper('advr')->__('Item(s)'),
                'sortable' => false,
                'filter' => false,
                'expression' => 'sales_order_table.entity_id',
                'frame_callback' => array($this, 'products'),
                'export_callback' => array($this, 'products'),
                'hidden' => true,
            ),

            'tracking_number' => array(
                'header' => Mage::helper('advr')->__('Tracking Number'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => array($this, 'trackingNumber'),
                'export_callback' => array($this, 'trackingNumber'),
                'hidden' => true,
            ),

            'payment_method' => array(
                'type' => 'options',
                'header' => Mage::helper('advr')->__('Payment Type'),
                'hidden' => true,
                'options' => $paymentMethodOptions,
                'expression' => 'sales_order_payment_table.method',
                'table' => 'sales/order_payment',
            ),

            'total_qty_ordered' => array(
                'header' => Mage::helper('advr')->__('Quantity Ordered'),
                'type' => 'number',
            ),

            'base_tax_amount' => array(
                'header' => Mage::helper('advr')->__('Tax'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_shipping_amount' => array(
                'header' => Mage::helper('advr')->__('Shipping'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_discount_amount' => array(
                'header' => Mage::helper('advr')->__('Discount'),
                'type' => 'currency',
            ),

            'base_total_refunded' => array(
                'header' => Mage::helper('advr')->__('Refunded'),
                'type' => 'currency',
            ),

            'base_total_paid' => array(
                'header' => Mage::helper('advr')->__('Paid'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_total_invoiced' => array(
                'header' => Mage::helper('advr')->__('Total Invoiced'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_grand_total' => array(
                'header' => Mage::helper('advr')->__('Grand Total'),
                'type' => 'currency',
                'chart' => true,
            ),

            'gross_profit' => array(
                'header' => Mage::helper('advr')->__('Gross Profit'),
                'type' => 'currency',
                'frame_callback' => array(Mage::helper('advr/callback'), 'discount'),
                'expression' => '(sales_order_table.base_subtotal_invoiced - sales_order_table.base_total_invoiced_cost)',
                'discount_from' => 'base_grand_total',
                'table' => 'sales_order_table',
                'chart' => false,
            ),
        );

        $columns['actions'] = array(
            'header' => 'Actions',
            'hidden' => true,
            'actions' => array(
                array(
                    'caption' => Mage::helper('advr')->__('View'),
                    'callback' => array($this, 'rowUrlCallback'),
                ),
            ),
        );

        return $columns;
    }

    public function createdAt($value, $row, $column)
    {
        $data = Mage::app()->getLocale()
            ->date($row->getCreatedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)->toString();
        return $data;
    }

    private function applyFilter($collection)
    {
        if (null !== $this->getRequest()->getPost('orders')) {
            $collection->addFieldToFilter('sales_order_table.entity_id', array('in' => explode(',', ($this->getRequest()->getPost('orders')))));
        }
    }

    public function rowUrlCallback($row)
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getEntityId()));
    }

    public function products($value, $row, $column)
    {
        $data = array();
        $row = Mage::getModel('sales/order')->load($row->getEntityId());
        $collection = $row->getAllVisibleItems();
        foreach ($collection as $item) {
            $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $item->getProductId()));
            $data[] = '<a class="nobr" target="_blank" href="'.$url.'">'
                .$item->getSku()
                .' / '
                .Mage::helper('core/string')->truncate($item->getName(), 50)
                .' / '.intval($item->getQtyOrdered())
                .' × '.Mage::helper('core')->currency($item->getBasePrice())
                .'</a>';
        }

        return implode('<br>', $data);
    }

    public function trackingNumber($value, $row, $column)
    {
        $trackNumbers = array();

        $row = Mage::getModel('sales/order')->load($row->getEntityId());
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($row);

        foreach ($shipmentCollection as $shipment) {
            foreach ($shipment->getAllTracks() as $trackNumber) {
                $trackNumbers[] = $trackNumber->getNumber();
            }
        }

        return implode('<br>', $trackNumbers);
    }

    public function getFilterColumns()
    {
        // Restrict columns available only for this report
        return array_intersect_key($this->getCollection()->getColumns(), $this->getColumns());
    }
}
