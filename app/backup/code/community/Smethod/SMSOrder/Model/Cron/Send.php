<?php 
class Smethod_SMSOrder_Model_Cron_Send  extends Mage_Core_Model_Abstract
{
	public function Run(){
		
		// Todo
		// get all datafrom db and get high proirity for send message
		$sql = "select id,orderid,phonenumber,message,priority from tb_ordersmsnotification 
			where processstatus = 'N'
			order by priority desc";
		$dbconnRead = Mage::getSingleton('core/resource')->getConnection('core_read');
		$data = $dbconnRead->fetchAll($sql);
		$sendlist = array();
		foreach ($data as $key => $value) {
			 
			// if(array_key_exists($value['orderid'], $sendlist)){
			// 	//compare priority
			// 	if($sendlist[$value['orderid']]['priority'] < $value['priority']){
			// 		$sendlist[$value['orderid']] = $value;	
			// 	}
			// } else {
			// 	$sendlist[$value['orderid']] = $value;
			// }
			//send All Message
			$sendlist[] = $value;
		}
		
		$helper = Mage::helper('SMSOrder');
		foreach ($sendlist as $key => $value) {
			$result = $helper->smsSend($value['phonenumber'],$value['message']);
			Mage::log($result, null, 'track.log',true);
			$xml = @simplexml_load_string( $result);

			if (!is_object($xml)) {
		        $sqlUldate = "update tb_ordersmsnotification set processstatus='E' , processenddate=NOW(), processstatusmsg='Response Error' where id='".$value['id']."'";
		    } else {
		        if ($xml->send->status == 'success'){
		            $sqlUldate = "update tb_ordersmsnotification set processstatus='S' , processenddate=NOW(),processstatusmsg='[".$xml->send->uuid.']'.$xml->send->message."' where id='".$value['id']."'";
		        } else {
		            $sqlUldate = "update tb_ordersmsnotification set processstatus='E' , processenddate=NOW(),processstatusmsg='[".$xml->send->uuid.']'.$xml->send->message."' where id='".$value['id']."'";
		        }
		    }
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
        	$write->query($sqlUldate);
			unset($write);        	
			//update DB
		}

		unset($dbconnRead);
	}
}
?>