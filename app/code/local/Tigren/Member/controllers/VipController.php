<?php

class Tigren_Member_VipController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function changeAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function indexAction()
    {
        // $user_data = Mage::getSingleton('customer/session')->getSearchmember();
        // echo '<pre>',print_r($user_data,1),'</pre>';
        // echo $now = date("d-m-Y");
        // echo $next = date("d-m-Y",strtotime('+one year', $now));
        // $this->changevip_statusAction();
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

    public function dateThai($format = 'Y-m-d H:i:s'){
        return date($format, strtotime('+7 hour', strtotime(gmdate('Y-m-d H:i:s'))));
    }

    protected function _getWriteAdapter()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $connection;
    }

    public function renewCustomerVipAction()
    {
        $memberHelper = Mage::helper('member');
        $apiclient = $this->include_nusoap();
        $NuSOAPClient = $apiclient['NuSOAPClient'];
        $pointRenew = $this->getRequest()->getParam('renew_points');
        $session = Mage::getSingleton('customer/session');
        $customer = Mage::getModel('customer/customer')->load($session->getCustomer()->getId());
        $renewDays =  Mage::getStoreConfig('rewardpoints/display/renew_date_expired_vip');
        $renew = $memberHelper->renewVip($customer, $renewDays, $apiclient, $NuSOAPClient);
        if($renew[0]['Success'] == '1') {
            $memberHelper->api_removepoint($customer->getVipMemberId(), $pointRenew, $apiclient, $NuSOAPClient);
            $pos_point = $memberHelper->apigetpoint($customer->getVipMemberId(), $apiclient, $NuSOAPClient);
            $web_point = $memberHelper->getWebPoint($customer->getId());
            if($pos_point) {
                $diff_point = ($pos_point ?: 0) - $web_point;
                if($diff_point != 0) {
                    $title = $this->__("Use Point for Renewal");
                    $extra_content = $this->__("burn_point");
                    $point_balance   = $pos_point;

                    $query           = "INSERT INTO rewardpoints_customer (customer_id, point_balance) VALUES (:customer_id, :point_balance) ON DUPLICATE KEY UPDATE point_balance = values(point_balance);";
                    $resource        = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $binds           = array(
                        'customer_id' => $customer->getId(),
                        'point_balance' => $point_balance
                    );
                    $writeConnection->query($query, $binds);
                    $memberHelper->log_reward($customer->getId(), $memberHelper->getDataWebPoint($customer->getId()), $diff_point, $title, $extra_content);
                    $customer->setVipMemberExpireDate($renew['VipExpireDateAfterRenew'])
                        ->save();
                    Mage::getSingleton('customer/session')->addSuccess('Renewed Vip Member Expire Date');
                    Mage::helper('member')->logAPI('|Renew Vip Member|');
                    Mage::helper('member')->logAPI('|[TRANS]|-------- Renew Status --------|');
                    Mage::helper('member')->logAPI('success');
                }
            }
        }
        $this->getResponse()->setBody(true);
    }

    public function upgradeCustomerVipAction()
    {
        $memberHelper = Mage::helper('member');
        $apiclient = $this->include_nusoap();
        $NuSOAPClient = $apiclient['NuSOAPClient'];
        $pointUse = $this->getRequest()->getParam('points');
        $session = Mage::getSingleton('customer/session');
        $customer = Mage::getModel('customer/customer')->load($session->getCustomer()->getId());
        $member_code = $this->getnewcustomercode();
        $response['upgrade_success'] = 0;
        if(!$customer->getData('vip_member_id') && !$customer->getData('vip_member_expire_date')) {
            $customerToPOS = $memberHelper->addCustomerToPos($customer, $member_code , $apiclient, $NuSOAPClient);
            $customer_id = $customerToPOS->getId();
            $vip_member_code = $customerToPOS->getVipMemberId();
            $web_point = $memberHelper->getWebPoint($customer_id);
            /*
            for first time update web point to POS
             */
            $sync_pos_point = @$customer->getSyncPosPoint() ?: false;
            if($web_point > 0){
                $memberHelper->api_addpoint($vip_member_code, $web_point, $apiclient, $NuSOAPClient);
                $customer->setSyncPosPoint(true)->save();
                sleep(5);
            }
        }
        $upgradeVip = $memberHelper->upgradeToVip($customer, $apiclient, $NuSOAPClient);
        if($upgradeVip[0]['Success'] == '1') {
            Mage::helper('member')->api_removepoint($customer->getVipMemberId(), $pointUse, $apiclient, $NuSOAPClient);
            $pos_point = $memberHelper->apigetpoint($customer->getVipMemberId(), $apiclient, $NuSOAPClient);
            $web_point = $memberHelper->getWebPoint($customer->getId());
            if($pos_point && ($pos_point > $pointUse)) {
                $diff_point = ($pos_point ?: 0) - $web_point;
                if($diff_point != 0) {
                    $title = $this->__("Use Point for upgrade VIP");
                    $extra_content = $this->__("burn_point");
                    $point_balance   = $pos_point;
                    $query           = "INSERT INTO rewardpoints_customer (customer_id, point_balance) VALUES (:customer_id, :point_balance) ON DUPLICATE KEY UPDATE point_balance = values(point_balance);";
                    $resource        = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $binds           = array(
                        'customer_id' => $customer->getId(),
                        'point_balance' => $point_balance
                    );
                    $writeConnection->query($query, $binds);
                    $memberHelper->log_reward($customer->getId(), $memberHelper->getDataWebPoint($customer->getId()), $diff_point, $title, $extra_content);
                    if($customer->getGroupId() != 4){
                        $customer->setGroupId(4)
                            ->setVipMemberExpireDate($upgradeVip['VipExpireDateAfterUpgrading'])
                            ->save();
                        $this->saveVipNotifyMember($customer->getId());
                        $response['upgrade_success'] = 1;
                        Mage::getSingleton('customer/session')->addSuccess('Upgraded to Vip Member');
                    }
                }
            }
            else {
                $response['upgrade_success'] = 2;
            }
        }

        Mage::helper('member')->logAPI('|Upgrade Vip Member|');
        Mage::helper('member')->logAPI('|[TRANS]|-------- Upgrade Status --------|');
        Mage::helper('member')->logAPI($response['upgrade_success'] == 1 ? 'success' : 'failed');
        Mage::helper('member')->logAPI('POS point: ' . $memberHelper->apigetpoint($customer->getVipMemberId(), $apiclient, $NuSOAPClient));
        Mage::helper('member')->logAPI('Web point: ' . $memberHelper->getWebPoint($customer->getId()));
        if($response['upgrade_success'] == 2) {
            Mage::helper('member')->logAPI('Reason: Not enough points');
        }
        $this->getResponse()->setBody(json_encode($response));
    }

    public function changevipAction()
    {
    	$passowrd_admin = $this->getRequest()->getParam('passowrd_admin');
    	$vip_level_id = $this->getRequest()->getParam('vip_member_l_value');
    	$check_password_admin = 'admin123';
    	$data_send['error'] = '0';
    	if($passowrd_admin == $check_password_admin){
    		$this->changeviplevelAction($vip_level_id);
    		// $vip_level = 2;
    		$change_status = $this->changevip_statusAction($vip_level_id);
    		if($change_status == 1){
    			Mage::getSingleton('core/session')->addSuccess('Change Vip Success');
    		}else{
    			$data_send['error'] = '2';
    		}
    	}else{
    		$data_send['error'] = '1';
    	}
    	$this->getResponse()->setBody(json_encode($data_send));
    }
    public function changeviplevelAction($vip_level_id)
    {
    	$user_data = Mage::getSingleton('customer/session')->getSearchmember();
    	if($user_data){
	        $customer = Mage::getModel('customer/customer')->load($user_data['entity_id']);

	        if($vip_level_id == 1){
	        	$member_group_id = 1;
	        }elseif($vip_level_id == 2){
				$date = new DateTime("now", new DateTimeZone('Asia/Bangkok') );
				$register_date = $date->format('Y-m-d');
				$vip_expire_date = date("Y-m-d", strtotime( $register_date. " + 1 year"));
				$customer->setVipMemberExpireDate($vip_expire_date);
				$member_group_id = 4;
	        }
	        $customer->setGroupId($member_group_id)->save();
    	}

	    return 0;
    }
    public function changevip_statusAction($vip_level = 1)
    {
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
		{
			// --------------------------------------------------------
			// Call Webservice Function CustomerEdit
			// --------------------------------------------------------
			// echo "<br>";
			// echo "----------------------------------------------<br>";
			// echo "Test Function CustomerEdit()<br>";
			// echo "----------------------------------------------<br>";

			$user_data = Mage::getSingleton('customer/session')->getSearchmember();
			$Barcode = $user_data['vip_member_id'];
			$CustomerCode = $user_data['vip_member_id'];
			$Name = $user_data['firstname']." ".$user_data['lastname'];
			$ContactName = $user_data['firstname']." ".$user_data['lastname'];
			$EMail = $user_data['email'];
			$Address = "";
			$Tel = "";
			$Mobile = "";
			$Fax = "";
			$TaxCode = "";
			$RDBranchName = "";
			$MemberLevelString = $vip_level;
			$MemberExpireString = "31-12-2018";

			$DataReturn = $NuSOAPClient->call("CustomerEdit", array(
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
				"RDBranchName" => $RDBranchName,
				"MemberLevelString" => $MemberLevelString,
				"MemberExpireString" => $MemberExpireString));

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
				// echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				// echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				// echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
				if($ReturnValue[0]["Success"] == 1){
					return 1;
				}else{
					return 0;
				}
			}
		}
    }
	public function include_nusoap()
	{
		include("lib/nusoap/nusoap.php");
		$NuSOAPClientPath = Mage::getStoreConfig('mgfapisetting/mgfapi/apiurl');
		$Continue = true;
		$ReturnValue = "";

		if ($Continue)
		{
			// Create Webservice variable
			$NuSOAPClient = new nusoap_client($NuSOAPClientPath,true);
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn) 
			{
				// Display the error
				echo 'Error nusoap_client Constructor : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			$data_return['NuSOAPClient'] = $NuSOAPClient;
			$data_return['Continue'] = $Continue;
			return $data_return;
		}
	}

	public function testAction(){

        $apiclient = $this->include_nusoap();
        $NuSOAPClient = $apiclient['NuSOAPClient'];
        $session = Mage::getSingleton('customer/session');
        $customer = Mage::getModel('customer/customer')->load($session->getCustomer()->getId());
        $pos_point = Mage::helper('member')->apigetpoint($customer->getVipMemberId(), $apiclient, $NuSOAPClient);
        var_dump($pos_point);
    }

    public function saveVipNotifyMember($customerId)
    {
        if (!$customerId) {
            return $this;
        }
        try {
            /** @var Tigren_Member_Model_Notify_Vip $notify * */
            $notify = Mage::getModel('member/notify_vip');
            $notifyById = $notify->load($customerId, 'customer_id');
            if(!$notifyById) {
                $notify->setData('customer_id', $customerId);
                $notify->setData('notified_member', 0);
                $notify->save();
            }
            else {
                $notifyById->setData('notified_member', 0);
                $notifyById->save();
            }
        }
        catch (Exception $e) {
            var_dump($e->getMessage());die;
        }
    }
}