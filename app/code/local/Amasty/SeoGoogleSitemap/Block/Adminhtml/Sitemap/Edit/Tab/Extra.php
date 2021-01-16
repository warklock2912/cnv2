<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tab_Extra extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $this->helper = Mage::helper('amseogooglesitemap');

        $model = Mage::registry('am_sitemap_profile');

        $this->_extraFields();

        $this->getForm()->setValues($model->getData());

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

        /*  $fieldset->addField('products_url', 'select', array(
              'label'     => $this->helper->__('Products Url Settings'),
              'name'      => 'products_url',
              'title'     => $this->helper->__('Product Url Settings'),
              'options'   => $this->helper->getProductUrlSettings()
          ));*/



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

        $fieldset->addField('categories_captions', 'select', array(
            'label'     => $this->helper->__('Add Images Titles'),
            'name'      => 'categories_captions',
            'title'     => $this->helper->__('Add Images Titles'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('categories_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'categories_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
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
            )
        );

        $fieldset->addField('tags_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'tags_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

    }

    private function _extraFields()
    {
        $fieldset = $this->getForm()->addFieldset('amseogooglesitemap_form_content_extra', array('legend' => Mage::helper('amseogooglesitemap')->__('Extra Links')));
        $fieldset->addField('extra', 'select', array(
            'label'     => $this->helper->__('Include Extra Links'),
            'name'      => 'extra',
            'title'     => $this->helper->__('Include Extra Links'),
            'options'   => $this->helper->getYesNo()
        ));

        $fieldset->addField('extra_priority', 'text',
            array(
                'label'     => Mage::helper('amseogooglesitemap')->__('Priority'),
                'name'      => 'extra_priority',
                'note'     => Mage::helper('amseogooglesitemap')->__('0.01-0.99'),
            )
        );

        $fieldset->addField('extra_frequency', 'select', array(
            'label'     => $this->helper->__('Frequency'),
            'name'      => 'extra_frequency',
            'title'     => $this->helper->__('Frequency'),
            'options'   => $this->helper->getFrequency()
        ));

        $fieldset->addField('extra_links', 'textarea', array(
            'label'     => $this->helper->__('Extra Links to include'),
            'name'      => 'extra_links',
            'note' => $this->helper->__('Links to add, one per line')
        ));

    }
}