<?php

/**
 * Envios Kanguro Shipping
 *
 * @author Javier Telio Z <jtelio118@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Envioskanguro\Shipping\WebService\RateRequest;

use Magento\Checkout\Model\Session;

use Envioskanguro\Shipping\Model\RateFactory;
use Envioskanguro\Shipping\Plugin\Logger\Logger;

class Storage
{
    /** 
     * @var Logger $logger
     */
    protected $logger;

    /** 
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var RateFactory $rateFactory
     */
    protected $rateFactory;

    public function __construct(
        Logger $logger,
        Session $checkoutSession,
        RateFactory $rateFactory
    ) {
        $this->logger = $logger;
        $this->rateFactory = $rateFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Setting Quote in database to be used
     * @param integer $quoteId
     * @param array $rates
     */
    public function setRates($quoteId, $rates)
    {
        $sessionId = $this->checkoutSession->getQuote()->getId();
        $flag = $this->getRateByCurrentQuote($sessionId)->getId();

        $model = $this->rateFactory->create();
        $rateData = [
            "content" => serialize($rates),
            "quote_id" => $quoteId,
            "session_id" => $sessionId,
        ];

        if ($flag === null) {
            $model->addData($rateData);
            $model->save();
        } else {
            $model->load($flag);
            $model->addData($rateData);
            $model->save();
        }
    }

    /**
     * Retrieving Quote from database by Current Quote
     * @return mixed
     */
    public function getRateByCurrentQuote($quoteId)
    {
        $model = $this->rateFactory->create();

        return $model->load($quoteId, 'session_id');
    }

    /**
     * Retrieving Quote from database by tracking number
     * @return mixed
     */
    public function getRateByTrackingNumber($trackingNumber)
    {
        $model = $this->rateFactory->create();

        return $model->load($trackingNumber, 'tracking_number');
    }

    /**
     * Retrieving Quote from database by Increment ID
     * @return mixed
     */
    public function getRateByIncrementId($incrementId)
    {
        $model = $this->rateFactory->create();

        return $model->load($incrementId, 'order');
    }

    /**
     * Update Quote from database
     * 
     * @param integer $orderId
     * @param array $quote
     * 
     * @return void
     */
    public function updateQuote($orderId, $quote)
    {
        $model = $this->rateFactory->create();

        $model->load($orderId, 'session_id');
        $model->addData($quote);
        $model->save();
    }
}
