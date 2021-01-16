<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Pdfinvoiceplus Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @author      Magestore Developer
 */
class Magestore_Pdfinvoiceplus_Block_Totals_Order extends Mage_Sales_Block_Order_Totals
{
    protected function _prepareLayout() {
        parent::__construct();
        $taxBlock = $this->getLayout()->createBlock('pdfinvoiceplus/totals_tax')
                ->setTemplate('pdfinvoiceplus/tax/order/tax.phtml');
        $this->setChild('tax',$taxBlock);
    }
    protected function _initTotals()
    {
        parent::_initTotals();
    }
    
     public function getOrder()
    {
        if ($this->_order === null) {
            if (Mage::registry('current_order')) {
                $this->_order = Mage::registry('current_order');
            } else {
                $order_Id = $this->getRequest()->getParam('order_id');
                $this->_order = Mage::getModel('sales/order')->load($order_Id);
            }
        }
        return $this->_order;
    }
}