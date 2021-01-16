<?php
class Tigren_Kpayment_Model_Api2_Restapi_Rest_Charge_V1 extends Tigren_Kpayment_Model_Api2_Restapi
{

    public function _create(array $data) {

        $firstName = $data['firstname'];
        $lastName = $data['lastname'];
        $email = $data['email'];
        $password = $data['password'];

//        $customer = Mage::getModel("customer/customer");
//
//        $customer->setFirstname($firstName);
//        $customer->setLastname($lastName);
//        $customer->setEmail($email);
//        $customer->setPasswordHash(md5($password));
//        $customer->save();

//        return $this->_getLocation($customer);
        return  json_encode(array("testing","Success"));
    }
}