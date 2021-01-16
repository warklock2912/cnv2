<?php
require_once Mage::getModuleDir('controllers','Mage_Customer').DS."AccountController.php";
class Marginframe_Apiregister_AccountController extends Mage_Customer_AccountController{

    public function preDispatch()
    {
        $store = Mage::app()->getStore();
        if($this->getRequest()->getParam('renew')) {
            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getStoreConfig('rewardpoints/display/frontend_link', $store));
        }
        // a brute-force protection here would be nice
        parent::preDispatch();
    }

    public function editPostAction(){
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();
            /** @var $customerForm Mage_Customer_Model_Form */
            $customerForm = $this->_getModel('customer/form');
            $customerForm->setFormCode('customer_account_edit')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());
            $data_telephone = $this->getRequest()->getParam('amcustomerattr');
            $api_data = $customerData;
            $api_data['telephone'] = $data_telephone['telephone'];
            // echo '<pre>',print_r($api_data,1),'</pre>';die;
            $errors = array();
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $errors = array();

                // If password change was requested then add it to common validation scheme
                if ($this->getRequest()->getParam('change_password')) {
                    $currPass   = $this->getRequest()->getPost('current_password');
                    $newPass    = $this->getRequest()->getPost('password');
                    $confPass   = $this->getRequest()->getPost('confirmation');

                    $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                    if ( $this->_getHelper('core/string')->strpos($oldPass, ':')) {
                        list($_salt, $salt) = explode(':', $oldPass);
                    } else {
                        $salt = false;
                    }

                    if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                        if (strlen($newPass)) {
                            /**
                             * Set entered password and its confirmation - they
                             * will be validated later to match each other and be of right length
                             */
                            $customer->setPassword($newPass);
                            $customer->setPasswordConfirmation($confPass);
                        } else {
                            $errors[] = $this->__('New password field cannot be empty.');
                        }
                    } else {
                        $errors[] = $this->__('Invalid current password');
                    }
                }

                // Validate account and compose list of errors if any
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($errors, $customerErrors);
                }
            }

            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
                $this->_redirect('*/*/edit');
                return $this;
            }

            try {
                $customer->cleanPasswordsValidationData();
                $customer->save();
                $this->_getSession()->setCustomer($customer)
                    ->addSuccess($this->__('The account information has been saved.'));
                $this->apiedit($api_data);
                $this->_redirect('customer/account');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirect('*/*/edit');
    }
	public function apiedit($data_save)
	{
		$apiclient = $this->include_nusoap();
		$NuSOAPClient = $apiclient['NuSOAPClient'];
		$Continue = $apiclient['Continue'];
		if ($Continue)
		{
			$customer = Mage::getSingleton('customer/session')->getCustomer();

			$Barcode = $customer->getVipMemberId();
			$CustomerCode = $customer->getVipMemberId();
			$Name = $data_save['firstname']." ".$data_save['lastname'];
			$ContactName = $data_save['firstname']." ".$data_save['lastname'];
			$EMail = $data_save['email'];
			$Address = "";
			$Tel = "";
			$Mobile = $data_save['telephone'];
			$Fax = "";
			$TaxCode = "";
			$RDBranchName = "";

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
				"RDBranchName" => $RDBranchName));

			// Check for errors
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn)
			{
				// Display the error
				// echo 'Error Call Function CustomerEdit : '.$ErrorReturn.'<br>';
				$Continue = false;
			}
			else
			{
				$ReturnValue = json_decode($DataReturn["CustomerEditResult"],true); // json decode from web service
				// echo "Success = ".$ReturnValue[0]["Success"]."<br>";
				// echo "ErrorCode = ".$ReturnValue[0]["ErrorCode"]."<br>";
				// echo "ErrorMessage = ".$ReturnValue[0]["ErrorMessage"]."<br>";
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
}