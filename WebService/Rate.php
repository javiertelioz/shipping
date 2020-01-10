<?php

/**
 * Envios Kanguro Shipping
 *
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @author     Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\WebService;

use EnviosKanguro\Api;

use Envioskanguro\Shipping\WebService\Mode;
use Envioskanguro\Shipping\WebService\RateRequest\Storage;
use Envioskanguro\Shipping\WebService\Mapping\RateInterface;

use Psr\Log\LoggerInterface;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Rate implements RateInterface
{
    /**
     * @var Api
     */
    protected $client;

    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /** 
     * @var Mode
     */
    protected $mode;

    /** 
     * @var Storage
     */
    protected $storage;

    public function __construct(
        Mode $mode,
        Storage $storage,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->mode = $mode;
        $this->logger = $logger;
        $this->storage = $storage;
        $this->scopeConfig = $scopeConfig;

        $token = $this->scopeConfig->getValue('carriers/envioskanguro/token');
        $environment = $this->scopeConfig->getValue('carriers/envioskanguro/environment');

        $this->client = new Api($token, $environment);
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
        $rates = $this->client->post('rate', $quotingData);
        
        if (isset($rates['body']->data)) {

            $quoteId = $rates['body']->data->id;
            $mapping = $this->mapping($rates['body']->data);

            $this->storage->setRates($quoteId, $mapping);

            return $mapping;
        }

        return [];
    }

    /**
     * Generate Code
     * 
     * @return string code
     */
    protected function generateCode($name)
    {
        return strtolower(strtok($name, " "));
    }

    /** 
     * Mapping Request
     * 
     * @return array
     */
    protected function mapping($rates)
    {
        $available = [];

        foreach ($rates->rates as $rate) {
            $available[] = [
                'code'      => $this->generateCode($rate->name),
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

        return $this->mode->getMethods($available);
    }
}
