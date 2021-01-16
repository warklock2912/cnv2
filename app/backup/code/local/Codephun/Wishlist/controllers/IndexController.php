<?php

/**
 * Wishlist front controller
 *
 * @category    Codephun
 * @package     Codephun_Wishlist
 * @author      Bjarne Oeverli <my@email.com>
 */
require_once Mage::getModuleDir('controllers', 'Mage_Wishlist') . DS . 'IndexController.php';
class Codephun_Wishlist_IndexController extends Mage_Wishlist_IndexController
{
	public function removeAction()
    {
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('wishlist/item')->load($id);
        if (!$item->getId()) {
            return $this->norouteAction();
        }
        $wishlist = $this->_getWishlist($item->getWishlistId());
        if (!$wishlist) {
            return $this->norouteAction();
        }
        try {
			Mage::getSingleton('core/session')->addSuccess('Item has been removed from Wishlist successfully'); 
            $item->delete();
            $wishlist->save();
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('customer/session')->addError(
                $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage())
            );
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(
                $this->__('An error occurred while deleting the item from wishlist.')
            );
        }

        Mage::helper('wishlist')->calculate();

        $this->_redirectReferer(Mage::getUrl('*/*'));
    }
}