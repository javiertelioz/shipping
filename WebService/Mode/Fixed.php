<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\Mode;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Fixed
{
    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /**
     * QuotingDataInitializer constructor.
     * @param Extractor $rateRequestExtractor
     * @param OrderInterfaceBuilder $orderBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function getRate($rates)
    {
        $rate = array_slice($rates, 0, 1);
        $rate[0]['custom_price'] = (float) $this->getPrice();

        return $rate;
    }

    protected function getPrice()
    {
        return $this->scopeConfig->getValue('carriers/envioskanguro/price');
    }
}
