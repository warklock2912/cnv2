<?php
class Smethod_SMSOrder_Model_Observer extends Mage_Core_Model_Abstract
{
	public function getMessage($SMSCode , $reference = array()){
		$SMSMessage = Mage::getStoreConfig('SMSOrder/messages/order_message' , Mage::app()->getStore());
		if(count($reference)>0){
			foreach ($reference as $key => $value) {
				$SMSMessage = str_replace('%'.$key.'%', $value,$SMSMessage);
			}
		}
		
		return $SMSMessage;
	}

	public function getMessageConfig($code , $reference = array()){
		$SMSMessage = Mage::getStoreConfig('SMSOrder/'.$code.'/order_message' , Mage::app()->getStore());
		if(count($reference)>0){
			foreach ($reference as $key => $value) {
				$SMSMessage = str_replace('%'.$key.'%', $value,$SMSMessage);
			}
		}
		return $SMSMessage;
	}

	public function order_save_after($observer){
		$active = Mage::getStoreConfig('SMSOrder/config_api/active', Mage::app()->getStore());
		if ($active == 1) {
			try {
				$order = $observer->getEvent()->getOrder();


				$increment_id =	$order->getIncrementId();
				$new_state = strtolower(trim($order->getState()));
				$new_status = strtolower(trim($order->getStatus()));

				$old_state = $order->getOrigData('state');
				$old_status = $order->getOrigData('status');

				$order_payment = $order->getPayment()->getMethod();
				$grand_total	= $order->getGrandTotal();
				$grand_total	= number_format($grand_total);
				$order_id = (int)$order->getId();
				$phone_number = $order->getBillingAddress()->getTelephone();


				$message = '';
				$detail = '';

				if ($old_state != $new_state || $old_status != $new_status ) {

					if(Mage::getStoreConfig('SMSOrder/orderstatus_a/active', Mage::app()->getStore())){
						if(	$new_status == Mage::getStoreConfig('SMSOrder/orderstatus_a/order_status')
							&& $new_state == Mage::getStoreConfig('SMSOrder/orderstatus_a/order_state')
							&& $order_payment 	== Mage::getStoreConfig('SMSOrder/orderstatus_a/order_payment')
						){
							$ref = array(
										'ORDER_ID' => $increment_id,
										'AMOUNT' => $grand_total
									);
							$message = $this->getMessageConfig('orderstatus_a',$ref);
						}
					}



					if(Mage::getStoreConfig('SMSOrder/orderstatus_b/active', Mage::app()->getStore())){
						$orderPayment = explode(',', Mage::getStoreConfig('SMSOrder/orderstatus_b/order_payment'));
						if($new_status == Mage::getStoreConfig('SMSOrder/orderstatus_b/order_status')
							&& $new_state 	== Mage::getStoreConfig('SMSOrder/orderstatus_b/order_state')
							&& in_array($order_payment, $orderPayment)
						){
							$paymentmessage = Mage::getStoreConfig('SMSOrder/orderstatus_b/order_paymentmessage');

							$ref = array(
										'ORDER_ID' => $increment_id,
										'AMOUNT' => $grand_total
									);
							$message = $this->getMessageConfig('orderstatus_b', $ref);

						}
					}

					if(Mage::getStoreConfig('SMSOrder/orderstatus_c/active', Mage::app()->getStore())){

						$orderPayment = explode(',', Mage::getStoreConfig('SMSOrder/orderstatus_c/order_payment'));

						if($new_status == Mage::getStoreConfig('SMSOrder/orderstatus_c/order_status')
							&& $new_state 	== Mage::getStoreConfig('SMSOrder/orderstatus_c/order_state')
							&& in_array($order_payment, $orderPayment)){
							$ref = array(
										'ORDER_ID' => $increment_id,
										'AMOUNT' => $grand_total
									);
							$message = $this->getMessageConfig('orderstatus_c',$ref);
						}
					}

					if($message!= ''){
						$helper = Mage::helper('SMSOrder');
						$result = $helper->smsSend($phone_number,$message);
						Mage::log($result, null, 'SMSOrder.log',true);
						Mage::log($message, null, 'SMSMsg.log',true);

					} else {

					}
				}
			} catch(Exception $e) {
				Mage::log((string)$e, null, "SMSOrder.log",true);
				Mage::log($message, null, 'SMSMsg.log',true);
			}
		}

	}

	/////////////////// Tracking /////////////////// 
	public function shipments(Varien_Event_Observer $observer) {
		// $new_state = strtolower(trim($order->getState()));
		// $new_status = strtolower(trim($order->getStatus()));
		// $old_state = $order->getOrigData('state');
		// $old_status = $order->getOrigData('status');

		// if ($old_state != $new_state || $old_status != $new_status ) {
			$active = Mage::getStoreConfig('SMSOrder/config_api/active', Mage::app()->getStore());
			$active_track = Mage::getStoreConfig('SMSOrder/messages/active', Mage::app()->getStore());
			if ($active == 1 && $active_track == 1) {
				$message = '';
				$detail = '';
				$event = $observer->getEvent();
				$track = $event->getTrack();
				$trackingId =  $track->getNumber();
				// The shipment itself can be found in the track object,
				// and the order inside the shipment object:
				$shipment = $track->getShipment();
				

				$order = $shipment->getOrder();
				$track_title = $order->getTracksCollection()->getFirstItem()->getTitle();
				$orderShip  = $order->getShippingMethod(); //Fetch the shipping method from order

				if($orderShip != 'storepickup_storepickup'){

					$new_state = strtolower(trim($order->getState()));
					$new_status = strtolower(trim($order->getStatus()));
					$order_id = $order->getIncrementId();
					$grand_total = $order->getGrandTotal();
						$SMSCode = 'tracking_msg';

						$ref = array(
							'TRACKING_NUMBER'=>$trackingId,
							'ORDER_ID'=>$order_id,
							'TRACK_TITLE'=>$track_title
						);
					$message = $this->getMessage('orderstatus_track',$ref);
					$phone_number = $order->getBillingAddress()->getTelephone();
					$helper = Mage::helper('SMSOrder');
					$result = $helper->smsSend($phone_number,$message);
					Mage::log($result, null, 'SMSOrder.log',true);
					Mage::log($result, null, 'SMSMsg.log',true);
				}
			}
		// }
	}
}
