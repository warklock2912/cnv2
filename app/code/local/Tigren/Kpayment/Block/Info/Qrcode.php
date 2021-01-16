<?php

class Tigren_Kpayment_Block_Info_Qrcode extends Mage_Payment_Block_Info
{
    protected $_instructions;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payment/info/tigren/kpaymentqrcode.phtml');
    }

    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getInfo()->getAdditionalInformation('instructions');
            if(empty($this->_instructions)) {
                $this->_instructions = $this->getMethod()->getInstructions();
            }
        }
        return $this->_instructions;
    }
}