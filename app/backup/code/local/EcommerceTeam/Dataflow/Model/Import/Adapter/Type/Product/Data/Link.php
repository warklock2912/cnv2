<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Link
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    protected $_relationData = array();
    protected $_linkTypes = array();
    /** @var  string  */
    protected $_productTable;
    /** @var  string */
    protected $_linkTable;

    protected function _construct()
    {
        $this->_productTable  = $this->_config->getResource()->getTableName('catalog/product');
        $this->_linkTable     = $this->_config->getResource()->getTableName('catalog/product_link');
        $this->_linkTypes     = array(
            Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED,
            Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL,
            Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL,
        );

        $select = $this->_config->getResourceConnection()->select();
        $select->from($this->_config->getResource()->getTableName('catalog/product_link_type'),
            array('code', 'link_type_id'));
        $this->_linkTypes = $this->_config->getResourceConnection()->fetchPairs($select);

        parent::_construct();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        foreach ($this->_linkTypes as $linkTypeCode => $linkTypeId) {
            if (isset($data[$linkTypeCode]) || !empty($data[$linkTypeCode])) {
                if (self::DELETE_VALUE_FLAG == $data[$linkTypeCode]) {
                    $this->_relationData[$linkTypeId][$data['sku']] = null;
                } else {
                    $this->_relationData[$linkTypeId][$data['sku']] = explode(',', $data[$linkTypeCode]);
                }
            }
        }

        return $this;
    }

    /**
     * @param $skuToId
     * @return $this
     */
    public function afterProcess($skuToId)
    {
        if (!empty($this->_relationData)) {
            foreach ($this->_relationData as $linkTypeId  => $relationData) {
                foreach ($relationData as $productSku => $linkedProductSku) {
                    if (is_null($linkedProductSku)) {
                        $this->_writeConnection->delete($this->_linkTable,
                            $this->_writeConnection->quoteInto('product_id = ?', $skuToId[$productSku])
                            . ' AND '
                            . $this->_writeConnection->quoteInto('link_type_id = ?', $linkTypeId));
                    } else {
                        $select = $this->_writeConnection->select();
                        $select->from(array('product' => $this->_productTable), null);
                        $select->from(array('linked_product' => $this->_productTable), null);
                        $select->columns(array(
                            'link_type_id'      => new Zend_Db_Expr("\"{$linkTypeId}\""),
                            'product_id'        => 'product.entity_id',
                            'linked_product_id' => 'linked_product.entity_id',
                        ));
                        $select->where('product.sku = ?', $productSku);
                        $select->where('linked_product.sku IN (?)', $linkedProductSku);
                        $select->group('linked_product_id');

                        $this->_writeConnection->query($this->_writeConnection->insertFromSelect($select,
                            $this->_linkTable,
                            array('link_type_id', 'product_id', 'linked_product_id'),
                            Varien_Db_Adapter_Interface::INSERT_IGNORE
                        ));
                    }
                }
            }
        }

        return parent::afterProcess($skuToId);
    }
}