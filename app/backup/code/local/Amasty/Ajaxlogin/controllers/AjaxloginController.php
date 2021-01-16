<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
require_once 'Mage/Customer/controllers/AccountController.php';

class Amasty_Ajaxlogin_AjaxloginController extends Mage_Customer_AccountController
{
    public function preDispatch()
    {
        // a brute-force protection here would be nice
        Mage_Core_Controller_Front_Action::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        $openActions = array(
            'create',
            'index',
            'header',
            'login',
            'logoutsuccess',
            'forgotpassword',
            'forgotpasswordpost',
            'resetpassword',
            'resetpasswordpost',
            'confirm',
            'iframe',
            'confirmation'
        );
        $pattern = '/^(' . implode('|', $openActions) . ')/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }
    
    //show login popup
    public function indexAction()
    {
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_login', 'form_login')
                             ->setTemplate('amasty/amajaxlogin/customer/form/login.phtml');
        $message = $block->toHtml();
        $this->showCartPopup($this->__('Login'), "", $message, 3);
    }
    
    public function forgotpasswordAction()
    {
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_login', 'form_login')
                         ->setTemplate('amasty/amajaxlogin/customer/form/forgotpassword.phtml');
        $message = $block->toHtml();
        $title = $this->__('Forgot Your Password?');
        $this->showCartPopup($title, "", $message, 3);
    }
    
    public function forgotpasswordpostAction()
    {
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_login', 'form_login')
                         ->setTemplate('amasty/amajaxlogin/customer/form/forgotpassword.phtml');
        $message = $block->toHtml();
        $title = $this->__('Forgot Your Password?');
        
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->showCartPopup($title, $this->__('Invalid email address.'), $message, 1);
                return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    $this->showCartPopup($title, $exception->getMessage(), $message, 1);
                    return;
                }
            }
            
            $this->showCartPopup($title, Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email)), $message, 2);
            return;
        } else {
            $this->showCartPopup($title, $this->__('Please enter your email.'), $message, 1);
            return;
        }
        
        
        
        $this->showCartPopup($title, "", $message, 3);
    }
    
    //reload header after login
    public function headerAction()
    {
        $this->loadLayout(array('default')); 
        $header = Mage::app()->getLayout('default')->getBlock('header');
        $this->getResponse()->setBody($header->toHtml());
    }
    
    public function logoutAction()
    {
        $this->_getSession()->logout()->setBeforeAuthUrl(Mage::getUrl());
        $this->showCartPopup($this->__('Login'), $this->__('You are now logged out.'),"", 2);
    }
    
    //default magento login action
    public function loginAction()
    {
        $title = $this->__('Login');
        $text = "";
        $status = 3;
        if ($this->_getSession()->isLoggedIn()) {
           $text =  $this->__('You are already logged in.');
           $status = 1;
            $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_login', 'form_login')
                             ->setTemplate('amasty/amajaxlogin/customer/form/login.phtml');
            $message = $block->toHtml();
            $this->showCartPopup($title, $text, $message, $status);
            return;
        }
        $session = $this->_getSession();
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try { 
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $text =  $this->_welcomeCustomer($session->getCustomer(), true);
                        $status = 2;
                    }
                    else {
                        $text =  $this->__('You are now logged in.');
                        $status = 2;
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $text = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $text = $e->getMessage();
                            break;
                        default:
                            $text = $e->getMessage();
                    }
                    $status = 1;
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    $text = $e->getMessage();
                    $status = 1;
                    $session->setUsername($login['username']);
                    Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $text = $this->__('Login and password are required.');
                $status = 1;
            }
        }
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_login', 'form_login')
                             ->setTemplate('amasty/amajaxlogin/customer/form/login.phtml');
        $message = $block->tohtml();
        $this->showCartPopup($title, $text, $message, $status);
    }

    public function createAction(){
        $title = $this->__('sign up');
        $text =  $this->__('');
        $status = 3;
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_register', 'form_register')
            ->setTemplate('amasty/amajaxlogin/customer/form/register.phtml');
        $message = $block->toHtml();
        $this->showCartPopup($title, $text, $message, $status);
    }

    public function createPostAction(){
        $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));

        /** @var $session Mage_Customer_Model_Session */
        $title = $this->__('sign up');
        $text = "";
        $status = 3;
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $text =  $this->__('You are logged in yet.');
            $status = 1;
            $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_login', 'form_login')
                ->setTemplate('amasty/amajaxlogin/customer/form/login.phtml');
            $message = $block->toHtml();
            $this->showCartPopup($title, $text, $message, $status);
            return;
        }

        $customer = $this->_getCustomer();

        try {
            $errors = $this->_getCustomerErrors($customer);

            if (empty($errors)) {
                $customer->cleanPasswordsValidationData();
                if($memberId = $this->getRequest()->getParam('vip_member_id')){
                  $customer->setData('vip_member_id', $memberId);
                  $customer->setData('vip_member_status', '1');
                }
                $customer->save();
                $this->_dispatchRegisterSuccess($customer);
                $this->_successProcessRegistration($customer);
                return;
            } else {
                $text = $errors;
                $status = 1;
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $text = $this->__('There is already an account with this email address.');
            } else {
                $text = $this->_escapeHtml($e->getMessage());
            }
            $status = 1;
        } catch (Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            $text = $e->getMessage();
            $status = 1;
        }

        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_register', 'form_register')
            ->setTemplate('amasty/amajaxlogin/customer/form/register.phtml');
        $message = $block->toHtml();
        $this->showCartPopup($title, $text, $message, $status);
    }

    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = $this->_getUrl('*/*/index', array('_secure' => true));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $url = $this->_welcomeCustomer($customer);
            $title = $this->__('sign up');
            $text = "<p>" . $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName()) . "</p>";
            $status = 2;
            $block = Mage::app()->getLayout()->createBlock('amajaxlogin/customer_form_login', 'form_login')
                ->setTemplate('amasty/amajaxlogin/customer/form/login.phtml');
            $message = $block->tohtml();
            $this->showCartPopup($title, $text, $message, $status);
        }
    }
       
    //creating finale popup 
    public function showCartPopup($title="", $text = "", $message = "", $is_error = 0){
       if($is_error == 1) {
           $text = "<div class='am-ajax-error'><img src=" . Mage::getDesign()->getSkinUrl('images/amasty/amajaxlogin/error.png',array('_area'=>'frontend')) . " alt=''/>"
                    . $text . "</div>";
       }
       if($is_error == 2) {
           $text = "<div class='am-ajax-success'><img src=" . Mage::getDesign()->getSkinUrl('images/amasty/amajaxlogin/success.png',array('_area'=>'frontend')) . " alt=''/>"
                    . $text . "</div>";
           $message = "";
       }
       
       $result = array(
              'title'     =>  $title, 
              'message'   =>  $message, 
              'error'     =>  $text,
              'is_error'  =>  $is_error
        ); 
        if(Mage::getStoreConfig('amajaxlogin/general/redirect')) {
            $result['redirect'] = Mage::getStoreConfig('amajaxlogin/general/redirect_url')? Mage::getStoreConfig('amajaxlogin/general/redirect_url'):Mage::getStoreConfig('amajaxlogin/general/redirect');
        }   
       
        $result = $this->replaceJs($result);
        $this->getResponse()->setBody($result);
    }
   
    //replace js in one place    
    public function replaceJs($result)
    {
         $arrScript = array();
         $result['script'] = '';               
         preg_match_all("@<script type=\"text/javascript\">(.*?)</script>@s",  $result['message'], $arrScript);
         $result['message'] = preg_replace("@<script type=\"text/javascript\">(.*?)</script>@s",  '', $result['message']);
         foreach($arrScript[1] as $script){ 
             $result['script'] .= $script;                 
         }
         $result['script'] =  preg_replace("@var @s",  '', $result['script']); 
         return Zend_Json::encode($result);
    } 
    
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $text = "<p>" . $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName()) . "</p>";
        $userPrompt = '';
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        return $text . "<p>" . $userPrompt . "</p>";
    }
    
    public function _login($userInfo, $token, $type, $typeName) {
        $title = 'amajaxlogin_'. $type .'_id';
        $customerBySocialId = Mage::helper('amajaxlogin')->getCustomerBySocialId($title, $userInfo['id']);
        //add data to the customer
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            if($customerBySocialId) {
                $this->showCartPopup($this->__('Login'), $this->__('This account already have another %s user.', $typeName), "", 2);
                return;    
            }
            
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            Mage::helper('amajaxlogin')->connectByEmail(
                $customer,
                $userInfo['id'],
                $token,
                $type
            );
            
             $this->showCartPopup($this->__('Login'), $this->__('Your %s account is now connected to your store accout. You can now login using our %s Connect button or using store account credentials you will receive to your email address.', $typeName, $typeName), "", 2); 
                
            return;
        }
        
        if($customerBySocialId) {
            // Existing connected user - login
            Mage::helper('amajaxlogin')->loginByCustomer($customerBySocialId);

            $this->showCartPopup($this->__('Login'), $this->__('You have successfully logged in using your %s account.', $typeName), "", 2);
            return;
        }
        if ('tw' == $type) {
              $this->showCartPopup($this->__('Login'), $this->__('We don`t find any account fot your credential.', $typeName), "", 1);
              return;  
        }
        $customerByEmail = null;
        if(array_key_exists('email', $userInfo))
            $customerByEmail = Mage::helper('amajaxlogin')
                ->getCustomerByEmail($userInfo['email']);

        if($customerByEmail) {                
           
            Mage::helper('amajaxlogin')->connectByEmail(
                $customerByEmail,
                $userInfo['id'],
                $token,
                $type
            );

            $this->showCartPopup($this->__('Login'), $this->__('We have discovered you already have an account at our store. Your %s account is now connected to your store account.', $typeName), "", 2);
            return;
        }

        // New connection - create, attach, login
        if(empty($userInfo['first_name'])) {
            $this->showCartPopup($this->__('Login'), $this->__('Sorry, could not retrieve your %s first name. Please try again.', $typeName), "", 1);
            return;
        }

        if(empty($userInfo['last_name'])) {
            $this->showCartPopup($this->__('Login'), $this->__('Sorry, could not retrieve your %s last name. Please try again.', $typeName), "", 1);
            return;
        }
        
        if(empty($userInfo['email'])) {
            $this->showCartPopup($this->__('Login'), $this->__('Sorry, could not retrieve your %s email. Please try again.', $typeName), "", 1);
            return;
        }

        Mage::helper('amajaxlogin')->connectByCreatingAccount(
            $userInfo['email'],
            $userInfo['first_name'],
            $userInfo['last_name'],
            $userInfo['id'],
            $token,
            $type
        );

        $this->showCartPopup($this->__('Login'), $this->__('Your %s account is now connected to your new user account at our store. Now you can login using our %s Connect button or using store account credentials you will receive to your email address.', $typeName, $typeName), "", 2);
    }
}
