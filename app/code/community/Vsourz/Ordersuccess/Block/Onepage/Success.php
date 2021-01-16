<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Vsourz
 * @package     Vsourz_Ordersuccess
 * @copyright   Vsourz Ltd
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout success page
 *
 * @category   Vsourz
 * @package    Vsourz_Ordersuccess
 * @author     Aliasgar Bharmal
 */
class Vsourz_Ordersuccess_Block_Onepage_Success extends Mage_Checkout_Block_Onepage_Success
{
	public function getTopBlock(){
		$cmsBlockIdAbove=Mage::getStoreConfig('tab1/cms_block_section/cms_block_id_above');	
		if((Mage::getModel('cms/block')->load($cmsBlockIdAbove)->getIsActive())==true) {
			return $cmsBlockIdAbove;
		}
		else
		{
			return false;
		}
	}
	
	public function getBottomBlock(){
		$cmsBlockIdBelow=Mage::getStoreConfig('tab1/cms_block_section/cms_block_id_below');	
		if((Mage::getModel('cms/block')->load($cmsBlockIdBelow)->getIsActive())==true) {
			return $cmsBlockIdBelow;
		}
		else
		{
			return false;
		}
	}	
}
