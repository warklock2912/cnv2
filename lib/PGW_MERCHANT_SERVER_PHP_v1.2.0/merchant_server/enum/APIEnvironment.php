<?php

require 'Enum.php';

class APIEnvironment extends Enum {

    const SANDBOX = "https://sandbox-pgw.2c2p.com/payment/4.1/";
    const PRODUCTION = "https://pgw.2c2p.com/payment/4.1/";
    const PRODUCTION_INDONESIA = "https://pgwid.2c2p.com/payment/4.1/";
  }
  
?>
