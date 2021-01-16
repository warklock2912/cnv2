<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Layer extends Mage_CatalogSearch_Model_Layer {

    public function prepareProductCollection($collection) {
        $this->_prepareAttributeFilter($collection);
        $this->_prepareCategoryFilter($collection);

        $collection
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->setStore(Mage::app()->getStore())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addStoreFilter()
                ->addUrlRewrite()->getSelect()->columns('ABS(1) as relevance');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        return $this;
    }

    protected function _prepareAttributeFilter($collection) {
        $helper = Mage::helper('mageworx_searchsuite');
        $parameter = $helper->getSearchParameter();
        if ($parameter) {
            $attribute = Mage::getSingleton('eav/entity_attribute')->loadByCode('catalog_product', $parameter);
            if ($attribute && $attribute->getId() && $attribute->getIsAttributesSearch()) {

                $like = $helper->escapeLikeValue(Mage::helper('catalogsearch')->getQuery()->getQueryText(), array('position' => 'any'));
                if ($attribute->getBackendType() == 'int') {
                    $collection
                            ->getSelect()->columns('ABS(1) as relevance')
                            ->join(array('attr' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int')), 'attr.entity_id = e.entity_id and attr.attribute_id = ' . $attribute->getId(), array('attribute_id'))
                            ->join(array('option' => $collection->getTable('eav/attribute_option')), 'attr.attribute_id = option.attribute_id and attr.value = option.option_id', array('option_id'))
                            ->join(array('option_value' =>
                                $collection->getTable('eav/attribute_option_value')), 'option.option_id = option_value.option_id and option_value.value like '
                                    . $collection->getConnection()->quote($like) . ' and option_value.store_id IN (' . Mage_Core_Model_App::ADMIN_STORE_ID . ', ' . Mage::app()->getStore()->getId() . ')', array($parameter => 'value'));
                } else if ($collection->isEnabledFlat()) {
                    $collection
                            ->getSelect()
                            ->join(array('attr' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_' . $attribute->getBackendType())), 'attr.entity_id = e.entity_id and attr.attribute_id = ' . $attribute->getId() . ' and attr.value like ' . $collection->getConnection()->quote($like) . ' ', array());
                } else {
                    $collection->addFieldToFilter(array(
                        array('attribute' => $attribute->getAttributeCode(), 'like' => $like),
                    ));
                }
                return $this;
            }
        }
        $collection->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText());
        return $this;
    }

    protected function _prepareCategoryFilter($collection) {
        $helper = Mage::helper('mageworx_searchsuite');
        $categoryId = $helper->getSearchCategory();
        if ($categoryId && $categoryId > 0) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category->getId() == $categoryId) {
                $category->setIsAnchor(true);
                $collection->addCategoryFilter($category);
            }
        }
        return $this;
    }

}
