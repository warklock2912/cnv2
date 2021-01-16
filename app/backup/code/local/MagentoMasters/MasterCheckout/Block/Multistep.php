<?php
class MagentoMasters_MasterCheckout_Block_Multistep extends Mage_Checkout_Block_Onepage
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * Define steps for MasterCheckout
     * @return array
     */
    public function getSteps()
    {
        $steps = array(
            'login' => array(
//                'label' => $this->__('Personal information'),
                'label' => Mage::getStoreConfig('mastercheckout/steptitles/step1'),
                'allow' => true,
                'is_show' => true,
                'child_htmls' => array(
                    'login' => !$this->isCustomerLoggedIn(),
                    'shipping' => true,
                    'billing' => true,
                    'step1footer' => true
                ),
            ),
            'payment' => array(
//                'label' => $this->__('Payment & Shipment'),
                'label' => Mage::getStoreConfig('mastercheckout/steptitles/step2'),
                'allow' => true,
                'is_show' => true,
                'child_htmls' => array(
                    'payment' => true,  'shipping_method' => true, 'step2footer' => true
                ),
            ),
            'review' => array(
//                'label' => $this->__('Control'),
                'label' => Mage::getStoreConfig('mastercheckout/steptitles/step3'),
                'allow' => true,
                'is_show' => true,
                'child_htmls' => array(
                    'review' => true
                ),
            ),

        );
        return $steps;
    }
}