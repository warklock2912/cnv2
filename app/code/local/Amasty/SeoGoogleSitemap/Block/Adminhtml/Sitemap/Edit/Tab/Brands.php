<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Brands extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_brandFields();

        $this->getForm()->setValues($model->getData());

        return parent::_prepareForm();
    }

    private function _brandFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_landing', array('legend' => Mage::helper('amseogooglesitemap')->__('Brand Pages')));
        $fieldset->addField('brands', 'select', array(
            'label'     => $this->helper->__('Include brand pages'),
            'name'      => 'brands',
            'title'     => $this->helper->__('Include brand pages'),
            'options'   => $this->helper->getYesNo(),
            'note'      => 'See <a href="//amasty.com/improved-layered-navigation.html">Improved Layered Navigation</a> module.'
        ));

        $fieldset->addField('brands_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'brands_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
            )
        );

        $fieldset->addField('brands_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'brands_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

    }
}