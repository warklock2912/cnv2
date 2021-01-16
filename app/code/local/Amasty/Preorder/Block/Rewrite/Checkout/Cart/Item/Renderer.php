<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


class Amasty_Preorder_Block_Rewrite_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * @return string
     */
    public function getProductName()
    {
        $productName = parent::getProductName();
        if ($this->isNeedNote() && Mage::helper('ampreorder')->getQuoteItemIsPreorder($this->getItem())) {
            $productName .= ' (' . Mage::helper('ampreorder')->getQuoteItemPreorderNote($this->getItem()) . ')';
        }

        return $productName;
    }

    /**
     * @return bool
     */
    public function isNeedNote()
    {
        return !in_array(get_class($this->getRenderedBlock()), array(
            'Mage_Checkout_Block_Cart'
        ));
    }
}
