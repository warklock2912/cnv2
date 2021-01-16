<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Landing extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_landingFields();

        $this->getForm()->setValues($model->getData());

        return parent::_prepareForm();
    }

    private function _landingFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_landing', array('legend' => Mage::helper('amseogooglesitemap')->__('Landing Pages')));
        $fieldset->addField('landing', 'select', array(
            'label'     => $this->helper->__('Include landing pages'),
            'name'      => 'landing',
            'title'     => $this->helper->__('Include landing pages'),
            'options'   => $this->helper->getYesNo(),
            'note'      => 'See <a href="//amasty.com/landing-pages.html">Landing Pages</a> module.'
        ));

        $fieldset->addField('landing_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'landing_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
            )
        );

        $fieldset->addField('landing_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'landing_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

    }
}