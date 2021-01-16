<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */

class Amasty_GeoipRedirect_Model_Observer {

    protected $redirectAllowed = false;

    public function redirectStore($observer) {
        $cookie = Mage::getSingleton('core/cookie');
        $cookie->set('am_geoipredirect', 1);

        $controller = $observer->getControllerAction();

        $ipRestriction = Mage::getStoreConfig('amgeoipredirect/restriction/ip_restriction');
        $currentIp = Mage::helper('core/http')->getRemoteAddr();
        if (!empty($ipRestriction)) {
            $ipRestriction = array_map("rtrim", explode(PHP_EOL, $ipRestriction));
            foreach ($ipRestriction as $ip) {
                if ($currentIp && $currentIp == $ip) {
                    return;
                }
            }
        }
        $isApi = $controller->getRequest()->getControllerModule() == 'Mage_Api';
        if ($isApi || !Mage::helper('amgeoipredirect')->isModuleEnabled('Amasty_Geoip')) {
            return;
        }
        $userAgent = Mage::app()->getRequest()->getHeader('USER_AGENT');
        $userAgentsIgnore = Mage::getStoreConfig('amgeoipredirect/restriction/user_agents_ignore');
        if (!empty($userAgentsIgnore)) {
            $userAgentsIgnore = explode(',', $userAgentsIgnore);
            $userAgentsIgnore = array_map("trim", $userAgentsIgnore);
            foreach ($userAgentsIgnore as $agent) {
                if ($userAgent && $agent && stripos($userAgent, $agent) !== false) {
                    return;
                }
            }
        }
        $addToUrl = $this->applyLogic();
        if (Mage::getStoreConfig('amgeoipredirect/general/enable', Mage::app()->getStore()->getId()) && $this->redirectAllowed) {
            $ip = Mage::helper('core/http')->getRemoteAddr();
            $location = Mage::getModel('amgeoip/geolocation')->locate($ip);
            $country = $location->getCountry();

            $session = Mage::getSingleton('customer/session');
            if (Mage::getStoreConfig('amgeoipredirect/restriction/first_visit_redirect')) {
                $getAmYetRedirectStore = $session->getAmYetRedirectStore();
                $getAmYetRedirectCurrency = $session->getAmYetRedirectCurrency();
                $getAmYetRedirectUrl = $session->getAmYetRedirectUrl();
            } else {
                $getAmYetRedirectStore = 0;
                $getAmYetRedirectCurrency = 0;
                $getAmYetRedirectUrl = 0;
            }

            if (!$getAmYetRedirectUrl && Mage::getStoreConfig('amgeoipredirect/country_url/enable_url')) {
                $urlMapping = unserialize(Mage::getStoreConfig('amgeoipredirect/country_url/url_mapping', Mage::app()->getStore()->getId()));
                $currentUrl = Mage::helper('core/url')->getCurrentUrl();
                foreach ($urlMapping as $value) {
                    if (is_array($value['country_url'])) {
                        $checkCountry = in_array($country, $value['country_url']);
                    } else {
                        $checkCountry = $value['country_url'] == $country;
                    }
                    if ($checkCountry && $value['url_mapping'] != $currentUrl) {
                        $session->setAmYetRedirectUrl(1);
                        Mage::app()->getResponse()->setRedirect($value['url_mapping']);
                        Mage::app()->getResponse()->sendResponse();
                        exit;
                    }
                }
            }
            if (!$getAmYetRedirectStore && Mage::getStoreConfig('amgeoipredirect/country_store/enable_store')) {
                $allStores = Mage::app()->getStores();
                foreach ($allStores as $store) {
                    $currentStoreUrl = str_replace('&amp;', '&', $store->getCurrentUrl(false));
                    $redirectStoreUrl = trim($currentStoreUrl, '/') . $addToUrl;
                    $countries = Mage::getStoreConfig('amgeoipredirect/country_store/affected_countries', $store->getId());
                    if (!Mage::getStoreConfig('amgeoipredirect/restriction/redirect_between_websites')) {
                        $useMultistores = $store->getWebsiteId() == Mage::app()->getStore()->getWebsiteId();
                    } else {
                        $useMultistores = true;
                    }
                    if ($country && $countries && strpos($countries, $country) !== false
                        && $store->getId() != Mage::app()->getStore()->getId()
                        && $useMultistores
                    ) {
                        $session->setAmYetRedirectStore(1);
                        Mage::app()->setCurrentStore($store);
                        Mage::app()->getResponse()->setRedirect($redirectStoreUrl);
                        Mage::app()->getResponse()->sendResponse();
                        exit;
                    }
                }
            }
            if (!$getAmYetRedirectCurrency && Mage::getStoreConfig('amgeoipredirect/country_currency/enable_currency')) {
                $currencyMapping = unserialize(Mage::getStoreConfig('amgeoipredirect/country_currency/currency_mapping', Mage::app()->getStore()->getId()));
                foreach ($currencyMapping as $value) {
                    if (is_array($value['country_currency'])) {
                        $checkCountry = in_array($country, $value['country_currency']);
                    } else {
                        $checkCountry = $value['country_currency'] == $country;
                    }
                    if ($checkCountry && Mage::app()->getStore()->getCurrentCurrencyCode() != $value['currency']) {
                        $session->setAmYetRedirectCurrency(1);
                        Mage::app()->getStore()->setCurrentCurrencyCode($value['currency']);
                    }
                }
            }
        }
    }

    protected function applyLogic() {
        $applyLogic = Mage::getStoreConfig('amgeoipredirect/restriction/apply_logic');
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $baseUrl = Mage::app()->getStore()->getCurrentUrl();
        switch ($applyLogic) {
            case Amasty_GeoipRedirect_Model_Source_ApplyLogic::ALL_URLS :
                $this->redirectAllowed = true;
                $url = substr($currentUrl, strlen($baseUrl)-1);
                return $url;
                break;
            case Amasty_GeoipRedirect_Model_Source_ApplyLogic::SPECIFIED_URLS :
                $acceptedUrls = explode(PHP_EOL, Mage::getStoreConfig('amgeoipredirect/restriction/accepted_urls'));
                foreach ($acceptedUrls as $url) {
                    $url = trim($url);
                    if ($url && $currentUrl && strpos($currentUrl, $url)) {
                        $this->redirectAllowed = true;
                        return $url;
                    }
                }
                break;
            case Amasty_GeoipRedirect_Model_Source_ApplyLogic::EXCEPT_URLS :
                $exceptedUrls = explode(PHP_EOL, Mage::getStoreConfig('amgeoipredirect/restriction/excepted_urls'));
                foreach ($exceptedUrls as $url) {
                    $url = trim($url);
                    if ($url && $currentUrl && strpos($currentUrl, $url)) {
                        $this->redirectAllowed = false;
                        return $url;
                    } else {
                        $this->redirectAllowed = true;
                    }
                }
                break;
            case Amasty_GeoipRedirect_Model_Source_ApplyLogic::HOMEPAGE_ONLY :
                $routeName = Mage::app()->getRequest()->getRouteName();
                $action = Mage::app()->getFrontController()->getRequest()->getActionName();
                if($routeName == 'cms' && $action == 'index') {
                    $this->redirectAllowed = true;
                }
                break;
        }
        return '';
    }
}