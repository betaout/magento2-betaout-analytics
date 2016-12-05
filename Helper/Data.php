<?php
/**
 * Copyright 2016 Betaout
 *
 * This file is part of Betaout_Analytics.
 *
 * Betaout_Analytics is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Betaout_Analytics is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Betaout_Analytics.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Betaout\Analytics\Helper;

use Magento\Store\Model\Store;

/**
 * BEtaout data helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * System config XML paths
     */
    const XML_PATH_ENABLED = 'betaout/analytics/betaout_enabled';
    const XML_PATH_APIKEY = 'betaout/analytics/betaout_apikey';
    const XML_PATH_PROJECT_ID ='betaout/analytics/betaout_projectid';


    /**
     * Check if Betaout is enabled
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isTrackingEnabled($store = null)
    {
        $apiKey = $this->getApiKey($store);
        $projectId = $this->getProjectId($store);
        return $apiKey && $projectId && $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Betaout APIKEY
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getApiKey($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_APIKEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Piwik base URL
     *
     * @param null|string|bool|int|Store $store
     * @param null|bool $secure
     * @return string
     */
    public function getBaseUrl($store = null, $secure = null)
    {
        if (is_null($secure)) {
            $secure = $this->_request->isSecure();
        }
        $host ="api.betaout.com/v2";
        return ($secure ? 'https://' : 'https://') . $host . '/';
    }

    /**
     * Retrieve Betaout project ID
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getProjectId($store = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_PROJECT_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getMediaBaseUrl() {
       $om = \Magento\Framework\App\ObjectManager::getInstance();
       $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
       $currentStore = $storeManager->getStore();
       return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."catalog/product";
    }
    
    public function getCustomerIdentity() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
       
        $user=array();
        if($customerSession->isLoggedIn()) {
            $customerData=$customerSession->getCustomerData();
            $user['email']=$customerData->getEmail();
            $user['customer_id']=$customerData->getId();

        }else{
            $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
            $user=  json_decode(base64_decode($cookieManager->getCookie('_ampUSER')),true);
        }
        return $user;
     }
     public function getToken() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
        return $cookieManager->getCookie('_ampUITN');
       
     }
     public function setCookies($data){
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
          $cookieManager->setCookie('_ampUSER',  base64_encode($data));
         
     }
}
