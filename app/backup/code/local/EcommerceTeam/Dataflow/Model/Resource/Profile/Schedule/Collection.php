<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Resource_Profile_Schedule_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ecommerceteam_dataflow/profile_schedule');
    }

    public function joinProfileInformation()
    {
        $this->join(
            array('profile' => 'ecommerceteam_dataflow/profile_import'),
            'profile_id=entity_id',
            array('profile_name' => 'name')
        );

        return $this;
    }
}
