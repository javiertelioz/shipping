<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Model\Actions;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Envioskanguro\Shipping\Model\RateFactory;
use Envioskanguro\Shipping\Plugin\Logger\Logger;
use Envioskanguro\Shipping\WebService\TrackingService;
use Envioskanguro\Shipping\WebService\QuotationService;
use Envioskanguro\Shipping\WebService\RateRequest\Storage;
use Envioskanguro\Shipping\Model\Actions\AutoInvoiceService;
use Envioskanguro\Shipping\Model\Actions\AutoShipmentService;

class OrderActions
{
    /** 
     * Prefix Shipping Code
     */
    const PREFIX_SHIPPING_CODE = 'envioskanguro';

    /** 
     * @var Logger $logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /** 
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /** 
     * @var Storage $storage
     */
    protected $storage;

    /**
     * @var RateFactory $rateFactory
     */
    protected $rateFactory;

    /**
     * @var TrackingService $trackingService
     */
    protected $trackingService;

    /** 
     * @var QuotationService $quotationService
     */
    protected $quotationService;

    /**
     * @var AutoInvoiceService $autoInvoiceService
     */
    protected $autoInvoiceService;

    /**
     * @var AutoShipmentService $autoShipmentService
     */
    protected $autoShipmentService;

    public function __construct(
        Logger $logger,
        Storage $storage,
        RateFactory $rateFactory,
        OrderFactory $orderFactory,
        TrackingService $trackingService,
        ScopeConfigInterface $scopeConfig,
        QuotationService $quotationService,
        AutoInvoiceService $autoInvoiceService,
        AutoShipmentService $autoShipmentService
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
        $this->rateFactory = $rateFactory;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->trackingService = $trackingService;
        $this->quotationService = $quotationService;
        $this->autoInvoiceService = $autoInvoiceService;
        $this->autoShipmentService = $autoShipmentService;
    }

    /**
     * Execute Auto Invoice and Shipment
     * If is enable in Magento config
     * 
     * @param Order $order
     */
    public function execute(Order $order)
    {
        $shippingMethod = $order->getShippingMethod();

        if (strstr($shippingMethod, self::PREFIX_SHIPPING_CODE)) {

            try {
                $this->autoInvoiceService->execute($order);
                $this->autoShipmentService->execute($order);

            } catch (\Throwable $e) {
                $this->logger->debug('Auto Actions Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Load and Execute Actions
     */
    public function executeByTrackingNumber($trackingNumber)
    {
        $rate = $this->storage->getRateByTrackingNumber($trackingNumber);

        $this->logger->debug($rate->getOrder());

        try {
            $order = $this->orderFactory->create()->loadByIncrementId($rate->getOrder());
            $this->execute($order);

        } catch (\Throwable $e) {
            $this->logger->debug('Error: Order not found: ' . $rate->getOrder());
        }
    }
}
