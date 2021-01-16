<?php

/*
 * Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Confirmpayment_Model_Cpform extends Mage_Core_Model_Abstract {

  public function _construct() {
    parent::_construct();
    $this->_init('confirmpayment/cpform');
  }
  
  public function getValuesData()
    {
        $data = $this->getData();
        if(!empty($data)){
            try{
                $data = unserialize($data);
            }catch(Exception $e){
                $data = array();
            }
        }
        return $data;
    }

}
