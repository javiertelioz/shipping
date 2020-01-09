<?php
namespace Envioskanguro\Shipping\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderSaveAfterObserver implements ObserverInterface {

	protected $_logger;

	public function __construct(
		\Psr\Log\LoggerInterface $logger
		/*\Magento\Framework\Registry $registry*/
	) {
		$this->_logger = $logger;
		//$this->registry = $registry;
		// Observer initialization code...
		// You can use dependency injection to get any class this observer may need.
	}

	public function execute(\Magento\Framework\Event\Observer $observer) {
		$order = $observer->getOrder();
		$method = $order->getShippingMethod(); //envioskanguro_standar_223
		/*$rates = $this->_registry->registry('Rates');
	        $data = $rates['body']->data;
	        foreach ($$data as $key => $rate) {
	        	# code...
				$this->_logger->info(' |->>> '.$rate, true);
*/

		// Additional observer execution code...
		return $this;
	}
}