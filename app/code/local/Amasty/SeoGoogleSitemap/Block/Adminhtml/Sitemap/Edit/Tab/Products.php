<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_productFields();

        $this->getForm()->setValues($model->getData());

        $this->setChild('form_after', $this->getLayout()
            ->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap('products_thumbs', 'products_thumbs')
            ->addFieldMap('products_captions_template', 'products_captions_template')
            ->addFieldMap('products_captions', 'products_captions')
            ->addFieldDependence('products_captions_template', 'products_thumbs', 1)
            ->addFieldDependence('products_captions_template', 'products_captions', 1)
            ->addFieldDependence('products_captions', 'products_thumbs', 1)
        );

        return parent::_prepareForm();
    }


    private function _productFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_products', array('legend' => Mage::helper('amseogooglesitemap')->__('Products')));
        $fieldset->addField('products', 'select', array(
            'label'     => $this->helper->__('Include products'),
            'name'      => 'products',
            'title'     => $this->helper->__('Include products'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('products_thumbs', 'select', array(
            'label'     => $this->helper->__('Add Images'),
            'name'      => 'products_thumbs',
            'title'     => $this->helper->__('Add Images'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('products_captions', 'select', array(
            'label'     => $this->helper->__('Add Images Titles'),
            'name'      => 'products_captions',
            'title'     => $this->helper->__('Add Images Titles'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('products_captions_template', 'text', array(
            'label'     => $this->helper->__('Template for image title'),
            'name'      => 'products_captions_template',
            'title'     => $this->helper->__('Template for image title'),
            'note'		=> $this->helper->__('Specify text to be used for empty captions with {product_name} placeholder for product name. Example - "enjoy {product_name} from e-store"')
        ));

        $fieldset->addField('products_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'products_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
                'class' => 'validate-number validate-number-range number-range-0.01-0.99'
            )
        );

        $fieldset->addField('products_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'products_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

        $fieldset->addField('products_modified', 'select', array(
            'label'     => $this->helper->__('Include Last Modified'),
            'name'      => 'products_modified',
            'title'     => $this->helper->__('Include Last Modified'),
            'options'   => $this->helper->getYesNo()
        ));
    }
}