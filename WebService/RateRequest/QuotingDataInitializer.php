<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;

use Psr\Log\LoggerInterface;

use Envioskanguro\Shipping\WebService\Rate;
use Envioskanguro\Shipping\WebService\RateRequest\Extractor;

use Magento\Framework\Exception\LocalizedException;

class QuotingDataInitializer
{
    /**
     * @var Extractor
     */
    protected $rateRequestExtractor;

    /** 
     * @var Rate Service
     */
    protected $rateService;

    /** 
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * QuotingDataInitializer constructor.
     * @param Extractor $rateRequestExtractor
     * @param OrderInterfaceBuilder $orderBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        Rate $rateService,
        Extractor $rateRequestExtractor
    ) {
        $this->logger = $logger;
        $this->rateService = $rateService;
        $this->rateRequestExtractor = $rateRequestExtractor;
    }

    /**
     * Create a order for quoting purposes.
     *
     * The order is being built from the quote and rate request.
     * The order may include
     * - dynamic checkout fields,
     * - delivery location selected during checkout.
     *
     * @throws LocalizedException
     */
    public function getAvailableRates()
    {
        $quoting = $this->rateRequestExtractor->getQuotingData();

        return $this->rateService->getRates($quoting);
    }

}
