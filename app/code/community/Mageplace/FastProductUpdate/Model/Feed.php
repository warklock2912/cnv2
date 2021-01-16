<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Mageplace_FastProductUpdate
 */

class Mageplace_FastProductUpdate_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const CACHE_NAME = 'mpfastproductupdate_notifications_lastcheck';
	
	const XML_FEED_URL_PATH     = 'mpfastproductupdate/feed/url';
    const XML_USE_HTTPS_PATH    = 'mpfastproductupdate/feed/use_https';
    const XML_FREQUENCY_PATH    = 'mpfastproductupdate/feed/check_frequency';
    const XML_FREQUENCY_ENABLE  = 'mpfastproductupdate/feed/enabled';
    const XML_LAST_UPDATE_PATH  = 'mpfastproductupdate/feed/last_update';

	
    public static function check()
    {
		if(!Mage::getStoreConfig(self::XML_FREQUENCY_ENABLE)) {
			return;
        }
		
		return Mage::getModel('mpfastproductupdate/feed')->checkUpdate();
    }
	
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 3600;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache(self::CACHE_NAME);
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), self::CACHE_NAME);
        return $this;
    }
    
    public function getFeedData()
    {
        $url = $this->getFeedUrl();
        
		$ch = @curl_init();
        @curl_setopt($ch, CURLOPT_URL, $url); 
        @curl_setopt($ch, CURLOPT_FAILONERROR, 1);  
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);  
        @curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
        $data = @curl_exec($ch); 
        @curl_close($ch);  
        
        if ($data === false) {
            return false;
        }
		
        try {
            $xml  = new SimpleXMLElement($data);
        } catch (Exception $e) {
            return false;
        }
		
        return $xml;
    }   
	
    public function getDate($rssDate)
    {
        return gmdate('Y-m-d H:i:s', strtotime($rssDate));
    }
   
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . Mage::getStoreConfig(self::XML_FEED_URL_PATH);
        }
        
		return $this->_feedUrl;        
    }

    public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }
        
        $feedData = array();

        $feedXml = $this->getFeedData();
        
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity'      => (int)$item->severity ? (int)$item->severity : 3,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }
             
            
            if ($feedData) {
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }

        }
        
		$this->setLastUpdate();
       
		return $this;
    }
 }
