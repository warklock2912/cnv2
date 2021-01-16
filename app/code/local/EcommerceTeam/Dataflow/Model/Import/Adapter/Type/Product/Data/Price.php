<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Price
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    const ALL_WEBSITES = 'all';
    const ALL_GROUPS   = 'all';

    /** @var  string  */
    protected $_productTable;
    /** @var  string */
    protected $_groupPriceTable;
    /** @var  string */
    protected $_tierPriceTable;
    /** @var  string */
    protected $_customerGroups;

    protected $_groupPriceData;
    protected $_tierPriceData;

    protected function _construct()
    {
        $this->_productTable   = $this->_config->getResource()->getTableName('catalog/product');
        $this->_groupPriceTable    = $this->_config->getResource()->getTableName('catalog/product_attribute_group_price');
        $this->_tierPriceTable = $this->_config->getResource()->getTableName('catalog/product_attribute_tier_price');
        $select = $this->_config->getResourceConnection()->select();
        $select->from($this->_config->getResource()->getTableName('customer/customer_group'),
           array('customer_group_code', 'customer_group_id'));
        $this->_customerGroups = $this->_config->getResourceConnection()->fetchPairs($select);
        $this->_groupPriceData = array();
        $this->_tierPriceData = array();

        parent::_construct();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        if (!empty($data['group_pricing'])) {
            $this->_groupPriceData[$data['sku']] = explode('|', $data['group_pricing']);
        }
        if (!empty($data['tier_pricing'])) {
            $this->_tierPriceData[$data['sku']] = explode('|', $data['tier_pricing']);
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
        $websites = $this->_config->getWebsites();
        if (!empty($this->_groupPriceData)) {
            foreach ($this->_groupPriceData as $sku => $groupPrices) {
                $this->_writeConnection->delete($this->_groupPriceTable,
                                        $this->_writeConnection->quoteInto('entity_id = ?', $skuToId[$sku]));
                foreach ($groupPrices as $groupPrice) {
                    $groupPrice = explode(',', $groupPrice);
                    if ((count($websites) == 1) || ($groupPrice[1] == self::ALL_WEBSITES)) {
                        $websiteId = 0;
                    } else {
                        if (!isset($websites[$groupPrice[1]])) {
                            throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception(sprintf('Webiste %s doesn\'t exists', $groupPrice[1]));
                        }
                        $websiteId = $websites[$groupPrice[1]];
                    }
                    if (!isset($this->_customerGroups[$groupPrice[0]])) {
                        throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception(sprintf('Customer Group %s doesn\'t exists', $groupPrice[0]));
                    }
                    $values = array(
                        'entity_id'           => $skuToId[$sku],
                        'all_groups'          => 0,
                        'customer_group_id'   => $this->_customerGroups[$groupPrice[0]],
                        'website_id'          => $websiteId,
                        'value'               => $groupPrice[2],
                    );
                    $this->_writeConnection->insertOnDuplicate($this->_groupPriceTable, $values);
                }
            }
        }

        if (!empty($this->_tierPriceData)) {
            foreach ($this->_tierPriceData as $sku => $tierPrices) {
                $this->_writeConnection->delete($this->_tierPriceTable,
                                        $this->_writeConnection->quoteInto('entity_id = ?', $skuToId[$sku]));
                foreach ($tierPrices as $tierPrice) {
                    $tierPrice = explode(',', $tierPrice);
                    if ((count($websites) == 1) || ($tierPrice[1] == self::ALL_WEBSITES)) {
                        $websiteId = 0;
                    } else {
                        if (!isset($websites[$tierPrice[1]])) {
                            throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception(sprintf('Webiste %s doesn\'t exists', $tierPrice[1]));
                        }
                        $websiteId = $websites[$tierPrice[1]];
                    }
                    $allGroups = 0;
                    if ($tierPrice[0] == self::ALL_GROUPS) {
                        $customerGroupId = 0;
                        $allGroups = 1;
                    } else {
                        if (!isset($this->_customerGroups[$tierPrice[0]])) {
                            throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception(sprintf('Customer Group %s doesn\'t exists', $tierPrice[0]));
                        }
                        $customerGroupId = $this->_customerGroups[$tierPrice[0]];
                    }
                    $values = array(
                        'entity_id'           => $skuToId[$sku],
                        'all_groups'          => $allGroups,
                        'customer_group_id'   => $customerGroupId,
                        'website_id'          => $websiteId,
                        'qty'                 => $tierPrice[2],
                        'value'               => $tierPrice[3],
                    );
                    $this->_writeConnection->insertOnDuplicate($this->_tierPriceTable, $values);
                }
            }
        }

        return parent::afterProcess($skuToId);
    }
}