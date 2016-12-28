<?php
namespace Betaout\Analytics\Controller\Export;
use Magento\Framework\App\Action\Context;
class Product extends \Magento\Framework\App\Action\Action {
  protected $_productCollectionFactory;
  protected $_storeManager;
  protected $_categoryFactory;
  
public function __construct(Context $context,
         \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Magento\Catalog\Model\CategoryFactory $categoryFactory,
         array $data = []
        ) {
       $this->_productCollectionFactory =$productCollectionFactory;
       $this->_storeManager = $storeManager;
       $this->_categoryFactory = $categoryFactory;
       parent::__construct($context);
    }

public function sendData($data){
        $key=isset($_GET['apiKey'])?$_GET['apiKey']:"";
        $projectId=isset($_GET['projectId'])?$_GET['projectId']:"";
        if($key!="" && $projectId!=""){
        $url="https://api.betaout.com/v2/bulk/products";
        $sdata['apikey']=$key;
        $sdata['project_id']=$projectId;
        $sdata['products']=$data;
        $jdata = json_encode($data);
        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 10000);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jdata);
        $result     = curl_exec($curl);
        $response   = json_decode($result);
        curl_close($curl);
        }else{
            return array("error"=>"Api key and ProjectId required","responseCode"=>500);
        }
   }
   public function execute() {
        try {
            $limit = isset($_GET['limit']) ? $_GET['limit'] : "5";
            $cpage = isset($_GET['pageNo']) ? $_GET['pageNo'] : 1;
            $products =$this->_productCollectionFactory->getCollection()
                    ->addAttributeToSelect('*') // select all attributes
                    ->setPageSize($limit); // limit number of results returned
           $lpages = $products->getLastPageNumber();
           $products->setCurPage($cpage); // set the offset (useful for pagination)
         
            $productData = array();
            $i = 0;
// we iterate through the list of products to get attribute values
            foreach ($products as $product) {
                $productData[$i]['name'] = $product->getName(); //get name
                $productData[$i]['price'] = (float) $product->getPrice(); //get price as cast to float
                $productData[$i]['id'] = $product->getId();
                $productData[$i]['sku'] = $product->getSku();
                $productData[$i]['currency'] = Mage::app()->getStore()->getBaseCurrencyCode();
                $productData[$i]['image_url'] = $product->getImageUrl();
                $productData[$i]['product_url'] = $product->getProductUrl();
                $productData[$i]['brandname'] = $product->getResource()->getAttribute('manufacturer') ? $product->getAttributeText('manufacturer') : false;
                $categories = array();
                $categoryIds = $product->getCategoryIds();
                // getCategoryIds(); returns an array of category IDs associated with the product
                foreach ($categoryIds as $category_id) {
                    $cateName = Mage::getModel('catalog/category')->load($category_id['entity_id']);
                    $name = $cateName->getName();
                    $id = $cateName->getEntityId();
                    $pid = $cateName->getParent_id();
                    if ($pid == 1) {
                        $pid = 0;
                    }
                    $categories[] = array("cat_id" => $id, "cat_name" => $name, "parent_cat_id" => $pid);
                }
                $productData[$i]['categories'] = $categories;
                $i++;
            }
           
       $send = isset($_GET['send']) ? $_GET['send'] : "1";
        if($send){
         self::sendData($productData);
          $result=array("lastPage"=>$lpages,"cpage"=>$cpage,"responseCode"=>200);
        }else{
         $result=array("lastPage"=>$lpages,"cpage"=>$cpage,"responseCode"=>200,'data'=>$productData);  
        }
//       self::sendData($productData);
//       $result=array("lastPage"=>$lpages,"cpage"=>$cpage,"responseCode"=>200);
        } catch (Exception $ex) {
            
        }
    }

}
