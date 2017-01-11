<?php
namespace Betaout\Analytics\Controller\Export;
use Magento\Framework\App\Action\Context;
class Order extends \Magento\Framework\App\Action\Action {
  protected $_orderCollectionFactory;
  protected $_storeManager;
  protected $_categoryFactory;
  
public function __construct(Context $context,
         \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Magento\Catalog\Model\CategoryFactory $categoryFactory,
         array $data = []
        ) {
       $this->_orderCollectionFactory =$orderCollectionFactory;
       $this->_storeManager = $storeManager;
       $this->_categoryFactory = $categoryFactory;
       parent::__construct($context);
    }
public function sendData($data) {
        $key=isset($_GET['apiKey'])?$_GET['apiKey']:"";
        $projectId=isset($_GET['projectId'])?$_GET['projectId']:"";
       if($key!="" && $projectId!=""){
        $url="https://api.betaout.com/v2/bulk/orders/";
        $sdata['apikey']=$key;
        $sdata['project_id']=$projectId;
        $sdata['orders']=$data;
        $jdata = json_encode($sdata);
        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 10000);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jdata);
        $result = curl_exec($curl);
        return $response = json_decode($result);
        curl_close($curl);
        }else{
            return array("error"=>"Api key and ProjectId required","responseCode"=>500);
        }
    }

   public function execute()
    {
    try{
    $status=isset($_GET['status'])?$_GET['status']:"complete";
    $cpage=isset($_GET['pageNo'])?$_GET['pageNo']:1;
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 50;
    $totalCount = isset($_GET['totalCount']) ? $_GET['totalCount'] : 0;
    if($totalCount){
     $orders =$this->_orderCollectionFactory->create()
         ->addFieldToSelect('*')
         ->addFieldToFilter('status',$status);
      $count=$orders->Count();
      $result=array("total"=>$count,"cpage"=>0,"responseCode"=>200);  
     echo json_encode($result);
    }else{
    $orders =$this->_orderCollectionFactory->create()
         ->addFieldToSelect('*')
         ->addFieldToFilter('status',$status) 
         ->setPageSize($limit);
     $lpages = $orders->getLastPageNumber();
     $orders->setCurPage($cpage);
 $count=$orders->Count();
 $ordata=array();
 $j=0;
  if($count){
       foreach ($orders as $order)  {
          
                $orderId = $order->getId();
                $order_id = $order->getIncrementId();
                $email=  $order->getData('customer_email');
                $data=array();
                $data['email']=$email;
                $customer = $order->getShippingAddress();
                 if (is_object($customer)) {
                 $data['email']=$customer->getEmail();
                 $data['phone'] = $customer->getTelephone();
                 $data['customer_id'] = $customer->getCustomerId();
                 }
                $data= array_filter($data);
                $items = $order->getAllVisibleItems();
                $itemcount = count($items);
                $i = 0;
                $actionData = array();
                foreach ($order->getAllVisibleItems() as $item) {
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

                    $actionData[$i]['id'] = $product->getId();
                    $actionData[$i]['name'] = $product->getName();
                    $actionData[$i]['sku'] = $product->getSku();
                    $actionData[$i]['price'] = $product->getPrice();
                    $actionData[$i]['currency'] =$this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                    $actionData[$i]['image_url'] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."catalog/product".$product->getImage();
                    $actionData[$i]['product_url'] = $product->getProductUrl();
                    $actionData[$i]['brandname'] = $product->getResource()->getAttribute('manufacturer') ? $product->getAttributeText('manufacturer') : false;
                    $actionData[$i]['quantity'] = (int) $item->getQtyOrdered();
                    $actionData[$i]['categories'] = $cateHolder;
                   
                    $i++;
                }

              
                $TotalPrice = $order->getGrandTotal();
                $totalShippingPrice = $order->getShippingInclTax();
             
                $subTotalPrice = $order->getSubtotal();
                $orderInfo["revenue"] = $subTotalPrice - abs($order->getDiscountAmount());
                $orderInfo["total_price"] = $TotalPrice;
                $orderInfo["shipping_price"] = $totalShippingPrice;
                $orderInfo['order_id'] = $order->getIncrementId();
                $orderInfo['promo_code'] = $order->getCouponCode();
                $orderInfo['discount'] = abs($order->getDiscountAmount());
                $orderInfo['currency'] = $order->getOrderCurrencyCode();
                $orderInfo['order_status'] = $status;
                $orderInfo['taxes'] = $order->getShippingTaxAmount();
                $orderInfo['payment_method']= $order->getPayment()->getMethodInstance()->getTitle();;  
                $orderInfo['products']=$actionData; 
                $orderInfo['created_time']=strtotime($order->getData('created_at'));
                
                $actionDescription = array(
                    'identifiers' => $data,
                    'properties' => $orderInfo
                  );
              
          $ordata[$j]=$actionDescription;
          $j++;
        }
        $send = isset($_GET['send']) ? $_GET['send'] : "1";
        if($send){
         self::sendData($ordata);
         $result=array("lastPage"=>$lpages,"cpage"=>$cpage,"responseCode"=>200);
        }else{
         $result=array("lastPage"=>$lpages,"cpage"=>$cpage,"responseCode"=>200,'data'=>$ordata);  
        }
        echo json_encode($result);
      }else{
          $result=array("lastPage"=>0,"cpage"=>0,"responseCode"=>400,'data'=>"No Data found");  
          echo json_encode($result);
      }
    }
        
   }catch(Exception $e){
       echo json_encode(array("error"=>$e->getMessage()));
   }
       
    }

}
