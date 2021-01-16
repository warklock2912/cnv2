<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Import_Edit_Tab_Mapping
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
        $this->setData('template', 'ecommerceteam/dataflow/import/profile/edit/mapping.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        return $this->getProfile()->getMapping();
    }
}
