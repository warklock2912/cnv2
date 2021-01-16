<?php

require_once Mage::getBaseDir().'/lib/omise-php/lib/Omise.php';
$config = Mage::getModel('omise_gateway/config')->load(1);
if ($config->getTestMode()) {
    define('OMISE_PUBLIC_KEY', $config->getPublicKeyTest());
    define('OMISE_SECRET_KEY', $config->getSecretKeyTest());
} else {
    define('OMISE_PUBLIC_KEY', $config->getPublicKey());
    define('OMISE_SECRET_KEY', $config->getSecretKey());
}

class Tigren_Ruffle_Helper_Omise extends Mage_Core_Helper_Abstract
{
    public function getCustomerOmise($customerOmiseId)
    {
        $customerOmise = OmiseCustomer::retrieve($customerOmiseId);

        return $customerOmise;
    }

    public function getListCardCustomerOmise($customerOmiseId)
    {
        if ($customerOmiseId) {
            $card = $this->getCustomerOmise($customerOmiseId)->getCards()->getValueCardList();

            return $card;
        }

        return false;
    }

    public function getDefaultIdCardCustomerOmise($customerOmiseId)
    {
        if (!$customerOmiseId) {
            return false;
        }

        $customerOmise = $this->getCustomerOmise($customerOmiseId)->getValue();
        if (!empty($customerOmise['default_card'])) {
            return $customerOmise['default_card'];
        } else {
            return $this;
        }
    }

    public function getDefaultCardCustomerOmise($customerOmiseId, $cardId)
    {
        $card = $this->getCustomerOmise($customerOmiseId)->getCards()->retrieve($cardId);

        return $card->getValue();
    }

    public function createCustomerOmise($customer, $omiseToken)
    {
        $customerApi = OmiseCustomer::create(array(
            'email' => $customer->getEmail(),
            'description' => $customer->getName(),
            'card' => $omiseToken,
        ));

        return $customerApi;
    }

    public function updateInvoiceFromChargeID($chargeId)
    {
        $charge = Mage::getModel('omise_gateway/api_charge')->find($chargeId);
        $charge->capture();

        return $charge;

    }
}
