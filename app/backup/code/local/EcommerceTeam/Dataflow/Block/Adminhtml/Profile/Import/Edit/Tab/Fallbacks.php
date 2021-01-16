<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2016 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.5.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Import_Edit_Tab_Fallbacks
    extends Mage_Adminhtml_Block_Abstract
{
    /**
     * @return EcommerceTeam_Dataflow_Model_Profile_Import
     */
    public function getProfile()
    {
        return Mage::registry('profile');
    }

    protected function _construct()
    {
        $this->setData('template', 'ecommerceteam/dataflow/import/profile/edit/fallbacks.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getFallbacks()
    {
        return $this->getProfile()->getFallbacks();
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return array(
            'all' =>  $this->__('All Products'),
            'empty_only' => $this->__('Only when value is empty'),
            'not_empty' => $this->__('Only when value is not empty'),
        );
    }
}
