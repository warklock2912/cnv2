<?php
/*
* Copyright (c) 2018 Margin Frame Arrang
*/
class Marginframe_Shippop_Adminhtml_ShippopController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('shippop/mange')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Shippop Manager'), Mage::helper('adminhtml')->__('Shippop Manager'))
			;
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()->renderLayout();
	}
	public function printAction() {
		$purchase_id = $this->getRequest()->getParam('shippop_purchase_id');
		$print = $this->label( $purchase_id );

		echo $print['html'];
		
	}
	public function label( $purchase_id ) {
        $url 	= Mage::getStoreConfig('Ship/config_api/url', Mage::app()->getStore());
        $apikey = Mage::getStoreConfig('Ship/config_api/apikey', Mage::app()->getStore());
        $sizeprint = 'letter';
        $post_data = array(
            'api_key'=>$apikey,
            'purchase_id' => $purchase_id,
            'size'=> $sizeprint
        );
        $post_data = http_build_query($post_data);
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, 'http://mkpservice.'.$url.'/label/');
        curl_setopt($curl,CURLOPT_POST, sizeof($post_data));
        curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }
}
?>