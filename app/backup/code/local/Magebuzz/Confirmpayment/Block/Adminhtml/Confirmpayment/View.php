<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */
class Magebuzz_Confirmpayment_Block_Adminhtml_Confirmpayment_View extends Mage_Adminhtml_Block_Widget_Form_Container {

  public function __construct() {
    parent::__construct();
    $this->setTemplate('confirmpayment/submit/view.phtml');
    $this->_removeButton('reset');
//    $this->_removeButton('save');
    $this->_removeButton('delete');
  }

  public function getSubmit() {
    /** @var Amasty_Customform_Model_Form_Submit $submit */
    $submit = Mage::registry('confirmpayment_current_submit');

    return $submit;
  }

  public function getValuesData() {
    $values = $this->getSubmit()->getData();
    unset($values['form_id']);
//    unset($values['status']);
    return $values;
  }



}
