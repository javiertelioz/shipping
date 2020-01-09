<?php
namespace Envioskanguro\Shipping\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderSaveAfterObserver implements ObserverInterface {

	protected $_logger;

	public function __construct(
		\Psr\Log\LoggerInterface $logger
	) {
		$this->_logger = $logger;
		// Observer initialization code...
		// You can use dependency injection to get any class this observer may need.
	}

	public function execute(\Magento\Framework\Event\Observer $observer) {
		$order = $observer->getOrder();
		$method = $order->getShippingMethod(); //envioskanguro_standar_223
		$this->_logger->info(' |->>> ', explode("_", $method));
		// Additional observer execution code...
		return $this;
	}
}