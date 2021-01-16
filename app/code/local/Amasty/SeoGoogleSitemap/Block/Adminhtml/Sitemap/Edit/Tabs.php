<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('amsitemap_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amseogooglesitemap')->__('Manage profile'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => Mage::helper('amseogooglesitemap')->__('General'),
            'title' => Mage::helper('amseogooglesitemap')->__('General'),
            'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_general')->toHtml(),
        ));

        $this->addTab('products', array(
            'label' => Mage::helper('amseogooglesitemap')->__('Products'),
            'title' => Mage::helper('amseogooglesitemap')->__('Products'),
            'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_products')->toHtml(),
        ));

        $this->addTab('categories', array(
            'label' => Mage::helper('amseogooglesitemap')->__('Categories'),
            'title' => Mage::helper('amseogooglesitemap')->__('Categories'),
            'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_categories')->toHtml(),
        ));

        $this->addTab('pages', array(
            'label' => Mage::helper('amseogooglesitemap')->__('Pages'),
            'title' => Mage::helper('amseogooglesitemap')->__('Pages'),
            'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_pages')->toHtml(),
        ));

        $this->addTab('tags', array(
            'label' => Mage::helper('amseogooglesitemap')->__('Tags'),
            'title' => Mage::helper('amseogooglesitemap')->__('Tags'),
            'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_tags')->toHtml(),
        ));

        $this->addTab('extra', array(
            'label' => Mage::helper('amseogooglesitemap')->__('Extra Links'),
            'title' => Mage::helper('amseogooglesitemap')->__('Extra Links'),
            'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_extra')->toHtml(),
        ));

        if (Mage::helper('core')->isModuleEnabled('Amasty_Shopby')) {
            $this->addTab('brands', array(
                'label' => Mage::helper('amseogooglesitemap')->__('Brand Pages'),
                'title' => Mage::helper('amseogooglesitemap')->__('Brand Pages'),
                'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_brands')->toHtml(),
            ));
        }

        if (Mage::helper('core')->isModuleEnabled('Magpleasure_Blog')) {
            $this->addTab('landing', array(
                'label' => Mage::helper('amseogooglesitemap')->__('Blog Pages'),
                'title' => Mage::helper('amseogooglesitemap')->__('Blog Pages'),
                'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_blog')->toHtml(),
            ));
        }


        if (Mage::helper('core')->isModuleEnabled('Amasty_Xlanding')) {
            $this->addTab('landing', array(
                'label' => Mage::helper('amseogooglesitemap')->__('Landing Pages'),
                'title' => Mage::helper('amseogooglesitemap')->__('Landing Pages'),
                'content' => $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tab_landing')->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }
}