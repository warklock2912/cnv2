<?php

class Mage_P2c2predirect_Helper_Request extends Mage_Core_Helper_Abstract
{
    private $currency_helper, $hash_helper;
    private $p2c2predirect_form_fields = array(
        "version" => "",
        "merchant_id" => "",
        "payment_description" => "",
        "order_id" => "",
        "invoice_no" => "",
        "currency" => "",
        "amount" => "",
        "customer_email" => "",
        "pay_category_id" => "",
        "promotion" => "",
        "user_defined_1" => "",
        "user_defined_2" => "",
        "user_defined_3" => "",
        "user_defined_4" => "",
        "user_defined_5" => "",
        "result_url_1" => "",
        "result_url_2" => "",
        "payment_option" => "",
        "enable_store_card" => "",
        "stored_card_unique_id" => "",
        "request_3ds" => "",
        "payment_expiry" => "",
        "default_lang" => "",
        "statement_descriptor" => "",
        "hash_value" => "");

    function __construct()
    {
        $this->currency_helper = Mage::helper('p2c2predirect/CurrencyCode');
        $this->hash_helper = Mage::helper('p2c2predirect/Hash');
    }

    /* Generate the hidden form for make payment to 2c2p PG */

    public function p2c2predirect_construct_request($parameter)
    {

        $stored_card = Mage::getStoreConfig('payment/p2c2predirect/stored_card', Mage::app()->getStore());

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            if ($stored_card) {
                $enable_store_card = "Y";
                $this->p2c2predirect_form_fields["enable_store_card"] = $enable_store_card;

                if (!empty($parameter['stored_card_unique_id'])) {
                    $this->p2c2predirect_form_fields["stored_card_unique_id"] = $parameter['stored_card_unique_id'];
                }
            }
        }

        $this->p2c2predirect_create_common_form_field($parameter);
        $this->p2c2predirect_123_payment_expiry($parameter);

        $this->p2c2predirect_form_fields['hash_value'] = $this->hash_helper->create_hash($this->p2c2predirect_form_fields);

        $strHtml = '<form name="p2c2predirectform" action="'.$this->p2c2predirect_redirect_url().'" method="post"/>';
        foreach ($this->p2c2predirect_form_fields as $key => $value) {
            if (!empty($value)) {
                $strHtml .= '<input type="hidden" name="'.htmlentities($key).'" value="'.htmlentities($value).'">';
            }
        }

        $strHtml .= '<input type="hidden" name="request_3ds" value="">';
        $strHtml .= '</form>';
        $strHtml .= '<script type="text/javascript">';
        $strHtml .= 'document.p2c2predirectform.submit()';
        $strHtml .= '</script>';

        return $strHtml;
    }

    /* This function is used to get the calculate amount according to currency type. */
    public function p2c2predirect_get_amount_by_currency_type($currency_type, $amount)
    {

        $exponent = 0;
        $isFouned = false;

        foreach ($this->currency_helper->get_Currency_code() as $key => $value) {
            if ($key === $currency_type) {
                $exponent = $value['exponent'];
                $isFouned = true;
                break;
            }
        }

        if ($isFouned) {
            if ($exponent == 0 || empty($exponent)) {
                $amount = (int)$amount;
            } else {
                $pg_2c2p_exponent = $this->currency_helper->get_currency_exponent();
                $multi_value = $pg_2c2p_exponent[$exponent];
                $amount = ($amount * $multi_value);
            }
        }

        return str_pad($amount, 12, '0', STR_PAD_LEFT);
    }

    /* Creating basic payment form field that require in 2c2p PG  */

    function p2c2predirect_get_store_currency_code()
    {

        $currency_code = Mage::app()->getStore()->getCurrentCurrency()->getCode();

        foreach ($this->currency_helper->get_currency_code() as $key => $value) {
            if ($key === $currency_code) {
                return $value['Num'];
            }
        }

        return "";
    }

    /* Get currency code into number instead of character */

    function p2c2predirect_123_payment_expiry($paymentBody)
    {
        $payment_expiry = Mage::getStoreConfig('payment/p2c2predirect/payment_expiry_123', Mage::app()->getStore());

        if (empty($payment_expiry) || !isset($payment_expiry) || !is_numeric($payment_expiry)) {
            $payment_expiry = 8;
        }

        if (!($payment_expiry >= 8 && $payment_expiry <= 720)) {
            //Set default 123 payment expiry. If validation is failed from merchant configuration sections.
            $payment_expiry = 8;
        }

        $date = date("Y-m-d H:i:s");
        $strTimezone = date_default_timezone_get();
        $date = new DateTime($date, new DateTimeZone($strTimezone));
        $date->modify("+".$payment_expiry."hours");
        $payment_expiry = $date->format("Y-m-d H:i:s");

        $this->p2c2predirect_form_fields["payment_expiry"] = $payment_expiry;
    }

    /* Set 123 payment type payment expiry. */

    function p2c2predirect_redirect_url()
    {
        $test_mode = Mage::getStoreConfig('payment/p2c2predirect/gatewayurl', Mage::app()->getStore());

        if ($test_mode) {
            return 'https://demo2.2c2p.com/2C2PFrontEnd/RedirectV3/payment';
        } else {
            return 'https://t.2c2p.com/RedirectV3/payment';
        }
    }

    /* Get 2C2P PG redirect url based on mode selected by merchand. */

    private function p2c2predirect_create_common_form_field($parameter)
    {

        $merchant_id = Mage::getStoreConfig('payment/p2c2predirect/merchantid', Mage::app()->getStore());
        $p2c2predirect_return_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'p2c2predirect/payment/response';
        $currency = $this->p2c2predirect_get_store_currency_code();
        $selected_lang = Mage::getStoreConfig('payment/p2c2predirect/toc2p_language', Mage::app()->getStore());

        $default_lang = !empty($selected_lang) ? $selected_lang : 'en';

        $this->p2c2predirect_form_fields["version"] = "7.0";
        $this->p2c2predirect_form_fields["merchant_id"] = $merchant_id;
        $this->p2c2predirect_form_fields["payment_description"] = $parameter['payment_description'];
        $this->p2c2predirect_form_fields["order_id"] = $parameter['order_id'];
        $this->p2c2predirect_form_fields["invoice_no"] = "";
        $this->p2c2predirect_form_fields["currency"] = $currency;
        $this->p2c2predirect_form_fields["amount"] = $parameter['amount'];
        $this->p2c2predirect_form_fields["customer_email"] = $parameter['customer_email'];
        $this->p2c2predirect_form_fields["pay_category_id"] = "";
        $this->p2c2predirect_form_fields["promotion"] = "";
        $this->p2c2predirect_form_fields["user_defined_1"] = "";
        $this->p2c2predirect_form_fields["user_defined_2"] = "";
        $this->p2c2predirect_form_fields["user_defined_3"] = "";
        $this->p2c2predirect_form_fields["user_defined_4"] = "";
        $this->p2c2predirect_form_fields["user_defined_5"] = "";
        $this->p2c2predirect_form_fields["request_3ds"] = "";
        $this->p2c2predirect_form_fields["result_url_1"] = $p2c2predirect_return_url; // Specify by plugin
        $this->p2c2predirect_form_fields["result_url_2"] = $p2c2predirect_return_url; // Specify by plugin
        $this->p2c2predirect_form_fields["payment_option"] = "CC"; // Pass by default Payment option as A
        $this->p2c2predirect_form_fields["default_lang"] = $default_lang; // Set lang Parameter.
    }


    /* This function is used to get the calculate amount according to currency type. */
    public function p2c2p_get_amount_by_currency_type($currency_type, $amount)
    {

        $exponent = 0;
        $isFouned = false;

        foreach ($this->currency_helper->get_Currency_code() as $key => $value) {
            if ($key === $currency_type) {
                $exponent = $value['exponent'];
                $isFouned = true;
                break;
            }
        }

        if ($isFouned) {
            if ($exponent == 0 || empty($exponent)) {
                $amount = (int)$amount;
            } else {
                $pg_2c2p_exponent = $this->currency_helper->get_currency_exponent();
                $multi_value = $pg_2c2p_exponent[$exponent];
                $amount = ($amount * $multi_value);
            }
        }

        return str_pad($amount, 12, '0', STR_PAD_LEFT);
    }

    /* Generate the hidden form for make payment to 2c2p PG */
    public function p2c2p_construct_request($parameter){

        $stored_card  = Mage::getStoreConfig('payment/p2c2p/stored_card', Mage::app()->getStore());

        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            if ($stored_card) {
                $enable_store_card = "Y";
                $this->p2c2predirect_form_fields["enable_store_card"] = $enable_store_card;

                if(!empty($parameter['stored_card_unique_id'])){
                    $this->p2c2predirect_form_fields["stored_card_unique_id"] = $parameter['stored_card_unique_id'];
                }
            }
        }

        $this->p2c2p_create_common_form_field($parameter);
        $this->p2c2p_123_payment_expiry($parameter);

        $this->p2c2predirect_form_fields['hash_value']  = $this->hash_helper->create_hash($this->p2c2predirect_form_fields);

        $strHtml = '<form name="p2c2pform" action="'. $this->p2c2p_redirect_url() .'" method="post"/>';
        foreach ($this->p2c2predirect_form_fields as $key => $value) {
            if (!empty($value)) {
                $strHtml .= '<input type="hidden" name="' . htmlentities($key) . '" value="' . htmlentities($value) . '">';
            }
        }

        $strHtml .= '<input type="hidden" name="request_3ds" value="">';
        $strHtml .= '</form>';
        $strHtml .= '<script type="text/javascript">';
        $strHtml .= 'document.p2c2pform.submit()';
        $strHtml .= '</script>';
        return $strHtml;
    }
}
