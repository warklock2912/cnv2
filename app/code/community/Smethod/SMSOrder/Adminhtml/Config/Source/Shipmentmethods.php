<?php

 class Smethod_SMSOrder_Adminhtml_Config_Source_Shipmentmethods
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            
            $shipments = Mage::getSingleton('shipping/config')->getActiveCarriers();
            //$methods = array(array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('--Please Select--')));
            foreach ($shipments as $shipCode=>$paymentModel) {
                // $paymentTitle = Mage::getStoreConfig('payment/'.$shipCode.'/title');
                $methods[$shipCode] = array(
                    'label'   => $shipCode,
                    'value' => $shipCode,
                );
            }

            $this->_options = $methods;
        }
        return $this->_options;
    }

}
