<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_MinMaxQtyOrderPerCate
* @author     Extension Team
* @copyright  Copyright (c) 2014-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/

class Bss_MinMaxQtyOrderPerCate_Block_Config_OptionMinMaxQty extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    protected $_categoryRenderer;
    protected $_groupRenderer;

    protected function  _getCateRenderer() 
    {
        if (!$this->_categoryRenderer) {
            $this->_categoryRenderer = $this->getLayout()->createBlock(
                'minmaxqtyorderpercate/config_category', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_categoryRenderer;
    }

    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'minmaxqtyorderpercate/config_customergroup', '',
                array('is_render_to_js_template' => true)
            );
            $this->_groupRenderer->setClass('customer_group_select');
            $this->_groupRenderer->setExtraParams('style="width:170px"');
        }
        return $this->_groupRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn('customer_group_id', array(
            'label' => Mage::helper('minmaxqtyorderpercate')->__('Customer Group'),
            'renderer' => $this->_getGroupRenderer(),
        ));
        $this->addColumn('category_id', array(
            'label' => Mage::helper('minmaxqtyorderpercate')->__('Category'),
            'renderer' => $this->_getCateRenderer(),
        ));
        $this->addColumn('min_sale_qty', array(
            'label' => Mage::helper('minmaxqtyorderpercate')->__('Min Qty'),
            'style' => 'width:50px',
        ));
        $this->addColumn('max_sale_qty', array(
            'label' => Mage::helper('minmaxqtyorderpercate')->__('Max Qty'),
            'style' => 'width:50px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('minmaxqtyorderpercate')->__('Add Qty');
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getCateRenderer()->calcOptionHash($row->getData('category_id')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('customer_group_id')),
            'selected="selected"'
        );
    }
}
