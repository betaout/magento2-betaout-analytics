<?php
namespace Betaout\Analytics\Controller\Export;
use Magento\Framework\App\Action\Context;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_dataHelper;
  
    public function __construct(Context $context,
            \Betaout\Analytics\Helper\Data $dataHelper,
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
            array $data = [])
    { 
        $this->_dataHelper = $dataHelper;
        $this->_statusCollectionFactory=$statusCollectionFactory;
        return parent::__construct($context);
    }

    public function execute()
    { 
     $sapiKey=$this->_dataHelper->getApiKey();
     $sprojectId=$this->_dataHelper->getProjectId();
     $options = $this->_statusCollectionFactory->create()->toOptionArray();        
     $result=array("apiKey"=>$sapiKey,
                   "projectId"=>$sprojectId,
                   "status"=>$options,
                   "responseCode"=>200);
     echo json_encode($result);
    }
    
    public function getOrderimport(){
        
    }
}