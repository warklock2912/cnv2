<?php	 

	class Mage_P2c2p_Block_Form_P2c2p extends Mage_Payment_Block_Form
	{
		protected function _construct()
		{			
			parent::_construct();
			$this->setTemplate('p2c2p/form/p2c2p.phtml');
		}
        public function getCcMonths()
        {
            $months = $this->getData('cc_months');
            if (is_null($months)) {
                $months[0] =  $this->__('Month');
                $months = array_merge($months, $this->_getConfig()->getMonths());
                $this->setData('cc_months', $months);
            }
            return $months;
        }
        public function getCcYears()
        {
            $years = $this->getData('cc_years');
            if (is_null($years)) {
                $years = $this->_getConfig()->getYears();
                $years = array(0 => $this->__('Year')) + $years;
                $this->setData('cc_years', $years);
            }

            return $years;
        }
        protected function _getConfig()
        {
            return Mage::getSingleton('payment/config');
        }
	}


