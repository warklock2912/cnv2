<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Adminhtml_AmCustomerAttrGroupSelectorController
    extends Mage_Adminhtml_Controller_Action
{
    public function getGroupDataAction()
    {
        $param = Mage::app()->getRequest()->getParam('param');

        if (!$param) {
            $this->getResponse()->setBody('');
            return;
        }

        $groupValues = Mage::getResourceModel('customer/group_collection')
            ->setRealGroupsFilter()
            ->load()
            ->toOptionArray();

        foreach ($groupValues as $key => $val) {
            $response[$val['value']] = $val['label'];
        }

        $result = Zend_Json::encode($response);

        $this->getResponse()->setBody($result);

    }
}