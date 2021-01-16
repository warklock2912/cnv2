<?php
class Steverobbins_Redismanager_TestController extends Mage_Core_Controller_Front_Action
{

	public function testAction(){
		$flushThis = null;
		$hp = Mage::helper('redismanager');
		$config = Mage::getStoreConfig('redismanager/settings/includename');
		$includeName = array();
		if($config!=''){
			$includeName = explode(',', $config);
		}
		
		echo count($includeName);
		
		if(count($includeName)>0){
			foreach ($hp->getServices() as $service) {
	            $serviceMatch = $service['host'] . ':' . $service['port'];
	            if (in_array($service['name'], $includeName)
	                || in_array($serviceMatch, $flushed)
	                || (!is_null($flushThis) && $flushThis != $serviceMatch)
	            ) {
	                continue;
	            }
	            try {
	                $hp->getRedisInstance(
	                    $service['host'],
	                    $service['port'],
	                    $service['password'],
	                    $service['db']
	                )->getRedis()->flushAll();
	                $flushed[] = $serviceMatch;
	                // Mage::getSingleton('core/session')->addSuccess($this->__('%s flushed', $serviceMatch));
	            } catch (Exception $e) {
	                // Mage::getSingleton('core/session')->addError($e->getMessage());
	            }
        	}
        }

		echo "<pre>";
		print_r($includeName);
		echo "</pre>";
	}
}
?>