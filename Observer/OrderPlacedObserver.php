<?php
namespace Betaout\Analytics\Observer;
use Magento\Framework\Event\ObserverInterface;
class OrderPlacedObserver implements ObserverInterface
{

    protected $_betaoutTracker;

    protected $_dataHelper;
    protected $_orderCollectionFactory;
    protected $_storeManager;
    protected $_categoryFactory;
    protected $_productRepository;
    protected $_orderFacory;
    protected $_orderObject;
    public function __construct(
        \Betaout\Analytics\Model\Tracker $betaoutTracker,
        \Betaout\Analytics\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order $orderFacory,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->_betaoutTracker = $betaoutTracker;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_productRepository=$productRepository;
        $this->_orderFacory=$orderFacory;
        $this->_orderObject=$order;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
      
      try{
       $order = $observer->getEvent()->getOrder();   
       $orderId=$order->getIncrementId();
      
        $betaoutItems = [];
        $betaoutOrder = [];
        $i=0;
        $data=array();
           $billingAddress=$order->getBillingAddress();
            
            if (is_object($billingAddress)) {
                    $data['email']=$billingAddress->getEmail();
                    $data['phone'] = $billingAddress->getTelephone();
                    $data['customer_id'] = $order->getCustomerId();
                    $person['firstname'] = $billingAddress->getFirstname();
                    $person['lastname'] = $billingAddress->getLastname();
                    $person['postcode'] = $billingAddress->getPostcode();
                    $person['city'] = $billingAddress->getCity();
                    $person['dob'] = $order->getCustomerDob();
                    $person['street'] = $billingAddress->getStreetFull();
                    $person['ip'] = $order->getRemoteIp();
                    $person['gender']=$order->getCustomerGender();
                    try {
                     $data=  array_filter($data);
                     $result=$this->_betaoutTracker->identify($data);
                      } catch (Exception $ex) {
                        
                    }
                  $person = array_filter($person);
                  $properties['update']=$person;
                  $this->_betaoutTracker->userProperties($data, $properties);
            }
            /* @var $order \Magento\Sales\Model\Order */
            foreach ($order->getAllVisibleItems() as $item) {
               $product=$item->getProduct();
               if($product->getTypeId()!="configurable" && $product->getTypeId()!="grouped" && $product->getTypeId()!="bundle"){
               $newProduct=self::loadMyProduct($product->getSku());
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
                $id   = $item->getProductId();
                $pprice=$newProduct->getFinalPrice();
                if($pprice==0){
                 $pprice=(float) $item->getBasePriceInclTax();
                }
                
                $sku   = $newProduct->getSku();
                $name  = $item->getName();
                $qty   = (float) $item->getQtyOrdered();
                
                $betaoutItems[$i]["id"] = $id;
                $betaoutItems[$i]["sku"] = $sku;
                $betaoutItems[$i]["name"]=$name;
                $betaoutItems[$i]["price"]=$pprice;
                $betaoutItems[$i]["quantity"]=$qty;
                $betaoutItems[$i]["productType"]=$product->getTypeId();
                $betaoutItems[$i]["newproductType"]=$newProduct->getTypeId();
                $betaoutItems[$i]['image_url'] = $this->_dataHelper->getMediaBaseUrl().$product->getImage();
                $betaoutItems[$i]['categories'] = $cateHolder;
                $betaoutItems[$i]['currency'] = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                $i++;
               }
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
                'activity_type' => 'order_placed',
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
