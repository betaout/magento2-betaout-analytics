<?php
namespace Betaout\Analytics\Controller\Export;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    protected $_orderCollectionFactory;
    public function __construct(Context $context,
                                PageFactory $pageFactory,
             \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory)
    { 
        $this->pageFactory = $pageFactory;
         $this->_orderCollectionFactory =$orderCollectionFactory;
        return parent::__construct($context);
    }

    public function execute()
    { 
        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }
    
    public function getOrderimport(){
        echo "getorder import";
        $orders =$this->_orderCollectionFactory->create()
         ->addFieldToSelect('*')
         ->setPageSize(10)
         ->setCurPage(1)
         ->addAttributeToSelect('entity_id');
  echo $count=$orders->Count();
    }
}