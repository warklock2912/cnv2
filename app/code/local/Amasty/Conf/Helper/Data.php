<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_USE_SIMPLE_PRICE     = 'amconf/general/use_simple_price';
    const XML_PATH_OPTIONS_IMAGE_SIZE   = 'amconf/list/listimg_size';
    const ADMIN_OPTION_ITEMS_LIMIT      = 20;

    public function getImageUrl($optionId, $width, $height)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR .
            'amconf' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        $url = "";
        $swatchModel = Mage::getModel('amconf/swatch')->load($optionId);
        $extension = $swatchModel->getExtension();
        if (file_exists($uploadDir . $optionId . '.' . $extension))
        {

            $url =  'amconf' . '/' . 'images' . '/' . $optionId . '.' . $extension;
            if($width && $height){
                return Mage::helper('amconf/image')->init($url)->resize($width, $height);
            }
            else{
                return Mage::getBaseUrl('media') . $url;
            }
        } elseif (file_exists($uploadDir . $optionId . '.jpg')) {
            $url = 'amconf' . '/' . 'images' . '/' . $optionId . '.jpg';
            if($width && $height){
                return Mage::helper('amconf/image')->init($url)->resize($width, $height);
            }
            else{
                return Mage::getBaseUrl('media') . $url;
            }
        }

        return $url;
    }

    public function getLimit(){
        return self::ADMIN_OPTION_ITEMS_LIMIT;
    }

    public function getPlaceholderUrl($attributeId, $width, $height)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR .
            'amconf' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . '/attr_' . $attributeId . '.jpg')) {
            $url = 'amconf' . '/' . 'images' . '/attr_' . $attributeId . '.jpg';
            if ($width && $height) {
                return Mage::helper('amconf/image')->init($url)->resize($width, $height);
            } else {
                return Mage::getBaseUrl('media') . $url;
            }
        }
        return "";
    }

    public function getConfigUseSimplePrice()
    {
        return Mage::getStoreConfig(self::XML_PATH_USE_SIMPLE_PRICE);
    }

    public function getOptionsImageSize()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPTIONS_IMAGE_SIZE);
    }

    public function getHtmlBlock($_product, $html)
    {
        if(Mage::getStoreConfig('amconf/list/list_index')) {
            $blockForForm = Mage::app()->getLayout()->
            createBlock('amconf/catalog_product_view_type_configurablelIndex',
                'product.info.options.configurable',
                array('product' => $_product)
            );
        }
        else{
            $blockForForm = Mage::app()->getLayout()->
            createBlock('amconf/catalog_product_view_type_configurablel',
                'product.info.options.configurable',
                array('product' => $_product)
            );
        }
        $blockForForm->setTemplate("amasty/amconf/configurable.phtml");
        $html .= '<div class="amconf-block" id="amconf-block-' . $_product->getId() . '">' .
            $blockForForm->toHtml() .
            '</div>';

        return $html;
    }

    /*  set configurable price as min from simple price
    * templates:
    * app\design\frontend\base\default\template\catalog\product\view\tierprices.phtml
    * app\design\frontend\base\default\template\catalog\product\price.phtml
    * $_product = Mage::helper('amconf')->getSimpleProductWithMinPrice($_product);
    */
    public function getSimpleProductWithMinPrice($_product)
    {
        $flag = true;

        $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
        $collection = $conf->getUsedProductCollection()
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions()
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addStoreFilter(Mage::app()->getStore()->getId());

        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();
        $collection->getSelect()->reset('order');
        $collection->getSelect()->order('minimal_price','asc');

        $first =  $collection->getFirstItem();
        $last =  $collection->getLastItem();
        if($first->getMinimalPrice() == $last->getMinimalPrice()){
            $flag = false;
        }
        return array($first, $flag);

    }

    public function getAjaxUrl()
    {
        $url = Mage::getUrl('amconf/ajax/ajax');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
}
