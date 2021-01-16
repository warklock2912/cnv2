<?php
class Marginframe_Apiregister_IndexController extends Mage_Core_Controller_Front_Action{
	public function indexAction()
	{
		// echo 'test';
		// Mage::log("hello", null, 'test.log', true);
		// $customer = Mage::getSingleton('customer/session')->getCustomer();
  //  		$data_customer = $customer->getData();
  //  		$data_customerset = Mage::getModel('customer/customer')->load($data_customer['entity_id']);
  //  		$customer->setVipMemberId('0018051100002')->save();
   		// echo '<pre>',print_r($data_customer,1),'</pre>';
   		// echo $run_numer = '00001';
   		// echo Mage::getStoreConfig('mgfapisetting/mgfapi/apiurl');
		// $h = "7";
		// $hm = $h * 60;
		// $ms = $hm * 60;
		// echo $date = gmdate("ymd", time()+($ms));
   		// echo $this->getnewcustomercode();
				// $customer = Mage::getSingleton('customer/session')->getCustomer();
				// echo '<pre>',print_r($customer->getData(),1),'</pre>';
				// $data_customer = $customer->getData();
				// $data_customerset = Mage::getModel('customer/customer')->load($data_customer['entity_id']);
   				// $customer->setVipMemberId('test');
   				// $customer->save();
		// $date = new DateTime("now", new DateTimeZone('Asia/Bangkok') );
		// $now = $date->format('Y-m-d');

  //  		// $now = '2020-08-08';
  //  		$collection = Mage::getResourceModel('customer/customer_collection')
  //  		->addFieldToFilter('vip_member_expire_date',array('lteq'=>$now));
  //  		$q_customer = $collection->getData();
  //  		foreach ($q_customer as $key => $data) {
  //  			$customer = Mage::getModel('customer/customer')->load($data['entity_id']);
  //  			$customer->setGroupId('1');
  //  			$customer->save();
  //  		}
   		// $order = Mage::getModel('sales/order')->loadByIncrementId('ORD-18-09-06-00000290');
   		
   		// $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
   		// $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
   		// $order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true)->save();
   		// echo '<pre>',print_r($order->getData(),1),'</pre>';

		// $customer_id = '19768';
		// $point_balance = '50';


		echo 'test=';
		$member_code = '03137';
		// $this->api_addpoint($member_code,500);
		// $this->api_removepoint($member_code,60);
		echo $this->getpointAction();




	}
	public function getnewcustomercode()
	{
		$h = "7";
		$hm = $h * 60;
		$ms = $hm * 60;
		$date = gmdate("ymd", time()+($ms));
   		$store_code = '00';
   		$date_code_config = Mage::getStoreConfig('mgfapisetting/registercode/datecode');
   		$run_code_config =Mage::getStoreConfig('mgfapisetting/registercode/runcode');
   		if($date == $date_code_config){
   			Mage::getConfig()->saveConfig('mgfapisetting/registercode/runcode', $run_code_config+1);
   			$run_code = $run_code_config;
   		}else{
   			Mage::getConfig()->saveConfig('mgfapisetting/registercode/datecode', $date);
   			Mage::getConfig()->saveConfig('mgfapisetting/registercode/runcode', '2');
   			$run_code = '00001';
   		}
		$run_numer = str_pad($run_code,5,"0",STR_PAD_LEFT);
   		return $store_code.$date.$run_numer;
	}
	public function newregisterAction()
	{

		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
		{
			// --------------------------------------------------------
			// Call Webservice Function CustomerAdd
			// --------------------------------------------------------
			echo "----------------------------------------------<br>";
			echo "Test Function CustomerAdd()<br>";
			echo "----------------------------------------------<br>";

			// $Barcode = "BC002";
			// $CustomerCode = "CU001";
			// $Name = "test Company";
			// $ContactName = "test system";
			// $EMail = "test@test.com";
			// $Address = "12/5 Moo 3 Lamlukka Pathumtani 12130";
			// $Tel = "021234567";
			// $Mobile = "0991234567";
			// $Fax = "021234568";
			// $TaxCode = "1234567890123";
			// $RDBranchName = "Head Office";

			$Barcode = "0018061300003";
			$CustomerCode = "0018061300003";
			$Name = "test001 system";
			$ContactName = "test001 system";
			$EMail = "test004@test.com";
			$Address = "";
			$Tel = "";
			$Mobile = "";
			$Fax = "";
			$TaxCode = "";
			$RDBranchName = "";
			$MemberLevelString = "2";
			$MemberExpireString = "15-05-2018";

			$DataReturn = $NuSOAPClient->call("CustomerAdd", array(
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
				"MemberExpireString" => $MemberExpireString));

			// Check for errors
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn)
			{
				// Display the error
				echo 'Error Call Function CustomerAdd : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			else
			{
				$ReturnValue = json_decode($DataReturn["CustomerAddResult"],true); // json decode from web service
				echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
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
	public function editAction()
	{
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
		{
			// --------------------------------------------------------
			// Call Webservice Function CustomerEdit
			// --------------------------------------------------------
			echo "<br>";
			echo "----------------------------------------------<br>";
			echo "Test Function CustomerEdit()<br>";
			echo "----------------------------------------------<br>";


			$Barcode = "0019011000004";
			$CustomerCode = "0019011000004";
			$Name = "testgen1 meepooh1";
			$ContactName = "testgen1112 meepooh1112";
			$EMail = "";
			$Address = "";
			$Tel = "";
			$Mobile = "";
			$Fax = "";
			$TaxCode = "";
			$RDBranchName = "";
			$MemberLevelString = "2";
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
				echo 'Error Call Function CustomerEdit : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			else
			{
				$ReturnValue = json_decode($DataReturn["CustomerEditResult"],true); // json decode from web service
				echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
			}
		}
	}
	public function getpointAction()
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
			// echo "Test Function PointBalance()<br>";
			// echo "----------------------------------------------<br>";

			$Barcode = "0019011000004";

			$DataReturn = $NuSOAPClient->call("PointBalance", array(
				"AuthenCode" => "CNVSabuy",
				"Barcode" => $Barcode));
			
			// Check for errors
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn) 
			{
				// Display the error
				// echo 'Error Call Function PointBalance : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			else 
			{
				$ReturnValue = json_decode($DataReturn["PointBalanceResult"],true); // json decode from web service
				// echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				// echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				// echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
				// echo "PointBalance = ".$ReturnValue[0]["PointBalance"]."<br>";
				return $ReturnValue[0]["PointBalance"];
			}
		}
	}
	public function api_addpoint($member_code,$point)
	{
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
		{
			// $Barcode = "0018061300005";
			$Barcode = $member_code;

			$DataReturn = $NuSOAPClient->call("PointAdd", array(
				"AuthenCode" => "CNVSabuy",
				"Barcode" => $Barcode,
				"PointString" => $point));
			
			// Check for errors
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn) 
			{
				// Display the error
				echo 'Error Call Function PointAdd : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			else 
			{
				$ReturnValue = json_decode($DataReturn["PointAddResult"],true); // json decode from web service
				echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
				echo "PointBalance = ".$ReturnValue[0]["PointBalance"]."<br>";
			}
		}
	}
	public function api_removepoint($member_code,$point)
	{
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
		{
			// --------------------------------------------------------
			// Call Webservice Function PointRemove
			// --------------------------------------------------------
			echo "<br>";
			echo "----------------------------------------------<br>";
			echo "Test Function PointRemove()<br>";
			echo "----------------------------------------------<br>";

			// $Barcode = "0018061300005";
			$Barcode = $member_code;

			$DataReturn = $NuSOAPClient->call("PointRemove", array(
				"AuthenCode" => "CNVSabuy",
				"Barcode" => $Barcode,
				"PointString" => $point));
			
			// Check for errors
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn) 
			{
				// Display the error
				echo 'Error Call Function PointRemove : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			else 
			{
				$ReturnValue = json_decode($DataReturn["PointRemoveResult"],true); // json decode from web service
				echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
				echo "PointBalance = ".$ReturnValue[0]["PointBalance"]."<br>";
			}
		}
	}
	public function addpointAction()
	{
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
		{
			// --------------------------------------------------------
			// Call Webservice Function CustomerEdit
			// --------------------------------------------------------
			echo "<br>";
			echo "----------------------------------------------<br>";
			echo "Test Function PointAdd()<br>";
			echo "----------------------------------------------<br>";

			$Barcode = "0019011000004";

			$DataReturn = $NuSOAPClient->call("PointAdd", array(
				"AuthenCode" => "CNVSabuy",
				"Barcode" => $Barcode,
				"PointString" => "25"));
			
			// Check for errors
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn) 
			{
				// Display the error
				echo 'Error Call Function PointAdd : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			else 
			{
				$ReturnValue = json_decode($DataReturn["PointAddResult"],true); // json decode from web service
				echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
				echo "PointBalance = ".$ReturnValue[0]["PointBalance"]."<br>";
			}
		}
	}
	public function removepointAction()
	{
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
	{
		// --------------------------------------------------------
		// Call Webservice Function PointRemove
		// --------------------------------------------------------
		echo "<br>";
		echo "----------------------------------------------<br>";
		echo "Test Function PointRemove()<br>";
		echo "----------------------------------------------<br>";

		$Barcode = "0019011000004";

		$DataReturn = $NuSOAPClient->call("PointRemove", array(
			"AuthenCode" => "CNVSabuy",
			"Barcode" => $Barcode,
			"PointString" => "74"));
		
		// Check for errors
		$ErrorReturn = $NuSOAPClient->getError();
		if ($ErrorReturn) 
		{
			// Display the error
			echo 'Error Call Function PointRemove : '.$ErrorReturn.'<br>';
			$Continue = false;
		}
		else 
		{
			$ReturnValue = json_decode($DataReturn["PointRemoveResult"],true); // json decode from web service
			echo "Success = ".$ReturnValue[0]["Success"]."<br>";
			echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
			echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
			echo "PointBalance = ".$ReturnValue[0]["PointBalance"]."<br>";
		}
	}
	}
	public function DatecodeAction()
	{
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		var_dump('DatecodeAction :: ');
		$ob = new Marginframe_Apiregister_Model_Observer();
		var_dump('DatecodeAction 1:: ');
		$res = $ob->getnewcustomercode();
		var_dump('DatecodeAction 2:: ');
		var_dump($res);
		die;
	}


}