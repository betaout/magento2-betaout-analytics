<?php

namespace Betaout\Analytics\Controller\Export;

use Magento\Framework\App\Action\Context;

class Customer extends \Magento\Framework\App\Action\Action {

    protected $_customerCollectionFactory;

    public function __construct(Context $context,
            \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    ) {

        $this->_customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context);
    }

    public function getCustomerimport() {
;
    }

    public function sendData($data) {
        $key=isset($_GET['apiKey'])?$_GET['apiKey']:"";
        $projectId=isset($_GET['projectId'])?$_GET['projectId']:"";
        if($key!="" && $projectId!=""){
        $url="https://api.betaout.com/v2/bulk/users/";
        $sdata['apikey']=$key;
        $sdata['project_id']=$projectId;
        $sdata['useragent'] = $_SERVER['HTTP_USER_AGENT'];
        $sdata['users']=$data;
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

    public function execute() {
        $limit = isset($_GET['limit']) ? $_GET['limit'] : "5";
        $cpage = isset($_GET['pageNo']) ? $_GET['pageNo'] : 1;
        $collection = $this->_customerCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->joinAttribute('shipping_firstname', 'customer_address/firstname', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_lastname', 'customer_address/lastname', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_company', 'customer_address/company', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_street', 'customer_address/street', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_postcode', 'customer_address/postcode', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_telephone', 'customer_address/telephone', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_city', 'customer_address/city', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_region', 'customer_address/region', 'default_shipping', null, 'left')
                ->joinAttribute('shipping_country_id', 'customer_address/country_id', 'default_shipping', null, 'left');

        $collection->setPageSize($limit);
        $lpages = $collection->getLastPageNumber();

        $collection->setCurPage($cpage);
        $customerData = array();
        $i = 0;
        // we iterate through the list of products to get attribute values
        foreach ($collection as $customer) {
            $customerArray = $customer->toArray();
            $customerData[$i]['identifiers']['email']=$customerArray['email'];
            $customerData[$i]['identifiers']['phone']=$customerArray['shipping_telephone'];
            $customerData[$i]['identifiers']['customer_id']=$customerArray['entity_id'];
            $customerData[$i]['properties']['update']['firstname']=$customerArray['firstname'];
            $customerData[$i]['properties']['update']['city']=$customerArray['shipping_city'];
            $customerData[$i]['properties']['update']['region']=$customerArray['shipping_region'];
            $customerData[$i]['properties']['update']['country']=$customerArray['shipping_country_id'];
            $customerData[$i]['properties']['update']['streeet']=$customerArray['shipping_street'];
            $customerData[$i]['properties']['update']['postcode']=$customerArray['shipping_postcode'];
            $customerData[$i]['properties']['update']['createdTime']= strtotime($customerArray['created_at']);
            $customerData[$i]['properties']['update']['company']=$customerArray['shipping_company'];
            $i++;
        }
        $send = isset($_GET['send']) ? $_GET['send'] : "1";
        if($send){
         self::sendData($customerData);
          $result=array("lastPage"=>$lpages,"cpage"=>$cpage,"responseCode"=>200);
        }else{
         $result=array("lastPage"=>$lpages,"cpage"=>$cpage,"responseCode"=>200,'data'=>$customerData);  
        }
       
        echo json_encode($result);
    }

}
