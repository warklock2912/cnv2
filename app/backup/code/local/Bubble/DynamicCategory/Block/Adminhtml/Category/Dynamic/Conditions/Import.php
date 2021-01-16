<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Block_Adminhtml_Category_Dynamic_Conditions_Import extends Mage_Adminhtml_Block_Template
{
    /**
     * Category Model
     *
     * @var Mage_Catalog_Model_Category Category
     */
    protected $_category;

    /**
     * Define a custom template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bubble/dynamiccategory/conditions/import.phtml');
    }

    /**
     * Retrieve the current selected category in the admin view.
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::registry('category');
        }

        return $this->_category;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param bool $addEmpty
     * @return array
     */
    public function getCategoryOptions($category = null, $addEmpty = true)
    {
        $options = array();

        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            );
        }

        if (!$category) {
            $category = Mage::getModel('catalog/category')->load(Mage_Catalog_Model_Category::TREE_ROOT_ID);
        }

        $conds = $category->getDynamicProductsConds();
        if (is_string($conds)) {
            $conds = unserialize($conds);
        }
        if ($category->getLevel() > 0) {
            $label = trim(str_repeat('--', $category->getLevel() - 1) . ' ' . $category->getName());
            $options[] = array(
                'value' => $category->getId(),
                'label' => $label,
                'params' => empty($conds) ? array('disabled' => 'disabled') : array(),
            );
        }

        /** @var Mage_Catalog_Model_Resource_Category_Collection $children */
        $children = $category->getCollection();
        $children->joinUrlRewrite()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->addAttributeToSelect('dynamic_products_conds')
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->addFieldToFilter('parent_id', $category->getId());
        foreach ($children as $child) {
            /** @var Mage_Catalog_Model_Category $child */
            $options = array_merge($options, $this->getCategoryOptions($child, false));
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getCategorySelectHtml()
    {
        $select = Mage::app()->getLayout()->createBlock('core/html_select')
            ->setId('import_conditions_field')
            ->setName('import_conditions')
            ->setClass('select')
            ->setOptions($this->getCategoryOptions());

        return $select->toHtml();
    }

    /**
     * @return string
     */
    public function getImportCondsUrl()
    {
        return $this->getUrl('*/dynamic_category/importConds', array('_current' => true, 'store' => null));
    }
}
