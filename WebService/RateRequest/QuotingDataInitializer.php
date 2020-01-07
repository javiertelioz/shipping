<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;


use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use Envioskanguro\Shipping\WebService\Rate;

use Envioskanguro\Shipping\WebService\RateRequest\Extractor;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Exception\LocalizedException;

// use Temando\Shipping\Model\OrderInterfaceBuilder;

class QuotingDataInitializer
{
    /**
     * @var Extractor
     */
    private $rateRequestExtractor;

    /** 
     * @var Rate Service
     */
    private $rateService;

    /** 
     * Log
     */
    protected $_logger;

    /**
     * QuotingDataInitializer constructor.
     * @param Extractor $rateRequestExtractor
     * @param OrderInterfaceBuilder $orderBuilder
     */
    public function __construct(
        Rate $rateService,
        LoggerInterface $logger,
        Extractor $rateRequestExtractor
    ) {
        $this->rateService = $rateService;
        $this->rateRequestExtractor = $rateRequestExtractor;
        $this->_logger = $logger;
    }

    /**
     * Create a order for quoting purposes.
     *
     * The order is being built from the quote and rate request.
     * The order may include
     * - dynamic checkout fields,
     * - delivery location selected during checkout.
     *
     * @param RateRequest $rateRequest
     * @throws LocalizedException
     */
    public function getAvailableRates(RateRequest $rateRequest)
    {
        $quoting = $this->rateRequestExtractor->getQuotingData($rateRequest);
        
        return $this->rateService->getRates($quoting);

    }
}
