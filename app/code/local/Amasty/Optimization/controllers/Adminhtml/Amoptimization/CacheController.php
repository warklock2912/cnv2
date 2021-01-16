<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Adminhtml_Amoptimization_CacheController extends Mage_Adminhtml_Controller_Action
{
    public function flushAction()
    {
        try {
            Mage::helper('amoptimization')->flushCache();
            $this->_getSession()->addSuccess(
                $this->__('Minification cache has been cleaned.')
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                $this->__('An error occurred while clearing minification cache.')
            );
        }

        $this->_redirect('adminhtml/cache/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/cache');
    }
}
