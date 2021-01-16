<?php
class CNV_CustomerGrid_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid{
    /**
     * override the _prepareCollection to add an other attribute to the grid
     * @return $this
     */

    protected function _prepareCollection(){
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->addAttributeToSelect('card_id')
            //if the attribute belongs to the customer, use the lines below
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            //if the attribute belongs to the customer address, comment the line above and use the one below
            //->joinAttribute('mobile', 'customer_address/mobile', 'default_billing', null, 'left')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
//            ->joinAttribute('card_id', 'customer/card_id', 'entity_id', null, 'left');

//        $collection->getSelect()
//            ->joinLeft(
//                array('table_alias' => 'crystal_card_id'),
//                'e.entity_id = table_alias.customer_id',
//                array('card_id' => 'table_alias.card_id')
//            );
//        var_dump($collection->getSelect()->__toString());die('sdsa');
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        /*
                //code from Mage_Adminhtml_Block_Widget_Grid::_prepareCollection()
                //since calling parent::_prepareCollection will render the code above useless
                //and you cannot call in php parent::parent::_prepareCollection()
                if ($this->getCollection()) {

                    $this->_preparePage();

                    $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
                    $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
                    $filter   = $this->getParam($this->getVarNameFilter(), null);

                    if (is_null($filter)) {
                        $filter = $this->_defaultFilter;
                    }

                    if (is_string($filter)) {
                        $data = $this->helper('adminhtml')->prepareFilterString($filter);
                        $this->_setFilterValues($data);
                    }
                    else if ($filter && is_array($filter)) {
                        $this->_setFilterValues($filter);
                    }
                    else if(0 !== sizeof($this->_defaultFilter)) {
                        $this->_setFilterValues($this->_defaultFilter);
                    }

                    if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                        $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
                        $this->_columns[$columnId]->setDir($dir);
                        $this->_setCollectionOrder($this->_columns[$columnId]);
                    }

                    if (!$this->_isExport) {
                        $this->getCollection()->load();
                        $this->_afterLoadCollection();
                    }
                }

                return $this;
        */
    }

    /**
     * override the _prepareColumns method to add a new column after the 'email' column
     * if you want the new column on a different position just change the 3rd parameter
     * of the addColumnAfter method to the id of your desired column
     */
    protected function _prepareColumns(){
//        $cardId = $this->_columns["entity_id"];
        $this->addColumnAfter('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname',
            'width'     =>'150px',
        ),'entity_id');
        $this->addColumnAfter('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname',
            'width'     =>'150px',
        ),'entity_id');
        $this->addColumnAfter('card_id', array(
            'header'    => Mage::helper('customer')->__('Card ID'),
            'index'     => 'card_id',
            'type'  => 'varchar',
            'filter_condition_callback' => array($this, '_filterCardId'),
        ),'vip_member_id');
        return parent::_prepareColumns();
    }
    public function getExportTypes()
    {
        if(Mage::getSingleton('admin/session')->isAllowed('admin/customer/export_customer')){
            return empty($this->_exportTypes) ? false : $this->_exportTypes;
        }else{
            return false;
        }
    }
    protected function _filterCardId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (!$value) {
            return $this;
        }
        $collection->getSelect()
            ->joinLeft(
                array('table_alias' => 'crystal_card_id'),
                'e.entity_id = table_alias.customer_id',
                array('card_id' => 'table_alias.card_id')
            )->where(
                "table_alias.card_id like ?"
                , "%$value%");
//        $this->getCollection()->getSelect()->where(
//            "table_alias.card_id like ?"
//            , "%$value%");
        return $this;
    }
}
