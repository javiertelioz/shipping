<?php

/**
 * Envios Kanguro Shipping
 *
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @author     Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\WebService;

use EnviosKanguro\Api;

use Envioskanguro\Shipping\WebService\RateRequest\Storage;

use Psr\Log\LoggerInterface;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Accept
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
     * @var Storage
     */
    protected $storage;

    public function __construct(
        Storage $storage,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
        $this->scopeConfig = $scopeConfig;

        $token = $this->scopeConfig->getValue('carriers/envioskanguro/token');
        $environment = $this->scopeConfig->getValue('carriers/envioskanguro/environment');

        $this->client = new Api($token, $environment);
    }

    /**
     * Accept Quote purposes.
     * 
     * @param string $shippingMethod
     */
    public function process($shippingMethod)
    {
        $rate = $this->getSelectRate($shippingMethod);

        $request = $this->client->put(
            'rate/' . $rate['quote_id'],
            ['rate_id' =>  $rate['rate_id']],
            []
        );

        if (isset($request['body']->data)) {
            $this->logger->debug(var_export($request['body'], true));
        }
    }

    /** 
     * Get Select Rate
     */
    protected function getSelectRate($shippingMethod)
    {
        $code = explode('_', $shippingMethod);

        $quote = $this->storage->getRateByCurrentQuote();
        $rates = unserialize($quote->getContent());

        $index = array_search($code[0], array_column($rates, 'code'));

        return $rates[$index];
    }
}
