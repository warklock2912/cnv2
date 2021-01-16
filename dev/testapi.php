<?php

      $proxy = new SoapClient('http://carnivalbkk.crystal-techs.com/api/v2_soap/?wsdl=1'); // TODO : change url

try {
    $sessionId = $proxy->login('crystal-test', '6e9b3066c3e6b80b766952c4ddfedf5c'); // TODO : change login and pwd if necessary

    $result = $proxy->catalogCategoryTree($sessionId, '4');
    var_dump($result);
} catch (SoapFault $e) {
    print($proxy->__getLastResponse());
}


