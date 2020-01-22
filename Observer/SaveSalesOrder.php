<?php

/**
 * Envios Kanguro Shipping
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @author Javier Telio Z <jtelio118@gmail.com>
 */

namespace Envioskanguro\Shipping\Observer;

use Envioskanguro\Shipping\Plugin\Logger\Logger;
use Envioskanguro\Shipping\Model\Actions\OrderActions as Actions;
use Envioskanguro\Shipping\WebService\TrackingService;
use Envioskanguro\Shipping\WebService\QuotationService;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class SaveSalesOrder implements ObserverInterface
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
     * @var EventManager $eventManager
     */
    private $eventManager;

    /** 
     * @var ScopeConfig $scopeConfig
     */
    protected $scopeConfig;

    /** 
     * @var Actions $actions;
     */
    protected $actions;

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
        Actions $actions,
        EventManager $eventManager,
        ScopeConfigInterface $scopeConfig,
        TrackingService $trackingService,
        QuotationService $quotationService
    ) {
        $this->logger = $logger;
        $this->actions = $actions;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->trackingService = $trackingService;
        $this->quotationService = $quotationService;
    }

    /**
     * Execute Observer
     * Save the shipping method selected by the customer
     * 
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $shippingMethod = $order->getShippingMethod();

        if (strstr($shippingMethod, self::PREFIX_SHIPPING_CODE)) {
            $this->quotationService->setSelectedQuotation($order);

            $this->actions->execute($order);
        }

        return $this;
    }
}
