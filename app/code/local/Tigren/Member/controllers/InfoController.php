<?php

class Tigren_Member_InfoController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function showAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
	public function getpointAction()
	{
		$member_code = $this->getRequest()->getParam('member_code');
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($member_code)
		{
			// --------------------------------------------------------
			// Call Webservice Function CustomerEdit
			// --------------------------------------------------------
			// echo "<br>";
			// echo "----------------------------------------------<br>";
			// echo "Test Function PointBalance()<br>";
			// echo "----------------------------------------------<br>";

			$Barcode = $member_code;
			// $Barcode = '0018061300005';

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
				$data_send = $ReturnValue[0]["PointBalance"];
			}
		}else{
			$data_send = 0;
		}
		$this->getResponse()->setBody(json_encode($data_send));
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
}