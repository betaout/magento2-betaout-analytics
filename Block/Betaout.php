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

namespace Betaout\Analytics\Block;

/**
 * Betaout page block
 *
 */
class Betaout extends \Magento\Framework\View\Element\Template
{

    /**
     * JSON encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Piwik tracker model
     *
     * @var \Betaout\Analytics\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Betaout data helper
     *
     * @var \Betaout\Analytics\Helper\Data
     */
    protected $_dataHelper = null;
    protected $_registry;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Betaout\Analytics\Model\Tracker $tracker
     * @param \Betaout\Analytics\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Betaout\Analytics\Model\Tracker $tracker,
        \Betaout\Analytics\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_tracker = $tracker;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Betaoput tracker actions
     *
     * @return \Betaout\Analytics\Model\Tracker
     */
    public function getTracker()
    {
        return $this->_tracker;
    }

    /**
     * Populate tracker with actions before rendering
     *
     * @return void
     */
    protected function _prepareTracker()
    {
        $tracker = $this->getTracker();
    }

    /**
     * Get javascript tracker options
     *
     * @return array
     */
    public function getJsOptions()
    {
        return [
            'apiKey'     => $this->getApiKey(),
            'projectId'     => $this->getProjectId(),
            'actions'    => $this->getTracker()->toArray()
        ];
    }

    

   
    public function getProjectId()
    {
        return $this->_dataHelper->getProjectId();
    }
    public function getApiKey()
    {
        return $this->_dataHelper->getApiKey();
    }

    /**
     * Get tracking pixel URL
     *
     * @return string
     */
    public function getTrackingPixelUrl()
    {
        $params = array(
            'idsite' => $this->getSiteId(),
            'rec'    => 1,
            'url'    => $this->_urlBuilder->getCurrentUrl()
        );
        return $this->getTrackerUrl() . '?' . http_build_query($params);
    }

    /**
     * Encode data to a JSON string
     *
     * @param mixed $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Generate Betaout tracking script
     *
     * @return string
     */
    protected function _toHtml()
    {
       
            $this->_prepareTracker();
            return parent::_toHtml();
        
        //return '';
    }
    
    public function getCurrentProduct()
    {       
        return $this->_registry->registry('current_product');
    }  
}
