<?php

/**
 * Envios Kanguro Shipping
 *
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @author     Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\WebService;


use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use EnviosKanguro\Api;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Rate
{
    /**
     * Client
     */
    protected $_client;

    /** 
     * Log
     */
    protected $_logger;
    
    /** 
     * Config
     */
    protected $_scopeConfig;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {

        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;

        $token = $this->_scopeConfig->getValue('carriers/envioskanguro/token');
        $this->_client = new Api($token, 'development');
    }

    /**
     * Create a order for quoting purposes.
     *
     * The order is being built from the quote and rate request.
     * 
     * @param RateRequest $rateRequest
     * 
     */
    public function getRates($quotingData)
    {
        $rates = $this->_client->post('rate', $quotingData);
        
        $this->_logger->debug(var_export($rates['body']->data, true));

        if (isset($rates['body']->data)) {
            return $rates['body']->data;
        }

        return [];
    }
}
