<?php

/*
* Copyright (c) 2017 www.tigren.com
*/
require_once Mage::getBaseDir().'/lib/omise-php/lib/Omise.php';
$config = Mage::getModel('omise_gateway/config')->load(1);
if ($config->getTestMode()) {
    define('OMISE_PUBLIC_KEY', $config->getPublicKeyTest());
    define('OMISE_SECRET_KEY', $config->getSecretKeyTest());
} else {
    define('OMISE_PUBLIC_KEY', $config->getPublicKey());
    define('OMISE_SECRET_KEY', $config->getSecretKey());
}

class Tigren_Ruffle_IndexController extends Mage_Core_Controller_Front_Action
{

    public $_listwinner = array();

    public function preDispatch()
    {
        parent::preDispatch();

        // a brute-force protection here would be nice
        // Mage_Core_Controller_Front_Action::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        $openActions = array(
            'index',
            'thankyou',
            'card',
            // 'confirm',
        );
        $pattern = '/^('.implode('|', $openActions).')/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('ruffle')->__('Raffle'));
        $this->renderLayout();
    }

    public function confirmAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $personalId = $data['personal_id'];
            $msg = $data['msg'];
            $customer_id = $data['customer_id'];
            $telephone = $data['telephone'];
            $docInvoice = $data['doc_invoice'];
            $joinerCollection = Mage::getModel('ruffle/joiner')->getCollection()
                ->addFieldToFilter('product_id', $data['product'])
                ->addFieldToFilter(array('telephone', 'customer_id', 'personal_id'), array(
                    array('telephone', 'eq' => $telephone),
                    array('customer_id', 'eq' => $customer_id),
                    array('personal_id', 'eq' => $personalId),
                    array('doc_invoice', 'eq' => $docInvoice)));

            $joinerCollection->getSelect()->joinLeft(array('ruffle' => 'ruffle'), 'main_table.ruffle_id = ruffle.ruffle_id', array("is_active" => 'ruffle.is_active'))->where('ruffle.is_active = 1');
            if ($joinerCollection->getData()) {
                $product = Mage::getModel('catalog/product')->load($data['product']);
                $url = $product->getProductUrl();
                Mage::getSingleton('core/session')->addError($this->__('This account OR Personal ID OR Telephone already used. Please check again'));
                $this->_redirectUrl($url);

                return;
            }
            if (!empty($data['storepickup_id'])) {
                $storePickupId = $data['storepickup_id'];
                $storePickup = Mage::getModel('storepickup/store')->load($storePickupId);
                if ($storePickup->getId()) {
                    $data['customer_ruffle_address'] = $storePickup->getData('address');
                }
                $data['firstname'] = $data['firstname_pickup'];
                $data['lastname'] = $data['lastname_pickup'];
            }
            if (!empty($data['use_creditcard'])) {
                $data['use_creditcard'] = 1;
                ///force save card token if case 2c2p
                $data['customer_card_token'] = $data['p2c2p_card_token'];
//                $data['isSaveCard'] = 1;

                /// this will use for omise
//                if (!empty($data['omise_card_id']) && $data['omise_card_id'] != 'new_omise_card') {
//                    $data['customer_card_token'] = $data['omise_card_id'];
//                    $data['isSaveCard'] = 1; // case use existing card
//                } else {
//                    if(!$data['isSaveCard']){
//                        $data['customer_card_token'] = $data['omise_token'];
//                    }else{
//                        $customer = Mage::getSingleton('customer/session')->getCustomer();
//                        $cardList = Mage::helper('ruffle/omise')->getListCardCustomerOmise($customer->getCustomerApiId());
//                        if ($cardList) {
//                            $lastCardKey = $cardList['total'];
//                            $lastCard = $cardList['data'][$lastCardKey - 1];
//                            $data['customer_card_token'] = $lastCard['id'];
//                        }
//                    }
//                }
            }else{
                $data['use_creditcard'] = 0;
                $data['customer_card_token'] = '';
            }


            if (!empty($data['super_attribute'])) {
                $ruffleData = array(
                    'product_id' => $data['product'],
                    'super_attribute' => $data['super_attribute'],
                    'tel' => $telephone,
                    'personal_id' => $personalId,
                    'msg' => $msg,
                    'doc_invoice' => $docInvoice,
                    'announce_date' => $data['announce_date'],
                    'email_text' => $data['email_text'],
                    'customer_ruffle_address' => isset($data['customer_ruffle_address']) ? $data['customer_ruffle_address'] : $data['street'],
                    'country_id' => $data['country_id'],
                    'city_id' => $data['city_id'],
                    'region_id' => $data['region_id'],
                    'subdistrict_id' => $data['subdistrict_id'],
                    'postcode' => $data['postcode'],
                    'storepickup_id' => isset($data['storepickup_id']) ? $data['storepickup_id'] : 0,
                    'customer_card_token' => $data['customer_card_token'],
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'use_creditcard' => $data['use_creditcard'],
                    'is_savecard' => $data['isSaveCard'],
                );
            } else {
                $ruffleData = array(
                    'product_id' => $data['product'],
                    'tel' => $telephone,
                    'personal_id' => $personalId,
                    'msg' => $msg,
                    'doc_invoice' => $docInvoice,
                    'announce_date' => $data['announce_date'],
                    'email_text' => $data['email_text'],
                    'customer_ruffle_address' => isset($data['customer_ruffle_address']) ? $data['customer_ruffle_address'] : $data['street'],
                    'country_id' => $data['country_id'],
                    'region_id' => $data['region_id'],
                    'city_id' => $data['city_id'],
                    'subdistrict_id' => $data['subdistrict_id'],
                    'postcode' => $data['postcode'],
                    'storepickup_id' => isset($data['storepickup_id']) ? $data['storepickup_id'] : 0,
                    'customer_card_token' => $data['customer_card_token'],
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'use_creditcard' => $data['use_creditcard'],
                    'is_savecard' => $data['isSaveCard'],
                );
            }

            Mage::getSingleton('customer/session')->setRuffleData($ruffleData);
            $this->loadLayout();
            $this->getLayout()->getBlock('head')->setTitle(Mage::helper('ruffle')->__('Raffle Confirmation'));
            $this->renderLayout();
        }else{
            $this->_redirect('*/index');

        }
    }

    public function joinAction()
    {
        $ruffleData = Mage::getSingleton('customer/session')->getRuffleData();
        if (isset($ruffleData) & !empty($ruffleData)) {
            $productId = $ruffleData['product_id'];
            $product = Mage::getModel('catalog/product')->load($productId);
            $ruffle = Mage::getModel('ruffle/ruffle')->getRuffleByProductId($productId);
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $productOptions = '';
            if (isset($ruffleData['super_attribute']) && !empty($ruffleData['super_attribute'])) {
                $productOptions = serialize($ruffleData['super_attribute']);
            }
            $childSelected = Mage::getModel('ruffle/product')->getChildProduct($productId, $productOptions);
            if ($childSelected) {
                $childProductName = $childSelected->getName();
            }
            if ($product && $ruffle) {
                try {
                    $joinerModel = Mage::getModel('ruffle/joiner');
                    $joinerData = array(
                        'ruffle_id' => $ruffle->getId(),
                        'customer_id' => $customer->getId(),
                        'product_id' => $productId,
                        'customer_name' => $customer->getName(),
                        'email_address' => $customer->getEmail(),
                        'ruffle_number' => Mage::helper('ruffle')->generateRuffleNumber($ruffle),
                        'telephone' => $ruffleData['tel'],
                        'product_name' => $childProductName,
                        'product_sku' => $product->getSku(),
                        'product_options' => $productOptions,
                        'personal_id' => $ruffleData['personal_id'],
                        'msg' => $ruffleData['msg'],
                        'doc_invoice' => $ruffleData['doc_invoice'],
                        'customer_ruffle_address' => $ruffleData['customer_ruffle_address'],
                        'country_id' => $ruffleData['country_id'],
                        'region_id' => $ruffleData['region_id'],
                        'city_id' => $ruffleData['city_id'],
                        'subdistrict_id' => $ruffleData['subdistrict_id'],
                        'postcode' => $ruffleData['postcode'],
                        'storepickup_id' => isset($ruffleData['storepickup_id']) ? $ruffleData['storepickup_id'] : 0,
                        'customer_card_token' => $ruffleData['customer_card_token'],
                        'firstname' => $ruffleData['firstname'],
                        'lastname' => $ruffleData['lastname'],
                        'is_savecard' => $ruffleData['is_savecard'],
                    );
                    $joinerModel->setData($joinerData)
                        ->setJoinedDate(now())
                        ->save();
//             generate Pdf
                    $customerModel = Mage::getModel('customer/customer')->load($customer->getId());
                    $customerModel->setData('telephone', $ruffleData['tel']);
                    $customerModel->save();

                    $content = $this->getContentHtml($joinerModel);
                    $name = 'Information_'.$joinerModel->getData('joiner_id').'_'.$joinerModel->getData('ruffle_number').'.pdf';
                    if (!file_exists(Mage::getBaseDir('media').DS.'pdf_ruffle')) {
                        mkdir(Mage::getBaseDir('media').DS.'pdf_ruffle', 0777, true);
                    }
                    file_put_contents(Mage::getBaseDir('media').DS.'pdf_ruffle'.DS.$name, $content);
                    $joinerModel->setData('pdf_path', $name)->save();

                    $product_img = Mage::helper('catalog/image')->init($product, 'small_image')->resize(265);

                    $joinerData['product_img'] = $product_img;
                    $joinerData['announce_date'] = $ruffleData['announce_date'];
                    $joinerData['email_text'] = $ruffleData['email_text'];

                    $sendEmail = Mage::getModel('ruffle/email')->sendEmailAfterJoining($joinerData, $name);

                    Mage::getSingleton('customer/session')->setRuffleJoined($joinerModel);
                    Mage::getSingleton('customer/session')->setSuccess('Joined');
                    $this->_redirect('*/*/thankyou');
                } catch (Exception $e) {
                    $this->getResponse()->setBody($e->getMessage());
                }
            }
        }
    }

    public function thankyouAction()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirectUrl(Mage::getBaseUrl());

            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('ruffle')->__('Thank you'));
        $this->renderLayout();
    }

    public function getContentHtml($joinerModel)
    {
        $htmlPdf = $this->getLayout()->createBlock('core/template')->setData('ruffle', $joinerModel)->setTemplate('ruffle/pdf_ruffle.phtml')->toHtml();
        $html = '';
        $html .= $htmlPdf;
        $modelPdf = Mage::getSingleton('pdfinvoiceplus/entity_pdfgenerator');
        $pdf = $modelPdf->loadPdf();
        $pdf->WriteHTML($html);

        return $pdf->Output('', 'S');
    }

    public function testAction()
    {
        $startIds = 19595;
        $count = 200;
        $productId = 43003;
        $product = Mage::getModel('catalog/product')->load($productId);
        $customerIds = array(19380, 19349, 18928, 18851, 18652, 18643, 18601, 18573, 18468, 18257, 18233, 18157);
        foreach ($customerIds as $i) {
            $customer = Mage::getModel('customer/customer')->load($i);
            $joinerModel = Mage::getModel('ruffle/joiner');
            $joinerData = array(
                'ruffle_id' => 1,
                'customer_id' => $customer->getId(),
                'product_id' => $productId,
                'customer_name' => $customer->getName(),
                'email_address' => $customer->getEmail(),
                'ruffle_number' => Mage::helper('ruffle')->generateRuffleNumber(),
                'telephone' => $ruffleData['tel'],
                'product_name' => $product->getName(),
                'product_sku' => $product->getSku(),
            );
            $joinerModel->setData($joinerData)
                ->setJoinedDate(now())
                ->save();
        }
        $productId = '43336';
        $_product = Mage::getModel('catalog/product')->load($productId);
        $attributes = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
        Zend_debug::dump($_product->getData());
        foreach ($attributes as $attribute) {
            foreach ($attribute['values'] as $value) {
                if ($value['value_index'] == 739) {
                    $this->getResponse()->setBody($value['store_label']);
                }
            }
        }
        $attribute = Mage::getModel('eav/entity_attribute')->loadById('255');

        die('xxx');
    }

    public function getWinnerNameAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        $winner_array = array();
        $winner_list = Mage::getModel('ruffle/joiner')
            ->getCollection()
            ->addFieldToFilter('is_winner', 1);
        if ($winner_list->getData()) {
            foreach ($winner_list as $_winner) {
                $winner_array[] = $_winner->getData('customer_name');
            }
        }
        $_response['success'] = 'true';
        $_response['list_winner'] = $winner_array;
        $this->getResponse()->setBody(json_encode($_response));

        return;
    }

    public function cardAction()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirectUrl(Mage::getBaseUrl());

            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('ruffle')->__('Card'));
        $this->renderLayout();
    }


    public function updateNewCardToCustomerAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $data = $this->getRequest()->getPost();
        if (!empty($data['card']['omise_token'])) {
            try {
                $customerOmise = Mage::helper('ruffle/omise')->getCustomerOmise($customer->getCustomerApiId());
                $customerOmise->update(array(
                    'card' => $data['card']['omise_token'],
                ));
                Mage::getSingleton('core/session')->addSuccess('The card was added successfully');
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError('The card was not added successfully');
            }
            $this->_redirect('omise/card/list');
        }
    }

    public function destroyCardOmiseAction()
    {
        try {
            $idCard = $this->getRequest()->getParam('id');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerOmise = Mage::helper('ruffle/omise')->getCustomerOmise($customer->getCustomerApiId());
            $card = $customerOmise->getCards()->retrieve($idCard);
            $card->destroy();

            $card->isDestroyed();
            Mage::getSingleton('core/session')->addSuccess('The card was deleted successfully');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('The card was not deleted successfully');
        }

        $this->_redirect('omise/card/list');
    }

    public function setDefaultCardOmiseAction()
    {
        try {
            $idCard = $this->getRequest()->getParam('id');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerOmise = Mage::helper('ruffle/omise')->getCustomerOmise($customer->getCustomerApiId());
            $customerOmise->update(array(
                'default_card'       => $idCard,
            ));
            Mage::getSingleton('core/session')->addSuccess('The card was set as primary successfully');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('The card was not set as primary successfully');
        }

        $this->_redirect('omise/card/list');
    }

    public function showFormStepOneOldAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        $params = $this->getRequest()->getParams();
        $checkParams = json_decode($params['check'], true);
        $popup = Mage::app()->getLayout()->createBlock('ruffle/card')
            ->setData('params', $params)
            ->setData('check', $checkParams);
        if(!empty($checkParams['use_creditcard'])){
//          $html_popup = $popup->setTemplate('ruffle/list_card.phtml')->toHtml();    //use for omise only
            $html_popup = $popup->setTemplate('p2c2p/list_card.phtml')->toHtml();

        }else{
            $html_popup = $popup->setTemplate('ruffle/form-information.phtml')->toHtml();
        }

        $_response['success'] = 'true';
        $_response['html_popup'] = $html_popup;

        $this->getResponse()->setBody(json_encode($_response));

        return;
    }


    public function showFormStepOneAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        $params = $this->getRequest()->getParams();
        $checkParams = json_decode($params['check'], true);
        $popup = Mage::app()->getLayout()->createBlock('ruffle/card')
            ->setData('params', $params)
            ->setData('check', $checkParams);
        if(!empty($checkParams['use_creditcard'])){
            // $html_popup = $popup->setTemplate('ruffle/list_card.phtml')->toHtml();
            $html_popup = $popup->setTemplate('ruffle/term_condition.phtml')->toHtml();
        }else{
            $html_popup = $popup->setTemplate('ruffle/form-information.phtml')->toHtml();
        }

        $_response['success'] = 'true';
        $_response['html_popup'] = $html_popup;

        $this->getResponse()->setBody(json_encode($_response));

        return;
    }

    public function showFormStepTwoAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        $params = $this->getRequest()->getParams();
        $checkParams = json_decode($params['check'], true);
        $popup = Mage::app()->getLayout()->createBlock('ruffle/card')
            ->setData('params', $params)
            ->setData('check', $checkParams);
        if(!empty($checkParams['use_creditcard'])){
            // $html_popup = $popup->setTemplate('ruffle/list_card.phtml')->toHtml();
            $html_popup = $popup->setTemplate('p2c2p/list_card.phtml')->toHtml();

        }else{
            $html_popup = $popup->setTemplate('ruffle/form-information.phtml')->toHtml();
        }

        $_response['success'] = 'true';
        $_response['html_popup'] = $html_popup;

        $this->getResponse()->setBody(json_encode($_response));

        return;
    }


    public function addCardToCustomerAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $data = $this->getRequest()->getPost();
        $checkParams = json_decode($data['check'], true);
        try {
            $isSavedCard = $data['isSavedCard'];
            if (!empty($data['omise_token'])) {
                if($isSavedCard){
                    if ($customer->getCustomerApiId()) {
                        $customerApi = OmiseCustomer::retrieve($customer->getCustomerApiId());
                        $customerApi->update(array(
                            'card' => $data['omise_token'],
                        ));
                    } else {
                        $customerApi = OmiseCustomer::create(array(
                            'email' => $customer->getEmail(),
                            'description' => $customer->getName(),
                            'card' => $data['omise_token'],
                        ));
                    }
                    $customer->setCustomerApiId($customerApi['id']);
                    $customer->save();
                    $result['customer_id_token'] = $customerApi['id'];
                }else{
                    $data['omise_token'] = $data['omise_token'];
                }
                // $data['omise_card_id'] =  $data['omise_token']; //new
            }else{
                // $data['omise_card_id'] = $data['omise_card_id'];//card
            }

            $html_popup = Mage::app()->getLayout()->createBlock('ruffle/card')
                ->setData('check', $checkParams)
                ->setData('omise_card_id', $data['omise_card_id'])
                ->setData('omise_token', $data['omise_token'])
                ->setData('isSavedCard', $isSavedCard)
                ->setTemplate('ruffle/form-information.phtml')->toHtml();
            $result['html_popup'] = $html_popup;
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $result['result'] = true;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
