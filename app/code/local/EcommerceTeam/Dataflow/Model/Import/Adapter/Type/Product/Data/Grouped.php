<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Grouped
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var  string  */
    protected $_productTable;
    /** @var  string */
    protected $_linkTable;
    /** @var  string */
    protected $_linkType;
    /** @var  string */
    protected $_productRelationTable;

    protected $_assignedProductData;

    protected function _construct()
    {
        $this->_productTable  = $this->_config->getResource()->getTableName('catalog/product');
        $this->_linkTable     = $this->_config->getResource()->getTableName('catalog/product_link');
        $this->_linkType      = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;
        $this->_productRelationTable = $this->_config->getResource()->getTableName('catalog/product_relation');
        $this->_assignedProductData = array();

        parent::_construct();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        if (!empty($data['associated_simple_products'])) {
            $this->_assignedProductData[$data['sku']] = explode(',', $data['associated_simple_products']);
        }

        return $this;
    }

    /**
     * @param $skuToId
     * @return $this
     * @throws EcommerceTeam_Dataflow_Model_Import_Adapter_Exception
     */
    public function afterProcess($skuToId)
    {
        if (!empty($this->_assignedProductData)) {
            foreach ($this->_assignedProductData as $sku => $assignedProducts) {
                $this->_writeConnection->delete($this->_linkTable,
                                $this->_writeConnection->quoteInto('product_id = ?', $skuToId[$sku])
                                . ' AND '
                                . $this->_writeConnection->quoteInto('link_type_id = ?', $this->_linkType));
                $this->_writeConnection->delete($this->_productRelationTable,
                                $this->_writeConnection->quoteInto('parent_id = ?', $skuToId[$sku]));
                foreach ($assignedProducts as $assignedProduct) {
                    if (isset($skuToId[$assignedProduct])) {
                        $values = array(
                            'product_id'                => $skuToId[$sku],
                            'linked_product_id'         => $skuToId[$assignedProduct],
                            'link_type_id'              => $this->_linkType,
                        );
                        $this->_writeConnection->insert($this->_linkTable, $values);
                        $values = array(
                            'parent_id'         => $skuToId[$sku],
                            'child_id'          => $skuToId[$assignedProduct],
                        );
                        $this->_writeConnection->insert($this->_productRelationTable, $values);
                    }
                }
            }
        }

        return parent::afterProcess($skuToId);
    }
}