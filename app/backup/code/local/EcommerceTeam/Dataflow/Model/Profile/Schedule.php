<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Profile_Schedule
    extends Mage_Cron_Model_Schedule
{
    /**
     * Class constructor
     */
    public function _construct()
    {
        $this->_init('ecommerceteam_dataflow/profile_schedule');
    }
}