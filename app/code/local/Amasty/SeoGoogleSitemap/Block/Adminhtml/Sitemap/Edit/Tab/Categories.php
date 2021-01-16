<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Categories extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_categoryFields();

        $this->getForm()->setValues($model->getData());

        $this->setChild('form_after', $this->getLayout()
            ->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap('categories_thumbs', 'categories_thumbs')
            ->addFieldMap('categories_captions', 'categories_captions')
            ->addFieldDependence('categories_captions', 'categories_thumbs', 1)
        );

        return parent::_prepareForm();
    }

    private function _categoryFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_categories', array('legend' => Mage::helper('amseogooglesitemap')->__('Categories')));
        $fieldset->addField('categories', 'select', array(
            'label'     => $this->helper->__('Include categories'),
            'name'      => 'categories',
            'title'     => $this->helper->__('Include categories'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('categories_thumbs', 'select', array(
            'label'     => $this->helper->__('Add Images'),
            'name'      => 'categories_thumbs',
            'title'     => $this->helper->__('Add Images'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('categories_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'categories_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
                'class' => 'validate-number validate-number-range number-range-0.01-0.99'
            )
        );

        $fieldset->addField('categories_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'categories_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

        $fieldset->addField('categories_modified', 'select', array(
            'label'     => $this->helper->__('Include Last Modified'),
            'name'      => 'categories_modified',
            'title'     => $this->helper->__('Include Last Modified'),
            'options'   => $this->helper->getYesNo()
        ));
    }
}