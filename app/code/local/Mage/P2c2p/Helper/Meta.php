<?php
class Mage_P2c2p_Helper_Meta extends Mage_Core_Helper_Abstract
{
	public function p2c2p_meta($request){
		$customer_id = Mage::getSingleton('customer/session')->getId();
		
		$model = Mage::getModel('p2c2p/meta');

		if(!$model) {
			die("2C2P Expected Model not available.");
		}
		
		$model->setData('order_id' , array_key_exists('order_id',$request) ? $request['order_id'] : '' );
		$model->setData('user_id' , $customer_id);
		$model->setData('version' , array_key_exists('version',$request) ? $request['version'] : '' );
		$model->setData('request_timestamp' , array_key_exists('request_timestamp',$request) ? $request['request_timestamp'] : '' );
		$model->setData('merchant_id' , array_key_exists('merchant_id',$request) ? $request['merchant_id'] : '');
		$model->setData('invoice_no' , array_key_exists('invoice_no',$request) ? $request['invoice_no'] : '' );
		$model->setData('currency' , array_key_exists('currency',$request) ? $request['currency'] : '' );
		$model->setData('amount' , array_key_exists('amount',$request) ? $request['amount'] : '');
		$model->setData('transaction_ref' , array_key_exists('transaction_ref',$request) ? $request['transaction_ref'] : '' );
		$model->setData('approval_code' , array_key_exists('approval_code',$request) ? $request['approval_code'] : '' );
		$model->setData('eci' , array_key_exists('eci',$request) ? $request['eci'] : '' );
		$model->setData('transaction_datetime' , array_key_exists('transaction_datetime',$request) ? $request['transaction_datetime'] : '' );
		$model->setData('payment_channel' , array_key_exists('payment_channel',$request) ? $request['payment_channel'] : '' );
		$model->setData('payment_status' , array_key_exists('payment_status',$request) ? $request['payment_status'] : '' );
		$model->setData('channel_response_code' , array_key_exists('channel_response_code',$request) ? $request['channel_response_code'] : '' );
		$model->setData('channel_response_desc' , array_key_exists('channel_response_desc',$request) ? $request['channel_response_desc'] : '' );
		$model->setData('masked_pan' , array_key_exists('masked_pan',$request) ? $request['masked_pan'] : '' );
		$model->setData('stored_card_unique_id' , array_key_exists('stored_card_unique_id',$request) ? $request['stored_card_unique_id'] : '' );
		$model->setData('backend_invoice' , array_key_exists('backend_invoice',$request) ? $request['backend_invoice'] : '' );
		$model->setData('paid_channel' , array_key_exists('paid_channel',$request) ? $request['paid_channel'] : '' );
		$model->setData('paid_agent' , array_key_exists('paid_agent',$request) ? $request['paid_agent'] : '' );
		$model->setData('recurring_unique_id' , array_key_exists('recurring_unique_id',$request) ? $request['recurring_unique_id'] : '' );
		$model->setData('user_defined_1' , array_key_exists('user_defined_1',$request) ? $request['user_defined_1'] : '' );
		$model->setData('user_defined_2' , array_key_exists('user_defined_2',$request) ? $request['user_defined_2'] : '' );
		$model->setData('user_defined_3' , array_key_exists('user_defined_3',$request) ? $request['user_defined_3'] : '' );
		$model->setData('user_defined_4' , array_key_exists('user_defined_4',$request) ? $request['user_defined_4'] : '' );
		$model->setData('user_defined_5' , array_key_exists('user_defined_5',$request) ? $request['user_defined_5'] : '' );
		$model->setData('browser_info' , array_key_exists('browser_info',$request) ? $request['browser_info'] : '' );
		$model->setData('ippPeriod' , array_key_exists('ippPeriod',$request) ? $request['ippPeriod'] : '');
		$model->setData('ippInterestType' , array_key_exists('ippInterestType',$request) ? $request['ippInterestType'] : '' );
		$model->setData('ippInterestRate' , array_key_exists('ippInterestRate',$request) ? $request['ippInterestRate'] : '' );
		$model->setData('ippMerchantAbsorbRate' , array_key_exists('ippInterestRate',$request) ? $request['ippMerchantAbsorbRate'] : '' );

		$model->save();
	}
}
