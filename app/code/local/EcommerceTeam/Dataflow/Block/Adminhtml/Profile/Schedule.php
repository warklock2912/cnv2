<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Schedule
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize class prefixes and labels
     */
    public function __construct()
    {
        $this->_blockGroup = 'ecommerceteam_dataflow';
        $this->_controller = 'adminhtml_profile_schedule';
        parent::__construct();
        $this->_headerText = $this->__('Schedule Log');
    }
}
