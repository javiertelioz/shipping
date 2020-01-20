<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;

use Envioskanguro\Shipping\Model\Mode;
use Envioskanguro\Shipping\WebService\RateService;
use Envioskanguro\Shipping\WebService\RateRequest\Extractor;

use Magento\Framework\Exception\LocalizedException;

class QuotingDataInitializer
{
    /**
     * @var Mode $modality
     */
    protected $modality;

    /**
     * @var Extractor
     */
    protected $rateRequestExtractor;

    /** 
     * @var RateService $rateService
     */
    protected $rateService;

    /**
     * QuotingDataInitializer constructor.
     * 
     * @param Mode $modality
     * @param Extractor $rateRequestExtractor
     * @param OrderInterfaceBuilder $orderBuilder
     */
    public function __construct(
        Mode $modality,
        RateService $rateService,
        Extractor $rateRequestExtractor
    ) {
        $this->modality = $modality;
        $this->rateService = $rateService;
        $this->rateRequestExtractor = $rateRequestExtractor;
    }

    /**
     * Create a order for quoting purposes.
     *
     * The order is being built from the quote and rate request.
     * The order may include
     * 
     * - delivery location selected during checkout.
     *
     * @throws LocalizedException
     */
    public function getAvailableRates()
    {
        $quoting = $this->rateRequestExtractor->getQuotingData();

        if (!empty($quoting)) {
            $rates = $this->rateService->getRates($quoting);

            return $this->modality->getShippingMethods($rates);
        }
        
        return [];
    }
}
