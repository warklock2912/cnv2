<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */
class Amasty_Payrestriction_Block_Adminhtml_Rule_Edit_Tab_Daystime extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

            /* @var $hlp Amasty_Payrestriction_Helper_Data */
        $hlp = Mage::helper('ampayrestriction');

        $fldInfo = $form->addFieldset('daystime', array('legend'=> $hlp->__('Days and Time')));

        $fldInfo->addField('days', 'multiselect', array(
            'label'     => $hlp->__('Days of the week'),
            'name'      => 'days[]',
            'values'    => $hlp->getAllDays(),
            'note'      => $hlp->__('Leave empty or select all to apply the rule every day'),
        ));

        $fldInfo->addField('time_from', 'select', array(
            'label'     => $hlp->__('Time From:'),
            'name'      => 'time_from',
            'options'   => $hlp->getAllTimes(),
        ));

        $fldInfo->addField('time_to', 'select', array(
            'label'     => $hlp->__('Time To:'),
            'name'      => 'time_to',
            'options'   => $hlp->getAllTimes(),
        ));

        //set form values
        $form->setValues(Mage::registry('ampayrestriction_rule')->getData());

        return parent::_prepareForm();
    }
}
