<?php
/**
 * Copyright 2016 Betaout Analytics
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
 * along with Henhed_Piwik.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Betaout\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `catalog_controller_product_view'
 *
 */
class ProductRemoveCartObserver implements ObserverInterface
{

    /**
     * Piwik tracker instance
     *
     * @var \Betaout\Analytics\Model\Tracker
     */
    protected $_betaoutTracker;

    /**
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Betaout\Analytics\Model\Tracker $piwikTracker
     * @param \Betaout\Analytics\Helper\Data $dataHelper
     */

    protected $_storeManager;
    protected $_checkoutSession;
    public function __construct(
        \Betaout\Analytics\Model\Tracker $betaoutTracker,
        \Betaout\Analytics\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_betaoutTracker = $betaoutTracker;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Push EcommerceView to tracker on product view page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\ProductViewObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    try{
        mail("rohit@getamplify.com", "magento 20", "remove_from_cart");
        $product = $observer->getEvent()->getQuote_item();
        $actionData = array();
        $cartInfo=array();
        $actionData[0]['id'] = $product->getId();
        $actionData[0]['name'] = $product->getName();
        $actionData[0]['sku'] = $product->getSku();
        $actionData[0]['price'] = $product->getFinalPrice();
        $actionData[0]['currency'] = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $actionData[0]['image_url'] = $this->_dataHelper->getMediaBaseUrl().$product->getImage();
        $actionData[0]['product_url'] = $product->getProductUrl(); 
        $actionData[0]['quantity']=$product->getQty();
        $subprice = (float) $product->getQty() * $product->getFinalPrice();
        
        $userdata=$this->_dataHelper->getCustomerIdentity();
        $quote = $this->_checkoutSession->getQuote();
        $cartInfo['total']=(float) $quote->getBaseGrandTotal()- $subprice;
        $cartInfo['revenue']=(float) $quote->getBaseGrandTotal()- $subprice;
        $cartInfo['currency']= $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $actionDescription = array(
                "activity_type" => 'remove_from_cart',
                "identifiers" =>$userdata,
                "products" => $actionData,
                "cart_info"=>$cartInfo
                );
       mail("rohit@getamplify.com", "magento 20 remove_cart",  json_encode($actionDescription));
        $this->_betaoutTracker->customer_action($actionDescription);
        }catch(Exception $ex){
        mail("rohit@getamplify.com", "magento 20 remove_cart_error",  "remove_cart_error");   
        }
    }
}
