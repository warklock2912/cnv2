<?php

 class Smethod_SMSOrder_Adminhtml_Config_Source_Paymentmethods
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            
            $payments = Mage::getSingleton('payment/config')->getActiveMethods();
            //$methods = array(array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('--Please Select--')));
            foreach ($payments as $paymentCode=>$paymentModel) {
                $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
                $methods[$paymentCode] = array(
                    'label'   => $paymentCode,
                    'value' => $paymentCode,
                );
            }

            $this->_options = $methods;
        }
        return $this->_options;
    }

}
