<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected $_currentAttributes;
    const SMALL_SIZE = 50;
    const BIG_SIZE = 100;
    
    protected function _afterToHtml($html)
    {
        $attributeIdsWithImages = Mage::registry('amconf_images_attrids');
        $html = parent::_afterToHtml($html);
        if ('product.info.options.configurable' == $this->getNameInLayout()  && !Mage::app()->getRequest()->isAjax())
        {
            if (Mage::getStoreConfig('amconf/general/hide_dropdowns') )
            {
                if (!empty($attributeIdsWithImages))
                {
                    foreach ($attributeIdsWithImages as $attrIdToHide)
                    {
                        $html = preg_replace('@(id="attribute' . $attrIdToHide . ')(-)?([0-9]*)(")(\s+)(class=")(.*?)(super-attribute-select)(-)?([0-9]*)@', '$1$2$3$4$5$6$7$8$9$10 no-display', $html);
                    }
                }
            }
            $_useSimplePrice =  (Mage::helper('amconf')->getConfigUseSimplePrice() == 2 ||(Mage::helper('amconf')->getConfigUseSimplePrice() == 1 AND $this->getProduct()->getData('amconf_simple_price')))? true : false;
            
            $simpleProducts = $this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->getProduct());
            if ($this->_currentAttributes)
            {
                $this->_currentAttributes = array_unique($this->_currentAttributes);
                $reloadValues = explode ( ',', Mage::getStoreConfig('amconf/general/reload_content'));
                $dropdownPrice = Mage::getStoreConfig('amconf/general/dropdown_price');
                if ( $dropdownPrice ) {
                    $confData['parentPrice'] = Mage::helper('core')->currency($this->getProduct()->getFinalPrice(), false, false);
                }
                foreach ($simpleProducts as $simple)
                {
                    /* @var $simple Mage_Catalog_Model_Product */
                    $key = array();

                    foreach ($this->_currentAttributes as $attributeCode)
                    {
                        $key[] = $simple->getData($attributeCode);
                    }
                    
                    if ($key)
                    {
                        $strKey = implode(',', $key);
                        // @todo check settings:
                        // array key here is a combination of choosen options
                        $confData[$strKey] = array();

                        if(in_array('name',$reloadValues)){
                            $confData[$strKey]['name'] = $simple->getName();
                        }
                        if(in_array('short_description', $reloadValues)){
                            $confData[$strKey]['short_description'] = $this->helper('catalog/output')->productAttribute($simple, nl2br($simple->getShortDescription()), 'short_description');
                        }
                        if(in_array('description', $reloadValues)){
                            $confData[$strKey]['description' ]  = $this->helper('catalog/output')->productAttribute($simple, $simple->getDescription(), 'description');
                        }
                        if(in_array('attributes', $reloadValues)){
                            $_currProduct = Mage::registry('product');
                            if (!is_null(Mage::registry('product')))
                            {
                                Mage::unregister('product');
                            }
                            Mage::register('product', $simple);
                            $confData[$strKey]['attributes']       = Mage::app()->getLayout()->createBlock('catalog/product_view_attributes', 'product.attributes.child', array('template' => "catalog/product/view/attributes.phtml"))->setProduct($simple)->toHtml() ;
                            if (!is_null(Mage::registry('product')))
                            {
                                Mage::unregister('product');
                            }
                            Mage::register('product', $_currProduct);
                        }                        

                        $confData[$strKey]['not_is_in_stock' ]  = !$simple->isSaleable();
                             //$confData[$strKey]['sku' ] = $simple->getSku();
                        
                        if ($_useSimplePrice)
                        {
                            $tierPriceHtml = $this->getTierPriceHtml($simple);
                            $confData[$strKey]['price_html'] = str_replace('product-price-' . $simple->getId(), 'product-price-' . $this->getProduct()->getId(), $this->getPriceHtml($simple) . $tierPriceHtml);
                            $confData[$strKey]['price_clone_html'] = str_replace('product-price-' . $simple->getId(), 'product-price-' . $this->getProduct()->getId(), $this->getPriceHtml($simple, false, '_clone') . $tierPriceHtml);

                            if ( $dropdownPrice ) {
                                $confData[$strKey]['price'] = Mage::helper('core')->currency($simple->getFinalPrice(), false, false);
                                $confData['dropdownPrice'] = $dropdownPrice;
                            }
                        }
                        
                        if ($simple->getImage() && $simple->getImage() !="no_selection" && in_array('image', $reloadValues))
                        {
                            $url  = $this->getUrl('amconf/ajax', array('id' => $simple->getId()));
                            if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
                            {
                                $url = str_replace('http:', 'https:', $url);
                            }
                            $confData[$strKey]['media_url'] = $url;
                            if(Mage::getStoreConfig('amconf/general/oneselect_reload')) {
                                $k = $strKey;
                                if(strpos($strKey, ',')){
                                    $k = substr($strKey, 0, strpos($strKey, ','));
                                }
                                if(!(array_key_exists($k, $confData) && array_key_exists('media_url', $confData[$k]))){
                                    $confData[$k]['media_url'] = $confData[$strKey]['media_url']; 
                                }
                            }
                            else{
                                //for changing only after first select 
                            }
                        }
                    }
                }
                if (Mage::getStoreConfig('amconf/general/show_clear'))
                {
                    $html = '<a href="#" onclick="javascript: spConfig.clearConfig(); return false;">' . $this->__('Reset Configuration') . '</a>' . $html;
                }
                $url  = $this->getUrl('amconf/ajax', array('id' => $this->getProduct()->getId()));
                if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
                {
                    $url = str_replace('http:', 'https:', $url);
                }

                $confData['selector']['name'] = Mage::getStoreConfig('amconf/css_selector/name');
                $confData['selector']['short_description'] = Mage::getStoreConfig('amconf/css_selector/short_description');
                $confData['selector']['description'] = Mage::getStoreConfig('amconf/css_selector/description');
                $confData['selector']['attributes'] = Mage::getStoreConfig('amconf/css_selector/attributes_block');

                $html = '<script type="text/javascript">
                            try{
                                var amConfAutoSelectAttribute = ' . intval(Mage::getStoreConfig('amconf/general/auto_select_attribute')) . ';
                                confData = new AmConfigurableData(' . Zend_Json::encode($confData) . ');
                                confData.textNotAvailable = "' . $this->__('Choose previous option please...') . '";
                                confData.mediaUrlMain = "' . $url . '";
                                confData.oneAttributeReload = "' . (boolean)Mage::getStoreConfig('amconf/general/oneselect_reload') . '";
                                confData.imageContainer = "' . Mage::getStoreConfig('amconf/css_selector/image') . '";
                                confData.useSimplePrice = "' . intval($_useSimplePrice) . '";
                            }
                            catch(ex){}
                        </script>'. $html;
            }
        }

        return $html;
    }
    
    protected function getImagesFromProductsAttributes(){
        $collection = Mage::getModel('amconf/product_attribute')->getCollection();
        $collection->addFieldToFilter('use_image_from_product', 1);
        
        $collection->getSelect()->join( array(
            'prodcut_super_attr' => $collection->getTable('catalog/product_super_attribute')),
                'main_table.product_super_attribute_id = prodcut_super_attr.product_super_attribute_id', 
                array('prodcut_super_attr.attribute_id')
            );
        
        $collection->addFieldToFilter('prodcut_super_attr.product_id', $this->getProduct()->getEntityId());
        
        
        $attributes = $collection->getItems();
        $ret = array();
        
        foreach($attributes as $attribute){
            $ret[] = $attribute->getAttributeId();
        }
        
        return $ret;
    }

  public function _getJsonConfig()
  {
    $attributes = array();
    $options    = array();
    $store      = $this->getCurrentStore();
    $taxHelper  = Mage::helper('tax');
    $currentProduct = $this->getProduct();

    $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
    if ($preconfiguredFlag) {
      $preconfiguredValues = $currentProduct->getPreconfiguredValues();
      $defaultValues       = array();
    }

    foreach ($this->getAllowProducts() as $product) {
      $productId  = $product->getId();
      $isConfigRuffle = Mage::helper('ruffle')->checkProductAssignToRuffle($currentProduct);
      if($isConfigRuffle){
        $isWinnerRuffle = Mage::helper('ruffle')->checkWinnerRuffle($currentProduct);
        if(!$isWinnerRuffle){
          $isSimpleRuffleQuota = Mage::helper('ruffle')->checkProductRunningRuffle($product);
          if(!$isSimpleRuffleQuota){
            continue;
          }
        }
      }

      foreach ($this->getAllowAttributes() as $attribute) {
        $productAttribute   = $attribute->getProductAttribute();
        $productAttributeId = $productAttribute->getId();
        $attributeValue     = $product->getData($productAttribute->getAttributeCode());
        if (!isset($options[$productAttributeId])) {
          $options[$productAttributeId] = array();
        }

        if (!isset($options[$productAttributeId][$attributeValue])) {
          $options[$productAttributeId][$attributeValue] = array();
        }
        $options[$productAttributeId][$attributeValue][] = $productId;
      }
    }

    $this->_resPrices = array(
      $this->_preparePrice($currentProduct->getFinalPrice())
    );

    foreach ($this->getAllowAttributes() as $attribute) {
      $productAttribute = $attribute->getProductAttribute();
      $attributeId = $productAttribute->getId();
      $info = array(
        'id'        => $productAttribute->getId(),
        'code'      => $productAttribute->getAttributeCode(),
        'label'     => $attribute->getLabel(),
        'options'   => array()
      );

      $optionPrices = array();
      $prices = $attribute->getPrices();
      if (is_array($prices)) {
        foreach ($prices as $value) {
          if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
            continue;
          }
          $currentProduct->setConfigurablePrice(
            $this->_preparePrice($value['pricing_value'], $value['is_percent'])
          );
          $currentProduct->setParentId(true);
          Mage::dispatchEvent(
            'catalog_product_type_configurable_price',
            array('product' => $currentProduct)
          );
          $configurablePrice = $currentProduct->getConfigurablePrice();

          if (isset($options[$attributeId][$value['value_index']])) {
            $productsIndex = $options[$attributeId][$value['value_index']];
          } else {
            $productsIndex = array();
          }

          $info['options'][] = array(
            'id'        => $value['value_index'],
            'label'     => $value['label'],
            'price'     => $configurablePrice,
            'oldPrice'  => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
            'products'  => $productsIndex,
          );
          $optionPrices[] = $configurablePrice;
        }
      }
      /**
       * Prepare formated values for options choose
       */
      foreach ($optionPrices as $optionPrice) {
        foreach ($optionPrices as $additional) {
          $this->_preparePrice(abs($additional-$optionPrice));
        }
      }
      if($this->_validateAttributeInfo($info)) {
        $attributes[$attributeId] = $info;
      }

      // Add attribute default value (if set)
      if ($preconfiguredFlag) {
        $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
        if ($configValue) {
          $defaultValues[$attributeId] = $configValue;
        }
      }
    }

    $taxCalculation = Mage::getSingleton('tax/calculation');
    if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
      $taxCalculation->setCustomer(Mage::registry('current_customer'));
    }

    $_request = $taxCalculation->getDefaultRateRequest();
    $_request->setProductClassId($currentProduct->getTaxClassId());
    $defaultTax = $taxCalculation->getRate($_request);

    $_request = $taxCalculation->getRateRequest();
    $_request->setProductClassId($currentProduct->getTaxClassId());
    $currentTax = $taxCalculation->getRate($_request);

    $taxConfig = array(
      'includeTax'        => $taxHelper->priceIncludesTax(),
      'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
      'showBothPrices'    => $taxHelper->displayBothPrices(),
      'defaultTax'        => $defaultTax,
      'currentTax'        => $currentTax,
      'inclTaxTitle'      => Mage::helper('catalog')->__('Incl. Tax')
    );

    $config = array(
      'attributes'        => $attributes,
      'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
      'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
      'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
      'productId'         => $currentProduct->getId(),
      'chooseText'        => Mage::helper('catalog')->__('Choose an Option...'),
      'taxConfig'         => $taxConfig
    );

    if ($preconfiguredFlag && !empty($defaultValues)) {
      $config['defaultValues'] = $defaultValues;
    }

    $config = array_merge($config, $this->_getAdditionalConfig());

    return Mage::helper('core')->jsonEncode($config);
  }

    public function getJsonConfig()
    {
        $attributeIdsWithImages = array();
//        $jsonConfig = parent::getJsonConfig();
        $jsonConfig = $this->_getJsonConfig();
        $config = Zend_Json::decode($jsonConfig);
        $productImagesAttributes = $this->getImagesFromProductsAttributes();
      
        foreach ($config['attributes'] as $attributeId => $attribute)
        {
            $this->_currentAttributes[] = $attribute['code'];
            
            $attr = Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id');
            if ($attr->getUseImage())
            {
                $attributeIdsWithImages[] = $attributeId;
                $config['attributes'][$attributeId]['use_image']        = 1;
                $config['attributes'][$attributeId]['enable_carousel']  =  Mage::getStoreConfig('amconf/general/swatch_carou');
                $config['attributes'][$attributeId]['config']           = $attr->getData();
                
                $smWidth     = $attr->getSmallWidth() != "0"? $attr->getSmallWidth() : self::SMALL_SIZE;
                $smHeight    = $attr->getSmallHeight()!= "0"? $attr->getSmallHeight(): self::SMALL_SIZE;
                $bigWidth    = $attr->getBigWidth()!= "0"?    $attr->getBigWidth()   : self::BIG_SIZE;
                $bigHeight   = $attr->getBigHeight()!= "0"?   $attr->getBigHeight()  : self::BIG_SIZE;
            
                foreach ($attribute['options'] as $i => $option)
                {
                    if (in_array($attributeId, $productImagesAttributes)){
                        
                        foreach($option['products'] as $product_id){
                               
                            $product = Mage::getModel('catalog/product')->load($product_id);
                            $config['attributes'][$attributeId]['options'][$i]['image'] = 
                                (string)Mage::helper('catalog/image')->init($product, 'image')->resize($smWidth, $smHeight);
                            $config['attributes'][$attributeId]['options'][$i]['bigimage'] = 
                                (string)Mage::helper('catalog/image')->init($product, 'image')->resize($bigWidth, $bigHeight);
                            break;
                        }
                    }
                    else {
                        $imgUrl     = Mage::helper('amconf')->getImageUrl($option['id'], $smWidth, $smHeight);
                        $tooltipUrl = Mage::helper('amconf')->getImageUrl($option['id'], $bigWidth, $bigHeight);
                        if($imgUrl == ""){
                            $imgUrl     = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $smWidth, $smHeight);
                            $tooltipUrl = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $bigWidth, $bigHeight);
                        }
                        $config['attributes'][$attributeId]['options'][$i]['image'] = $imgUrl;
                        $config['attributes'][$attributeId]['options'][$i]['bigimage'] = $tooltipUrl;

                        $swatchModel = Mage::getModel('amconf/swatch')->load($option['id']);
                        $config['attributes'][$attributeId]['options'][$i]['color'] = $swatchModel->getColor();
                    }
                }
            }
        }
        Mage::unregister('amconf_images_attrids');
        Mage::register('amconf_images_attrids', $attributeIdsWithImages, true);

        return Zend_Json::encode($config);
    }
    
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }
        if ($this->getRequest()->getParam('wishlist_next')){
            $additional['wishlist_next'] = 1;
        }
        $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $addUrlValue = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
        $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);
        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }
    
    public function isSalable($product = null){
         $salable = parent::isSalable($product);
 
        if ($salable !== false) {
            $salable = false;
            if (!is_null($product)) {
                $this->setStoreFilter($product->getStoreId(), $product);
            }
 
            if (!Mage::app()->getStore()->isAdmin() && $product) {
                $collection = $this->getUsedProductCollection($product)
                    ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                    ->setPageSize(1)
                    ;
                if ($collection->getFirstItem()->getId()) {
                    $salable = true;
                }
            } else {
                foreach ($this->getUsedProducts(null, $product) as $child) {
                    if ($child->isSalable()) {
                        $salable = true;
                        break;
                    }
                }
            }
        }
 
        return $salable;
    }
    
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                /**
                * Should show all products (if setting set to Yes), but not allow "out of stock" to be added to cart
                */
                 if ($product->isSaleable() || Mage::getStoreConfig('amconf/general/out_of_stock') ) {
                    if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                    {
                        if (in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
                            $products[] = $product;
                        }
                    }
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }
   
}


