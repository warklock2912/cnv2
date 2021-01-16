<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoSingleUrl
 */


class Amasty_SeoSingleUrl_Model_Resource_Sitemap_Catalog_Product extends Mage_Sitemap_Model_Resource_Catalog_Product
{
	/**
	 * Get product collection array
	 *
	 * @param int $storeId
	 * @return array
	 */
	public function getCollection($storeId)
	{
		/** @var Amasty_SeoSingleUrl_Helper_Data $helper */
		$helper = Mage::helper('amseourl');
		if (Amasty_SeoSingleUrl_Helper_Data::urlRewriteHelperEnabled() || $helper->useDefaultProductUrlRules()) {
			return parent::getCollection($storeId);
		}

		$products = array();

		/* @var $store Mage_Core_Model_Store */
		$store = Mage::app()->getStore($storeId);
		if (!$store) {
			return false;
		}

		$this->_select = $this->_getWriteAdapter()->select()
			->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName()))
			->join(
				array('w' => $this->getTable('catalog/product_website')),
				'main_table.entity_id=w.product_id',
				array()
			)
			->where('w.website_id=?', $store->getWebsiteId());

		/** @var Amasty_SeoSingleUrl_Helper_Product_Url_Rewrite $helper */
		$helper = Mage::helper('amseourl/product_url_rewrite');
		$helper->joinTableToSelect($this->_select, $storeId);

        $version = Mage::getVersionInfo();

        $addFilterMethod = $version['minor'] <= 7 ? '_addFilterLte17' : '_addFilter';

        $this->$addFilterMethod($storeId, 'visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), 'in');
        $this->$addFilterMethod($storeId, 'status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in');

        $query = $this->_getWriteAdapter()->query($this->_select);
        while ($row = $query->fetch()) {
            $product = $this->_prepareProduct($row);
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * Prepare product
     *
     * @param array $productRow
     * @return Varien_Object
     */
    protected function _prepareProduct(array $productRow)
    {
        $product = new Varien_Object();
        $product->setId($productRow[$this->getIdFieldName()]);
        $productUrl = !empty($productRow['request_path']) ? $productRow['request_path'] : 'catalog/product/view/id/' . $product->getId();
        $product->setUrl($productUrl);
        return $product;
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Zend_Db_Select
     */
    protected function _addFilterLte17($storeId, $attributeCode, $value, $type = '=')
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $attribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);

            $this->_attributesCache[$attributeCode] = array(
                'entity_type_id'    => $attribute->getEntityTypeId(),
                'attribute_id'      => $attribute->getId(),
                'table'             => $attribute->getBackend()->getTable(),
                'is_global'         => $attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'backend_type'      => $attribute->getBackendType()
            );
        }

        $attribute = $this->_attributesCache[$attributeCode];

        if (!$this->_select instanceof Zend_Db_Select) {
            return false;
        }

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
                break;
        }

        if ($attribute['backend_type'] == 'static') {
            $this->_select->where('main_table.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->_select->join(
                array('t1_'.$attributeCode => $attribute['table']),
                'main_table.entity_id=t1_'.$attributeCode.'.entity_id AND t1_'.$attributeCode.'.store_id=0',
                array()
            )
                ->where('t1_'.$attributeCode.'.attribute_id=?', $attribute['attribute_id']);

            if ($attribute['is_global']) {
                $this->_select->where('t1_'.$attributeCode.'.value'.$conditionRule, $value);
            } else {
                $ifCase = $this->_select->getAdapter()->getCheckSql('t2_'.$attributeCode.'.value_id > 0', 't2_'.$attributeCode.'.value', 't1_'.$attributeCode.'.value');
                $this->_select->joinLeft(
                    array('t2_'.$attributeCode => $attribute['table']),
                    $this->_getWriteAdapter()->quoteInto('t1_'.$attributeCode.'.entity_id = t2_'.$attributeCode.'.entity_id AND t1_'.$attributeCode.'.attribute_id = t2_'.$attributeCode.'.attribute_id AND t2_'.$attributeCode.'.store_id=?', $storeId),
                    array()
                )
                    ->where('('.$ifCase.')'.$conditionRule, $value);
            }
        }

        return $this->_select;
    }
}
