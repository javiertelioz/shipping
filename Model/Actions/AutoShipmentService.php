<?php

/**
 * Envios Kanguro Shipping
 *
 * @author     Javier Telio Z <jtelio118@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\Model\Actions;

use Envioskanguro\Shipping\Plugin\Logger\Logger;
use Envioskanguro\Shipping\WebService\QuotationService;
use Envioskanguro\Shipping\WebService\RateRequest\Storage;

use Magento\Sales\Model\Order;
use Magento\Shipping\Model\ShipmentNotifier;

use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Sales\Model\Convert\Order as OrderConverte;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Framework\Exception\LocalizedException;

class AutoShipmentService
{
    /**
     * Prefix Shipping Code
     */
    const PREFIX_SHIPPING_CODE = 'envioskanguro';

    /**
     * Store Config
     */
    const AUTO_GENERATE_SHIPMENT_PATH = 'carriers/envioskanguro/auto_generate_shipment';

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

    /**
     * @var OrderConverte $convertService
     */
    protected $converteService;

    /**
     * @var TrackFactory $trackFactory
     */
    protected $trackFactory;

    /**
     * @var ShipmentNotifier $shipmentNotifier
     */
    protected $shipmentNotifier;

    /** 
     * @var QuotationService $quotationService
     */
    protected $quotationService;

    public function __construct(
        Logger $logger,
        Storage $storage,
        ScopeConfigInterface $scopeConfig,
        TrackFactory $trackFactory,
        OrderConverte $converteService,
        ShipmentNotifier $shipmentNotifier,
        QuotationService $quotationService
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
        $this->scopeConfig = $scopeConfig;
        $this->trackFactory = $trackFactory;
        $this->converteService = $converteService;
        $this->quotationService = $quotationService;
        $this->shipmentNotifier = $shipmentNotifier;
    }

    /**
     * Make Shipment
     * 
     * @param Order $order
     * @return void
     */
    public function execute(Order $order)
    {
        $this->quotationService->authorizeQuotation($order);

        $autoShipment = $this->scopeConfig->getValue(self::AUTO_GENERATE_SHIPMENT_PATH);

        if (!$autoShipment) {
            return;
        }

        if (empty($this->getTrackingNumber($order))) {
            return;
        }

        $this->logger->debug('Create Shipment');

        try {
            if (!$order->canShip()) {
                return;
            }
            $shipment = $this->converteService->toShipment($order);

            foreach ($order->getAllItems() as $item) {

                if (!$item->getQtyToShip() || $item->getIsVirtual()) {
                    continue;
                }

                $qtyShipped = $item->getQtyToShip();

                $shipmentItem = $this->converteService
                    ->itemToShipmentItem($item)
                    ->setQty($qtyShipped);

                $shipment->addItem($shipmentItem);
            }

            $shipment->register();

            $data = [
                'title' => 'Envios Kanguro',
                'carrier_code' => self::PREFIX_SHIPPING_CODE,
                'number' => $this->getTrackingNumber($order),
            ];

            $shipment->getOrder()->setIsInProcess(true);

            $track = $this->trackFactory->create()
                ->addData($data);

            $shipment->addTrack($track)
                ->save();

            $shipment->save();

            $shipment->getOrder()->save();

            $this->shipmentNotifier->notify($shipment);

            $shipment->save();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Get Tracking Number
     * 
     * @param Order $order
     * @return String $tracking_number
     */
    protected function getTrackingNumber(Order $order)
    {
        $rate = $this->storage->getRateByIncrementId($order->getIncrementId());

        return $rate->getTrackingNumber();
    }
}
