<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Block_Adminhtml_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    /**
     * @param Varien_Data_Tree_Node $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());
        if ($this->_withProductCount) {
            $result .= sprintf(
                '<span id="category_products_count_%d">(%d)</span>',
                $node->getId(),
                $node->getProductCount()
            );
        }

        return $result;
    }
}
