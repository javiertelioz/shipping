<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Observer;

use Envioskanguro\Shipping\Plugin\Logger\Logger;
use Envioskanguro\Shipping\WebService\QuotationService;
use Envioskanguro\Shipping\WebService\TrackingService;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CreateOrderLabel implements ObserverInterface
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
     * @var Logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /**
     * @var TrackingService $trackingService
     */
    protected $trackingService;

    /** 
     * @var QuotationService $quotationService
     */
    protected $quotationService;

    public function __construct(
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        TrackingService $trackingService,
        QuotationService $quotationService
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->trackingService = $trackingService;
        $this->quotationService = $quotationService;
    }

    /**
     * Execute Observer
     * 
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $statuscode = $order->getStatus();

        $shippingMethod = $order->getShippingMethod();

        $configOrderStatus = $this->scopeConfig->getValue(self::STATUS_CONFIG_PATH);

        if (strstr($shippingMethod, self::PREFIX_SHIPPING_CODE)) {

            if ($statuscode === $configOrderStatus) {
                $this->logger->debug('Process Order: ' .$order->getId());
                
                $rate = $this->quotationService->authorizeQuotation($order);

                $this->logger->debug('Download Tracking: ' . $rate->getTrackingNumber());
                $this->trackingService->downloadTracking($rate->getTrackingNumber());
            }
        }

        return $this;
    }
}
