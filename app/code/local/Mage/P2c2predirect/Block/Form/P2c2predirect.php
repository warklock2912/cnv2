<?php

class Mage_P2c2predirect_Block_Form_P2c2predirect extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('p2c2predirect/form/p2c2p.phtml');
    }
}
