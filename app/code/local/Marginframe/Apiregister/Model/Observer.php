<?php

require_once(Mage::getBaseDir()."/lib/nusoap/nusoap.php");

class Marginframe_Apiregister_Model_Observer extends Mage_Core_Model_Abstract
{

    public $_NuSOAPClient = false;

    public function getNuSOAPClient()
    {
        $NuSOAPClientPath    = Mage::getStoreConfig('mgfapisetting/mgfapi/apiurl');
        //preg_match('/^([http:\/\/|https:\/\/]\w.+):[\d]+\//', $NuSOAPClientPath, $apiurl);
        //preg_match('/^[http:\/\/|https:\/\/]\w.+:([\d]+)\//', $NuSOAPClientPath, $apiport);
        //$apiurl = @$apiurl[1] ?: '';
        //$apiport = @$apiport[1] ?: 80;
        $ping = $this->ping($NuSOAPClientPath);
        if ($ping === false){
            $this->_NuSOAPClient = false;
            Mage::log('POS down. '.$NuSOAPClientPath, null, 'pos_api_'.$this->dateThai('Y-m-d').'.log');
            return false;
        }
        if (!$this->_NuSOAPClient) {
            $ReturnValue         = "";
            // Create Webservice variable
            $this->_NuSOAPClient = new nusoap_client($NuSOAPClientPath, true);
            $proxy               = $this->_NuSOAPClient->getProxy();

            // Set timeouts, nusoap default is 30
            $this->_NuSOAPClient->timeout          = 5;
            $this->_NuSOAPClient->response_timeout = 5;

            $ErrorReturn = $this->_NuSOAPClient->getError();
            if ($ErrorReturn) {
                // Display the error
                $error = 'Error nusoap_client Constructor : '.$ErrorReturn.'<br>';
                Mage::log($error, null, 'pos_api_'.$this->dateThai('Y-m-d').'.log');
                $this->_NuSOAPClient = false;
            }
        }
        return $this->_NuSOAPClient;
    }

    private function ping($url, $timeout = 5)
    {
        // must set $url first....
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //timeout in seconds
        // do your curl thing here
        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($http_status == 200){
            return true;
        }
        return false;

        // $tB = microtime(true);
        // $fP = fSockOpen($host, $port, $errno, $errstr, $timeout);
        // if (!$fP) { return false; }
        // $tA = microtime(true);
        // return round((($tA - $tB) * 1000), 0)." ms";
    }

    public function addCustomerToPos($customer)
    {
        $NuSOAPClient    = $this->getNuSOAPClient();

        if ($NuSOAPClient && $customer->getId()) {

            // --------------------------------------------------------
            // Call Webservice Function CustomerAdd
            // --------------------------------------------------------
            // echo "----------------------------------------------<br>";
            // echo "Test Function CustomerAdd()<br>";
            // echo "----------------------------------------------<br>";

            $member_code     = $this->getnewcustomercode();
            $date            = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
            $register_date   = $date->format('Y-m-d');
            $vip_expire_date = date("d-m-Y", strtotime($register_date . " + 1 year"));

            $data_customer   = $customer->getData();

            $Barcode            = $member_code;
            $CustomerCode       = $member_code;
            $Name               = $data_customer['firstname'] . " " . $data_customer['lastname'];
            $ContactName        = $data_customer['firstname'] . " " . $data_customer['lastname'];
            $EMail              = $data_customer['email'];
            $Address            = "";
            $Tel                = $data_customer['telephone'];
            $Mobile             = "";
            $Fax                = "";
            $TaxCode            = "";
            $RDBranchName       = "";
            $MemberLevelString  = "1";
            $MemberExpireString = $vip_expire_date; //"31-12-2019";
            $DataReturn         = $NuSOAPClient->call("CustomerAdd", array(
                "AuthenCode" => "CNVSabuy",
                "Barcode" => $Barcode,
                "CustomerCode" => $CustomerCode,
                "Name" => $Name,
                "ContactName" => $ContactName,
                "EMail" => $EMail,
                "Address" => $Address,
                "Tel" => $Tel,
                "Mobile" => $Mobile,
                "Fax" => $Fax,
                "TaxCode" => $TaxCode,
                "RDBranchName " => $RDBranchName,
                "MemberLevelString" => $MemberLevelString,
                "MemberExpireString" => $MemberExpireString
            ));

            // Check for errors
            $ErrorReturn = $NuSOAPClient->getError();
            if ($ErrorReturn) {
                // Display the error
                $error = 'Error Call Function CustomerAdd : '.$ErrorReturn.'<br>';
                Mage::log($error, null, 'pos_api_'.$this->dateThai('Y-m-d').'.log');

                //$Continue = false;
                return false;
            } else {
                $ReturnValue = json_decode($DataReturn["CustomerAddResult"], true); // json decode from web service
                // echo "Success = ".$ReturnValue[0]["Success"]."<br>";
                // echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
                // echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
                // $data_customer = $customer->getData();
                // $data_customerset = Mage::getModel('customer/customer')->load($data_customer['entity_id']);
                $customer->setVipMemberId($member_code);
                $customer->save();
            }
        }
        return $customer;
    }

    public function customerRegisterSuccess(Varien_Event_Observer $observer)
    {
        $customer      = $observer->getEvent()->getCustomer();
        $this->addCustomerToPos($customer);
    }

    public function loginsavepoint($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        //var_dump('sync_point:: ', $customer->getSyncPosPoint(), $customer->getVipMemberId());
        //die;

        if($this->getNuSOAPClient() === false){
            return;
        }

        if($customer->getVipMemberId() === '0000000000000'){
            $customer = $this->addCustomerToPos($customer);
        }

        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $customer_id = $customer->getId();
        $customer        = Mage::getModel('customer/customer')->load($customer_id);
        $vip_member_code = $customer->getVipMemberId();

        /*
        get web point
         */
        $query          = "SELECT * FROM rewardpoints_customer WHERE customer_id = :customer_id";
        $binds          = array(
            'customer_id' => $customer_id
        );
        $dataweb_point  = $readConnection->query($query, $binds);
        $dataweb_point  = $dataweb_point->fetch();
        // echo '<pre>',print_r($data_fetch,1),'</pre>';
        $web_point = @$dataweb_point['point_balance'] ?: 0;

        /*
        for first time update web point to POS
         */
        $sync_pos_point = @$customer->getSyncPosPoint() ?: false;
        if(!$sync_pos_point && $web_point > 0){
            $this->api_addpoint($vip_member_code, $web_point);
            $customer->setSyncPosPoint(true)->save();
            sleep(5);
        }

        /*
        every time when customer login get point from POS and update in web
         */
        $pos_point = $this->apigetpoint($vip_member_code);
        if($pos_point){
            $diff_point = ($pos_point ?: 0) - $web_point;
            if($diff_point != 0){
                $point_balance   = $pos_point;
                $query           = "INSERT INTO rewardpoints_customer (customer_id, point_balance) VALUES (:customer_id, :point_balance) ON DUPLICATE KEY UPDATE point_balance = values(point_balance);";
                $resource        = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $binds           = array(
                    'customer_id' => $customer_id,
                    'point_balance' => $point_balance
                );
                $writeConnection->query($query, $binds);
                $this->log_reward($customer_id, $dataweb_point, $diff_point);
            }
        }
    }
    public function customerUpdateApp($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if($this->getNuSOAPClient() === false){
            return;
        }

        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $customer_id = $customer->getId();
        $vip_member_code = $customer->getVipMemberId();

        /*
        get web point
         */
        $query          = "SELECT * FROM rewardpoints_customer WHERE customer_id = :customer_id";
        $binds          = array(
            'customer_id' => $customer_id
        );
        $dataweb_point  = $readConnection->query($query, $binds);
        $dataweb_point  = $dataweb_point->fetch();
        // echo '<pre>',print_r($data_fetch,1),'</pre>';
        $web_point = @$dataweb_point['point_balance'] ?: 0;

        /*
        every time when customer login get point from POS and update in web
         */
        $pos_point = $this->apigetpoint($vip_member_code);
        if($pos_point){
            $diff_point = ($pos_point ?: 0) - $web_point;
            if($diff_point != 0){
                $point_balance   = $pos_point;
                $query           = "INSERT INTO rewardpoints_customer (customer_id, point_balance) VALUES (:customer_id, :point_balance) ON DUPLICATE KEY UPDATE point_balance = values(point_balance);";
                $resource        = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $binds           = array(
                    'customer_id' => $customer_id,
                    'point_balance' => $point_balance
                );
                $writeConnection->query($query, $binds);
                $this->log_reward($customer_id, $dataweb_point, $diff_point);
            }
        }
    }
    public function log_reward($customer_id, $dataweb_point, $diff_point)
    {
        $customer        = Mage::getModel('customer/customer')->load($customer_id);
        $resource        = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $query           = "INSERT INTO rewardpoints_transaction (
            reward_id,
            customer_id,
            customer_email,
            action,
            title,
            point_amount,
            STATUS,
            created_time,
            updated_time,
            extra_content
        )
        VALUES
            (
                :reward_id,
                :customer_id,
                :customer_email,
                'admin',
                'POS Update',
                :point_amount,
                '3',
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP,
                'admin'
            )";
        $binds           = array(
            'reward_id' => $dataweb_point['reward_id'],
            'customer_id' => $customer_id,
            'customer_email' => $customer->getEmail(),
            'point_amount' => $diff_point
        );
        $writeConnection->query($query, $binds);
    }
    public function apigetpoint($customer_barcode)
    {
        $NuSOAPClient = $this->getNuSOAPClient();
        if ($NuSOAPClient) {
            // --------------------------------------------------------
            // Call Webservice Function CustomerEdit
            // --------------------------------------------------------
            // echo "<br>";
            // echo "----------------------------------------------<br>";
            // echo "Test Function PointBalance()<br>";
            // echo "----------------------------------------------<br>";

            //$customer_barcode = "0018061300005";

            $DataReturn = $NuSOAPClient->call("PointBalance", array(
                "AuthenCode" => "CNVSabuy",
                "Barcode" => $customer_barcode
            ));

            // Check for errors
            $ErrorReturn = $NuSOAPClient->getError();
            if ($ErrorReturn) {
                // Display the error
                $error = 'Error Call Function PointBalance : ' . $ErrorReturn;
                Mage::log($error, null, 'pos_api_'.$this->dateThai('Y-m-d').'.log');
                //$Continue = false;
                return false;
            } else {
                $ReturnValue = json_decode($DataReturn["PointBalanceResult"], true); // json decode from web service
                // echo "Success = ".$ReturnValue[0]["Success"]."<br>";
                // echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
                // echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
                // echo "PointBalance = ".$ReturnValue[0]["PointBalance"]."<br>";
                return $ReturnValue[0]["PointBalance"];
            }
        }
        return false;
    }
    public function adddatevipexpire(Varien_Event_Observer $observer)
    {
        $order           = $observer->getEvent()->getOrder();
        $State           = $order->getState();
        $State_o         = $order->getOrigData('state');
        $stateProcessing = Mage_Sales_Model_Order::STATE_PROCESSING;
        if ($order->getState() == $stateProcessing && $order->getOrigData('state') != $stateProcessing) {
            if ($order->getGrandTotal() >= 3000) {
                $customer_id        = $order->getCustomerId();
                $data_customer_load = Mage::getModel('customer/customer')->load($customer_id);

                $end_date         = $data_customer_load->getVipMemberExpireDate();
                $back_3m_end_date = date('Y-m-d', strtotime("-3 months", strtotime($end_date)));
                $order_create     = date('Y-m-d', strtotime($order->getCreatedAt()));
                if ($order_create < $end_date && $order_create > $back_3m_end_date) {
                    $add_2year_expire_date = date('Y-m-d', strtotime("+2 years", strtotime($end_date)));
                    $data_customer_load->setVipMemberExpireDate($add_2year_expire_date)->save();
                }
            } else {
                return 0;
            }
        }
    }


    protected function _getWriteAdapter()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $connection;
    }

    public function getnewcustomercode()
    {
        $h                = "7";
        $hm               = $h * 60;
        $ms               = $hm * 60;
        $date             = gmdate("ymd", time() + ($ms));
        $store_code       = '00';
        $date_code_config = $this->getDatecode(); //Mage::getStoreConfig('mgfapisetting/registercode/datecode');
        $run_code_config  = $this->getRuncode(); //Mage::getStoreConfig('mgfapisetting/registercode/runcode');
        $transaction = $this->_getWriteAdapter();
        $transaction->beginTransaction();
        if ($date == $date_code_config) {
            $this->saveRuncode($run_code_config + 1);
            //Mage::getConfig()->saveConfig('mgfapisetting/registercode/runcode', $run_code_config+1);
        } else {
            $this->saveDatecode($date); //Mage::getConfig()->saveConfig('mgfapisetting/registercode/datecode', $date);
            $this->saveRuncode(2); //Mage::getConfig()->saveConfig('mgfapisetting/registercode/runcode', '2');
            $run_code_config = 1;
        }
        ;
        $run_number = str_pad($run_code_config, 5, "0", STR_PAD_LEFT);
        $transaction->commit();
        return $store_code . $date . $run_number;
    }

    public function getDatecode()
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $res  = $read->fetchOne("select value from core_config_data WHERE scope_id = :scope_id AND path = :path", array(
            'scope_id' => 0,
            'path' => 'mgfapisetting/registercode/datecode'
        ));
        return $res ?: '';
    }

    public function getRuncode()
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $res  = $read->fetchOne("select value from core_config_data WHERE scope_id = :scope_id AND path = :path", array(
            'scope_id' => 0,
            'path' => 'mgfapisetting/registercode/runcode'
        ));
        return $res ?: 1;
    }

    public function saveDatecode($datecode)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "insert into core_config_data values (null, 'default', 0, 'mgfapisetting/registercode/datecode', '$datecode') on duplicate key update value = '$datecode';";
        $write->query($query);
    }
    public function saveRuncode($runcode)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "insert into core_config_data values (null, 'default', 0, 'mgfapisetting/registercode/runcode', '$runcode') on duplicate key update value = $runcode;";
        $write->query($query);
    }

    public function getconfig_core($path)
    {
        if (!$path) {
            return 0;
        }
        $resource       = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query          = "SELECT * FROM core_config_data WHERE path = :path";
        $binds          = array(
            'path' => $path
        );
        $result         = $readConnection->query($query, $binds);
        $data_fetch     = $result->fetch();
        return $data_fetch['value'];
    }
    public function setconfig_core($path, $value)
    {
        if (!$path) {
            return 0;
        }
        $resource        = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $query           = "UPDATE core_config_data SET value = :value WHERE path = :path ";
        $binds           = array(
            'path' => $path,
            'value' => $value
        );
        $writeConnection->query($query, $binds);
        return 1;
    }


    public function checkvip_expire()
    {
        $date = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
        $now  = $date->format('Y-m-d');

        // $now = '2020-08-08';
        $collection = Mage::getResourceModel('customer/customer_collection')->addFieldToFilter('vip_member_expire_date', array(
            'lteq' => $now
        ));
        $q_customer = $collection->getData();
        foreach ($q_customer as $key => $data) {
            $customer = Mage::getModel('customer/customer')->load($data['entity_id']);
            $notify = Mage::getModel('member/notify_vip')->load('customer_id',$data['entity_id']);
            $customer->setGroupId('1');
            $customer->save();
            if($notify) {
                $notify->setData('notified_member', 0);
                $notify->save();
            }
        }
    }
    public function add_date_vip_expire()
    {

        $data_customer = mage::getModel('customer/customer')->getCollection();
        $data_customer->addFieldToFilter('vip_member_expire_date', array('notnull' => true));
        $data_all_c = $data_customer->getData();
        foreach ($data_all_c as $key => $data_c) {

            $customerId         = $data_c['entity_id'];
            $data_customer_load = Mage::getModel('customer/customer')->load($customerId);

            $end_date         = $data_customer_load->getVipMemberExpireDate();
            $back_3m_end_date = date('Y-m-d', strtotime("-3 months", strtotime($end_date)));
            $Collection       = Mage::getModel('sales/order')->getCollection();
            $Collection->addFieldToFilter('customer_id', $customerId);
            $Collection->addFieldToFilter('status', 'complete');
            $Collection->addFieldToFilter('grand_total', array(
                'gteq' => '3000'
            ));
            $Collection->addFieldToFilter('created_at', array(
                'gteq' => $back_3m_end_date
            ));
            $Collection->addFieldToFilter('created_at', array(
                'lteq' => $end_date
            ));

            if ($Collection->getData()) {
                $add_1year_expire_date = date('Y-m-d', strtotime("+1 years", strtotime($end_date)));
                $data_customer_load->setVipMemberExpireDate($add_1year_expire_date)->save();
            }


        }
    }
    public function addpoint_complete(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $State = $order->getState();
        $RewardpointsEarn = $order->getRewardpointsEarn();
        $State_o = $order->getOrigData('state');
        try{
            $stateComplete = Mage_Sales_Model_Order::STATE_PROCESSING;
            if($order->getState() == $stateComplete && $order->getOrigData('state') != $stateComplete){
                $customer_id = $order->getCustomerId();
                $data_customer_load = Mage::getModel('customer/customer')->load($customer_id);
                $vip_member_code = $data_customer_load->getVipMemberId();
                if($RewardpointsEarn>0 && $vip_member_code != null){
                    $this->api_addpoint($vip_member_code,$RewardpointsEarn, $order);
                }
                // else{
                //  return 0;
                // }
            }
        }catch(Exception $e){}
    }
    public function api_addpoint($member_code, $point, $order = null)
    {
        $this->logOrderRewardAPI("========== API LOG ==========");
        $NuSOAPClient = $this->getNuSOAPClient();
        if ($NuSOAPClient) {

            $DataReturn  = $NuSOAPClient->call("PointAdd", array(
                "AuthenCode" => "CNVSabuy",
                "Barcode" => $member_code,
                "PointString" => $point
            ));
            if($order) {
                $this->logOrderRewardAPI("========== API LOG ==========");
                $this->logOrderRewardAPI("|REQUEST| PointAdd");
                $this->logOrderRewardAPI("Order Id: " . $order->getIncrementId());
                $this->logOrderRewardAPI("Data: " . array(
                        "AuthenCode" => "CNVSabuy",
                        "Barcode" => $member_code,
                        "PointString" => $point
                    ));
                $this->logOrderRewardAPI("|RESPONSE| " . $DataReturn);
                $this->logOrderRewardAPI("========== END ==========");
            }
            // Check for errors
            $ErrorReturn = $NuSOAPClient->getError();
            if ($ErrorReturn) {
                $error = 'Error Call Function PointAdd : '.$ErrorReturn;
                Mage::log($error, null, 'pos_api_'.$this->dateThai('Y-m-d').'.log');
                //$Continue = false;
                return false;
            } else {
                $ReturnValue = json_decode($DataReturn["PointAddResult"], true);
                return $ReturnValue[0]["Success"];
            }
        }
        return false;
    }
    public function removepoint_complete(Varien_Event_Observer $observer)
    {
        $order             = $observer->getEvent()->getOrder();
        $State             = $order->getState();
        $RewardpointsSpent = $order->getRewardpointsSpent();
        $State_o           = $order->getOrigData('state');
        $stateNew          = Mage_Sales_Model_Order::STATE_NEW;

        if ($order->getState() == $stateNew && $order->getOrigData('state') != $stateNew) {
            // Mage::log('State = '.$State." RewardpointsSpent=".$RewardpointsSpent, null, 'test.log', true);

            $customer_id        = $order->getCustomerId();
            $data_customer_load = Mage::getModel('customer/customer')->load($customer_id);
            $vip_member_code    = $data_customer_load->getVipMemberId();
            if ($RewardpointsSpent > 0 && $vip_member_code != null) {
                $this->api_removepoint($vip_member_code, $RewardpointsSpent);
            } else {
                return 0;
            }
        }
    }

    public function raffle_use_point(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $RewardpointsSpent =  $observer->getEvent()->getRewardPointSpent();

        $customer_id = $customer->getId();
        $vip_member_code = $customer->getVipMemberId();

        if ($RewardpointsSpent > 0 && $vip_member_code != null) {
            $this->api_removepoint($vip_member_code, $RewardpointsSpent);
        } else {
            return 0;
        }
    }

    public function api_removepoint($member_code, $point)
    {
        $NuSOAPClient = $this->getNuSOAPClient();
        if ($NuSOAPClient) {

            // $member_code = "0018061300005";

            $DataReturn = $NuSOAPClient->call("PointRemove", array(
                "AuthenCode" => "CNVSabuy",
                "Barcode" => $member_code,
                "PointString" => $point
            ));

            // Check for errors
            $ErrorReturn = $NuSOAPClient->getError();
            if ($ErrorReturn) {
                $error = 'Error Call Function PointRemove : '.$ErrorReturn;
                Mage::log($error, null, 'pos_api_'.$this->dateThai('Y-m-d').'.log');
                //$Continue = false;
                return false;
            } else {
                $ReturnValue = json_decode($DataReturn["PointRemoveResult"], true);
                return $ReturnValue[0]["Success"];
            }
        }
        return false;
    }

    public function dateThai($format = 'Y-m-d H:i:s'){
        return date($format, strtotime('+7 hour', strtotime(gmdate('Y-m-d H:i:s'))));
    }

    public function logOrderRewardAPI($message)
    {
        Mage::log($message, Zend_Log::DEBUG, 'order_reward_api_' . $this->dateThai('Y-m-d') . '.log', true);
        return true;
    }
}
