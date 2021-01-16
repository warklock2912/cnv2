<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Tags extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_tagFields();

        $this->getForm()->setValues($model->getData());

        return parent::_prepareForm();
    }

    private function _tagFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_tags', array('legend' => Mage::helper('amseogooglesitemap')->__('Tags')));
        $fieldset->addField('tags', 'select', array(
            'label'     => $this->helper->__('Include tags'),
            'name'      => 'tags',
            'title'     => $this->helper->__('Include tags'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('tags_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'tags_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
                'class' => 'validate-number validate-number-range number-range-0.01-0.99'
            )
        );

        $fieldset->addField('tags_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'tags_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

    }
}