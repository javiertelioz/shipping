<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Observer;

use Envioskanguro\Shipping\WebService\Accept;


use Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveSalesOrder implements ObserverInterface
{
    /** 
     * Prefix Shipping Code
     */
    const PREFIX_SHIPPING_CODE = 'envioskanguro';
    /** 
     * @var Logger
     */
    protected $logger;

    /** 
     * @var Accept
     */
    protected $accept;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        Accept $accept,
        LoggerInterface $logger
    ) {
        $this->accept = $accept;
        $this->logger = $logger;
    }

    /**
     * Execute Observer
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $shippingMethod = $order->getShippingMethod();

        if (strstr($shippingMethod, self::PREFIX_SHIPPING_CODE)) {
            $this->accept->process($shippingMethod);
        }

        return $this;
    }
}
