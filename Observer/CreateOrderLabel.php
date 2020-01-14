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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

class CreateOrderLabel implements ObserverInterface {
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
		QuotationService $quotationService,
		ObjectManagerInterface $objectManager
	) {
		$this->_objectManager = $objectManager;
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
	public function execute(Observer $observer) {
		$order = $observer->getOrder();
		$statuscode = $order->getStatus();

		$shippingMethod = $order->getShippingMethod();

		$configOrderStatus = $this->scopeConfig->getValue(self::STATUS_CONFIG_PATH);

		if (strstr($shippingMethod, self::PREFIX_SHIPPING_CODE)) {

			if ($statuscode === $configOrderStatus) {
				$this->logger->debug('Process Order: ' . $order->getId());

				$rate = $this->quotationService->authorizeQuotation($order);

				$this->logger->debug('Download Tracking: ' . $rate->getTrackingNumber());
				$this->trackingService->downloadTracking($rate->getTrackingNumber());

				// Check if order has already shipped or can be shipped
				if (!$order->canShip()) {
					throw new \Magento\Framework\Exception\LocalizedException(
						__('You can\'t create an shipment.')
					);
				}

				// Initialize the order shipment object
				$convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
				$shipment = $convertOrder->toShipment($order);

				// Loop through order items
				foreach ($order->getAllItems() AS $orderItem) {
					// Check if order item is virtual or has quantity to ship
					if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
						continue;
					}

					$qtyShipped = $orderItem->getQtyToShip();

					// Create shipment item with qty
					$shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

					// Add shipment item to shipment
					$shipment->addItem($shipmentItem);
				}

				// Register shipment
				$shipment->register();

				$data = [
					'carrier_code' => self::PREFIX_SHIPPING_CODE,
					'title' => 'Envios Kanguro',
					'number' => $rate->getTrackingNumber(),
				];

				$shipment->getOrder()->setIsInProcess(true);

				try {
					// Save created shipment and order
					$track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
					$shipment->addTrack($track)->save();
					$shipment->save();
					$shipment->getOrder()->save();

					// Send email
					$this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
						->notify($shipment);

					$shipment->save();
				} catch (\Exception $e) {
					throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
				}

			}
		}

		return $this;
	}
}
