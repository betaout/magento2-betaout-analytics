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
class OrderCancleObserver implements ObserverInterface
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
    protected $_productRepository;
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
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository        
    ) {
        $this->_betaoutTracker = $betaoutTracker;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_productRepository=$productRepository;
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
        $order = $observer->getEvent()->getOrder();   
        $orderId=$order->getIncrementId();
        if (empty($orderId)) {
           return $this;
        }
      
        //$collection = $this->_orderCollectionFactory->create();
        //$collection->addFieldToFilter('entity_id', ['in' => $orderId]);
        $betaoutItems = [];
        $betaoutOrder = [];
        $i=0;
        $data=array();
        $billingAddress=$order->getShippingAddress();
         
         if (is_object($billingAddress)) {
                    $data['email']=$billingAddress->getEmail();
                    $data['phone'] = $billingAddress->getTelephone();
                    $data['customer_id'] = $order->getCustomerId();
                    $person['firstname'] = $billingAddress->getFirstname();
                    $person['lastname'] = $billingAddress->getLastname();
                    $person['postcode'] = $billingAddress->getPostcode();
                    $person['city'] = $billingAddress->getCity();
                    $person['fax'] = $billingAddress->getfax();
                    $person['company'] = $billingAddress->getCompany();
                    $person['street'] = $billingAddress->getStreetFull();
                    try {
                     $data=  array_filter($data);
                     $result=$this->_betaoutTracker->identify($data);
                      } catch (Exception $ex) {
                      }
                   $person = array_filter($person);
                   $properties['update']=$person;
                 $result = $this->_betaoutTracker->userProperties($data, $properties);
             }
            foreach ($order->getAllItems() as $item) {
               $product=$item->getProduct();
               $newProduct=self::loadMyProduct($product->getSku());
               if($newProduct->getTypeId()=="configurable" || $newProduct->getTypeId()=="grouped" || $newProduct->getTypeId()=="bundle"){
                continue;
               }
               $productName = $newProduct->getName();
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
                $pprice=$newProduct->getFinalPrice();
                if($pprice==0){
                 $pprice=(float) $item->getBasePriceInclTax();
                }
                $id   = $item->getProductId();
                $gid=$id;
                $gname=$product->getName();
                if($id==$newProduct->getId()){
                    $gid=0;
                    $gname="";
                }
                $sku   = $item->getSku();
                $name  = $item->getName();
                $qty   = (float) $item->getQtyOrdered();
                
                $betaoutItems[$i]["id"] = $newProduct->getId();
                $betaoutItems[$i]["sku"] = $sku;
                $betaoutItems[$i]["name"]=$newProduct->getName();
                $betaoutItems[$i]["price"]=$pprice;
                $betaoutItems[$i]["quantity"]=$qty;
                $betaoutItems[$i]['image_url'] = $this->_dataHelper->getMediaBaseUrl().$newProduct->getImage();
                $betaoutItems[$i]['categories'] = $cateHolder;
                $betaoutItems[$i]['currency'] = $this->_storeManager->getStore()->getCurrentCurrencyCode();
               
                $i++;
             
             }

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
    
        if (!empty($betaoutOrder)) {
            $actionDescription = array(
                'activity_type' => 'order_refunded',
                "identifiers" =>$data,
                'products' => $betaoutItems,
                'order_info' => $betaoutOrder,
            );
       
         $result=$this->_betaoutTracker->customer_action($actionDescription);
        }
        
        return $this;
     
    }catch(Exception $e){
        
    }
}
 public function getProductById($id)
{
    return $this->_productRepository->getById($id);
}
public function loadMyProduct($sku)
{ 
    return $this->_productRepository->get($sku);
}
}
