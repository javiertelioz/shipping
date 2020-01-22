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

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Model\Service\InvoiceService;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AutoInvoiceService
{
    /**
     * Store Config
     */
    const AUTO_GENERATE_INVOICE_PATH = 'carriers/envioskanguro/auto_generate_invoice';

    /** 
     * @var Logger $logger
     */
    protected $logger;

    /** 
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /**
     * @var InvoiceService $invoiceService
     */
    protected $invoiceService;

    /**
     * @var TransactionFactory $transactionFactory
     */
    protected $transactionFactory;

    /** 
     * @var QuotationService $quotationService
     */
    protected $quotationService;

    public function __construct(
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        InvoiceService $invoiceService,
        QuotationService $quotationService,
        TransactionFactory $transactionFactory
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceService = $invoiceService;
        $this->quotationService = $quotationService;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Make Invoice
     * 
     * @param Order $order
     */
    public function execute(Order $order)
    {
        $this->quotationService->authorizeQuotation($order);

        $autoInvoice = $this->scopeConfig->getValue(self::AUTO_GENERATE_INVOICE_PATH);

        if (!$autoInvoice) {
            return;
        }

        $this->logger->debug('Create Invoice');

        try {
            if (!$order->canInvoice()) {
                return;
            }

            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
            $invoice->register();

            $transaction = $this->transactionFactory->create()
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transaction->save();

            if ($order->getState() !== Order::STATE_PROCESSING) {
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus(Order::STATE_PROCESSING);
            }

            $order->addStatusHistoryComment('Auto Invoice (Envios Kanguro)', false)
                ->setIsCustomerNotified(false);

            $order->save();
        } catch (\Exception $e) {
            $this->logger->debug('Invoice Error: ' . __($e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
