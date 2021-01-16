<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.0.4
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Category
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var  array */
    protected $_categoryTree = array();
    /** @var  string */
    protected $_categoryProductTable;

    /**
     * Initialize base data and config
     */
    protected function _construct()
    {
        $this->_categoryProductTable = $this->_resource->getTableName('catalog/category_product');
    }

    /**
     * @return $this
     * @throws EcommerceTeam_Dataflow_Model_Import_Adapter_Exception
     */
    protected function _initializeCategoryTree()
    {
        $rootCategoryId = $this->_store->getRootCategoryId();
        if (!$rootCategoryId) {
            $rootCategoryId = $this->_config->getDefaultWebsite()->getDefaultStore()->getRootCategoryId();
        }

        /** @var Mage_Catalog_Model_Category $rootCategory */
        $rootCategory = Mage::getModel('catalog/category')->load($rootCategoryId);

        if (is_null($rootCategory->getId())) {
            throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception('Root category not found.');
        }

        /** @var $categoryResource Mage_Catalog_Model_Resource_Category */
        $categoryResource = $rootCategory->getResource();

        /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = $rootCategory->getCollection();
        $collection->addAttributeToSelect(array('name', 'path', 'parent_id'));
        $collection->addIdFilter($categoryResource->getChildren($rootCategory, true));

        $this->_categoryTree[$rootCategory->getName()] = $this->_prepareCategoryTree($rootCategory, $collection);

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @return array
     */
    protected function _prepareCategoryTree(
        Mage_Catalog_Model_Category $category,
        Mage_Catalog_Model_Resource_Category_Collection $collection)
    {
        $childCategories = $collection->getItemsByColumnValue('parent_id', $category->getId());
        $children        = array();
        if (!empty($childCategories)) {
            foreach ($childCategories as $childCategory) {
                $children[$childCategory->getName()] = $this->_prepareCategoryTree($childCategory, $collection);
            }
        }

        return array('category' => $category, 'children' => $children);
    }

    /**
     * @return $this
     */
    public function beforePrepare()
    {
        $this->_initializeCategoryTree();

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function prepareData(array &$data)
    {
        if ($this->_config->getCanCreateCategories()) {
            $this->_prepareCategories($data);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function afterPrepare()
    {
        if ($this->_config->getCanCreateCategories()) {
            $this->_writeConnection->beginTransaction();
            try {
                foreach ($this->_categoryTree as $rootCategoryName => $categoryData) {
                    $this->_saveCategoryTree($this->_categoryTree[$rootCategoryName]);
                }
                $this->_writeConnection->commit();
            } catch (Exception $e) {
                $this->_writeConnection->rollBack();
                throw $e;
            }
        }

        return $this;
    }

    /**
     * @return $this|void
     */
    public function beforeProcess()
    {
        $this->_initializeCategoryTree();

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws EcommerceTeam_Dataflow_Exception
     */
    public function processData(array &$data)
    {
        try {
            if (isset($data['categories']) && $data['categories'] || isset($data['category_ids']) && $data['category_ids']) {
                if (isset($data['category_ids']) && $data['category_ids']) {
                    $categoryIds = explode(',', $data['category_ids']);
                    foreach ($categoryIds as $key => $id) {
                        $categoryIds[$key] = intval($id);
                    }
                } else {
                    $categoryIds = array();
                }

                if (isset($data['categories']) && $data['categories']) {
                    $categories  = explode(',', $data['categories']);
                    foreach ($categories as $categoryPath) {
                        foreach ($this->_categoryTree as $rootNode) {
                            $categoryIds = array_merge(
                                $categoryIds,
                                $this->getCategoryPathIds(explode('/', $categoryPath), $rootNode)
                            );
                        }
                    }
                }

                $categoryIds = array_unique($categoryIds);

                $where = sprintf('product_id = %d', $data['product_id']);
                if (!empty($categoryIds)) {
                    $where .= sprintf(' AND category_id NOT IN (%s)', implode(',', $categoryIds));
                }
                $this->_writeConnection->delete($this->_categoryProductTable, $where);

                if (!empty($categoryIds)) {
                    foreach ($categoryIds as $categoryId) {
                        $this->_writeConnection->insertOnDuplicate(
                            $this->_categoryProductTable,
                            array('product_id' => $data['product_id'], 'category_id' => $categoryId)
                        );
                    }
                }
            }
        } catch (Exception $e) {
            throw new EcommerceTeam_Dataflow_Exception("Can't save category relation.");
        }

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function _prepareCategories(array &$data)
    {
        if (isset($data['categories']) && $data['categories']) {
            $categories  = explode(',', $data['categories']);
            foreach ($categories as $categoryPath) {
                foreach ($this->_categoryTree as $rootCategoryName => $categoryData) {
                    $this->_updateCategoryTree(explode('/', $categoryPath), $this->_categoryTree[$rootCategoryName]);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $categoryPath
     * @param array $treeNode
     * @return $this
     */
    protected function _updateCategoryTree(array $categoryPath, array &$treeNode)
    {
        if ($categoryName = array_shift($categoryPath)) {
            if (!isset($treeNode['children'][$categoryName])) {
                $treeNode['children'][$categoryName] = array(
                    'category' => null,
                    'children' => array(),
                );
            }
            $this->_updateCategoryTree($categoryPath, $treeNode['children'][$categoryName]);
        }

        return $this;
    }

    /**
     * Save new categories
     *
     * @param array $node
     * @return $this
     */
    protected function _saveCategoryTree(array &$node)
    {
        foreach ($node['children'] as $categoryName => $categoryData) {
            if (is_null($categoryData['category'])) {
                $category = Mage::getModel('catalog/category');
                $category->addData(array(
                    'path'      => $node['category']->getData('path'),
                    'name'      => $categoryName,
                    'is_active' => true,
                ));
                $category->getResource()->save($category);
                $node['children'][$categoryName]['category'] = $category;
            }
            $this->_saveCategoryTree($node['children'][$categoryName]);
        }

        return $this;
    }

    /**
     * @param array $path
     * @param array $rootNode
     * @return array
     */
    public function getCategoryPathIds(array $path, array $rootNode)
    {
        $result      = array();
        $categories  = $rootNode['children'];
        while ($name = array_shift($path)) {
            if (isset($categories[$name])) {
                $result[]   = $categories[$name]['category']->getId();
                $categories = $categories[$name]['children'];
            }
        }

        return $result;
    }
}