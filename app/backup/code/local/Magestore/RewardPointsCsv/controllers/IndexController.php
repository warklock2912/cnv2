<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsCsv
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsCsv Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsCsv
 * @author      Magestore Developer
 */
class Magestore_RewardPointsCsv_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * index action
     */
    public function indexAction() {
        $collection = Mage::getModel('rewardpoints/customer')->getCollection();
//        $collection->getSelect()->join(array('customer_entity'=>Mage::getModel('core/resource')->getTableName('customer/entity'),
//            'main_table.customer_id = customer_entity.entity_id', array('table_alias.*')));
        $collection->getSelect()->joinLeft(array('customer_entity' => Mage::getModel('core/resource')->getTableName('customer/entity')), 'main_table.customer_id = customer_entity.entity_id', array('customer_entity.*'));
        foreach ($collection as $customer) {
            Zend_Debug::dump($customer->getData());
        }
        Zend_Debug::dump($collection->getSelect()->__toString());
        die('8888');
        $this->loadLayout();
        $this->renderLayout();
    }

}