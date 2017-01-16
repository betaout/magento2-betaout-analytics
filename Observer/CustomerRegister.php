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
 * Observer for `customer_register'
 *
 */
class CustomerRegister implements ObserverInterface
{

    protected $_betaoutTracker;
    protected $_trackerHelper;
    protected $_dataHelper;
    protected $_checkoutSession;

    public function __construct(
        \Betaout\Analytics\Model\Tracker $betaoutTracker,
        \Betaout\Analytics\Helper\Tracker $trackerHelper,
        \Betaout\Analytics\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_betaoutTracker = $betaoutTracker;
        $this->_trackerHelper = $trackerHelper;
        $this->_dataHelper = $dataHelper;
        $this->_checkoutSession = $checkoutSession;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {  
        $data=array();
        $customer = $observer->getCustomer();
     
        $data['email']=$customer->getEmail();
        $data['customer_id']=$customer->getId();
        try {
        mail("rohit@getamplify.com", "git Customer Register",  json_encode($data));
        $ndata=  array_filter($data);
        $this->_betaoutTracker->identify($ndata);
        }catch (Exception $ex) {
        }
        return $this;
    }
}
