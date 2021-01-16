<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Import_Edit_Tab_Schedule
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return EcommerceTeam_Dataflow_Model_Profile_Import
     */
    public function getProfile()
    {
        return Mage::registry('profile');
    }
    protected function _prepareForm()
    {
        /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
        $profile = Mage::registry('profile');

        $form = new Varien_Data_Form();

        $fieldSet = $form->addFieldset('main_fieldset', array('legend' => $this->__('Cron Settings')));
        $fieldSet->addField('schedule', 'text', array(
            'note'  => $this->__('For example: */30 * * * *'),
            'name'     => 'schedule',
            'label'    => $this->__('Cron Time'),
            'title'    => $this->__('Cron Time'),
            'required' => false,
            'value'    => $profile->getData('schedule'),
        ));

        /** @var Varien_Object $scheduleConfig */
        $scheduleConfig = $profile->getScheduleConfig();

        $fieldSet->addField('schedule_config_file', 'text', array(
            'note'     => $this->__('Path to file that must be used for import, <br/>for example: <strong>var/import/data.csv</strong>'),
            'name'     => 'schedule_config[file]',
            'label'    => $this->__('Data File'),
            'title'    => $this->__('Data File'),
            'required' => false,
            'value'    => $scheduleConfig->getData('file'),
        ));

        $fieldSet->addField('schedule_config_delete_file_after_process', 'checkbox', array(
            'name'     => 'schedule_config[delete_file_after_process]',
            'label'    => $this->__('Delete file after process data'),
            'title'    => $this->__('Delete file after process data'),
            'required' => false,
            'checked'  => (bool) $scheduleConfig->getData('delete_file_after_process'),
            'value'    => 'true',
        ));

        $this->setForm($form);
    }
}
