<?php

/**
 * Stabeaddon
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Stabeaddon.com license that is
 * available through the world-wide-web at this URL:
 * http://www.stabeaddon.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Rewardpoint
 * @copyright   Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/**
 * Rewardpoint Observer Model
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Rewardpoint
 * @author      Stabeaddon Developer
 */
class Magebuzz_Countingdown_Model_Observer {

  /**
   * process controller_action_predispatch event
   *
   * @return Stableaddon_Rewardpoint_Model_Observer
   */
  public function insertBlock($observer) {

    $storeName = Mage::app()->getStore()->getName();


    $controller = Mage::app()->getRequest()->getControllerName();

    $actions = Mage::app()->getRequest()->getActionName();


    $_block = $observer->getBlock();

    /* get Block type */
    $_type = $_block->getType();

    ZEND_DEBUG::dump($_block->getNameInLayout());
  }
}
