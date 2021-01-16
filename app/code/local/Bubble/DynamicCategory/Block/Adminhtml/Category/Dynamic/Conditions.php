<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Block_Adminhtml_Category_Dynamic_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Define a custom template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bubble/dynamiccategory/conditions.phtml');
    }

    /**
     * Retrieve the current selected category in the admin view.
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        return Mage::registry('category');
    }

    /**
     * Creates the form for the condition based selection of product attributes.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $data = array('conditions' => $this->getCategory()->getDynamicProductsConds());

        /* @var $model Bubble_DynamicCategory_Model_Rule */
        $model = Mage::getSingleton('dynamic_category/rule');

        $model->loadPost($data);
        $model->getConditions()->setJsFormObject('category_product_conditions_fieldset');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('category_product_');
        $form->setDataObject($this->getCategory());

        // New child url
        $newChildUrl = $this->getUrl('*/promo_catalog/newConditionHtml/form/category_product_conditions_fieldset');

        // Fieldset renderer
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('bubble/dynamiccategory/promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl);

        // Add new fieldset
        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            array('legend' => $this->__('Dynamic Products'))
        )->setRenderer($renderer);

        // Add new field to the fieldset
        $fieldset->addField('conditions', 'text', array(
            'name'  => 'conditions',
            'label' => $this->__('Conditions'),
            'title' => $this->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $this->setForm($form);

        return $this;
    }

    /**
     * Retrieve category dynamic products grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/dynamic_category/grid', array('_current' => true));
    }

    /**
     * Retrieve category product count URL
     *
     * @return string
     */
    public function getProductCountUrl()
    {
        return $this->getUrl('*/dynamic_category/count', array('_current' => true));
    }
}
