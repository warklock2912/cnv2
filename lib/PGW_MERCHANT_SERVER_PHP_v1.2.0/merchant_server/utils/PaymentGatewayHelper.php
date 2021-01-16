<?php

require '../vendor/autoload.php';

//Reference: https://github.com/lcobucci/jwt
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

class PaymentGatewayHelper {

    const PAYLOAD = "payload";

    private $jwt_sign_algorithm;

    public function __construct() {

        //Declare JWT sign algoritm
        $this->jwt_sign_algorithm = new Sha256();
    }

    /**
     * Generate request payload.
     *
     * @param stdClass $request_params Object of request params.
     * @param string $secret_key Merchant's secret key.
     * @return JSON Return request payload.
     */
    public function generatePayload($request_params, $secret_key) {

        //1) Create JWT builder
        $jwt_builder = new Builder();
        
        //2) Convert request params to array
        $request_params_array = get_object_vars($request_params);
        
        //3) Add individual param into JWT claim
        foreach ($request_params_array as $key => $value) {

            $jwt_builder->withClaim($key, $value);
        }

        //4) Sign by using HMAC-SHA256 payload message with merchant sercret key
        $jwt_token = $jwt_builder->getToken($this->jwt_sign_algorithm, new Key($secret_key));

        //5) Build request payload by using JWT structure (Header.Payload.Signature)
        $request_payload = new stdClass();
        $request_payload->payload = $jwt_token->__toString();
        
        //6) Encode request payload to JSON
        return $request_payload_json = json_encode(get_object_vars($request_payload));
    }

    /**
     * Verify API response payload signature.
     * 
     * @param string $response_payload_json Response payload from API response.
     * @param string $secret_key Merchant's secret key.
     * @return boolean Return result of verification for signature.
     */
    public function validatePayload($response_payload_json, $secret_key) {
    
        //1) Convert JSON string to string array
        $response_payload_json_array = $this->jsonToArray($response_payload_json);
    
        //2) Retrieve encoded response payload
        $response_payload = !empty($response_payload_json_array[self::PAYLOAD]) ? $response_payload_json_array[self::PAYLOAD] : '';

        //3) Parse response payload to JWT 
        $jwt_token = (new Parser())->parse((string) $response_payload);

        //4) Verify with JWT if TRUE means it's a valid signature else it's invalid signature should return error response to application
        return $jwt_token->verify($this->jwt_sign_algorithm, $secret_key);
    }

    /**
     * For API request.
     *
     * @param APIEnvironment $api_environment Request to specific API enviroment.
     * @param JSON $request_payload_json JSON of request payload.
     * @return JSON Return response payload.
     */
    public function requestAPI($api_environment, $request_payload_json) {

        return $response_payload_json = $this->doAPIRequest($api_environment, $request_payload_json);
    }

    /**
     * For parse encoded response and convert into stdClass object.
     *
     * @param JSON $response_payload_json JSON of response payload.
     * @return stdClass Return response payload object.
     */
    public function parseAPIResponse($response_payload_json) {
    
        //1) Convert JSON string to string array
        $response_payload_json_array = $this->jsonToArray($response_payload_json);
        
        //2) Retrieve encoded response payload
        $response_payload = !empty($response_payload_json_array[self::PAYLOAD]) ? $response_payload_json_array[self::PAYLOAD] : '';

        //3) Parse response payload to JWT 
        $jwt_token = (new Parser())->parse((string) $response_payload);

        //4) Decode the encoded reponse payload
        $response_payload = base64_decode($jwt_token->getRawPayload());
        
        //5) Convert JSON to object
        $response_payload_object = $this->jsonToObject($response_payload);

        return $response_payload_object;
    }
 
    /**
     * For verify payload contained in API response.
     * @param JSON $response_payload_json JSON of response payload.
     * @return boolean Return result payload exists.
     */
    public function containPayload($response_payload_json) {

        return !empty($this->jsonToArray($response_payload_json)[self::PAYLOAD]);
    }

    private function jsonToArray($json) {

        return json_decode($json, true);
    }

    private function jsonToObject($json) {

        return json_decode($json);
    }
  
    private function doAPIRequest($api_environment, $request_payload_json) {

        //CURL configuration
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $api_environment,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1, //Note: Must use POST method
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2, //Note: Must request with TLS1.2
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json', //Note: Must use JSON as content type
                'Accept: text/plain'
            ),
            CURLOPT_POSTFIELDS => $request_payload_json
        );
    
        curl_setopt_array($ch, $curl_options);
    
        return $api_response = curl_exec($ch); 
    }
}

?>