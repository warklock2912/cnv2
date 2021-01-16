<?php

class Magestore_Pdfinvoiceplus_Model_Entity_Itemscreditmemo extends Magestore_Pdfinvoiceplus_Model_Entity_Creditmemogenerator {

     public function processAllVars()
    {
         /* value and label */
        $varData = array();
        foreach ($this->getTheItems() as $item) {
            $allKeysLabel = array();
            $allKeys = array();
            $allVars = array();
            /* Change by Zeus 08/12 */
            foreach (array_keys($item) as $v) {
                if(isset($v) && isset($item[$v]['value']) && isset($item[$v]['label'])){
                    if(is_array($item[$v]['label']) && is_array($item[$v]['value']))
                $allKeysLabel['label_'.$v] = $item[$v]['value'].' '.$item[$v]['label'];
                }
                
                if(isset($item[$v]['value'])){
                $allKeys[$v] = $item[$v]['value'];
                }
            }
            /* End change */
            $allVars = array_merge($allKeysLabel, $allKeys);
            $varData[] = $allVars;
        }

        return $varData;
    }

    /**
     * Get the items for the source
     * @return array
     */
    public function getTheItems()
    {
        foreach ($this->getSource()->getAllItems() as $item)
        {
            
            $this->setItem($item);
            if ($item->getOrderItem()->getParentItem())
            {
                $theParent = $item->getOrderItem()->getParentItem();
                if (Mage::helper('pdfinvoiceplus/product')->isConfigurable($theParent->getProductId()))
                {
                    continue;
                }
                $isChild = true;
            } else
            {
                $isChild = false;
            }
             
            $imageData = Mage::helper('pdfinvoiceplus/product')->getTheProductImage($item->getProductId());
            $itemsPriceData = $this->isPriceDisplayOptions($item);
            $userAttributeData = Mage::helper('pdfinvoiceplus/product')->getDataAsVar(
                    $item->getProductId(), $this->getOrder()->getStoreId(), $isChild);

            $standardVars = $this->getSandardItemVars($item);

            $productioptions = $this->getItemOptions();

            if (isset($productioptions))
            {
                $attr[] = array_merge($itemsPriceData, $userAttributeData, $standardVars, $productioptions, $imageData);
            } else
            {
                $attr[] = array_merge($itemsPriceData, $userAttributeData, $standardVars, $imageData);
            }
        }
        return $attr;
    }

    public function getSandardItemVars($item)
    {
        $order = $this->getOrder();
        //edit by zeus 26/01
        $allItems = $order->getAllItems();
            foreach($allItems as $allItem){
           $taxpercent = $allItem->getTaxPercent();
             }
       
        $taxpercentst = number_format($taxpercent,2,',','').'%';
          //end edit
        $nameStyle = $item->getName();
        	/* Change by Zeus 08/12 */
		$bunleOptiones = NULL;
		/* End change */
        if ($item->getOrderItem()->getParentItem())
        {
            $nameStyle = $this->getValueHtml($item);
            $bunleOptiones = $this->getSelectionAttributes($item);
        }
        if ($item->getTaxAmount())
        {
            $taxAmount = $order->formatPriceTxt($item->getTaxAmount());
        }
        if ($item->getDiscountAmount())
        {
            $discountAmount = $order->formatPriceTxt($item->getDiscountAmount());
        }
       if($item->getQty()){
           $qty = (int) $item->getQty();
       }
        $itemSku = implode('<br/>', Mage::helper('catalog')->splitSku($item->getSku()));
        $standardVars = array(
            'items_name' => array(
                'value' => $item->getName(),
                'label' => 'Product Name'
            ),
            'bundle_items_option' => array(
                'value' => $bunleOptiones['option_label'],
                'label' => 'Bundle Name'
            ),
            'items_sku' => array(
                'value' => $itemSku,
                'label' => 'SKU'
            ),
            'items_qty' => array(
                'value' => $qty,
                'label' => 'Qty'
            ),
            'items_tax_amount' => array(
                'value' => $taxAmount,
                'label' => 'Tax Amount'
            ),
            'items_discount_amount' => array(
                'value' => $discountAmount,
                'label' => 'Discount Amount'
            ),
            'items_tax_percent' => array(
                'value' => $taxpercentst,
                'label' => 'Tax Percent'
            )
        );
        return $standardVars;
    }

    /**
     * Get the Item prices for display - need to review this part adn move the item system to do
     * @return array
     */
    public function getItemPricesForDisplay()
    {
        $order = $this->getOrder();
        $store = $this->getSource()->getStore();
        $item = $this->getItem();
        foreach ($item->getData() as $key => $value){
            $price['items_'.$key] = array('value' => $value );
            if($key == 'price_incl_tax'){
                $price['items_price_incl_tax']= array(
                   'value' => $order->formatPriceTxt($item->getPriceInclTax())
                );
            }
            if($key == 'row_total_incl_tax'){
                $price['items_row_total_incl_tax']= array(
                   'value' => $order->formatPriceTxt($item->getRowTotalInclTax())
                );
            }
            if ($key == 'price') {
                $price['items_price'] = array(
                    'value' => $order->formatPriceTxt($item->getPrice())
                );
            }
            if ($key == 'row_total') {
                $price['items_row_total'] = array(
                    'value' => $order->formatPriceTxt($item->getRowTotal())
                );
            }
        }
        return $price;
    }

    /**
     * Retrieve item options
     *
     * @return array
     */
    public function getItemOptions()
    {
        $result = array();
        
        if ($options = $this->getItem()->getOrderItem()->getProductOptions())
        {
            
            $result = Mage::helper('pdfinvoiceplus/items')->getItemOptions($options);
        }
        return $result;
    }

    /**
     * Return item Sku
     *
     * @param  $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getOrderItem()->getProductOptionByCode('simple_sku'))
            return $item->getOrderItem()->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }

    public function isPriceDisplayOptions($item = null)
    {
        if ($item)
        {
            if ($this->isChildCalculated($item))
            {
                return $itemsPriceData = $this->getItemPricesForDisplay();
            } else
            {
                return $itemsPriceData = array();
            }
        } else
        {
            return null;
        }
    }
    
    public function getAttributes()
    {
        return Mage::helper('pdfinvoiceplus/product')->getDataAsVar();
    }
}

?>