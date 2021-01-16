<?php

class Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Import extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_blockGroup = 'rewardpointscsv';
        $this->_controller = 'adminhtml_rewardpointscsv';
        $this->_mode = 'import';
        $this->_updateButton('save', 'label', Mage::helper('rewardpointscsv')->__('Import'));
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_formScripts[] = "
            function importAndPrint(){
                editForm.submit('" . $this->getUrl('*/*/processImport', array(
                    'print' => 'true'
                )) . "');
            }
        ";
    }

    public function getHeaderText() {
        return Mage::helper('rewardpointscsv')->__('Import Points');
    }

}