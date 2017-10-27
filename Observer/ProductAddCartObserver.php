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
 * along with Betaout.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Betaout\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `catalog_controller_product_view'
 *
 */
class ProductAddCartObserver implements ObserverInterface
{

    /**
     * Betaout tracker instance
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
    protected $_categoryFactory;
    protected $_productRepository;
    protected $_request;
    public function __construct(
        \Betaout\Analytics\Model\Tracker $betaoutTracker,
        \Betaout\Analytics\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory, 
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_betaoutTracker = $betaoutTracker;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_categoryFactory = $categoryFactory;
        $this->_productRepository=$productRepository;
        $this->_request=$request;
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
         $product = $observer->getEvent()->getProduct();
         $params=$this->_request->getParams();
         $actionData=array();
         $attrArray=array();
         $subprice=0;
       // getQuoteProductIds();
         if($product->getTypeID()=='grouped'){
                    $products = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    $i=0;
                    $pGid=0;
                    $pGname=$product->getName();
                    foreach($products as $p){
                        $productGrouped= self::getProductById($p->getId());
                        $productId = $p->getId(); 
                        $productName = $p->getName();
                        $qty=$params['super_group'][$productId];
                        if($qty==0){
                            continue;
                        }
                        $attributes = $productGrouped->getAttributes();
                        foreach ($attributes as $attribute) { 
                           if($attribute->getIsUserDefined()){
                            $attributeLabel = $attribute->getFrontendLabel();
                            $value = $attribute->getFrontend()->getValue($product);
                             if (!$product->hasData($attribute->getAttributeCode())) {
                                $value ="";
                            } elseif ((string)$value == '') {
                                $value = "";
                            } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                                $value =$value;
                            } elseif ($value instanceof Phrase) {
                                $value = $value->getText();
                            }
                             if (is_string($value) && strlen($value)) {
                              $attrArray[$attribute->getAttributeCode()]=$value;   
                            }
                          
                            
                           }

                        }
                       $catCollection = $p->getCategoryCollection();
                       $categs = $catCollection->exportToArray();
                       $cateHolder = array();
                       foreach ($categs as $cat) {
                            $cateName = $this->_categoryFactory->create()->load($cat['entity_id']);
                            $name =$cateName->getName();
                            $id = $cateName->getEntityId();
                            $pid = $cateName->getParent_id();
                            if ($pid == 1) {
                                $pid = 0;
                            }
                            if(!empty($name)){
                             $cateHolder[] = array("cat_id"=>$id,"cat_name" => $name, "parent_cat_id" => $pid);
                            }
                        }

                        $actionData[$i]['product_group_id']=$pGid;
                        $actionData[$i]['product_group_name']=$pGname;
                        $actionData[$i]['id'] =$productId;
                        $actionData[$i]['name'] =$productName;
                        $actionData[$i]['sku'] = $p->getSku();
                        $actionData[$i]['price'] = $p->getFinalPrice();
                        $actionData[$i]['currency'] =$this->_storeManager->getStore()->getCurrentCurrencyCode();
                        $actionData[$i]['image_url'] =$this->_dataHelper->getMediaBaseUrl().$p->getImage();
                        $actionData[$i]['product_url'] = $p->getProductUrl();
                        $actionData[$i]['quantity'] =$qty;
                        $actionData[$i]['categories'] = $cateHolder;
                        $actionData[$i]['specs']=$attrArray;
                        $i++;
                        $subprice = $subprice+($qty*$p->getFinalPrice());
                    }
            
        }else if($product->getTypeID() == 'configurable'){
                 $pGid=$product->getId();
                 $pGname=$product->getName();
                 $assProduct= self::loadMyProduct($product->getSku());
                 $productId = $assProduct->getId();
                 $productName = $assProduct->getName();
                 $pattributes = $product->getAttributes();
                   foreach ($pattributes as $attribute) { 
                       if($attribute->getIsUserDefined()){
                        $acode=$attribute->getAttributeCode();
                        $attributeLabel = $attribute->getFrontendLabel();
                        $value = $attribute->getFrontend()->getValue($product);
                        if (!$product->hasData($attribute->getAttributeCode())) {
                                $value ="";
                            } elseif ((string)$value == '') {
                                $value = "";
                            } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                                $value =$value;
                            } elseif ($value instanceof Phrase) {
                                $value = $value->getText();
                            }
                             if (is_string($value) && strlen($value)) {
                              $attrArray[$attribute->getAttributeCode()]=$value;   
                            }
                       }

                    }
                 
                 $attributes = $assProduct->getAttributes();
                   foreach ($attributes as $attribute) { 
                       if($attribute->getIsUserDefined()){
                        $acode=$attribute->getAttributeCode();
                        $attributeLabel = $attribute->getFrontendLabel();
                        $value = $attribute->getFrontend()->getValue($assProduct);
                        if (!$assProduct->hasData($attribute->getAttributeCode())) {
                                $value ="";
                            } elseif ((string)$value == '') {
                                $value = "";
                            } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                                $value =$value;
                            } elseif ($value instanceof Phrase) {
                                $value = $value->getText();
                            }
                             if (is_string($value) && strlen($value)) {
                              $attrArray[$attribute->getAttributeCode()]=$value;   
                            }
                       }

                    }
              
                  $catCollection = $product->getCategoryCollection();
                  $categs = $catCollection->exportToArray();
                  $cateHolder = array();
                    foreach ($categs as $cat) {
                        $cateName =$this->_categoryFactory->create()->load($cat['entity_id']);
                        $name =$cateName->getName();
                        $id = $cateName->getEntityId();
                        $pid = $cateName->getParent_id();
                        if ($pid == 1) {
                            $pid = 0;
                        }
                        if(!empty($name)){
                         $cateHolder[] = array("cat_id"=>$id,"cat_name" => $name, "parent_cat_id" => $pid);
                        }
                    }
               
                $actionData[0]['product_group_id']=$pGid;
                $actionData[0]['product_group_name']=$pGname;
                $actionData[0]['id'] =$productId;
                $actionData[0]['name'] =$productName;
                $actionData[0]['sku'] = $product->getSku();
                $actionData[0]['price'] = $product->getFinalPrice();
                $actionData[0]['currency'] =$this->_storeManager->getStore()->getCurrentCurrencyCode();
                $actionData[0]['image_url'] = $this->_dataHelper->getMediaBaseUrl().$product->getImage();
                $actionData[0]['product_url'] = $product->getProductUrl();
                $actionData[0]['quantity'] = (int) $product->getQty();
                $actionData[0]['categories'] = $cateHolder;
                $actionData[0]['specs']=$attrArray;
                $subprice = (float) $product->getQty() * $product->getFinalPrice();
        }else if($product->getTypeID() == 'bundle'){
           $mproduct=$product;
           $options = $product->getTypeInstance(true)->getOrderOptions($product);
           
           $optionIds = array_keys($options['info_buyRequest']['bundle_option']);
           $productName = $product->getName();
           $assproduct=array();
           $collection = $product->getTypeInstance(true)->getSelectionsCollection($optionIds, $product);
           $selection_map = array();
           foreach ($collection as $item) {
                $idata=$item->getData();
                if(!isset($selection_map[$idata['option_id']])) {
                   $selection_map[$idata['option_id']] = array();
                 }
                $selection_map[$idata['option_id']][$idata['selection_id']] = $idata;  
             } 
             //mail("rohit@getamplify.com","Bundle product selection map", json_encode($selection_map));
            foreach($options['info_buyRequest']['bundle_option'] as $op => $sel) {
            
              if(is_array($sel)){
                foreach($sel as $km){
                $assproduct[] = $selection_map[$op][$km]['product_id'];
               }   
              }else{
                $assproduct[] = $selection_map[$op][$sel]['product_id'];
               }
              
            }
           
            $pGid=0;
            $pGname=$productName;
            $i=0;
           if(count($assproduct)){
            foreach($assproduct as $pdata){
            $product= self::getProductById($pdata);
            $attributes = $product->getAttributes();
              foreach ($attributes as $attribute) { 
                  if($attribute->getIsUserDefined()){
                   $acode=$attribute->getAttributeCode();
                  
                   $attributeLabel = $attribute->getFrontendLabel();
                   $value = $attribute->getFrontend()->getValue($product);
                   if (!$product->hasData($attribute->getAttributeCode())) {
                                $value ="";
                        } elseif ((string)$value == '') {
                            $value = "";
                        } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                            $value =$value;
                        } elseif ($value instanceof Phrase) {
                            $value = $value->getText();
                        }

                        if (is_string($value) && strlen($value)) {
                          $attrArray[$attribute->getAttributeCode()]=$value;   
                        }
                   
                  }

               }
                $catCollection = $product->getCategoryCollection();
                $categs = $catCollection->exportToArray();
                $cateHolder = array();
                  foreach ($categs as $cat) {
                        $category =$this->_categoryFactory->create()->load($cat['entity_id']);
                        $name =$category->getName();
                        $id = $category->getEntityId();
                        $pid = $category->getParent_id();
                        if ($pid == 1) {
                            $pid = 0;
                        }
                        if(!empty($name)){
                         $cateHolder[] = array("cat_id"=>$id,"cat_name" => $name, "parent_cat_id" => $pid);
                        }
            
                    }
                $actionData[$i]['product_group_name']=$pGname;
                $actionData[$i]['id'] = $product->getId();
                $actionData[$i]['name'] = $product->getName();
                $actionData[$i]['sku'] =  $product->getSku();
                $actionData[$i]['price'] = $product->getFinalPrice();
                $actionData[$i]['currency'] = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                $actionData[$i]['image_url'] = $this->_dataHelper->getMediaBaseUrl().$product->getImage();
                $actionData[$i]['product_url'] = $product->getProductUrl(); 
                if($mproduct->getQty()){
                  $actionData[$i]['quantity']= (int) $mproduct->getQty();  
                }else{
                 $actionData[$i]['quantity']=1;   
                }
                $actionData[$i]['categories'] = $cateHolder;
                $i++;
              }
            }else{
                
            }
              $subprice = (float) $mproduct->getQty() * $mproduct->getFinalPrice();
        }else{
            $newproduct= self::loadMyProduct($product->getSku());
            $productName = $product->getName();
            $pGid=0;
            $pGname="";
            $attrArray['purchaseType']=$product->getTypeID();
            $attributes = $product->getAttributes();
              foreach ($attributes as $attribute) { 
                  if($attribute->getIsUserDefined()){
                   $acode=$attribute->getAttributeCode();
                  
                   $attributeLabel = $attribute->getFrontendLabel();
                   $value = $attribute->getFrontend()->getValue($product);
                   if (!$product->hasData($attribute->getAttributeCode())) {
                                $value ="";
                        } elseif ((string)$value == '') {
                            $value = "";
                        } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                            $value =$value;
                        } elseif ($value instanceof Phrase) {
                            $value = $value->getText();
                        }

                        if (is_string($value) && strlen($value)) {
                          $attrArray[$attribute->getAttributeCode()]=$value;   
                        }
                   
                  }

               }
                $catCollection = $product->getCategoryCollection();
                $categs = $catCollection->exportToArray();
                $cateHolder = array();
                  foreach ($categs as $cat) {
                        $category =$this->_categoryFactory->create()->load($cat['entity_id']);
                        $name =$category->getName();
                        $id = $category->getEntityId();
                        $pid = $category->getParent_id();
                        if ($pid == 1) {
                            $pid = 0;
                        }
                        if(!empty($name)){
                         $cateHolder[] = array("cat_id"=>$id,"cat_name" => $name, "parent_cat_id" => $pid);
                        }
            
                    }
                $actionData[0]['id'] = $newproduct->getId();
                $actionData[0]['name'] = $newproduct->getName();
                $actionData[0]['sku'] = $newproduct->getSku();
                $actionData[0]['price'] = $product->getFinalPrice();
                $actionData[0]['currency'] = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                $actionData[0]['image_url'] = $this->_dataHelper->getMediaBaseUrl().$product->getImage();
                $actionData[0]['product_url'] = $product->getProductUrl(); 
                $actionData[0]['new_product_type']=$newproduct->getTypeID();
                if($product->getQty()){
                  $actionData[0]['quantity']= (int) $product->getQty();  
                }else{
                 $actionData[0]['quantity']=1;   
                }
                $actionData[0]['categories'] = $cateHolder;
                $subprice = (float) $product->getQty() * $product->getFinalPrice();
            }
            
            
            $userdata=$this->_dataHelper->getCustomerIdentity();
            $cartInfo=array();
            $quote = $this->_checkoutSession->getQuote();
            $cartInfo['total']=(float) $quote->getBaseGrandTotal()+$subprice;
            $cartInfo['revenue']=(float) $quote->getBaseGrandTotal()+$subprice;
            $cartInfo['currency']= $this->_storeManager->getStore()->getCurrentCurrencyCode();
            $actionDescription = array(
                "activity_type" => 'add_to_cart',
                "identifiers" =>$userdata,
                "products" => $actionData,
                "cart_info"=>$cartInfo
            );
        
          $this->_betaoutTracker->customer_action($actionDescription);
        }catch(Exception $e){
           echo $e->getMessage();
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
