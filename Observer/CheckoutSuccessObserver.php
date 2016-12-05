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
 * Observer for `controller_action_predispatch_checkout_cart_index'
 *
 * @link https://app.betaout.com/http-api.html
 */
class CheckoutSuccessObserver implements ObserverInterface
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
     * Sales order collection factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $_orderCollectionFactory
     */
    protected $_orderCollectionFactory;
    protected $_storeManager;
    protected $_categoryFactory;
    /**
     * Constructor
     *
     * @param \Betaout\Analytics\Model\Tracker $piwikTracker
     * @param \Betaout\Analytics\Helper\Data $dataHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Betaout\Analytics\Model\Tracker $betaoutTracker,
        \Betaout\Analytics\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory 
    ) {
        $this->_betaoutTracker = $betaoutTracker;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
    }

    /**
     * Push trackEcommerceOrder to tracker on checkout success page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\CheckoutSuccessObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      
        try{
        $orderIds = $observer->getEvent()->getOrderIds();
        
        if (empty($orderIds) || !is_array($orderIds)
        ) {
            return $this;
        }

        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        // Group order items by SKU since Piwik doesn't seem to handle multiple
        // `addEcommerceItem' with the same SKU.
        $betaoutItems = [];
        // Aggregate all placed orders into one since Piwik seems to only
        // register one `trackEcommerceOrder' per request. (For multishipping)
        $betaoutOrder = [];
        $i=0;
        foreach ($collection as $order) {
            $billingAddress=$order->getBillingAddress();
            /* @var $order \Magento\Sales\Model\Order */
            foreach ($order->getAllVisibleItems() as $item) {
                /* @var $item \Magento\Sales\Model\Order\Item */
               $product=$item->getProduct();
               $catCollection = $product->getCategoryCollection();
               $categs = $catCollection->exportToArray();

                $cateHolder = array();
                foreach ($categs as $cat) {
                  $category = $this->_categoryFactory->create()->load($cat['entity_id']);
                  $name =$category->getName();
                  $id = $category->getEntityId();
                   $pid = $category->getParentId();
                  if ($pid == 1) {
                      $pid = 0;
                   }
                   if(!empty($name)){
                     $cateHolder[] = array("cat_id"=>$id,"cat_name" => $name, "parent_cat_id" => $pid);
                   }
                }
               $sku   = $item->getSku();
                $id   = $item->getId();
                $name  = $item->getName();
                $price = (float) $item->getBasePriceInclTax();
                $qty   = (float) $item->getQtyOrdered();
                $betaoutItems[$i]["id"] = $id;
                $betaoutItems[$i]["sku"] = $sku;
                $betaoutItems[$i]["name"]=$name;
                $betaoutItems[$i]["price"]=$price;
                $betaoutItems[$i]["quantity"]=$qty;
                $betaoutItems[$i]['image_url'] = $this->_dataHelper->getMediaBaseUrl().$product->getImage();
                $betaoutItems[$i]['categories'] = $cateHolder;
                $i++;
             }

            $orderId    = $order->getIncrementId();
            $grandTotal = (float) $order->getBaseGrandTotal();
            $subTotal   = (float) $order->getBaseSubtotalInclTax();
            $tax        = (float) $order->getBaseTaxAmount();
            $shipping   = (float) $order->getBaseShippingInclTax();
            $discount   = abs((float) $order->getBaseDiscountAmount());

            $betaoutOrder["order_id"]=$orderId;
            $betaoutOrder["total"]= $grandTotal;
            $betaoutOrder["revenue"] = $subTotal;
            $betaoutOrder["tax"] = $tax;
            $betaoutOrder["shipping"] = $shipping;
            $betaoutOrder["discount"] = $discount;
            $betaoutOrder['currency']= $this->_storeManager->getStore()->getCurrentCurrencyCode();
        }

        
        // Push `trackEcommerceOrder'
        if (!empty($betaoutOrder)) {

           $userdata=$this->_dataHelper->getCustomerIdentity();
            $actionDescription = array(
                'activity_type' => 'purchase',
                "identifiers" =>$userdata,
                'products' => $betaoutItems,
                'order_info' => $betaoutOrder,
            );
           mail("rohit@getamplify.com", "magento 20 purchase data",  json_encode($actionDescription) );
           $result=$this->_betaoutTracker->customer_action($actionDescription);
        }

        return $this;
    
    }catch(Exception $e){
        
    }
}
}
