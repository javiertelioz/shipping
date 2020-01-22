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

use Magento\Sales\Model\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;

class QuotationService
{
    /** 
     * Prefix Shipping Code
     */
    const PREFIX_SHIPPING_CODE = 'envioskanguro';

    /**
     * Status Config
     */
    const STATUS_CONFIG_PATH = 'carriers/envioskanguro/order_status_planned_to_ship';

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
     * @param Order $order
     * @return void
     */
    public function setSelectedQuotation(Order $order)
    {
        $data = [
            'order' => $order->getIncrementId(),
            'shipping_code' => $this->getShippingCode($order->getShippingMethod())
        ];

        $this->storage->updateQuote(
            $order->getQuoteId(),
            $data
        );
    }

    /**
     * Authorize the selected quote
     * 
     * @param Order $order
     * @return mixed 
     */
    public function authorizeQuotation($order)
    {
        $statuscode = $order->getStatus();
        $shippingMethod = $order->getShippingMethod();
        $configOrderStatus = $this->scopeConfig->getValue(self::STATUS_CONFIG_PATH);

        if (strstr($shippingMethod, self::PREFIX_SHIPPING_CODE)) {

            if ($statuscode === $configOrderStatus) {
                $rate = $this->storage->getRateByCurrentQuote($order->getQuoteId());
                $selected = $this->getSelectRate($shippingMethod, $rate);

                if (!is_null($rate->getTrackingNumber())) {
                    return $rate;
                }

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
        }

        return;
    }

    /** 
     * Get Shipping Method
     * 
     * @param string $shippingMethod
     * @return string 
     */
    protected function getShippingCode($shippingMethod)
    {
        $selected = explode('_', $shippingMethod);
        return end($selected);
    }

    /** 
     * Get customer selected rate
     * 
     * @param string $shippingMethod
     * @param Object $rate
     * @return array
     */
    protected function getSelectRate($shippingMethod, $rate)
    {
        $code = explode('_', $shippingMethod);
        $rates = unserialize($rate->getContent());

        $index = array_search($code[0], array_column($rates, 'code'));

        return $rates[$index];
    }
}
