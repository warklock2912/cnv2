<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_IndexController extends Mage_Core_Controller_Front_Action {

  public $_listwinner = array();
  	public function indexAction() {			
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('ruffle')->__('Raffle'));
		$this->renderLayout();
  	}

  	public function confirmAction() {
  		if ($data = $this->getRequest()->getPost()) {
        $personalId = $data['personal_id'];
        $msg = $data['msg'];
        $customer_id = $data['customer_id'];
        $telephone = $data['telephone'];
        $joinerCollection = Mage::getModel('ruffle/joiner')->getCollection()
          ->addFieldToFilter('product_id', $data['product'])
          ->addFieldToFilter(array('telephone', 'customer_id', 'personal_id'), array(
            array('telephone','eq'=>$telephone),
            array('customer_id','eq'=>$customer_id),
            array('personal_id', 'eq'=>$personalId) ));

        $joinerCollection->getSelect()->joinLeft(array('ruffle' =>'ruffle'), 'main_table.ruffle_id = ruffle.ruffle_id', array("is_active" =>'ruffle.is_active'))->where('ruffle.is_active = 1');

        if($joinerCollection->getData()){
          $product = Mage::getModel('catalog/product')->load($data['product']);
          $url = $product->getProductUrl();
          Mage::getSingleton('core/session')->addError($this->__('This account OR Personal ID OR Telephone already used. Please check again'));
          $this->_redirectUrl($url);
          return;
        }
        if(!empty($data['super_attribute'])){
    			$ruffleData = array(
    				'product_id' => $data['product'],
            'super_attribute'  => $data['super_attribute'],
            'tel' => $telephone,
            'personal_id' => $personalId,
            'msg' => $msg,
            'announce_date' => $data['announce_date'],
            'email_text' => $data['email_text']
    			);
        } else {
          $ruffleData = array(
            'product_id' => $data['product'],
            'tel' => $telephone,
            'personal_id' => $personalId,
            'msg' => $msg,
            'announce_date' => $data['announce_date'],
            'email_text' => $data['email_text']
          );
        }

  			Mage::getSingleton('customer/session')->setRuffleData($ruffleData);
  			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle(Mage::helper('ruffle')->__('Raffle Confirmation'));
			$this->renderLayout();
  		}
  	}

  	public function joinAction() {
  		$ruffleData = Mage::getSingleton('customer/session')->getRuffleData();
  		if (isset($ruffleData) & !empty($ruffleData)) {
  			$productId = $ruffleData['product_id'];
  			$product = Mage::getModel('catalog/product')->load($productId);
  			$ruffle = Mage::getModel('ruffle/ruffle')->getRuffleByProductId($productId);
  			$customer = Mage::getSingleton('customer/session')->getCustomer();
        $productOptions = '';
        if(isset($ruffleData['super_attribute']) && !empty($ruffleData['super_attribute'])){
          $productOptions = serialize($ruffleData['super_attribute']);
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
  						'product_name' => $product->getName(),
  						'product_sku' => $product->getSku(),
              'product_options' => $productOptions,
              'personal_id' => $ruffleData['personal_id'],
              'msg' => $ruffleData['msg']
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
  					return;
  				}
  				catch (Exception $e) {
                    $this->getResponse()->setBody($e->getMessage());
  					die('xxx');
  				}
  			}
  		}
  	}

  	public function thankyouAction() {
      if(!Mage::getSingleton('customer/session')->isLoggedIn()){
        $this->_redirectUrl(Mage::getBaseUrl());
        return;
      }
  		$this->loadLayout();
  		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('ruffle')->__('Thank you'));
  		$this->renderLayout();
  }

  public function getContentHtml($joinerModel){
    $htmlPdf = $this->getLayout()->createBlock('core/template')->setData('ruffle',$joinerModel)->setTemplate('ruffle/pdf_ruffle.phtml')->toHtml();
    $html = '';
    $html .= $htmlPdf;
    $modelPdf = Mage::getSingleton('pdfinvoiceplus/entity_pdfgenerator');
    $pdf = $modelPdf->loadPdf();
    $pdf->WriteHTML($html);
    return $pdf->Output('', 'S');
  }

  	public function testAction() {
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
          'product_sku' => $product->getSku()
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
    if($value['value_index'] == 739){
        $this->getResponse()->setBody($value['store_label']);
    }
  }
}
      $attribute = Mage::getModel('eav/entity_attribute')->loadById('255');
     
      die('xxx');
  	}

  public function getWinnerNameAction(){
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $_response = array();
      $winner_array = array();
      $winner_list  = Mage::getModel('ruffle/joiner')
        ->getCollection()
        ->addFieldToFilter('is_winner', 1);
      if($winner_list->getData()){
        foreach($winner_list as $_winner){
          $winner_array[] = $_winner->getData('customer_name');
        }
      }
      $_response['success'] = 'true';
      $_response['list_winner'] = $winner_array;
      $this->getResponse()->setBody(json_encode($_response));
      return;
  }
}