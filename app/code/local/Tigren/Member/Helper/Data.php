<?php
class Tigren_Member_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function logAPI($message)
    {
        Mage::log($message, Zend_Log::DEBUG, 'pos_burnpoint_api_' . $this->dateThai('Y-m-d') . '.log', true);
        return true;
    }

    public function dateThai($format = 'Y-m-d H:i:s'){
        return date($format, strtotime('+7 hour', strtotime(gmdate('Y-m-d H:i:s'))));
    }

    public function addCustomerToPos($customer, $member_code, $apiclient, $NuSOAPClient)
    {
        $Continue = $apiclient['Continue'];

        if ($Continue && $customer->getId()) {

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
            $MemberExpireString = "";
            $PriceType          = '2';
            $dataArray = array(
                "AuthenCode"            => "CNVSabuy",
                "Barcode"               => $Barcode,
                "CustomerCode"          => $CustomerCode,
                "Name"                  => $Name,
                "ContactName"           => $ContactName,
                "EMail"                 => $EMail,
                "Address"               => $Address,
                "Tel"                   => $Tel,
                "Mobile"                => $Mobile,
                "Fax"                   => $Fax,
                "TaxCode"               => $TaxCode,
                "RDBranchName "         => $RDBranchName,
                "MemberLevelString"     => $MemberLevelString,
                "MemberExpireString"    => $MemberExpireString,
                "PriceType"             => $PriceType
            );

            $DataReturn = $NuSOAPClient->call("CustomerAdd", $dataArray);

            Mage::helper('member')->logAPI('|----------------------------------------|');
            Mage::helper('member')->logAPI('|Customer Id:|' . $customer->getId());
            Mage::helper('member')->logAPI('|API: CustomerAdd|');
            Mage::helper('member')->logAPI('|[TRANS]|-------- Request data --------|');
            Mage::helper('member')->logAPI($dataArray);
            Mage::helper('member')->logAPI('|[TRANS]|-------- Response --------|');
            Mage::helper('member')->logAPI($DataReturn);

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

    public function upgradeToVip($customer, $apiclient, $NuSOAPClient)
    {
        $Continue = $apiclient['Continue'];

        if ($Continue)
        {
            $date            = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
            $format_date   = $date->format('d-m-Y');
            $upgradeDays = Mage::getStoreConfig('rewardpoints/display/upgrade_date_expired_vip');
            $vip_expire_date = date("d-m-Y", strtotime($format_date . " + " .$upgradeDays. "days"));

            $Barcode            = $customer->getData('vip_member_id');
            $CustomerCode       = $customer->getData('vip_member_id');
            $Name               = $customer->getData('firstname')." ".$customer->getData('lastname');
            $ContactName        = $customer->getData('firstname')." ".$customer->getData('lastname');
            $EMail              = $customer->getData('email');
            $Address            = "";
            $Tel                = "";
            $Mobile             = "";
            $Fax                = "";
            $TaxCode            = "";
            $RDBranchName       = "";
            $MemberLevelString  = '2';
            $MemberExpireString = $vip_expire_date;
            $PriceType          = '2';
            $dataArray = array(
                "AuthenCode"            => "CNVSabuy",
                "Barcode"               => $Barcode,
                "CustomerCode"          => $CustomerCode,
                "Name"                  => $Name,
                "ContactName"           => $ContactName,
                "EMail"                 => $EMail,
                "Address"               => $Address,
                "Tel"                   => $Tel,
                "Mobile"                => $Mobile,
                "Fax"                   => $Fax,
                "TaxCode"               => $TaxCode,
                "RDBranchName"          => $RDBranchName,
                "MemberLevelString"     => $MemberLevelString,
                "MemberExpireString"    => $MemberExpireString,
                "PriceType"             => $PriceType
            );

            $DataReturn = $NuSOAPClient->call("CustomerEdit", $dataArray);

            Mage::helper('member')->logAPI('|----------------------------------------|');
            Mage::helper('member')->logAPI('|Customer Id:|' . $customer->getId());
            Mage::helper('member')->logAPI('|API: CustomerEdit - Upgrade|');
            Mage::helper('member')->logAPI('|[TRANS]|-------- Request data --------|');
            Mage::helper('member')->logAPI($dataArray);
            Mage::helper('member')->logAPI('|[TRANS]|-------- Response --------|');
            Mage::helper('member')->logAPI($DataReturn);
            // Check for errors
            $ErrorReturn = $NuSOAPClient->getError();
            if ($ErrorReturn)
            {
                // Display the error
                // echo 'Error Call Function CustomerEdit : '.$ErrorReturn.'<br>';
                $Continue = false;
                return 0;
            }
            else
            {
                $ReturnValue = json_decode($DataReturn["CustomerEditResult"],true); // json decode from web service
                $ReturnValue['VipExpireDateAfterUpgrading'] = $vip_expire_date;
                return $ReturnValue;
                // echo "Success = ".$ReturnValue[0]["Success"]."<br>";
                // echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
                // echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
            }
        }
        return false;
    }

    public function renewVip($customer, $renewDays, $apiclient = NULL, $NuSOAPClient = NULL)
    {
        $Continue = $apiclient['Continue'];

        if ($Continue)
        {
            $date            = date("d-m-Y", strtotime($customer->getVipMemberExpireDate()));
            $vip_expire_date = date("d-m-Y", strtotime($date. " + ".$renewDays. "days"));

            $Barcode            = $customer->getData('vip_member_id');
            $CustomerCode       = $customer->getData('vip_member_id');
            $Name               = $customer->getData('firstname')." ".$customer->getData('lastname');
            $ContactName        = $customer->getData('firstname')." ".$customer->getData('lastname');
            $EMail              = $customer->getData('email');
            $Address            = "";
            $Tel                = "";
            $Mobile             = "";
            $Fax                = "";
            $TaxCode            = "";
            $RDBranchName       = "";
            $MemberLevelString  = '2';
            $MemberExpireString = $vip_expire_date;
            $PriceType          = '2';
            $dataArray = array(
                "AuthenCode"            => "CNVSabuy",
                "Barcode"               => $Barcode,
                "CustomerCode"          => $CustomerCode,
                "Name"                  => $Name,
                "ContactName"           => $ContactName,
                "EMail"                 => $EMail,
                "Address"               => $Address,
                "Tel"                   => $Tel,
                "Mobile"                => $Mobile,
                "Fax"                   => $Fax,
                "TaxCode"               => $TaxCode,
                "RDBranchName"          => $RDBranchName,
                "MemberLevelString"     => $MemberLevelString,
                "MemberExpireString"    => $MemberExpireString,
                "PriceType"             => $PriceType
            );

            $DataReturn = $NuSOAPClient->call("CustomerEdit", $dataArray);

            Mage::helper('member')->logAPI('|----------------------------------------|');
            Mage::helper('member')->logAPI('|API: CustomerEdit - Renew|');
            Mage::helper('member')->logAPI('|[TRANS]|-------- Request data --------|');
            Mage::helper('member')->logAPI($dataArray);
            Mage::helper('member')->logAPI('|[TRANS]|-------- Response --------|');
            Mage::helper('member')->logAPI($DataReturn);
            // Check for errors
            $ErrorReturn = $NuSOAPClient->getError();
            if ($ErrorReturn)
            {
                // Display the error
                // echo 'Error Call Function CustomerEdit : '.$ErrorReturn.'<br>';
                $Continue = false;
                return 0;
            }
            else
            {
                $ReturnValue = json_decode($DataReturn["CustomerEditResult"],true); // json decode from web service
                $ReturnValue['VipExpireDateAfterRenew'] = $vip_expire_date;
                return $ReturnValue;
                // echo "Success = ".$ReturnValue[0]["Success"]."<br>";
                // echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
                // echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
            }
        }
        return false;
    }

    public function apigetpoint($customer_barcode, $apiclient = NULL, $NuSOAPClient = NULL)
    {
        $Continue = $apiclient['Continue'];
        if ($Continue) {

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

    public function api_addpoint($member_code, $point, $apiclient = NULL, $NuSOAPClient = NULL)
    {
        $Continue = $apiclient['Continue'];
        if ($Continue) {

            $DataReturn  = $NuSOAPClient->call("PointAdd", array(
                "AuthenCode" => "CNVSabuy",
                "Barcode" => $member_code,
                "PointString" => $point
            ));
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

    public function api_removepoint($member_code, $point, $apiclient = NULL, $NuSOAPClient = NULL)
    {
        $Continue = $apiclient['Continue'];
        if ($Continue) {

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

    public function getWebPoint($customer_id)
    {
        $dataweb_point = $this->getDataWebPoint($customer_id);
        return $web_point = @$dataweb_point['point_balance'] ?: 0;
    }

    public function getDataWebPoint($customer_id)
    {
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query          = "SELECT * FROM rewardpoints_customer WHERE customer_id = :customer_id";
        $binds          = array(
            'customer_id' => $customer_id
        );
        $dataweb_point  = $readConnection->query($query, $binds);
        $dataweb_point  = $dataweb_point->fetch();
        return $dataweb_point;
    }

    public function log_reward($customer_id, $dataweb_point, $diff_point, $title, $extra_content)
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
                :title,
                :point_amount,
                '3',
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP,
                :extra_content
            )";
        $binds           = array(
            'reward_id' => $dataweb_point['reward_id'],
            'customer_id' => $customer_id,
            'customer_email' => $customer->getEmail(),
            'point_amount' => $diff_point,
            'title' => $title,
            'extra_content' => $extra_content
        );
        $writeConnection->query($query, $binds);
    }
}