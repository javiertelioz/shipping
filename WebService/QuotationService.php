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

use Magento\Framework\App\Config\ScopeConfigInterface;

class QuotationService
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
     * Accept Quote purposes.
     * 
     * @param string $order
     */
    public function setSelectedQuotation($order)
    {
        $this->storage->updateQuote(
            $order->getQuoteId(),
            [
                'order' => $order->getIncrementId(),
                'shipping_code' => $this->getShippingCode($order->getShippingMethod())
            ]
        );
    }

    /**
     * Authorize the selected quote
     * 
     * @param string $order
     */
    public function authorizeQuotation($order)
    {
        $rate = $this->storage->getRateByCurrentQuote($order->getQuoteId());
        $selected = $this->getSelectRate($order->getShippingMethod(), $rate);

        $request = $this->api->put(
            'rate/' . $selected['quote_id'],
            ['rate_id' =>  $selected['rate_id']],
            []
        );

        if (isset($request['body']->data->tracking_number)) {
            $this->logger->debug(var_export($request['body']->data, true));

            $rate->addData([
                'tracking_number' => $request['body']->data->tracking_number
            ]);

            $rate->save();

            return $rate;
        }
    }

    /** 
     * Get Shipping Method
     */
    protected function getShippingCode($shippingMethod)
    {
        $selected = explode('_', $shippingMethod);
        return end($selected);
    }

    /** 
     * Get Select Rate
     */
    protected function getSelectRate($shippingMethod, $rateData)
    {
        $code = explode('_', $shippingMethod);
        $rates = unserialize($rateData->getContent());

        $index = array_search($code[0], array_column($rates, 'code'));

        return $rates[$index];
    }
}
