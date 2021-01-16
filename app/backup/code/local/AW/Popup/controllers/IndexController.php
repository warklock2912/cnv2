<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Popup
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Popup_IndexController extends Mage_Core_Controller_Front_Action
{
    public function ajaxAction()
    {
        $response = new Varien_Object();
        $response->setError(0);
        try {
            $helper = Mage::helper('popup');
            if ($helper->hideByUserAgent()) {
                throw new Exception('Ignored User-Agent');
            }
            $popup = $helper->getPopup();

            if ($popup !== null && $popup->getId()) {
                $helper->setViewedPopup($popup->getId());
                $response->addData($popup->getData());
                $autoHideTime = Mage::helper('popup')->getAutoHide();
                if ($autoHideTime > 0) {
                    $response->setAutoHideTime($autoHideTime);
                }
            } else {
                throw new Exception('Popup not found');
            }

        } catch (Exception $e) {
            $response->setError(1);
            $response->setErrorMessage($e->getMessage());
        }
        $this->getResponse()->setBody($response->toJson());
        return;
    }
}
