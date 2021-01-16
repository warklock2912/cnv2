<?php

// app/code/local/payment/p2c2predirect/Helper/Data.php
class Mage_P2c2predirect_Helper_Data extends Mage_Core_Helper_Abstract
{

    function getResultDescription($responseCode)
    {

        switch ($responseCode) {
            case "000" :
                $result = "Transaction Successful";
                break;
            case "001" :
                $result = "Transaction status is pending";
                break;
            case "cancel" :
                $result = "Transaction faild";
                break;

        }

        return $result;
    }
}
