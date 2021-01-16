<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_asyncindex
 * @version   1.1.13
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_AsyncIndex_Block_Adminhtml_System_Config_Form_Field_ValidateProductIndex extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (!Mage::getStoreConfigFlag(Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT)) {
            $element->setDisabled('disabled')
                ->setValue(0)
                ->setComment('<span style="color:red">Product Flat Catalog</span> must be enabled for use this option. <br> For enable flat catalog, go to System > Configuration > Catalog > Frontend and set "Use Flat Catalog Product" to "Yes"');
        }

        return parent::_getElementHtml($element);
    }

}
