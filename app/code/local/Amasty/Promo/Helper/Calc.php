<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

    class Amasty_Promo_Helper_Calc extends Mage_Core_Helper_Abstract
    {
        function getQuoteSubtotal($quote, $rule)
        {
            $subtotal = 0;

            foreach($quote->getItemsCollection() as $item){
                if ($rule->getActions()->validate($item) && !$item->getIsPromo()){
                    $subtotal += $item->getRowTotal();
                }
            }

            return $subtotal;
        }
    }
?>