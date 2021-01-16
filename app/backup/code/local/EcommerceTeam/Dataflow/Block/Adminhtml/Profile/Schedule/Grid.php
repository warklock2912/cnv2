<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Schedule_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    
    /**
    * Init Grid default properties
    *
    */
    public function __construct()
    {
        parent::__construct();
        $this->setId('profile_schedule_grid');
        $this->setDefaultSort('scheduled_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }
    
    /**
    * @return EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Schedule_Grid
    */
    protected function _prepareCollection()
    {
        /** @var $collection EcommerceTeam_Dataflow_Model_Resource_Profile_Schedule_Collection */
        $collection = Mage::getResourceModel('ecommerceteam_dataflow/profile_schedule_collection');
        $collection->joinProfileInformation();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
    * @return EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Schedule_Grid
    */
    protected function _prepareColumns()
    {
        $this->addColumn('profile_id', array(
            'header'        => $this->__('Profile ID'),
            'index'         => 'profile_id',
            'width'         => '50px',
        ));

        $this->addColumn('profile_name', array(
            'header'        => $this->__('Profile Name'),
            'index'         => 'profile_name',
        ));

        $this->addColumn('status', array(
            'header'        => $this->__('Status'),
            'index'         => 'status',
        ));

        $this->addColumn('messages', array(
            'header'        => $this->__('Messages'),
            'index'         => 'messages',
        ));

        $this->addColumn('created_at', array(
            'header'        => $this->__('Created At'),
            'index'         => 'created_at',
            'type'      => 'datetime',
            'gmtoffset' => true
        ));

        $this->addColumn('scheduled_at', array(
            'header'        => $this->__('Scheduled At'),
            'index'         => 'scheduled_at',
            'type'      => 'datetime',
            'gmtoffset' => true
        ));

        $this->addColumn('executed_at', array(
            'header'        => $this->__('Executed At'),
            'index'         => 'executed_at',
            'type'      => 'datetime',
            'gmtoffset' => true
        ));

        $this->addColumn('finished_at', array(
            'header'        => $this->__('Finished At'),
            'index'         => 'finished_at',
            'type'      => 'datetime',
            'gmtoffset' => true
        ));

        return parent::_prepareColumns();
    }
    
    /**
    * @param Mage_Core_Model_Abstract $item
    * @return string
    */
    public function getRowUrl($item)
    {
        return false;
    }
}
