<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Adminhtml_AmCustomerAttrActivationController
    extends Mage_Adminhtml_Controller_Action
{

    public function massDeactivateAction()
    {
        $customerIds = $this->getRequest()->getParam('customer');

        $this->_checkCustomerIds($customerIds);

        try {
            $this->_processActivation($customerIds, '1');
            $this->_addSuccessMessage(
                Mage::helper('adminhtml')->__(
                    'Total of %d customers(s) were successfully deactivated',
                    count($customerIds)
                )
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('adminhtml/customer/index');
    }

    protected function _checkCustomerIds($ids)
    {
        if (!is_array($ids)) {
            $this->_addErrorMessage(
                Mage::helper('adminhtml')->__('Please select item(s)')
            );
            $this->_redirect('adminhtml/customer/index');
        }
    }

    protected function _addErrorMessage($message)
    {
        Mage::getSingleton('adminhtml/session')->addError($message);
    }

    protected function _processActivation($ids, $key)
    {
        foreach ($ids as $customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customer->setAmIsActivated($key);
            $customer->save();
        }
    }

    protected function _addSuccessMessage($message)
    {
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    public function massActivateAction()
    {
        $customerIds = $this->getRequest()->getParam('customer');

        $this->_checkCustomerIds($customerIds);

        try {
            $this->_processActivation($customerIds, '2');
            $this->_addSuccessMessage(
                Mage::helper('adminhtml')->__(
                    'Total of %d customers(s) were successfully activated',
                    count($customerIds)
                )
            );
        } catch (Exception $e) {
            $this->_addErrorMessage($e->getMessage());
        }

        $this->_redirect('adminhtml/customer/index');
    }

}