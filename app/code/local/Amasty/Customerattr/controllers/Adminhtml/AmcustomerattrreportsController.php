<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Adminhtml_AmCustomerAttrReportsController
    extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_title($this->__('View Customer Reports'));

        $this->loadLayout();
        $this->_addContent(
            $this->getLayout()->createBlock(
                'amcustomerattr/adminhtml_customer_reports'
            )
        )
            ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = Mage::getModel('catalog/entity_attribute');

        if ($id) {
            $model->load($id);
            $this->_title(
                $this->__(
                    'View Report for Attribute "%s"', $model->getAttributeCode()
                )
            );

            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('catalog')->__(
                        'This attribute no longer exists'
                    )
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::register('entity_attribute', $model);

        $this->loadLayout();
        $this->_addBreadcrumb(
            Mage::helper('amcustomerattr')->__('View Report'),
            Mage::helper('catalog')->__('View Report')
        )
            ->_addContent(
                $this->getLayout()->createBlock(
                    'amcustomerattr/adminhtml_customer_reports_edit'
                )
            )
            ->_addLeft(
                $this->getLayout()->createBlock(
                    'amcustomerattr/adminhtml_customer_reports_edit_tabs'
                )
            )
            ->renderLayout();
    }

}