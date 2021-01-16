<?php
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct() {
        parent::__construct();
        $this->setId('ruffle_items_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->_getRuffle()->getId()) {
            $this->setDefaultFilter(array('selected_items' => 1));
        }
    }

    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_items') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _getRuffle() {
    	$id = $this->getRequest()->getParam('id');
    	return Mage::getModel('ruffle/ruffle')->load($id);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*');
            if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        // $collection->addAttributeToFilter('is_ruffle', 1);
        // $collection->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('selected_items', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'selected_items',
            'values'            => $this->_getSelectedProducts(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

        $this->addColumn('qty', array(
            'header'        => Mage::helper('catalog')->__('Qty'),
            'type'          => 'number',
            'index'         => 'qty'
        ));

        $this->addColumn('general_qty', array(
            'header'            => Mage::helper('catalog')->__('General QTY'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'general_qty',
            'width'             => 60,
            'editable'          => true,
        ));

        $this->addColumn('vip_qty', array(
            'header'            => Mage::helper('catalog')->__('VIP QTY'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'vip_qty',
            'width'             => 60,
            'editable'          => true,
        ));

        return parent::_prepareColumns();
    }

	public function getGridUrl() {
        return $this->getUrl('*/*/productGrid', array('_current' => true));
    }

    protected function _getSelectedProducts() {
        $products = $this->getRuffleItems();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedRuffleItems());
        }
        return $products;
    }

    public function getSelectedRuffleItems() {
        $products = array();

        $ruffleItems = $this->_getRuffle()->getRuffleItems();
        if (is_array($ruffleItems)) {
            foreach ($this->_getRuffle()->getRuffleItems() as $productId => $qtyValue) {
                $qtyValue = base64_decode($qtyValue);
                $qty = Mage::helper('core/string')->parseQueryStr($qtyValue);
                $products[$productId] = array('vip_qty' => $qty['vip_qty'], 'general_qty' => $qty['general_qty']);
            }
        }
    	
        return $products;
    }
}