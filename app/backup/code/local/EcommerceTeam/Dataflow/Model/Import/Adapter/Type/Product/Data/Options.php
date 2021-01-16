<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Options
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var  string  */
    protected $_productTable;
    /** @var  string */
    protected $_optionsTable;
    /** @var  string */
    protected $_optionsTitleTable;
    /** @var  string */
    protected $_optionsPriceTable;
    /** @var  string */
    protected $_optionsValueTable;
    /** @var  string */
    protected $_optionsValueTitleTable;
    /** @var  string */
    protected $_optionsValuePriceTable;
    /** @var  array */
    protected $_optionTypes;
    /** @var  array */
    protected $_optionsData;

    protected function _construct()
    {
        $this->_productTable           = $this->_config->getResource()->getTableName('catalog/product');
        $this->_optionsTable           = $this->_config->getResource()->getTableName('catalog/product_option');
        $this->_optionsTitleTable      = $this->_config->getResource()->getTableName('catalog/product_option_title');
        $this->_optionsPriceTable      = $this->_config->getResource()->getTableName('catalog/product_option_price');
        $this->_optionsValueTable      = $this->_config->getResource()->getTableName('catalog/product_option_type_value');
        $this->_optionsValueTitleTable = $this->_config->getResource()->getTableName('catalog/product_option_type_title');
        $this->_optionsValuePriceTable = $this->_config->getResource()->getTableName('catalog/product_option_type_price');
        $this->_optionsData = array();
        
        $this->_optionTypes = array(
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE_TIME,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_TIME,
        );

        parent::_construct();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        if (!empty($data['custom_options'])) {
            $this->_optionsData[$data['sku']] = explode('|', $data['custom_options']);
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
        if (!empty($this->_optionsData)) {
            foreach ($this->_optionsData as $sku => $options) {
                $this->_writeConnection->delete($this->_optionsTable,
                                $this->_writeConnection->quoteInto('product_id = ?', $skuToId[$sku]));
                foreach ($options as $option) {
                    $option = str_replace("'", '"', $option);
                    $optionData = json_decode($option, true);
                    if ($optionData) {
                        if (in_array($optionData['type'], $this->_optionTypes)) {
                            $values = array(
                                'product_id'          => $skuToId[$sku],
                                'type'                => $optionData['type'],
                                'is_require'          => isset($optionData['is_require']) ? $optionData['is_require'] : 0,
                                'max_characters'      => isset($optionData['max_characters']) ? $optionData['max_characters'] : null,
                                'sku'                 => isset($optionData['sku']) ? $optionData['sku'] : '',
                                'sort_order'          => isset($optionData['sort_order']) ? $optionData['sort_order'] : 0,
                            );
                            $this->_writeConnection->insertOnDuplicate($this->_optionsTable, $values);
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
                                $this->_writeConnection->insertOnDuplicate($this->_optionsTitleTable, $values);
                            }
                            $prices = array();
                            if (isset($optionData['prices'])) {
                                $prices = $optionData['prices'];
                            }
                            if (isset($optionData['price'])) {
                                $prices[0] = array('price' => $optionData['price']);
                                if (isset($optionData['price_type'])) {
                                    $prices[0]['price_type'] = $optionData['price_type'];
                                }
                            }
                            foreach ($prices as $price) {
                                $values = array(
                                    'option_id'       => $optionId,
                                    'price'           => $price['price'],
                                    'store_id'        => (isset($title['store']) && isset($stores[$title['store']])) ? $stores[$title['store']] : 0,
                                    'price_type'      => isset($price['price_type']) ? $price['price_type'] : 'fixed',
                                );
                                $this->_writeConnection->insertOnDuplicate($this->_optionsPriceTable, $values);
                            }
                            if (in_array($optionData['type'],
                                    array(
                                        Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN,
                                        Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO,
                                        Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX,
                                        Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE
                                    )
                                )
                            ) {
                                $optionTypes = $optionData['values'];
                                foreach ($optionTypes as $optionType) {
                                    $values = array(
                                        'option_id'           => $optionId,
                                        'sku'                 => isset($optionType['sku']) ? $optionType['sku'] : '',
                                        'sort_order'          => isset($optionType['sort_order']) ? $optionType['sort_order'] : 0,
                                    );
                                    $this->_writeConnection->insertOnDuplicate($this->_optionsValueTable, $values);
                                    $valueId = $this->_writeConnection->lastInsertId();
                                    $titles = array();
                                    if (isset($optionType['titles'])) {
                                        $titles = $optionType['titles'];
                                    }
                                    if (isset($optionType['title'])) {
                                        $titles[0] = array('title' => $optionType['title']);
                                    }
                                    foreach ($titles as $title) {
                                        $values = array(
                                            'option_type_id'  => $valueId,
                                            'title'           => Mage::helper('ecommerceteam_dataflow')->processTitle($title['title']),
                                            'store_id'        => (isset($title['store']) && isset($stores[$title['store']])) ? $stores[$title['store']] : 0,
                                        );
                                        $this->_writeConnection->insertOnDuplicate($this->_optionsValueTitleTable, $values);
                                    }
                                    $prices = array();
                                    if (isset($optionType['prices'])) {
                                        $prices = $optionType['prices'];
                                    }
                                    if (isset($optionType['price'])) {
                                        $prices[0] = array('price' => $optionType['price']);
                                        if (isset($optionType['price_type'])) {
                                            $prices[0]['price_type'] = $optionType['price_type'];
                                        }
                                    }
                                    foreach ($prices as $price) {
                                        $values = array(
                                            'option_type_id'  => $valueId,
                                            'price'           => $price['price'],
                                            'store_id'        => (isset($price['store']) && isset($stores[$price['store']])) ? $stores[$price['store']] : 0,
                                            'price_type'      => isset($price['price_type']) ? $price['price_type'] : 'fixed',
                                        );
                                        $this->_writeConnection->insertOnDuplicate($this->_optionsValuePriceTable, $values);
                                    }
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