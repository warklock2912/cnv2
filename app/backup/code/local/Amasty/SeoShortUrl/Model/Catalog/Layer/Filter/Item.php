<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoShortUrl
 */
if (Mage::helper('amseoshorturl')->isDeniedModule() && Mage::helper('amseoshorturl')->isModuleEnabled('Amasty_Shopby')) {
    class Amasty_SeoShortUrl_Model_Catalog_Layer_Filter_Item extends Amasty_Shopby_Model_Catalog_Layer_Filter_Item {}
} else {
    class Amasty_SeoShortUrl_Model_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
    {
        public function getUrl()
        {
            if(Mage::helper('amseoshorturl')->isDeniedModule()) {
                return parent::getUrl();
            }

            /** @var Amasty_SeoShortUrl_Model_Url_Builder $urlBuilder */
            $urlBuilder = Mage::getModel('amseoshorturl/url_builder');
            $urlBuilder->reset();
            $urlBuilder->clearPagination();

            if ($this->getFilter()->getRequestVar() == 'cat') {
                $cat = Mage::getModel('catalog/category')->load($this->getValue());
                $urlBuilder->category = $cat;
            } else {
                $urlBuilder->changeQuery(array(
                    $this->getFilter()->getRequestVar() => $this->getValue(),
                ));
            }

            $url = $urlBuilder->getUrl();

            return $url;
        }


        public function getRemoveUrl()
        {
            if(Mage::helper('amseoshorturl')->isDeniedModule()) {
                return parent::getRemoveUrl();
            }
                /** @var Amasty_SeoShortUrl_Model_Url_Builder $urlBuilder */
            $urlBuilder = Mage::getModel('amseoshorturl/url_builder');
            $urlBuilder->reset();
            $urlBuilder->clearPagination();

            if ($this->getFilter()->getRequestVar() == 'cat') {
                /** @var Mage_Catalog_Model_Category $cat */
                $cat = Mage::getModel('catalog/category')->load($this->getValue());
                $urlBuilder->category = $cat->getParentCategory();
            } else {
                $urlBuilder->changeQuery(array(
                    $this->getFilter()->getRequestVar() => $this->getFilter()->getResetValue(),
                ));
            }

            $url = $urlBuilder->getUrl();

            return $url;
        }

    }
}
