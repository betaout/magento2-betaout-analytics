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
 * along with Betaout_Analytics.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Betaout\Analytics\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `catalog_controller_product_view'
 *
 */
class ProductViewObserver implements ObserverInterface
{

    /**
     * Betaout tracker instance
     *
     * @var \Betaout\Analytics\Model\Tracker
     */
    protected $_betaoutTracker;

    /**
     * Betaout data helper
     *
     * @var \Betaout\Analytics\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Betaout\Analytics\Model\Tracker $piwikTracker
     * @param \Betaout\Analytics\Helper\Data $dataHelper
     */

    protected $_storeManager;
    
    public function __construct(
       \Betaout\Analytics\Model\Tracker $betaoutTracker,   
       \Betaout\Analytics\Helper\Data $dataHelper,
       \Magento\Store\Model\StoreManagerInterface $storeManager   
    ) {
        $this->_betaoutTracker = $betaoutTracker;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Push EcommerceView to tracker on product view page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Betaout\Analytics\Observer\ProductViewObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
           
        $product = $observer->getEvent()->getProduct();
        /* @var $product \Magento\Catalog\Model\Product */
        $actionData = array();
        $actionData[0]['id'] = $product->getId();
        $actionData[0]['name'] = $product->getName();
        $actionData[0]['sku'] = $product->getSku();
        $actionData[0]['price'] = $product->getFinalPrice();
        $actionData[0]['currency'] = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $actionData[0]['image_url'] =$this->_dataHelper->getMediaBaseUrl().$product->getImage();
        $actionData[0]['product_url'] =$product->getProductUrl(); 
        $userdata=$this->_dataHelper->getCustomerIdentity();
        if($actionData[0]['price']==0){
            $actionData[0]['price']=0.001;
        }
        $actionDescription = array(
            'activity_type' => 'view',
            "identifiers" =>$userdata,
            'products' => $actionData
        );
        $this->_betaoutTracker->customer_action($actionDescription);
       
        }catch(Exception $ex){
            
        }

    }
   
}
