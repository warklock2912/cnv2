<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Mageplace_FastProductUpdate
 */

class Mageplace_FastProductUpdate_Model_Observer
{
	public function processAdminhtmlCoreBlockAbstractPrepareLayoutBefore($observer)
	{
		if ($observer->getData('block')->getData('type') == 'adminhtml/catalog_product') {
			if (Mage::getSingleton('admin/session')->isAllowed(Mageplace_FastProductUpdate_Helper_Const::ACL_INVENTORY_PATH)) {
				$js = "if($('fastinventoryuploadarea')) {"
					. "$('fastinventoryuploadarea').remove()"
					. "} else {"
					. "$$('.wrapper .content-header')[0].insert('"
					. "<div style=\'text-align:right\' id=\'fastinventoryuploadarea\' class=\'switcher\'>"
					. "<form id=\'csvinventoryuploadform\' action=\'" . Mage::helper('adminhtml')->getUrl('*/mpfastproductupdate_inventory/upload') . "\' enctype=\'multipart/form-data\' method=\'post\'>"
					. "<label for=\'fast_inventory_update\'>" . Mage::helper('mpfastproductupdate')->__('Select File to Import') . ":</label>&nbsp;"
					. "<input type=\'file\' id=\'fast_inventory_update\' name=\'" . (Mageplace_FastProductUpdate_Helper_Const::FILE_FIELD_NAME) . "\' value=\'\' />"
					. "<input type=\'hidden\' name=\'form_key\' value=\'" . Mage::getSingleton('core/session')->getFormKey() . "\'/>"
					. "<button type=\'submit\' class=\'scalable\'><span>" . Mage::helper('mpfastproductupdate')->__('Upload') . "</span></button>"
					. "<br /><br /><button type=\'button\' class=\'scalable\' onclick=\'location.href=&quot;" . Mage::helper('adminhtml')->getUrl('*/mpfastproductupdate_inventory/export') . "&quot;;\'><span>" . Mage::helper('mpfastproductupdate')->__('Export') . "</span></button>"
					. "</form>"
					. "</div>"
					. "');"
					. "}";

				$observer->getData('block')->addButton(
					'inventory_upload',
					array(
						'label' => Mage::helper('mpfastproductupdate')->__('Smart Stock Update'),
						'onclick' => $js,
						'class' => '',
					),
					1
				);
			}
		}
	}
}