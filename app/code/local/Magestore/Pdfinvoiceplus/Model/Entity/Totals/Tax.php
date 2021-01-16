<?php
class Magestore_Pdfinvoiceplus_Model_Entity_Totals_Tax extends Mage_Core_Model_Abstract
{

    /**
     * 
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        $config = Mage::getSingleton('tax/config');
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());

        if ($config->displaySalesTaxWithGrandTotal($store))
        {
            return array();
        }

        $totalsm = array(array(
                'all_tax_ammount' => array(
                    'value' => $this->getAmountPrefix() . $amount,
                    'label' => Mage::helper('pdfinvoiceplus')->__('Tax') . ':',
            )));
        $totalsf = $this->getFullTaxInfo();
        $totals = array_merge($totalsm, $totalsf);

        return $totals;
    }

    public function getFullTaxInfo()
    {
        $taxClassAmount = Mage::helper('tax')->getCalculatedTaxes($this->getOrder());

        if (!empty($taxClassAmount))
        {
            $shippingTax = Mage::helper('tax')->getShippingTax($this->getOrder());
            $taxClassAmount = array_merge($shippingTax, $taxClassAmount);

            foreach ($taxClassAmount as $tax)
            {

                $taxTitle = Mage::Helper('core/string')->cleanString($tax['title']);
                $taxTitle = 'tax_' . ereg_replace("[^A-Za-z0-9]", "", $taxTitle);
                $taxTitle = strtolower($taxTitle);
                $percent = $tax['percent'] ? ' (' . $tax['percent'] . '%)' : '';

                $tax_info[] = array(
                    $taxTitle => array(
                        'value' => $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($tax['tax_amount']),
                        'label' => Mage::helper('tax')->__($tax['title']) . $percent . ':'
                        ));
                $taxClassAmount = $tax_info;
            }
        } else
        {
            $rates = Mage::getResourceModel('sales/order_tax_collection')->loadByOrder($this->getOrder())->toArray();
            $fullInfo = Mage::getSingleton('tax/calculation')->reproduceProcess($rates['items']);
            $tax_info = array();

            if ($fullInfo)
            {
                foreach ($fullInfo as $info)
                {
                    if (isset($info['hidden']) && $info['hidden'])
                    {
                        continue;
                    }

                    $_amount = $info['amount'];

                    foreach ($info['rates'] as $rate)
                    {
                        $percent = $rate['percent'] ? ' (' . $rate['percent'] . '%)' : '';

                        $taxTitle = Mage::Helper('core/string')->cleanString($rate['title']);
                        $taxTitle = 'tax_' . ereg_replace("[^A-Za-z0-9]", "", $taxTitle);
                        $taxTitle = strtolower($taxTitle);

                        $tax_info[] = array(
                            $taxTitle => array(
                                'value' => $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($_amount),
                                'label' => Mage::helper('tax')->__($rate['title']) . $percent . ':',
                                ));
                    }
                }
            }
            $taxClassAmount = $tax_info;
        }

        return $taxClassAmount;
    }

    /**
     * Check if we can display total information in PDF
     *
     * @return bool
     */
    public function canDisplay()
    {
        $amount = $this->getAmount();
        return $this->getDisplayZero() || ($amount != 0);
    }

    public function getAmount()
    {
        return $this->getSource()->getDataUsingMethod('tax_amount');
    }
}
?>