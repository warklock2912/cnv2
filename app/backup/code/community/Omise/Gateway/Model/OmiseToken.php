<?php
class Omise_Gateway_Model_OmiseToken extends Omise_Gateway_Model_Omise
{
    /**
     * Creates a new Token
     * @param array $params
     * @return OmiseToken|Exception
     */
    public function createToken($data)
    {
        try {
            $params = array(
              'card' => array(
                'name' => $data['name'],
                'number' => $data['number'],
                'expiration_month' => $data['expiration_month'],
                'expiration_year' => $data['expiration_year'],
                'security_code' => $data['security_code']
              )
            );
            // return OmiseToken::create($params, $this->_public_key, $this->_secret_key);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}