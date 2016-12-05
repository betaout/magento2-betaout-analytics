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

namespace Betaout\Analytics\CustomerData\Checkout;

/**
 * Plugin for \Magento\Checkout\CustomerData\Cart
 *
 */
class CartPlugin
{

    /**
     * Checkout session instance
     *
     * @var \Magento\Checkout\Model\Session $_checkoutSession
     */
    protected $_checkoutSession;

    /**
     * Piwik data helper
     *
     * @var \Betaout\Analytics\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Tracker helper
     *
     * @var \Betaout\Analytics\Helper\Tracker $_trackerHelper
     */
    protected $_trackerHelper;

    /**
     * Tracker factory
     *
     * @var\Betaout\Analytics\Model\TrackerFactory $_trackerFactory
     */
    protected $_trackerFactory;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Betaout\Analytics\Helper\Data $dataHelper
     * @param \Betaout\Analytics\Helper\Tracker $trackerHelper
     * @param \Betaout\Analytics\Model\TrackerFactory $trackerFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Betaout\Analytics\Helper\Data $dataHelper,
        \Betaout\Analytics\Helper\Tracker $trackerHelper,
        \Betaout\Analytics\Model\TrackerFactory $trackerFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
        $this->_trackerHelper = $trackerHelper;
        $this->_trackerFactory = $trackerFactory;
    }

    /**
     * Add `trackEcommerceCartUpdate' checkout cart customer data
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
//        if ($this->_dataHelper->isTrackingEnabled()) {
//            $quote = $this->_checkoutSession->getQuote();
//            if ($quote->getId()) {
//                $tracker = $this->_trackerFactory->create();
//                $this->_trackerHelper->addQuote($quote, $tracker);
//                $result['betaoutActions'] = $tracker->toArray();
//            }
//        }
        return $result;
    }
}
