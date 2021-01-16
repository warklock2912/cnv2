<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Pages extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_pagesFields();

        $this->getForm()->setValues($model->getData());

        return parent::_prepareForm();
    }

    private function _pagesFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_pages', array('legend' => Mage::helper('amseogooglesitemap')->__('Pages')));
        $fieldset->addField('pages', 'select', array(
            'label'     => $this->helper->__('Include pages'),
            'name'      => 'pages',
            'title'     => $this->helper->__('Include pages'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('pages_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'pages_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
                'class' => 'validate-number validate-number-range number-range-0.01-0.99'
            )
        );
        $fieldset->addField('pages_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'pages_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

        $fieldset->addField('pages_modified', 'select', array(
            'label'     => $this->helper->__('Include Last Modified'),
            'name'      => 'pages_modified',
            'title'     => $this->helper->__('Include Last Modified'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('exclude_cms_aliases', 'textarea', array(
            'label'     => $this->helper->__('Exclude CMS pages'),
            'name'      => 'exclude_cms_aliases',
            'note' => $this->helper->__('URL Keys of CMS Pages to exclude, one per line')
        ));
    }
}