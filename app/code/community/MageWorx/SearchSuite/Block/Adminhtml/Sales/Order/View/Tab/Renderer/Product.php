<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Sales_Order_View_Tab_Renderer_Product extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Retrieve order model instance
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return Mage::registry('current_order');
    }

    public function render(Varien_Object $row) {
        $productId = $row->getData('product_id');
        $items = $this->getOrder()->getAllItems();
        $value = $productId;
        foreach ($items as $item) {
            if ($item->getProductId() == $productId) {
                $value = $item->getName();
                break;
            };
        }
        return $value;
    }

}
