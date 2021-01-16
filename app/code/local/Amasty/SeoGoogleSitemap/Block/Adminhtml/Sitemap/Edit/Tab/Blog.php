<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Blog extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_blogFields();

        $this->getForm()->setValues($model->getData());

        return parent::_prepareForm();
    }

    private function _blogFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_blog', array('legend' => Mage::helper('amseogooglesitemap')->__('Blog Pages')));
        $fieldset->addField('blog', 'select', array(
            'label'     => $this->helper->__('Include blog pages'),
            'name'      => 'blog',
            'title'     => $this->helper->__('Include blog pages'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('blog_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'blog_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
                'class' => 'validate-number validate-number-range number-range-0.01-0.99'
            )
        );
        $fieldset->addField('blog_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'blog_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));
    }
}