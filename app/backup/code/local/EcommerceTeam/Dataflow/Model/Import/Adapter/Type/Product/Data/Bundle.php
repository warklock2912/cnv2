<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Bundle
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var  string  */
    protected $_productTable;
    /** @var  string */
    protected $_optionTable;
    /** @var  string */
    protected $_optionValueTable;
    /** @var  string */
    protected $_selectionTable;
    /** @var  string */
    protected $_selectionPriceTable;
    /** @var  string */
    protected $_productRelationTable;

    protected $_bundleData;
    protected $_optionTypes;

    protected function _construct()
    {
        $this->_productTable            = $this->_config->getResource()->getTableName('catalog/product');
        $this->_optionTable             = $this->_config->getResource()->getTableName('bundle/option');
        $this->_optionValueTable        = $this->_config->getResource()->getTableName('bundle/option_value');
        $this->_selectionTable          = $this->_config->getResource()->getTableName('bundle/selection');
        $this->_selectionPriceTable     = $this->_config->getResource()->getTableName('bundle/selection_price');
        $this->_productRelationTable    = $this->_config->getResource()->getTableName('catalog/product_relation');
        $this->_bundleData = array();

        $types = Mage::getSingleton('bundle/source_option_type')->toOptionArray();
        foreach ($types as $type) {
            $this->_optionTypes[] = $type['value'];
        }

        parent::_construct();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        if (!empty($data['bundle_data'])) {
            $this->_bundleData[$data['sku']] = explode('|', $data['bundle_data']);
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
        $stores = $this->_config->getStores();
        if (!empty($this->_bundleData)) {
            foreach ($this->_bundleData as $sku => $options) {
                $parentProductId = $skuToId[$sku];
                $this->_writeConnection->delete($this->_optionTable,
                                $this->_writeConnection->quoteInto('parent_id = ?', $parentProductId));
                $this->_writeConnection->delete($this->_productRelationTable,
                                $this->_writeConnection->quoteInto('parent_id = ?', $parentProductId));
                foreach ($options as $option) {
                    $option = str_replace("'", '"', $option);
                    $optionData = json_decode($option, true);
                    if ($optionData) {
                        if (in_array($optionData['type'], $this->_optionTypes)) {

                            $values = array(
                                'parent_id'           => $parentProductId,
                                'type'                => $optionData['type'],
                                'required'            => isset($optionData['required']) ? $optionData['required'] : 0,
                                'position'            => isset($optionData['position']) ? $optionData['position'] : 0,
                            );
                            $this->_writeConnection->insertOnDuplicate($this->_optionTable, $values);
                            $optionId = $this->_writeConnection->lastInsertId();
                            $titles = array();
                            if (isset($optionData['titles'])) {
                                $titles = $optionData['titles'];
                            }
                            if (isset($optionData['title'])) {
                                $titles[0] = array('title' => $optionData['title']);
                            }
                            foreach ($titles as $title) {
                                $values = array(
                                    'option_id'       => $optionId,
                                    'title'           => Mage::helper('ecommerceteam_dataflow')->processTitle($title['title']),
                                    'store_id'        => (isset($title['store']) && isset($stores[$title['store']])) ? $stores[$title['store']] : 0,
                                );
                                $this->_writeConnection->insertOnDuplicate($this->_optionValueTable, $values);
                            }

                            $selections = array();
                            if (isset($optionData['selections'])) {
                                $selections = $optionData['selections'];
                                /**
                                 * 0 - product sku
                                 * 1 - position
                                 * 2 - is_default
                                 * 3 - price_type
                                 * 4 - price
                                 * 5 - qty
                                 * 6 - can_change_qty
                                 */
                                foreach ($selections as $selection) {
                                    $selection = explode(',', $selection);
                                     if (!isset($skuToId[$selection[0]])) {
                                        throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception(sprintf('Associated sku "%s" is not found', $selection[0]));
                                    }
                                    $values = array(
                                        'option_id'                => $optionId,
                                        'parent_product_id'        => $parentProductId,
                                        'product_id'               => $skuToId[$selection[0]],
                                        'position'                 => $selection[1],
                                        'is_default'               => $selection[2],
                                        'selection_price_type'     => $selection[3],
                                        'selection_price_value'    => $selection[4],
                                        'selection_qty'            => $selection[5],
                                        'selection_can_change_qty' => $selection[6],
                                    );
                                    $this->_writeConnection->insertOnDuplicate($this->_selectionTable, $values);
                                    $values = array(
                                        'parent_id'         => $parentProductId,
                                        'child_id'          => $skuToId[$selection[0]],
                                    );
                                    $this->_writeConnection->insert($this->_productRelationTable, $values);
                                }
                            }
                        }
                        $this->_writeConnection->update($this->_productTable, array('has_options' => 1),
                            $this->_writeConnection->quoteInto('entity_id = ?', $skuToId[$sku]));
                    }
                }
            }
        }

        return parent::afterProcess($skuToId);
    }
}