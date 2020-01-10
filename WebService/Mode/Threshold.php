<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\Mode;

use Psr\Log\LoggerInterface;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Threshold
{
    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var Session
     */
    protected $checkoutSession;

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
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
    }

    public function getRate($rates)
    {
        $rate = array_slice($rates, 0, 1);
        $total = (float) $this->checkoutSession->getQuote()->getGrandTotal();

        if ($total > $this->getFreeFromPrice()) {
            $rate[0]['custom_price'] = 0.0;
        } else {
            $rate[0]['custom_price'] = $this->getPrice();
        }

        return $rate;
    }

    protected function getPrice()
    {
        return (float) $this->scopeConfig->getValue('carriers/envioskanguro/price');
    }

    protected function getFreeFromPrice()
    {
        return (float) $this->scopeConfig->getValue('carriers/envioskanguro/free_from');
    }
}
