<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService;

use Envioskanguro\Shipping\WebService\Api\Api;
use Envioskanguro\Shipping\Plugin\Logger\Logger;
use Envioskanguro\Shipping\WebService\RateRequest\Storage;
use Envioskanguro\Shipping\WebService\Mapping\RateInterface;

use Magento\Framework\App\Config\ScopeConfigInterface;

class RateService implements RateInterface
{
    /**
     * @var Api $api
     */
    protected $api;

    /** 
     * @var Logger $logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig $scopeConfig
     */
    protected $scopeConfig;

    /** 
     * @var Storage $storage
     */
    protected $storage;

    public function __construct(
        Api $api,
        Logger $logger,
        Storage $storage,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->api = $api;
        $this->logger = $logger;
        $this->storage = $storage;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Create a order for quoting purposes.
     *
     * The order is being built from the quote and rate request.
     * 
     * @param $quotingData
     */
    public function getRates($quotingData): array
    {
        $rates = $this->api->post('rate', $quotingData);

        if (isset($rates['body']->data)) {

            $quoteId = $rates['body']->data->id;
            $rates = $this->mappingRequest($rates['body']->data);

            $this->storage->setRates($quoteId, $rates);

            return $rates;
        }

        return [];
    }

    /** 
     * Mapping Request
     * 
     * @return array
     */
    protected function mappingRequest($rates)
    {
        $available = [];

        foreach ($rates->rates as $rate) {
            $available[] = [
                'code'      => strtolower(strtok($rate->name, " ")),
                'name'      => $rate->name,
                'best'      => $rate->best,
                'rate_id'   => $rate->id,
                'quote_id'  => $rates->id,
                'ranking'   => $rate->ranking,
                'custom_price' => null,
                'original_price' => $rate->total_price,
                'created_at' => $rates->created_at,
            ];
        }

        $this->logger->debug('Shipping Rates: ' . var_export($available, true));

        return $available;
    }
}
